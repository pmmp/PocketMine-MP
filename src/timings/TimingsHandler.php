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
use function hrtime;

class TimingsHandler{
	/** @var bool */
	private static $enabled = false;
	/** @var int */
	private static $timingStart = 0;

	/** @return string[] */
	public static function printTimings() : array{
		$result = ["Minecraft"];

		foreach(TimingsRecord::getAll() as $timings){
			$time = $timings->getTotalTime();
			$count = $timings->getCount();
			if($count === 0){
				//this should never happen - a timings record shouldn't exist if it hasn't been used
				continue;
			}

			$avg = $time / $count;

			$result[] = "    " . $timings->getName() . " Time: $time Count: " . $count . " Avg: $avg Violations: " . $timings->getViolations();
		}

		$result[] = "# Version " . Server::getInstance()->getVersion();
		$result[] = "# " . Server::getInstance()->getName() . " " . Server::getInstance()->getPocketMineVersion();

		$entities = 0;
		$livingEntities = 0;
		foreach(Server::getInstance()->getWorldManager()->getWorlds() as $world){
			$entities += count($world->getEntities());
			foreach($world->getEntities() as $e){
				if($e instanceof Living){
					++$livingEntities;
				}
			}
		}

		$result[] = "# Entities " . $entities;
		$result[] = "# LivingEntities " . $livingEntities;

		$sampleTime = hrtime(true) - self::$timingStart;
		$result[] = "Sample time $sampleTime (" . ($sampleTime / 1000000000) . "s)";
		return $result;
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

	public static function reload() : void{
		TimingsRecord::clearRecords();
		if(self::$enabled){
			self::$timingStart = hrtime(true);
		}
	}

	public static function tick(bool $measure = true) : void{
		if(self::$enabled){
			TimingsRecord::tick($measure);
		}
	}

	/** @var string */
	private $name;
	/** @var TimingsHandler|null */
	private $parent = null;

	/** @var TimingsRecord|null */
	private $record = null;

	/** @var int */
	private $timingDepth = 0;

	public function __construct(string $name, ?TimingsHandler $parent = null){
		$this->name = $name;
		$this->parent = $parent;
	}

	public function getName() : string{ return $this->name; }

	public function startTiming() : void{
		if(self::$enabled){
			$this->internalStartTiming(hrtime(true));
		}
	}

	private function internalStartTiming(int $now) : void{
		if(++$this->timingDepth === 1){
			if($this->record === null){
				$this->record = new TimingsRecord($this);
			}
			$this->record->startTiming($now);
			if($this->parent !== null){
				$this->parent->internalStartTiming($now);
			}
		}
	}

	public function stopTiming() : void{
		if(self::$enabled){
			$this->internalStopTiming(hrtime(true));
		}
	}

	private function internalStopTiming(int $now) : void{
		if($this->timingDepth === 0){
			//TODO: it would be nice to bail here, but since we'd have to track timing depth across resets
			//and enable/disable, it would have a performance impact. Therefore, considering the limited
			//usefulness of bailing here anyway, we don't currently bother.
			return;
		}
		if(--$this->timingDepth !== 0){
			return;
		}

		if($this->record !== null){
			//this might be null if a timings reset occurred while the timer was running
			$this->record->stopTiming($now);
		}
		if($this->parent !== null){
			$this->parent->internalStopTiming($now);
		}
	}

	/**
	 * @return mixed the result of the given closure
	 *
	 * @phpstan-template TClosureReturn
	 * @phpstan-param \Closure() : TClosureReturn $closure
	 * @phpstan-return TClosureReturn
	 */
	public function time(\Closure $closure){
		$this->startTiming();
		try{
			return $closure();
		}finally{
			$this->stopTiming();
		}
	}

	/**
	 * @internal
	 */
	public function destroyCycles() : void{
		$this->record = null;
	}
}
