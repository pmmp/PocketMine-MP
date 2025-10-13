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

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\Water;

interface CoveredByWater{

	public function getWaterCover() : ?Water;

	/**
	 * @return $this
	 */
	public function setWaterCover(?Water $waterCover) : self;

	public function liquidCollide(Block $cause, Block $result) : bool;

	/**
	 * Returns whether block can be covered by water at the current moment.
	 * For example, double slabs can't be waterlogged, while single can.
	 */
	public function canBeCovered() : bool;

	/** Returns whether the block can be covered not only by source water, but also by the flowing. */
	public function canBeCoveredByFlowing() : bool;

	/** Returns whether water can flow through the given side. */
	public function isSideOpenToFlow(int $face) : bool;
}
