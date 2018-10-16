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

class AsyncTaskMemoryLeakTest extends Test{

	public function run(){
		$this->getPlugin()->getServer()->getAsyncPool()->submitTask(new TestAsyncTask());
	}

	public function tick(){
		if(TestAsyncTask::$destroyed === true){
			$this->setResult(Test::RESULT_OK);
		}
	}

	public function getName() : string{
		return "AsyncTask memory leak after completion";
	}

	public function getDescription() : string{
		return "Regression test for AsyncTasks objects not being destroyed after completion";
	}
}

class TestAsyncTask extends AsyncTask{
	public static $destroyed = false;

	public function onRun() : void{
		usleep(50 * 1000); //1 server tick
	}

	protected function reallyDestruct() : void{
		self::$destroyed = true;
	}
}
