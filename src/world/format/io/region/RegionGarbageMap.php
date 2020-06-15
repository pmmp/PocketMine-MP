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

namespace pocketmine\world\format\io\region;

use pocketmine\utils\AssumptionFailedError;
use function end;
use function ksort;
use function time;
use const SORT_NUMERIC;

final class RegionGarbageMap{

	/** @var RegionLocationTableEntry[] */
	private $entries = [];
	/** @var bool */
	private $clean = false;

	/**
	 * @param RegionLocationTableEntry[] $entries
	 */
	public function __construct(array $entries){
		foreach($entries as $entry){
			$this->entries[$entry->getFirstSector()] = $entry;
		}
	}

	/**
	 * @param RegionLocationTableEntry[]|null[] $locationTable
	 */
	public static function buildFromLocationTable(array $locationTable) : self{
		/** @var RegionLocationTableEntry[] $usedMap */
		$usedMap = [];
		foreach($locationTable as $entry){
			if($entry === null){
				continue;
			}
			if(isset($usedMap[$entry->getFirstSector()])){
				throw new AssumptionFailedError("Overlapping entries detected");
			}
			$usedMap[$entry->getFirstSector()] = $entry;
		}

		ksort($usedMap, SORT_NUMERIC);

		/** @var RegionLocationTableEntry[] $garbageMap */
		$garbageMap = [];

		/** @var RegionLocationTableEntry|null $prevEntry */
		$prevEntry = null;
		foreach($usedMap as $firstSector => $entry){
			$expectedStart = ($prevEntry !== null ? $prevEntry->getLastSector() + 1 : RegionLoader::FIRST_SECTOR);
			$actualStart = $entry->getFirstSector();
			if($expectedStart < $actualStart){
				//found a gap in the table
				$garbageMap[$expectedStart] = new RegionLocationTableEntry($expectedStart, $actualStart - $expectedStart, 0);
			}
			$prevEntry = $entry;
		}

		return new self($garbageMap);
	}

	/**
	 * @return RegionLocationTableEntry[]
	 * @phpstan-return array<int, RegionLocationTableEntry>
	 */
	public function getArray() : array{
		if(!$this->clean){
			ksort($this->entries, SORT_NUMERIC);

			/** @var int|null $prevIndex */
			$prevIndex = null;
			foreach($this->entries as $k => $entry){
				if($prevIndex !== null and $this->entries[$prevIndex]->getLastSector() + 1 === $entry->getFirstSector()){
					//this SHOULD overwrite the previous index and not appear at the end
					$this->entries[$prevIndex] = new RegionLocationTableEntry(
						$this->entries[$prevIndex]->getFirstSector(),
						$this->entries[$prevIndex]->getSectorCount() + $entry->getSectorCount(),
						0
					);
					unset($this->entries[$k]);
				}else{
					$prevIndex = $k;
				}
			}
			$this->clean = true;
		}
		return $this->entries;
	}

	public function add(RegionLocationTableEntry $entry) : void{
		if(isset($this->entries[$k = $entry->getFirstSector()])){
			throw new \InvalidArgumentException("Overlapping entry starting at " . $k);
		}
		$this->entries[$k] = $entry;
		$this->clean = false;
	}

	public function remove(RegionLocationTableEntry $entry) : void{
		if(isset($this->entries[$k = $entry->getFirstSector()])){
			//removal doesn't affect ordering and shouldn't affect fragmentation
			unset($this->entries[$k]);
		}
	}

	public function end() : ?RegionLocationTableEntry{
		$array = $this->getArray();
		$end = end($array);
		return $end !== false ? $end : null;
	}

	public function allocate(int $newSize) : ?RegionLocationTableEntry{
		foreach($this->getArray() as $start => $candidate){
			$candidateSize = $candidate->getSectorCount();
			if($candidateSize < $newSize){
				continue;
			}

			$newLocation = new RegionLocationTableEntry($candidate->getFirstSector(), $newSize, time());
			$this->remove($candidate);

			if($candidateSize > $newSize){ //we're not using the whole area, just take part of it
				$newGarbageStart = $candidate->getFirstSector() + $newSize;
				$newGarbageSize = $candidateSize - $newSize;
				$this->add(new RegionLocationTableEntry($newGarbageStart, $newGarbageSize, 0));
			}
			return $newLocation;

		}

		return null;
	}
}
