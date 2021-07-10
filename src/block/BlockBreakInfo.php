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

namespace pocketmine\block;

use pocketmine\item\Item;
use function get_class;

class BlockBreakInfo{
	/**
	 * If the tool is the correct type and high enough harvest level (tool tier), base break time is hardness multiplied
	 * by this value.
	 */
	public const COMPATIBLE_TOOL_MULTIPLIER = 1.5;
	/**
	 * If the tool is an incorrect type or too low harvest level (tool tier), base break time is hardness multiplied by
	 * this value.
	 */
	public const INCOMPATIBLE_TOOL_MULTIPLIER = 5.0;

	private float $hardness;
	private float $blastResistance;
	private int $toolType;
	private int $toolHarvestLevel;

	/**
	 * @param float|null $blastResistance default 5x hardness
	 */
	public function __construct(float $hardness, int $toolType = BlockToolType::NONE, int $toolHarvestLevel = 0, ?float $blastResistance = null){
		$this->hardness = $hardness;
		$this->toolType = $toolType;
		$this->toolHarvestLevel = $toolHarvestLevel;
		$this->blastResistance = $blastResistance ?? $hardness * 5;
	}

	public static function instant(int $toolType = BlockToolType::NONE, int $toolHarvestLevel = 0) : self{
		return new self(0.0, $toolType, $toolHarvestLevel, 0.0);
	}

	public static function indestructible(float $blastResistance = 18000000.0) : self{
		return new self(-1.0, BlockToolType::NONE, 0, $blastResistance);
	}

	/**
	 * Returns a base value used to compute block break times.
	 */
	public function getHardness() : float{
		return $this->hardness;
	}

	/**
	 * Returns whether the block can be broken at all.
	 */
	public function isBreakable() : bool{
		return $this->hardness >= 0;
	}

	/**
	 * Returns whether this block can be instantly broken.
	 */
	public function breaksInstantly() : bool{
		return $this->hardness == 0.0;
	}

	/**
	 * Returns the block's resistance to explosions. Usually 5x hardness.
	 */
	public function getBlastResistance() : float{
		return $this->blastResistance;
	}

	public function getToolType() : int{
		return $this->toolType;
	}

	/**
	 * Returns the level of tool required to harvest the block (for normal blocks). When the tool type matches the
	 * block's required tool type, the tool must have a harvest level greater than or equal to this value to be able to
	 * successfully harvest the block.
	 *
	 * If the block requires a specific minimum tier of tiered tool, the minimum tier required should be returned.
	 * Otherwise, 1 should be returned if a tool is required, 0 if not.
	 *
	 * @see Item::getBlockToolHarvestLevel()
	 */
	public function getToolHarvestLevel() : int{
		return $this->toolHarvestLevel;
	}

	/**
	 * Returns whether the specified item is the proper tool to use for breaking this block. This checks tool type and
	 * harvest level requirement.
	 *
	 * In most cases this is also used to determine whether block drops should be created or not, except in some
	 * special cases such as vines.
	 */
	public function isToolCompatible(Item $tool) : bool{
		if($this->hardness < 0){
			return false;
		}

		return $this->toolType === BlockToolType::NONE or $this->toolHarvestLevel === 0 or (
				($this->toolType & $tool->getBlockToolType()) !== 0 and $tool->getBlockToolHarvestLevel() >= $this->toolHarvestLevel);
	}

	/**
	 * Returns the seconds that this block takes to be broken using an specific Item
	 *
	 * @throws \InvalidArgumentException if the item efficiency is not a positive number
	 */
	public function getBreakTime(Item $item) : float{
		$base = $this->hardness;
		if($this->isToolCompatible($item)){
			$base *= self::COMPATIBLE_TOOL_MULTIPLIER;
		}else{
			$base *= self::INCOMPATIBLE_TOOL_MULTIPLIER;
		}

		$efficiency = $item->getMiningEfficiency(($this->toolType & $item->getBlockToolType()) !== 0);
		if($efficiency <= 0){
			throw new \InvalidArgumentException(get_class($item) . " has invalid mining efficiency: expected >= 0, got $efficiency");
		}

		$base /= $efficiency;

		return $base;
	}
}
