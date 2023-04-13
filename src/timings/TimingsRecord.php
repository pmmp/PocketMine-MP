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

use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use function floor;
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
	private static array $records = [];

	private static ?self $currentRecord = null;

	public static function clearRecords() : void{
		foreach(self::$records as $record){
			$record->handler->destroyCycles();
		}
		self::$records = [];
		self::$currentRecord = null;
	}

	/**
	 * @return self[]
	 * @phpstan-return array<int, self>
	 */
	public static function getAll() : array{ return self::$records; }

	public static function tick(bool $measure = true) : void{
		if($measure){
			foreach(self::$records as $record){
				if($record->curCount > 0){
					if($record->curTickTotal > Server::TARGET_NANOSECONDS_PER_TICK){
						$record->violations += (int) floor($record->curTickTotal / Server::TARGET_NANOSECONDS_PER_TICK);
					}
					if($record->curTickTotal > $record->peakTime){
						$record->peakTime = $record->curTickTotal;
					}
					$record->curTickTotal = 0;
					$record->curCount = 0;
					$record->ticksActive++;
				}
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

	private int $count = 0;
	private int $curCount = 0;
	private int $start = 0;
	private int $totalTime = 0;
	private int $curTickTotal = 0;
	private int $violations = 0;
	private int $ticksActive = 0;
	private int $peakTime = 0;

	public function __construct(
		//I'm not the biggest fan of this cycle, but it seems to be the most effective way to avoid leaking anything.
		private TimingsHandler $handler,
		private ?TimingsRecord $parentRecord
	){
		self::$records[spl_object_id($this)] = $this;
	}

	public function getId() : int{ return spl_object_id($this); }

	public function getParentId() : ?int{ return $this->parentRecord?->getId(); }

	public function getTimerId() : int{ return spl_object_id($this->handler); }

	public function getName() : string{ return $this->handler->getName(); }

	public function getGroup() : string{ return $this->handler->getGroup(); }

	public function getCount() : int{ return $this->count; }

	public function getCurCount() : int{ return $this->curCount; }

	public function getStart() : float{ return $this->start; }

	public function getTotalTime() : float{ return $this->totalTime; }

	public function getCurTickTotal() : float{ return $this->curTickTotal; }

	public function getViolations() : int{ return $this->violations; }

	public function getTicksActive() : int{ return $this->ticksActive; }

	public function getPeakTime() : float{ return $this->peakTime; }

	public function startTiming(int $now) : void{
		$this->start = $now;
		self::$currentRecord = $this;
	}

	public function stopTiming(int $now) : void{
		if($this->start == 0){
			return;
		}
		if(self::$currentRecord !== $this){
			if(self::$currentRecord === null){
				//timings may have been stopped while this timer was running
				return;
			}

			throw new AssumptionFailedError("stopTiming() called on a non-current timer");
		}
		self::$currentRecord = $this->parentRecord;
		$diff = $now - $this->start;
		$this->totalTime += $diff;
		$this->curTickTotal += $diff;
		++$this->curCount;
		++$this->count;
		$this->start = 0;
	}

	public static function getCurrentRecord() : ?self{
		return self::$currentRecord;
	}
}
