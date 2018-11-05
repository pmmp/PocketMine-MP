<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pmmp\TesterPlugin;

use pocketmine\event\Listener;
use pocketmine\event\server\CommandEvent;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{

	/** @var Test[] */
	protected $waitingTests = [];
	/** @var Test|null */
	protected $currentTest = null;
	/** @var Test[] */
	protected $completedTests = [];
	/** @var int */
	protected $currentTestNumber = 0;

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new CheckTestCompletionTask($this), 10);

		$this->waitingTests = [
			new tests\AsyncTaskMemoryLeakTest($this),
			new tests\AsyncTaskPublishProgressRaceTest($this)
		];
	}

	public function onServerCommand(CommandEvent $event){
		//The CI will send this command as a failsafe to prevent the build from hanging if the tester plugin failed to
		//run. However, if the plugin loaded successfully we don't want to allow this to stop the server as there may
		//be asynchronous tests running. Instead we cancel this and stop the server of our own accord once all tests
		//have completed.
		if($event->getCommand() === "stop"){
			$event->setCancelled();
		}
	}

	/**
	 * @return Test|null
	 */
	public function getCurrentTest(){
		return $this->currentTest;
	}

	public function startNextTest() : bool{
		$this->currentTest = array_shift($this->waitingTests);
		if($this->currentTest !== null){
			$this->getLogger()->notice("Running test #" . (++$this->currentTestNumber) . " (" . $this->currentTest->getName() . ")");
			$this->currentTest->start();
			return true;
		}

		return false;
	}

	public function onTestCompleted(Test $test){
		$message = "Finished test #" . $this->currentTestNumber . " (" . $test->getName() . "): ";
		switch($test->getResult()){
			case Test::RESULT_OK:
				$message .= "PASS";
				break;
			case Test::RESULT_FAILED:
				$message .= "FAIL";
				break;
			case Test::RESULT_ERROR:
				$message .= "ERROR";
				break;
			case Test::RESULT_WAITING:
				$message .= "TIMEOUT";
				break;
			default:
				$message .= "UNKNOWN";
				break;
		}

		$this->getLogger()->notice($message);

		$this->completedTests[$this->currentTestNumber] = $test;
		$this->currentTest = null;
	}

	public function onAllTestsCompleted(){
		$this->getLogger()->notice("All tests finished, stopping the server");
		$this->getServer()->shutdown();
	}
}
