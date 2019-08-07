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

namespace pocketmine\block\tile;

use pocketmine\block\RedstoneComparator;
use pocketmine\nbt\tag\CompoundTag;

/**
 * @deprecated
 * @see RedstoneComparator
 */
class Comparator extends Tile{
	private const TAG_OUTPUT_SIGNAL = "OutputSignal"; //int

	/** @var int */
	protected $signalStrength = 0;

	/**
	 * @return int
	 */
	public function getSignalStrength() : int{
		return $this->signalStrength;
	}

	/**
	 * @param int $signalStrength
	 */
	public function setSignalStrength(int $signalStrength) : void{
		$this->signalStrength = $signalStrength;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->signalStrength = $nbt->getInt(self::TAG_OUTPUT_SIGNAL, 0, true);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_OUTPUT_SIGNAL, $this->signalStrength);
	}
}
