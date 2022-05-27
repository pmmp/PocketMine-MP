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
use pocketmine\block\BlockIdentifierFlattened as BIDFlattened;
use pocketmine\block\BlockLegacyIds as LegacyIds;
use pocketmine\block\BlockTypeIds as Ids;
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\TreeType;
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
		}, LegacyIds::PLANKS, $treeType->getMagicNumber());
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
		}, LegacyIds::FENCE, $treeType->getMagicNumber());
	}

	public static function getWoodenSlabIdentifier(TreeType $treeType) : BIDFlattened{
		return new BIDFlattened(match($treeType->id()){
			TreeType::OAK()->id() => Ids::OAK_SLAB,
			TreeType::SPRUCE()->id() => Ids::SPRUCE_SLAB,
			TreeType::BIRCH()->id() => Ids::BIRCH_SLAB,
			TreeType::JUNGLE()->id() => Ids::JUNGLE_SLAB,
			TreeType::ACACIA()->id() => Ids::ACACIA_SLAB,
			TreeType::DARK_OAK()->id() => Ids::DARK_OAK_SLAB,
			default => throw new AssumptionFailedError("All tree types should be covered")
		}, LegacyIds::WOODEN_SLAB, [LegacyIds::DOUBLE_WOODEN_SLAB], $treeType->getMagicNumber());
	}

	public static function getLogIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_LOG, LegacyIds::LOG, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_LOG, LegacyIds::LOG, 1),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_LOG, LegacyIds::LOG, 2),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_LOG, LegacyIds::LOG, 3),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_LOG, LegacyIds::LOG2, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_LOG, LegacyIds::LOG2, 1),
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
		}, LegacyIds::WOOD, $treeType->getMagicNumber());
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
		}, LegacyIds::WOOD, $treeType->getMagicNumber() | BlockLegacyMetadata::WOOD_FLAG_STRIPPED);
	}

	public static function getLeavesIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_LEAVES, LegacyIds::LEAVES, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_LEAVES, LegacyIds::LEAVES, 1),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_LEAVES, LegacyIds::LEAVES, 2),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_LEAVES, LegacyIds::LEAVES, 3),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_LEAVES, LegacyIds::LEAVES2, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_LEAVES, LegacyIds::LEAVES2, 1),
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
		}, LegacyIds::SAPLING, $treeType->getMagicNumber());
	}

	public static function getWoodenFloorSignIdentifier(TreeType $treeType) : BID{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_SIGN, LegacyIds::SIGN_POST, 0, ItemIds::SIGN, TileSign::class);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_SIGN, LegacyIds::SPRUCE_STANDING_SIGN, 0, ItemIds::SPRUCE_SIGN, TileSign::class);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_SIGN, LegacyIds::BIRCH_STANDING_SIGN, 0, ItemIds::BIRCH_SIGN, TileSign::class);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_SIGN, LegacyIds::JUNGLE_STANDING_SIGN, 0, ItemIds::JUNGLE_SIGN, TileSign::class);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_SIGN, LegacyIds::ACACIA_STANDING_SIGN, 0, ItemIds::ACACIA_SIGN, TileSign::class);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_SIGN, LegacyIds::DARKOAK_STANDING_SIGN, 0, ItemIds::DARKOAK_SIGN, TileSign::class);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenWallSignIdentifier(TreeType $treeType) : BID{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_WALL_SIGN, LegacyIds::WALL_SIGN, 0, ItemIds::SIGN, TileSign::class);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_WALL_SIGN, LegacyIds::SPRUCE_WALL_SIGN, 0, ItemIds::SPRUCE_SIGN, TileSign::class);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_WALL_SIGN, LegacyIds::BIRCH_WALL_SIGN, 0, ItemIds::BIRCH_SIGN, TileSign::class);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_WALL_SIGN, LegacyIds::JUNGLE_WALL_SIGN, 0, ItemIds::JUNGLE_SIGN, TileSign::class);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_WALL_SIGN, LegacyIds::ACACIA_WALL_SIGN, 0, ItemIds::ACACIA_SIGN, TileSign::class);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_WALL_SIGN, LegacyIds::DARKOAK_WALL_SIGN, 0, ItemIds::DARKOAK_SIGN, TileSign::class);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenTrapdoorIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_TRAPDOOR, LegacyIds::WOODEN_TRAPDOOR, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_TRAPDOOR, LegacyIds::SPRUCE_TRAPDOOR, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_TRAPDOOR, LegacyIds::BIRCH_TRAPDOOR, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_TRAPDOOR, LegacyIds::JUNGLE_TRAPDOOR, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_TRAPDOOR, LegacyIds::ACACIA_TRAPDOOR, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_TRAPDOOR, LegacyIds::DARK_OAK_TRAPDOOR, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenButtonIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_BUTTON, LegacyIds::WOODEN_BUTTON, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_BUTTON, LegacyIds::SPRUCE_BUTTON, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_BUTTON, LegacyIds::BIRCH_BUTTON, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_BUTTON, LegacyIds::JUNGLE_BUTTON, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_BUTTON, LegacyIds::ACACIA_BUTTON, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_BUTTON, LegacyIds::DARK_OAK_BUTTON, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenPressurePlateIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_PRESSURE_PLATE, LegacyIds::WOODEN_PRESSURE_PLATE, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_PRESSURE_PLATE, LegacyIds::SPRUCE_PRESSURE_PLATE, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_PRESSURE_PLATE, LegacyIds::BIRCH_PRESSURE_PLATE, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_PRESSURE_PLATE, LegacyIds::JUNGLE_PRESSURE_PLATE, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_PRESSURE_PLATE, LegacyIds::ACACIA_PRESSURE_PLATE, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_PRESSURE_PLATE, LegacyIds::DARK_OAK_PRESSURE_PLATE, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenDoorIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_DOOR, LegacyIds::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_DOOR, LegacyIds::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_DOOR, LegacyIds::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_DOOR, LegacyIds::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_DOOR, LegacyIds::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_DOOR, LegacyIds::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenFenceGateIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_FENCE_GATE, LegacyIds::OAK_FENCE_GATE, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_FENCE_GATE, LegacyIds::SPRUCE_FENCE_GATE, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_FENCE_GATE, LegacyIds::BIRCH_FENCE_GATE, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_FENCE_GATE, LegacyIds::JUNGLE_FENCE_GATE, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_FENCE_GATE, LegacyIds::ACACIA_FENCE_GATE, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_FENCE_GATE, LegacyIds::DARK_OAK_FENCE_GATE, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenStairsIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_STAIRS, LegacyIds::OAK_STAIRS, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_STAIRS, LegacyIds::SPRUCE_STAIRS, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_STAIRS, LegacyIds::BIRCH_STAIRS, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_STAIRS, LegacyIds::JUNGLE_STAIRS, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_STAIRS, LegacyIds::ACACIA_STAIRS, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_STAIRS, LegacyIds::DARK_OAK_STAIRS, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getStrippedLogIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::STRIPPED_OAK_LOG, LegacyIds::STRIPPED_OAK_LOG, 0);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::STRIPPED_SPRUCE_LOG, LegacyIds::STRIPPED_SPRUCE_LOG, 0);
			case TreeType::BIRCH()->id():
				return new BID(Ids::STRIPPED_BIRCH_LOG, LegacyIds::STRIPPED_BIRCH_LOG, 0);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::STRIPPED_JUNGLE_LOG, LegacyIds::STRIPPED_JUNGLE_LOG, 0);
			case TreeType::ACACIA()->id():
				return new BID(Ids::STRIPPED_ACACIA_LOG, LegacyIds::STRIPPED_ACACIA_LOG, 0);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::STRIPPED_DARK_OAK_LOG, LegacyIds::STRIPPED_DARK_OAK_LOG, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getGlazedTerracottaIdentifier(DyeColor $color) : BlockIdentifier{
		switch($color->id()){
			case DyeColor::WHITE()->id():
				return new BID(Ids::WHITE_GLAZED_TERRACOTTA, LegacyIds::WHITE_GLAZED_TERRACOTTA, 0);
			case DyeColor::ORANGE()->id():
				return new BID(Ids::ORANGE_GLAZED_TERRACOTTA, LegacyIds::ORANGE_GLAZED_TERRACOTTA, 0);
			case DyeColor::MAGENTA()->id():
				return new BID(Ids::MAGENTA_GLAZED_TERRACOTTA, LegacyIds::MAGENTA_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIGHT_BLUE()->id():
				return new BID(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA, LegacyIds::LIGHT_BLUE_GLAZED_TERRACOTTA, 0);
			case DyeColor::YELLOW()->id():
				return new BID(Ids::YELLOW_GLAZED_TERRACOTTA, LegacyIds::YELLOW_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIME()->id():
				return new BID(Ids::LIME_GLAZED_TERRACOTTA, LegacyIds::LIME_GLAZED_TERRACOTTA, 0);
			case DyeColor::PINK()->id():
				return new BID(Ids::PINK_GLAZED_TERRACOTTA, LegacyIds::PINK_GLAZED_TERRACOTTA, 0);
			case DyeColor::GRAY()->id():
				return new BID(Ids::GRAY_GLAZED_TERRACOTTA, LegacyIds::GRAY_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIGHT_GRAY()->id():
				return new BID(Ids::LIGHT_GRAY_GLAZED_TERRACOTTA, LegacyIds::SILVER_GLAZED_TERRACOTTA, 0);
			case DyeColor::CYAN()->id():
				return new BID(Ids::CYAN_GLAZED_TERRACOTTA, LegacyIds::CYAN_GLAZED_TERRACOTTA, 0);
			case DyeColor::PURPLE()->id():
				return new BID(Ids::PURPLE_GLAZED_TERRACOTTA, LegacyIds::PURPLE_GLAZED_TERRACOTTA, 0);
			case DyeColor::BLUE()->id():
				return new BID(Ids::BLUE_GLAZED_TERRACOTTA, LegacyIds::BLUE_GLAZED_TERRACOTTA, 0);
			case DyeColor::BROWN()->id():
				return new BID(Ids::BROWN_GLAZED_TERRACOTTA, LegacyIds::BROWN_GLAZED_TERRACOTTA, 0);
			case DyeColor::GREEN()->id():
				return new BID(Ids::GREEN_GLAZED_TERRACOTTA, LegacyIds::GREEN_GLAZED_TERRACOTTA, 0);
			case DyeColor::RED()->id():
				return new BID(Ids::RED_GLAZED_TERRACOTTA, LegacyIds::RED_GLAZED_TERRACOTTA, 0);
			case DyeColor::BLACK()->id():
				return new BID(Ids::BLACK_GLAZED_TERRACOTTA, LegacyIds::BLACK_GLAZED_TERRACOTTA, 0);
		}
		throw new AssumptionFailedError("Switch should cover all colours");
	}

	public static function getStoneSlabIdentifier(int $blockTypeId, int $stoneSlabId, int $meta) : BlockIdentifierFlattened{
		$id = [
			1 => [LegacyIds::STONE_SLAB, LegacyIds::DOUBLE_STONE_SLAB],
			2 => [LegacyIds::STONE_SLAB2, LegacyIds::DOUBLE_STONE_SLAB2],
			3 => [LegacyIds::STONE_SLAB3, LegacyIds::DOUBLE_STONE_SLAB3],
			4 => [LegacyIds::STONE_SLAB4, LegacyIds::DOUBLE_STONE_SLAB4]
		][$stoneSlabId] ?? null;
		if($id === null){
			throw new \InvalidArgumentException("Stone slab type should be 1, 2, 3 or 4");
		}
		return new BlockIdentifierFlattened($blockTypeId, $id[0], [$id[1]], $meta);
	}
}
