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
use pmmp\thread\ThreadSafeArray;
use pocketmine\promise\PromiseResolver;
use pocketmine\snooze\SleeperHandler;
use pocketmine\thread\ThreadSafeClassLoader;
use pocketmine\utils\MainLogger;
use function define;
use function dirname;
use function microtime;
use function usleep;

class AsyncPoolTest extends TestCase{

	/** @var AsyncPool */
	private $pool;
	/** @var MainLogger */
	private $mainLogger;

	public function setUp() : void{
		@define('pocketmine\\COMPOSER_AUTOLOADER_PATH', dirname(__DIR__, 3) . '/vendor/autoload.php');
		$this->mainLogger = new MainLogger(null, false, "Main", new \DateTimeZone('UTC'));
		$this->pool = new AsyncPool(2, 1024, new ThreadSafeClassLoader(), $this->mainLogger, new SleeperHandler());
	}

	public function tearDown() : void{
		$this->pool->shutdown();
	}

	public function testTaskLeak() : void{
		$start = microtime(true);
		$this->pool->submitTask(new LeakTestAsyncTask());
		while(!LeakTestAsyncTask::$destroyed && microtime(true) < $start + 30){
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

	public function testThreadSafeSetResult() : void{
		$resolver = new PromiseResolver();
		$resolver->getPromise()->onCompletion(
			function(ThreadSafeArray $result) : void{
				self::assertCount(1, $result);
				self::assertSame(["foo"], (array) $result);
			},
			function() : void{
				self::fail("Promise failed");
			}
		);
		$this->pool->submitTask(new ThreadSafeResultAsyncTask($resolver));
		while($this->pool->collectTasks()){
			usleep(50 * 1000);
		}
	}

	/**
	 * This test ensures that the fix for an exotic AsyncTask::__destruct() reentrancy bug has not regressed.
	 *
	 * Due to an unset() in the function body, other AsyncTask::__destruct() calls could be triggered during
	 * an AsyncTask's destruction. If done in the wrong way, this could lead to a crash.
	 *
	 * @doesNotPerformAssertions This test is checking for a crash condition, not a specific output.
	 */
	public function testTaskDestructorReentrancy() : void{
		$this->pool->submitTask(new class extends AsyncTask{
			public function __construct(){
				$this->storeLocal("task", new class extends AsyncTask{

					public function __construct(){
						$this->storeLocal("dummy", 1);
					}

					public function onRun() : void{
						//dummy
					}
				});
			}

			public function onRun() : void{
				//dummy
			}
		});
		while($this->pool->collectTasks()){
			usleep(50 * 1000);
		}
	}

	public function testNullComplexDataFetch() : void{
		$this->pool->submitTask(new class extends AsyncTask{
			public function __construct(){
				$this->storeLocal("null", null);
			}

			public function onRun() : void{
				//dummy
			}

			public function onCompletion() : void{
				AsyncPoolTest::assertNull($this->fetchLocal("null"));
			}
		});
		while($this->pool->collectTasks()){
			usleep(50 * 1000);
		}
	}
}
