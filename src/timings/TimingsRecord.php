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

use function round;
use function spl_object_id;

/**
 * Represents a record collected by a timings handler.
 * This record will live until the end of the current timings session, even if its handler goes out of scope. This
 * ensures that timings collected by destroyed timers are still shown in the final report.
 */
final class TimingsRecord{
	/**
	 * @var self[]
	 * @phpstan-var array<int, self>
	 */
	private static $records = [];

	public static function clearRecords() : void{
		foreach(self::$records as $record){
			$record->handler->destroyCycles();
		}
		self::$records = [];
	}

	/**
	 * @return self[]
	 * @phpstan-return array<int, self>
	 */
	public static function getAll() : array{ return self::$records; }

	public static function tick(bool $measure = true) : void{
		if($measure){
			foreach(self::$records as $record){
				if($record->curTickTotal > 50000000){
					$record->violations += (int) round($record->curTickTotal / 50000000);
				}
				$record->curTickTotal = 0;
				$record->curCount = 0;
			}
		}else{
			foreach(self::$records as $record){
				$record->totalTime -= $record->curTickTotal;
				$record->count -= $record->curCount;

				$record->curTickTotal = 0;
				$record->curCount = 0;
			}
		}
	}

	/** @var TimingsHandler */
	private $handler;

	/** @var int */
	private $count = 0;
	/** @var int */
	private $curCount = 0;
	/** @var int */
	private $start = 0;
	/** @var int */
	private $totalTime = 0;
	/** @var int */
	private $curTickTotal = 0;
	/** @var int */
	private $violations = 0;

	public function __construct(TimingsHandler $handler){
		self::$records[spl_object_id($this)] = $this;
		//I'm not the biggest fan of this cycle, but it seems to be the most effective way to avoid leaking anything.
		$this->handler = $handler;
	}

	public function getName() : string{ return $this->handler->getName(); }

	public function getCount() : int{ return $this->count; }

	public function getCurCount() : int{ return $this->curCount; }

	public function getStart() : float{ return $this->start; }

	public function getTotalTime() : float{ return $this->totalTime; }

	public function getCurTickTotal() : float{ return $this->curTickTotal; }

	public function getViolations() : int{ return $this->violations; }

	public function startTiming(int $now) : void{
		$this->start = $now;
	}

	public function stopTiming(int $now) : void{
		if($this->start == 0){
			return;
		}
		$diff = $now - $this->start;
		$this->totalTime += $diff;
		$this->curTickTotal += $diff;
		++$this->curCount;
		++$this->count;
		$this->start = 0;
	}
}
