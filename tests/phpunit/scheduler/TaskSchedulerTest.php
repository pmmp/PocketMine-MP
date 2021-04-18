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

namespace pocketmine\scheduler;

use PHPUnit\Framework\TestCase;

class TaskSchedulerTest extends TestCase{

	/** @var TaskScheduler */
	private $scheduler;

	public function setUp() : void{
		$this->scheduler = new TaskScheduler();
	}

	public function tearDown() : void{
		$this->scheduler->shutdown();
	}

	public function testCancel() : void{
		$task = $this->scheduler->scheduleTask(new CancelTask());
		$cancelled = false;
		try{
			$task->run();
		}catch(CancelTaskException $e){
			$cancelled = true;
		}
		self::assertTrue($cancelled, "Task was not cancelled");
	}
}
