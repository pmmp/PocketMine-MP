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

use pocketmine\item\TieredTool;

class StoneSlab extends Slab{
	public const STONE = 0;
	public const SANDSTONE = 1;
	public const WOODEN = 2;
	public const COBBLESTONE = 3;
	public const BRICK = 4;
	public const STONE_BRICK = 5;
	public const QUARTZ = 6;
	public const NETHER_BRICK = 7;

	protected $id = self::STONE_SLAB;

	public function getDoubleSlabId() : int{
		return self::DOUBLE_STONE_SLAB;
	}

	public function getHardness() : float{
		return 2;
	}

	public function getName() : string{
		static $names = [
			self::STONE => "Stone",
			self::SANDSTONE => "Sandstone",
			self::WOODEN => "Wooden",
			self::COBBLESTONE => "Cobblestone",
			self::BRICK => "Brick",
			self::STONE_BRICK => "Stone Brick",
			self::QUARTZ => "Quartz",
			self::NETHER_BRICK => "Nether Brick"
		];
		return (($this->meta & 0x08) > 0 ? "Upper " : "") . ($names[$this->getVariant()] ?? "") . " Slab";
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}
}
