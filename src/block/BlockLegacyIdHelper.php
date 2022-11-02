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
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\TreeType;
use pocketmine\item\ItemIds;
use pocketmine\utils\AssumptionFailedError;

final class BlockLegacyIdHelper{

	public static function getWoodenFloorSignIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::SIGN_POST, 0, ItemIds::SIGN, TileSign::class),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_STANDING_SIGN, 0, ItemIds::SPRUCE_SIGN, TileSign::class),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_STANDING_SIGN, 0, ItemIds::BIRCH_SIGN, TileSign::class),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_STANDING_SIGN, 0, ItemIds::JUNGLE_SIGN, TileSign::class),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_STANDING_SIGN,0, ItemIds::ACACIA_SIGN, TileSign::class),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARKOAK_STANDING_SIGN, 0, ItemIds::DARKOAK_SIGN, TileSign::class),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getWoodenWallSignIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::WALL_SIGN, 0, ItemIds::SIGN, TileSign::class),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_WALL_SIGN, 0, ItemIds::SPRUCE_SIGN, TileSign::class),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_WALL_SIGN, 0, ItemIds::BIRCH_SIGN, TileSign::class),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_WALL_SIGN, 0, ItemIds::JUNGLE_SIGN, TileSign::class),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_WALL_SIGN, 0, ItemIds::ACACIA_SIGN, TileSign::class),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARKOAK_WALL_SIGN, 0, ItemIds::DARKOAK_SIGN, TileSign::class),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getWoodenTrapdoorIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::WOODEN_TRAPDOOR, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_TRAPDOOR, 0),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_TRAPDOOR, 0),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_TRAPDOOR, 0),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_TRAPDOOR, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_TRAPDOOR, 0),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getWoodenButtonIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::WOODEN_BUTTON, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_BUTTON, 0),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_BUTTON, 0),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_BUTTON, 0),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_BUTTON, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_BUTTON, 0),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getWoodenPressurePlateIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::WOODEN_PRESSURE_PLATE, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_PRESSURE_PLATE, 0),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_PRESSURE_PLATE, 0),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_PRESSURE_PLATE, 0),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_PRESSURE_PLATE, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_PRESSURE_PLATE, 0),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getWoodenDoorIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR),
			TreeType::DARK_OAK()->id() =>new BID(Ids::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getWoodenFenceIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_FENCE_GATE, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_FENCE_GATE, 0),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_FENCE_GATE, 0),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_FENCE_GATE, 0),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_FENCE_GATE, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_FENCE_GATE, 0),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getWoodenStairsIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_STAIRS, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_STAIRS, 0),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_STAIRS, 0),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_STAIRS, 0),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_STAIRS, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_STAIRS, 0),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getStrippedLogIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::STRIPPED_OAK_LOG, 0),
			TreeType::SPRUCE()->id() => new BID(Ids::STRIPPED_SPRUCE_LOG, 0),
			TreeType::BIRCH()->id() => new BID(Ids::STRIPPED_BIRCH_LOG, 0),
			TreeType::JUNGLE()->id() => new BID(Ids::STRIPPED_JUNGLE_LOG, 0),
			TreeType::ACACIA()->id() => new BID(Ids::STRIPPED_ACACIA_LOG, 0),
			TreeType::DARK_OAK()->id() => new BID(Ids::STRIPPED_DARK_OAK_LOG, 0),
			default => throw new AssumptionFailedError("Match should cover all wood types"),
		};
	}

	public static function getGlazedTerracottaIdentifier(DyeColor $color) : BID{
		return match($color->id()){
			DyeColor::WHITE()->id() => new BID(Ids::WHITE_GLAZED_TERRACOTTA, 0),
			DyeColor::ORANGE()->id() => new BID(Ids::ORANGE_GLAZED_TERRACOTTA, 0),
			DyeColor::MAGENTA()->id() => new BID(Ids::MAGENTA_GLAZED_TERRACOTTA, 0),
			DyeColor::LIGHT_BLUE()->id() => new BID(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA, 0),
			DyeColor::YELLOW()->id() => new BID(Ids::YELLOW_GLAZED_TERRACOTTA, 0),
			DyeColor::LIME()->id() => new BID(Ids::LIME_GLAZED_TERRACOTTA, 0),
			DyeColor::PINK()->id() => new BID(Ids::PINK_GLAZED_TERRACOTTA, 0),
			DyeColor::GRAY()->id() => new BID(Ids::GRAY_GLAZED_TERRACOTTA, 0),
			DyeColor::LIGHT_GRAY()->id() => new BID(Ids::SILVER_GLAZED_TERRACOTTA, 0),
			DyeColor::CYAN()->id() => new BID(Ids::CYAN_GLAZED_TERRACOTTA, 0),
			DyeColor::PURPLE()->id() => new BID(Ids::PURPLE_GLAZED_TERRACOTTA, 0),
			DyeColor::BLUE()->id() => new BID(Ids::BLUE_GLAZED_TERRACOTTA, 0),
			DyeColor::BROWN()->id() => new BID(Ids::BROWN_GLAZED_TERRACOTTA, 0),
			DyeColor::GREEN()->id() => new BID(Ids::GREEN_GLAZED_TERRACOTTA, 0),
			DyeColor::RED()->id() => new BID(Ids::RED_GLAZED_TERRACOTTA, 0),
			DyeColor::BLACK()->id() => new BID(Ids::BLACK_GLAZED_TERRACOTTA, 0),
			default => throw new AssumptionFailedError("Match should cover all colours"),
		};
	}

	public static function getStoneSlabIdentifier(int $stoneSlabId, int $meta) : BlockIdentifierFlattened{
		$id = [
			1 => [Ids::STONE_SLAB, Ids::DOUBLE_STONE_SLAB],
			2 => [Ids::STONE_SLAB2, Ids::DOUBLE_STONE_SLAB2],
			3 => [Ids::STONE_SLAB3, Ids::DOUBLE_STONE_SLAB3],
			4 => [Ids::STONE_SLAB4, Ids::DOUBLE_STONE_SLAB4]
		][$stoneSlabId] ?? null;
		if($id === null){
			throw new \InvalidArgumentException("Stone slab type should be 1, 2, 3 or 4");
		}
		return new BlockIdentifierFlattened($id[0], [$id[1]], $meta);
	}
}
