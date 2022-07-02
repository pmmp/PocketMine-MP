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

use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockTypeIds as Ids;
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\TreeType;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\item\ItemIds;
use pocketmine\utils\AssumptionFailedError;

final class BlockLegacyIdHelper{

	public static function getWoodenPlanksIdentifier(TreeType $treeType) : BID{
		return new BID(match($treeType->id()){
			TreeType::OAK()->id() => Ids::OAK_PLANKS,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_PLANKS,
			TreeType::BIRCH()->id() => Ids::BIRCH_PLANKS,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_PLANKS,
			TreeType::ACACIA()->id() => Ids::ACACIA_PLANKS,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_PLANKS,
			default => throw new AssumptionFailedError("All tree types should be covered")
		}, ItemIds::PLANKS, $treeType->getMagicNumber());
	}

	public static function getWoodenFenceIdentifier(TreeType $treeType) : BID{
		return new BID(match($treeType->id()){
			TreeType::OAK()->id() => Ids::OAK_FENCE,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_FENCE,
			TreeType::BIRCH()->id() => Ids::BIRCH_FENCE,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_FENCE,
			TreeType::ACACIA()->id() => Ids::ACACIA_FENCE,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_FENCE,
			default => throw new AssumptionFailedError("All tree types should be covered")
		}, ItemIds::FENCE, $treeType->getMagicNumber());
	}

	public static function getWoodenSlabIdentifier(TreeType $treeType) : BID{
		return new BID(match($treeType->id()){
			TreeType::OAK()->id() => Ids::OAK_SLAB,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_SLAB,
			TreeType::BIRCH()->id() => Ids::BIRCH_SLAB,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_SLAB,
			TreeType::ACACIA()->id() => Ids::ACACIA_SLAB,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_SLAB,
			default => throw new AssumptionFailedError("All tree types should be covered")
		}, ItemIds::WOODEN_SLAB, $treeType->getMagicNumber());
	}

