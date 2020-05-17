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

namespace pocketmine\timings;

use pocketmine\entity\Living;
use pocketmine\Server;
use function count;
use function fwrite;
use function microtime;
use function round;
use function spl_object_hash;
use const PHP_EOL;

class TimingsHandler{

	/** @var TimingsHandler[] */
	private static $HANDLERS = [];
	/** @var bool */
	private static $enabled = false;
	/** @var float */
	private static $timingStart = 0;

	/**
	 * @param resource $fp
	 *
	 * @return void
	 */
	public static function printTimings($fp){
		fwrite($fp, "Minecraft" . PHP_EOL);

		foreach(self::$HANDLERS as $timings){
			$time = $timings->totalTime;
			$count = $timings->count;
			if($count === 0){
				continue;
			}

			$avg = $time / $count;

			fwrite($fp, "    " . $timings->name . " Time: " . round($time * 1000000000) . " Count: " . $count . " Avg: " . round($avg * 1000000000) . " Violations: " . $timings->violations . PHP_EOL);
		}

		fwrite($fp, "# Version " . Server::getInstance()->getVersion() . PHP_EOL);
		fwrite($fp, "# " . Server::getInstance()->getName() . " " . Server::getInstance()->getPocketMineVersion() . PHP_EOL);

		$entities = 0;
		$livingEntities = 0;
		foreach(Server::getInstance()->getLevels() as $level){
			$entities += count($level->getEntities());
			foreach($level->getEntities() as $e){
				if($e instanceof Living){
					++$livingEntities;
				}
			}
		}

		fwrite($fp, "# Entities " . $entities . PHP_EOL);
		fwrite($fp, "# LivingEntities " . $livingEntities . PHP_EOL);

		$sampleTime = microtime(true) - self::$timingStart;
		fwrite($fp, "Sample time " . round($sampleTime * 1000000000) . " (" . $sampleTime . "s)" . PHP_EOL);
	}

	public static function isEnabled() : bool{
		return self::$enabled;
	}

	public static function setEnabled(bool $enable = true) : void{
		self::$enabled = $enable;
		self::reload();
	}

	public static function getStartTime() : float{
		return self::$timingStart;
	}

	/**
	 * @return void
	 */
	public static function reload(){
		if(self::$enabled){
			foreach(self::$HANDLERS as $timings){
				$timings->reset();
			}
			self::$timingStart = microtime(true);
		}
	}

	/**
	 * @return void
	 */
	public static function tick(bool $measure = true){
		if(self::$enabled){
			if($measure){
				foreach(self::$HANDLERS as $timings){
					if($timings->curTickTotal > 0.05){
						$timings->violations += (int) round($timings->curTickTotal / 0.05);
					}
					$timings->curTickTotal = 0;
					$timings->curCount = 0;
					$timings->timingDepth = 0;
				}
			}else{
				foreach(self::$HANDLERS as $timings){
					$timings->totalTime -= $timings->curTickTotal;
					$timings->count -= $timings->curCount;

					$timings->curTickTotal = 0;
					$timings->curCount = 0;
					$timings->timingDepth = 0;
				}
			}
		}
	}

	/** @var string */
	private $name;
	/** @var TimingsHandler|null */
	private $parent = null;

	/** @var int */
	private $count = 0;
	/** @var int */
	private $curCount = 0;
	/** @var float */
	private $start = 0;
	/** @var int */
	private $timingDepth = 0;
	/** @var float */
	private $totalTime = 0;
	/** @var float */
	private $curTickTotal = 0;
	/** @var int */
	private $violations = 0;

	public function __construct(string $name, TimingsHandler $parent = null){
		$this->name = $name;
		$this->parent = $parent;

		self::$HANDLERS[spl_object_hash($this)] = $this;
	}

	/**
	 * @return void
	 */
	public function startTiming(){
		if(self::$enabled){
			$this->internalStartTiming(microtime(true));
		}
	}

	private function internalStartTiming(float $now) : void{
		if(++$this->timingDepth === 1){
			$this->start = $now;
			if($this->parent !== null){
				$this->parent->internalStartTiming($now);
			}
		}
	}

	/**
	 * @return void
	 */
	public function stopTiming(){
		if(self::$enabled){
			$this->internalStopTiming(microtime(true));
		}
	}

	private function internalStopTiming(float $now) : void{
		if($this->timingDepth === 0){
			//TODO: it would be nice to bail here, but since we'd have to track timing depth across resets
			//and enable/disable, it would have a performance impact. Therefore, considering the limited
			//usefulness of bailing here anyway, we don't currently bother.
			return;
		}
		if(--$this->timingDepth !== 0 or $this->start == 0){
			return;
		}

		$diff = $now - $this->start;
		$this->totalTime += $diff;
		$this->curTickTotal += $diff;
		++$this->curCount;
		++$this->count;
		$this->start = 0;
		if($this->parent !== null){
			$this->parent->internalStopTiming($now);
		}
	}

	/**
	 * @return void
	 */
	public function reset(){
		$this->count = 0;
		$this->curCount = 0;
		$this->violations = 0;
		$this->curTickTotal = 0;
		$this->totalTime = 0;
		$this->start = 0;
		$this->timingDepth = 0;
	}

	/**
	 * @return void
	 */
	public function remove(){
		unset(self::$HANDLERS[spl_object_hash($this)]);
	}
}
