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
use pocketmine\utils\MainLogger;
use pocketmine\utils\Terminal;
use SOFe\Pathetique\Path;
use function define;
use function dirname;
use function microtime;
use function sys_get_temp_dir;
use function tempnam;
use function usleep;

class AsyncPoolTest extends TestCase{

	/** @var AsyncPool */
	private $pool;
	/** @var MainLogger */
	private $mainLogger;

	public function setUp() : void{
		Terminal::init();
		$this->mainLogger = new MainLogger(Path::new(tempnam(sys_get_temp_dir(), "pmlog")));
		$this->pool = new AsyncPool(2, 1024, new \BaseClassLoader(), $this->mainLogger);
	}

	public function tearDown() : void{
		$this->pool->shutdown();
		$this->mainLogger->shutdown();
		$this->mainLogger->join();
	}

	public function testTaskLeak() : void{
		$start = microtime(true);
		$this->pool->submitTask(new LeakTestAsyncTask());
		while(!LeakTestAsyncTask::$destroyed and microtime(true) < $start + 30){
			usleep(50 * 1000);
			$this->pool->collectTasks();
		}
		self::assertTrue(LeakTestAsyncTask::$destroyed, "Task was not destroyed after 30 seconds");
	}

	public function testPublishProgressRace() : void{
		$task = new PublishProgressRaceAsyncTask();
		$this->pool->submitTask($task);
		while($this->pool->collectTasks()){
			usleep(50 * 1000);
		}
		self::assertTrue(PublishProgressRaceAsyncTask::$success, "Progress was not reported before task completion");
	}
}
