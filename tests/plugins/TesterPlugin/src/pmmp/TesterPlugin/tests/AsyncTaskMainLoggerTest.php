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

namespace pmmp\TesterPlugin\tests;

use pmmp\TesterPlugin\Test;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\MainLogger;

class AsyncTaskMainLoggerTest extends Test{

	public function run(){
		$this->getPlugin()->getServer()->getAsyncPool()->submitTask(new class($this) extends AsyncTask{

			/** @var bool */
			protected $success = false;

			public function __construct(AsyncTaskMainLoggerTest $testObject){
				$this->storeLocal($testObject);
			}

			public function onRun(){
				ob_start();
				MainLogger::getLogger()->info("Testing");
				if(strpos(ob_get_contents(), "Testing") !== false){
					$this->success = true;
				}
				ob_end_flush();
			}

			public function onCompletion(Server $server){
				/** @var AsyncTaskMainLoggerTest $test */
				$test = $this->fetchLocal();
				$test->setResult($this->success ? Test::RESULT_OK : Test::RESULT_FAILED);
			}
		});
	}

	public function getName() : string{
		return "MainLogger::getLogger() works in AsyncTasks";
	}

	public function getDescription() : string{
		return "Verifies that the MainLogger is accessible by MainLogger::getLogger() in an AsyncTask";
	}


}
