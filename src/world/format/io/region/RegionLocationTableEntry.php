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

use function range;

class RegionLocationTableEntry{

	/** @var int */
	private $firstSector;
	/** @var int */
	private $sectorCount;
	/** @var int */
	private $timestamp;

	/**
	 * @param int $firstSector
	 * @param int $sectorCount
	 * @param int $timestamp
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(int $firstSector, int $sectorCount, int $timestamp){
		if($firstSector < 0){
			throw new \InvalidArgumentException("Start sector must be positive, got $firstSector");
		}
		$this->firstSector = $firstSector;
		if($sectorCount < 0 or $sectorCount > 255){
			throw new \InvalidArgumentException("Sector count must be in range 0...255, got $sectorCount");
		}
		$this->sectorCount = $sectorCount;
		$this->timestamp = $timestamp;
	}

	/**
	 * @return int
	 */
	public function getFirstSector() : int{
		return $this->firstSector;
	}

	/**
	 * @return int
	 */
	public function getLastSector() : int{
		return $this->firstSector + $this->sectorCount - 1;
	}

	/**
	 * Returns an array of sector offsets reserved by this chunk.
	 * @return int[]
	 */
	public function getUsedSectors() : array{
		return range($this->getFirstSector(), $this->getLastSector());
	}

	/**
	 * @return int
	 */
	public function getSectorCount() : int{
		return $this->sectorCount;
	}

	/**
	 * @return int
	 */
	public function getTimestamp() : int{
		return $this->timestamp;
	}

	/**
	 * @return bool
	 */
	public function isNull() : bool{
		return $this->firstSector === 0 or $this->sectorCount === 0;
	}
}
