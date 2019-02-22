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
use function spl_object_id;
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
		foreach(Server::getInstance()->getLevelManager()->getLevels() as $level){
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

	public static function reload(){
		if(self::$enabled){
			foreach(self::$HANDLERS as $timings){
				$timings->reset();
			}
			self::$timingStart = microtime(true);
		}
	}

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
	/** @var TimingsHandler */
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

	/**
	 * @param string         $name
	 * @param TimingsHandler $parent
	 */
	public function __construct(string $name, ?TimingsHandler $parent = null){
		$this->name = $name;
		$this->parent = $parent;

		self::$HANDLERS[spl_object_id($this)] = $this;
	}

	public function startTiming(){
		if(self::$enabled and ++$this->timingDepth === 1){
			$this->start = microtime(true);
			if($this->parent !== null and ++$this->parent->timingDepth === 1){
				$this->parent->start = $this->start;
			}
		}
	}

	public function stopTiming(){
		if(self::$enabled){
			if(--$this->timingDepth !== 0 or $this->start === 0){
				return;
			}

			$diff = microtime(true) - $this->start;
			$this->totalTime += $diff;
			$this->curTickTotal += $diff;
			++$this->curCount;
			++$this->count;
			$this->start = 0;
			if($this->parent !== null){
				$this->parent->stopTiming();
			}
		}
	}

	public function reset(){
		$this->count = 0;
		$this->curCount = 0;
		$this->violations = 0;
		$this->curTickTotal = 0;
		$this->totalTime = 0;
		$this->start = 0;
		$this->timingDepth = 0;
	}

	public function remove(){
		unset(self::$HANDLERS[spl_object_id($this)]);
	}
}