	public static function getLogIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_LOG, ItemIds::LOG, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_LOG, ItemIds::LOG, 1),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_LOG, ItemIds::LOG, 2),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_LOG, ItemIds::LOG, 3),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_LOG, ItemIds::LOG2, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_LOG, ItemIds::LOG2, 1),
			default => throw new AssumptionFailedError("All tree types should be covered")
		};
	}

	public static function getAllSidedLogIdentifier(TreeType $treeType) : BID{
		return new BID(match($treeType->id()){
			TreeType::OAK()->id() => Ids::OAK_WOOD,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_WOOD,
			TreeType::BIRCH()->id() => Ids::BIRCH_WOOD,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_WOOD,
			TreeType::ACACIA()->id() => Ids::ACACIA_WOOD,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_WOOD,
			default => throw new AssumptionFailedError("All tree types should be covered")
		}, ItemIds::WOOD, $treeType->getMagicNumber());
	}

	public static function getAllSidedStrippedLogIdentifier(TreeType $treeType) : BID{
		return new BID(match($treeType->id()){
			TreeType::OAK()->id() => Ids::STRIPPED_OAK_WOOD,
			TreeType::SPRUCE()->id() => Ids::STRIPPED_SPRUCE_WOOD,
			TreeType::BIRCH()->id() => Ids::STRIPPED_BIRCH_WOOD,
			TreeType::JUNGLE()->id() => Ids::STRIPPED_JUNGLE_WOOD,
			TreeType::ACACIA()->id() => Ids::STRIPPED_ACACIA_WOOD,
			TreeType::DARK_OAK()->id() => Ids::STRIPPED_DARK_OAK_WOOD,
			default => throw new AssumptionFailedError("All tree types should be covered")
		}, ItemIds::WOOD, $treeType->getMagicNumber() | BlockLegacyMetadata::WOOD_FLAG_STRIPPED);
	}

	public static function getLeavesIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_LEAVES, ItemIds::LEAVES, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_LEAVES, ItemIds::LEAVES, 1),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_LEAVES, ItemIds::LEAVES, 2),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_LEAVES, ItemIds::LEAVES, 3),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_LEAVES, ItemIds::LEAVES2, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_LEAVES, ItemIds::LEAVES2, 1),
			default => throw new AssumptionFailedError("All tree types should be covered")
		};
	}

	public static function getSaplingIdentifier(TreeType $treeType) : BID{
		return new BID(match($treeType->id()){
			TreeType::OAK()->id() => Ids::OAK_SAPLING,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_SAPLING,
			TreeType::BIRCH()->id() => Ids::BIRCH_SAPLING,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_SAPLING,
			TreeType::ACACIA()->id() => Ids::ACACIA_SAPLING,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_SAPLING,
			default => throw new AssumptionFailedError("All tree types should be covered")
		}, ItemIds::SAPLING, $treeType->getMagicNumber());
	}

	public static function getWoodenFloorSignIdentifier(TreeType $treeType) : BID{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_SIGN, ItemIds::SIGN, 0, TileSign::class);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_SIGN, ItemIds::SPRUCE_SIGN, 0, TileSign::class);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_SIGN, ItemIds::BIRCH_SIGN, 0, TileSign::class);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_SIGN, ItemIds::JUNGLE_SIGN, 0, TileSign::class);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_SIGN, ItemIds::ACACIA_SIGN, 0, TileSign::class);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_SIGN, ItemIds::DARKOAK_SIGN, 0, TileSign::class);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenWallSignIdentifier(TreeType $treeType) : BID{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_WALL_SIGN, ItemIds::SIGN, 0, TileSign::class);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_WALL_SIGN, ItemIds::SPRUCE_SIGN, 0, TileSign::class);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_WALL_SIGN, ItemIds::BIRCH_SIGN, 0, TileSign::class);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_WALL_SIGN, ItemIds::JUNGLE_SIGN, 0, TileSign::class);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_WALL_SIGN, ItemIds::ACACIA_SIGN, 0, TileSign::class);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_WALL_SIGN, ItemIds::DARKOAK_SIGN, 0, TileSign::class);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenTrapdoorIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_TRAPDOOR, ItemIds::WOODEN_TRAPDOOR, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_TRAPDOOR, ItemIds::SPRUCE_TRAPDOOR, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_TRAPDOOR, ItemIds::BIRCH_TRAPDOOR, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_TRAPDOOR, ItemIds::JUNGLE_TRAPDOOR, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_TRAPDOOR, ItemIds::ACACIA_TRAPDOOR, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_TRAPDOOR, ItemIds::DARK_OAK_TRAPDOOR, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenButtonIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_BUTTON, ItemIds::WOODEN_BUTTON, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_BUTTON, ItemIds::SPRUCE_BUTTON, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_BUTTON, ItemIds::BIRCH_BUTTON, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_BUTTON, ItemIds::JUNGLE_BUTTON, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_BUTTON, ItemIds::ACACIA_BUTTON, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_BUTTON, ItemIds::DARK_OAK_BUTTON, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenPressurePlateIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_PRESSURE_PLATE, ItemIds::WOODEN_PRESSURE_PLATE, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_PRESSURE_PLATE, ItemIds::SPRUCE_PRESSURE_PLATE, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_PRESSURE_PLATE, ItemIds::BIRCH_PRESSURE_PLATE, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_PRESSURE_PLATE, ItemIds::JUNGLE_PRESSURE_PLATE, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_PRESSURE_PLATE, ItemIds::ACACIA_PRESSURE_PLATE, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_PRESSURE_PLATE, ItemIds::DARK_OAK_PRESSURE_PLATE, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenDoorIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_DOOR, ItemIds::OAK_DOOR, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_DOOR, ItemIds::SPRUCE_DOOR, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_DOOR, ItemIds::BIRCH_DOOR, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_DOOR, ItemIds::JUNGLE_DOOR, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_DOOR, ItemIds::ACACIA_DOOR, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_DOOR, ItemIds::DARK_OAK_DOOR, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenFenceGateIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_FENCE_GATE, ItemIds::OAK_FENCE_GATE, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_FENCE_GATE, ItemIds::SPRUCE_FENCE_GATE, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_FENCE_GATE, ItemIds::BIRCH_FENCE_GATE, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_FENCE_GATE, ItemIds::JUNGLE_FENCE_GATE, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_FENCE_GATE, ItemIds::ACACIA_FENCE_GATE, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_FENCE_GATE, ItemIds::DARK_OAK_FENCE_GATE, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenStairsIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_STAIRS, ItemIds::OAK_STAIRS, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_STAIRS, ItemIds::SPRUCE_STAIRS, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_STAIRS, ItemIds::BIRCH_STAIRS, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_STAIRS, ItemIds::JUNGLE_STAIRS, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_STAIRS, ItemIds::ACACIA_STAIRS, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_STAIRS, ItemIds::DARK_OAK_STAIRS, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getStrippedLogIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::STRIPPED_OAK_LOG, ItemIds::STRIPPED_OAK_LOG, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::STRIPPED_SPRUCE_LOG, ItemIds::STRIPPED_SPRUCE_LOG, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::STRIPPED_BIRCH_LOG, ItemIds::STRIPPED_BIRCH_LOG, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::STRIPPED_JUNGLE_LOG, ItemIds::STRIPPED_JUNGLE_LOG, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::STRIPPED_ACACIA_LOG, ItemIds::STRIPPED_ACACIA_LOG, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::STRIPPED_DARK_OAK_LOG, ItemIds::STRIPPED_DARK_OAK_LOG, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getGlazedTerracottaIdentifier(DyeColor $color) : BlockIdentifier{
		switch($color->id()){
			case DyeColor::WHITE()->id():
				return new BID(Ids::WHITE_GLAZED_TERRACOTTA, ItemIds::WHITE_GLAZED_TERRACOTTA, 0);
			case DyeColor::ORANGE()->id():
				return new BID(Ids::ORANGE_GLAZED_TERRACOTTA, ItemIds::ORANGE_GLAZED_TERRACOTTA, 0);
			case DyeColor::MAGENTA()->id():
				return new BID(Ids::MAGENTA_GLAZED_TERRACOTTA, ItemIds::MAGENTA_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIGHT_BLUE()->id():
				return new BID(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA, ItemIds::LIGHT_BLUE_GLAZED_TERRACOTTA, 0);
			case DyeColor::YELLOW()->id():
				return new BID(Ids::YELLOW_GLAZED_TERRACOTTA, ItemIds::YELLOW_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIME()->id():
				return new BID(Ids::LIME_GLAZED_TERRACOTTA, ItemIds::LIME_GLAZED_TERRACOTTA, 0);
			case DyeColor::PINK()->id():
				return new BID(Ids::PINK_GLAZED_TERRACOTTA, ItemIds::PINK_GLAZED_TERRACOTTA, 0);
			case DyeColor::GRAY()->id():
				return new BID(Ids::GRAY_GLAZED_TERRACOTTA, ItemIds::GRAY_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIGHT_GRAY()->id():
				return new BID(Ids::LIGHT_GRAY_GLAZED_TERRACOTTA, ItemIds::SILVER_GLAZED_TERRACOTTA, 0);
			case DyeColor::CYAN()->id():
				return new BID(Ids::CYAN_GLAZED_TERRACOTTA, ItemIds::CYAN_GLAZED_TERRACOTTA, 0);
			case DyeColor::PURPLE()->id():
				return new BID(Ids::PURPLE_GLAZED_TERRACOTTA, ItemIds::PURPLE_GLAZED_TERRACOTTA, 0);
			case DyeColor::BLUE()->id():
				return new BID(Ids::BLUE_GLAZED_TERRACOTTA, ItemIds::BLUE_GLAZED_TERRACOTTA, 0);
			case DyeColor::BROWN()->id():
				return new BID(Ids::BROWN_GLAZED_TERRACOTTA, ItemIds::BROWN_GLAZED_TERRACOTTA, 0);
			case DyeColor::GREEN()->id():
				return new BID(Ids::GREEN_GLAZED_TERRACOTTA, ItemIds::GREEN_GLAZED_TERRACOTTA, 0);
			case DyeColor::RED()->id():
				return new BID(Ids::RED_GLAZED_TERRACOTTA, ItemIds::RED_GLAZED_TERRACOTTA, 0);
			case DyeColor::BLACK()->id():
				return new BID(Ids::BLACK_GLAZED_TERRACOTTA, ItemIds::BLACK_GLAZED_TERRACOTTA, 0);
		}
		throw new AssumptionFailedError("Switch should cover all colours");
	}

	public static function getStoneSlabIdentifier(int $blockTypeId, int $stoneSlabId, int $meta) : BID{
		$itemId = [
			1 => ItemIds::STONE_SLAB,
			2 => ItemIds::STONE_SLAB2,
			3 => ItemIds::STONE_SLAB3,
			4 => ItemIds::STONE_SLAB4
		][$stoneSlabId] ?? null;
		if($itemId === null){
			throw new \InvalidArgumentException("Stone slab type should be 1, 2, 3 or 4");
		}
		return new BID($blockTypeId, $itemId, $meta);
	}
}
