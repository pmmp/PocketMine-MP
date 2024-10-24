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

namespace pocketmine\item;

use pocketmine\block\BlockTypeIds;
use pocketmine\utils\LegacyEnumShimTrait;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static ToolTier DIAMOND()
 * @method static ToolTier GOLD()
 * @method static ToolTier IRON()
 * @method static ToolTier NETHERITE()
 * @method static ToolTier STONE()
 * @method static ToolTier WOOD()
 *
 * @phpstan-type TMetadata array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int[]}
 */
enum ToolTier{
	use LegacyEnumShimTrait;

	case WOOD;
	case GOLD;
	case STONE;
	case IRON;
	case DIAMOND;
	case NETHERITE;

	/**
	 * This function exists only to permit the use of named arguments and to make the code easier to read in PhpStorm.
	 * @param int[] $repairMaterials The typeId of the items that can be used to repair this tool in the anvil.
	 * @phpstan-return TMetadata
	 */
	private static function meta(int $harvestLevel, int $maxDurability, int $baseAttackPoints, int $baseEfficiency, int $enchantability, array $repairMaterials = []) : array{
		return [$harvestLevel, $maxDurability, $baseAttackPoints, $baseEfficiency, $enchantability, $repairMaterials];
	}

	/**
	 * @phpstan-return TMetadata
	 */
	private function getMetadata() : array{
		return match($this){
			self::WOOD => self::meta(1, 60, 5, 2, 15, [
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::OAK_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::SPRUCE_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::BIRCH_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::JUNGLE_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::ACACIA_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::DARK_OAK_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::CRIMSON_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::WARPED_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::CHERRY_PLANKS),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::MANGROVE_PLANKS)
			]),
			self::GOLD => self::meta(2, 33, 5, 12, 22, [ItemTypeIds::GOLD_INGOT]),
			self::STONE => self::meta(3, 132, 6, 4, 5, [
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::COBBLESTONE),
				ItemTypeIds::fromBlockTypeId(BlockTypeIds::COBBLED_DEEPSLATE)
			]),
			self::IRON => self::meta(4, 251, 7, 6, 14, [ItemTypeIds::IRON_INGOT]),
			self::DIAMOND => self::meta(5, 1562, 8, 8, 10, [ItemTypeIds::DIAMOND]),
			self::NETHERITE => self::meta(6, 2032, 9, 9, 15, [ItemTypeIds::NETHERITE_INGOT])
		};
	}

	public function getHarvestLevel() : int{
		return $this->getMetadata()[0];
	}

	public function getMaxDurability() : int{
		return $this->getMetadata()[1];
	}

	public function getBaseAttackPoints() : int{
		return $this->getMetadata()[2];
	}

	public function getBaseEfficiency() : int{
		return $this->getMetadata()[3];
	}

	/**
	 * Returns the value that defines how enchantable the item is.
	 *
	 * The higher an item's enchantability is, the more likely it will be to gain high-level enchantments
	 * or multiple enchantments upon being enchanted in an enchanting table.
	 */
	public function getEnchantability() : int{
		return $this->getMetadata()[4];
	}

	/**
	 * Returns the typeId of items that can be used to repair this tool in the anvil.
	 *
	 * @return int[]
	 */
	public function getRepairMaterials() : array{
		return $this->getMetadata()[5];
	}
}
