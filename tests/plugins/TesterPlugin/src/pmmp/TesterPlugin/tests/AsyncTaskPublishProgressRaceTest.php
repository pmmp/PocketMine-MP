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

class AsyncTaskPublishProgressRaceTest extends Test{

	public function getName() : string{
		return "Verify progress updates work as expected when finishing task";
	}

	public function getDescription() : string{
		return "Progress updates would be lost when finishing a task before its remaining progress updates were detected.";
	}

	public function run() : void{
		//this test is racy, but it should fail often enough to be a pest if something is broken

		$this->getPlugin()->getServer()->getAsyncPool()->submitTask(new class($this) extends AsyncTask{
			private const TLS_KEY_TEST = "test";

			private static $success = false;

			public function __construct(AsyncTaskPublishProgressRaceTest $t){
				$this->storeLocal(self::TLS_KEY_TEST, $t);
			}

			public function onRun() : void{
				$this->publishProgress("hello");
			}

			public function onProgressUpdate($progress) : void{
				if($progress === "hello"){
					// thread local on main thread
					self::$success = true;
				}
			}

			public function onCompletion() : void{
				/** @var AsyncTaskPublishProgressRaceTest $t */
				$t = $this->fetchLocal(self::TLS_KEY_TEST);
				$t->setResult(self::$success ? Test::RESULT_OK : Test::RESULT_FAILED);
			}
		});
	}
}
