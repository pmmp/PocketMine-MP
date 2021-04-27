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
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\TreeType;
use pocketmine\item\ItemIds;
use pocketmine\utils\AssumptionFailedError;

final class BlockLegacyIdHelper{

	public static function getWoodenFloorSignIdentifier(TreeType $treeType) : BID{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::SIGN_POST, 0, ItemIds::SIGN, TileSign::class);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_STANDING_SIGN, 0, ItemIds::SPRUCE_SIGN, TileSign::class);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_STANDING_SIGN, 0, ItemIds::BIRCH_SIGN, TileSign::class);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_STANDING_SIGN, 0, ItemIds::JUNGLE_SIGN, TileSign::class);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_STANDING_SIGN,0, ItemIds::ACACIA_SIGN, TileSign::class);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARKOAK_STANDING_SIGN, 0, ItemIds::DARKOAK_SIGN, TileSign::class);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenWallSignIdentifier(TreeType $treeType) : BID{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::WALL_SIGN, 0, ItemIds::SIGN, TileSign::class);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_WALL_SIGN, 0, ItemIds::SPRUCE_SIGN, TileSign::class);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_WALL_SIGN, 0, ItemIds::BIRCH_SIGN, TileSign::class);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_WALL_SIGN, 0, ItemIds::JUNGLE_SIGN, TileSign::class);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_WALL_SIGN, 0, ItemIds::ACACIA_SIGN, TileSign::class);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARKOAK_WALL_SIGN, 0, ItemIds::DARKOAK_SIGN, TileSign::class);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenTrapdoorIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BlockIdentifier(Ids::WOODEN_TRAPDOOR, 0);
			case TreeType::SPRUCE()->id():
				return new BlockIdentifier(Ids::SPRUCE_TRAPDOOR, 0);
			case TreeType::BIRCH()->id():
				return new BlockIdentifier(Ids::BIRCH_TRAPDOOR, 0);
			case TreeType::JUNGLE()->id():
				return new BlockIdentifier(Ids::JUNGLE_TRAPDOOR, 0);
			case TreeType::ACACIA()->id():
				return new BlockIdentifier(Ids::ACACIA_TRAPDOOR, 0);
			case TreeType::DARK_OAK()->id():
				return new BlockIdentifier(Ids::DARK_OAK_TRAPDOOR, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenButtonIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BlockIdentifier(Ids::WOODEN_BUTTON, 0);
			case TreeType::SPRUCE()->id():
				return new BlockIdentifier(Ids::SPRUCE_BUTTON, 0);
			case TreeType::BIRCH()->id():
				return new BlockIdentifier(Ids::BIRCH_BUTTON, 0);
			case TreeType::JUNGLE()->id():
				return new BlockIdentifier(Ids::JUNGLE_BUTTON, 0);
			case TreeType::ACACIA()->id():
				return new BlockIdentifier(Ids::ACACIA_BUTTON, 0);
			case TreeType::DARK_OAK()->id():
				return new BlockIdentifier(Ids::DARK_OAK_BUTTON, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenPressurePlateIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BlockIdentifier(Ids::WOODEN_PRESSURE_PLATE, 0);
			case TreeType::SPRUCE()->id():
				return new BlockIdentifier(Ids::SPRUCE_PRESSURE_PLATE, 0);
			case TreeType::BIRCH()->id():
				return new BlockIdentifier(Ids::BIRCH_PRESSURE_PLATE, 0);
			case TreeType::JUNGLE()->id():
				return new BlockIdentifier(Ids::JUNGLE_PRESSURE_PLATE, 0);
			case TreeType::ACACIA()->id():
				return new BlockIdentifier(Ids::ACACIA_PRESSURE_PLATE, 0);
			case TreeType::DARK_OAK()->id():
				return new BlockIdentifier(Ids::DARK_OAK_PRESSURE_PLATE, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenDoorIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenFenceIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BlockIdentifier(Ids::OAK_FENCE_GATE, 0);
			case TreeType::SPRUCE()->id():
				return new BlockIdentifier(Ids::SPRUCE_FENCE_GATE, 0);
			case TreeType::BIRCH()->id():
				return new BlockIdentifier(Ids::BIRCH_FENCE_GATE, 0);
			case TreeType::JUNGLE()->id():
				return new BlockIdentifier(Ids::JUNGLE_FENCE_GATE, 0);
			case TreeType::ACACIA()->id():
				return new BlockIdentifier(Ids::ACACIA_FENCE_GATE, 0);
			case TreeType::DARK_OAK()->id():
				return new BlockIdentifier(Ids::DARK_OAK_FENCE_GATE, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenStairsIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BlockIdentifier(Ids::OAK_STAIRS, 0);
			case TreeType::SPRUCE()->id():
				return new BlockIdentifier(Ids::SPRUCE_STAIRS, 0);
			case TreeType::BIRCH()->id():
				return new BlockIdentifier(Ids::BIRCH_STAIRS, 0);
			case TreeType::JUNGLE()->id():
				return new BlockIdentifier(Ids::JUNGLE_STAIRS, 0);
			case TreeType::ACACIA()->id():
				return new BlockIdentifier(Ids::ACACIA_STAIRS, 0);
			case TreeType::DARK_OAK()->id():
				return new BlockIdentifier(Ids::DARK_OAK_STAIRS, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getStrippedLogIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BlockIdentifier(Ids::STRIPPED_OAK_LOG, 0);
			case TreeType::SPRUCE()->id():
				return new BlockIdentifier(Ids::STRIPPED_SPRUCE_LOG, 0);
			case TreeType::BIRCH()->id():
				return new BlockIdentifier(Ids::STRIPPED_BIRCH_LOG, 0);
			case TreeType::JUNGLE()->id():
				return new BlockIdentifier(Ids::STRIPPED_JUNGLE_LOG, 0);
			case TreeType::ACACIA()->id():
				return new BlockIdentifier(Ids::STRIPPED_ACACIA_LOG, 0);
			case TreeType::DARK_OAK()->id():
				return new BlockIdentifier(Ids::STRIPPED_DARK_OAK_LOG, 0);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getGlazedTerracottaIdentifier(DyeColor $color) : BlockIdentifier{
		switch($color->id()){
			case DyeColor::WHITE()->id():
				return new BlockIdentifier(Ids::WHITE_GLAZED_TERRACOTTA, 0);
			case DyeColor::ORANGE()->id():
				return new BlockIdentifier(Ids::ORANGE_GLAZED_TERRACOTTA, 0);
			case DyeColor::MAGENTA()->id():
				return new BlockIdentifier(Ids::MAGENTA_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIGHT_BLUE()->id():
				return new BlockIdentifier(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA, 0);
			case DyeColor::YELLOW()->id():
				return new BlockIdentifier(Ids::YELLOW_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIME()->id():
				return new BlockIdentifier(Ids::LIME_GLAZED_TERRACOTTA, 0);
			case DyeColor::PINK()->id():
				return new BlockIdentifier(Ids::PINK_GLAZED_TERRACOTTA, 0);
			case DyeColor::GRAY()->id():
				return new BlockIdentifier(Ids::GRAY_GLAZED_TERRACOTTA, 0);
			case DyeColor::LIGHT_GRAY()->id():
				return new BlockIdentifier(Ids::SILVER_GLAZED_TERRACOTTA, 0);
			case DyeColor::CYAN()->id():
				return new BlockIdentifier(Ids::CYAN_GLAZED_TERRACOTTA, 0);
			case DyeColor::PURPLE()->id():
				return new BlockIdentifier(Ids::PURPLE_GLAZED_TERRACOTTA, 0);
			case DyeColor::BLUE()->id():
				return new BlockIdentifier(Ids::BLUE_GLAZED_TERRACOTTA, 0);
			case DyeColor::BROWN()->id():
				return new BlockIdentifier(Ids::BROWN_GLAZED_TERRACOTTA, 0);
			case DyeColor::GREEN()->id():
				return new BlockIdentifier(Ids::GREEN_GLAZED_TERRACOTTA, 0);
			case DyeColor::RED()->id():
				return new BlockIdentifier(Ids::RED_GLAZED_TERRACOTTA, 0);
			case DyeColor::BLACK()->id():
				return new BlockIdentifier(Ids::BLACK_GLAZED_TERRACOTTA, 0);
		}
		throw new AssumptionFailedError("Switch should cover all colours");
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
		return new BlockIdentifierFlattened($id[0], $id[1], $meta);
	}

	public static function getWallCoralFanIdentifier(CoralType $type) : BlockIdentifier{
		switch($type->id()){
			case CoralType::TUBE()->id(): return new BID(Ids::CORAL_FAN_HANG, 0, ItemIds::CORAL_FAN);
			case CoralType::BRAIN()->id(): return new BID(Ids::CORAL_FAN_HANG, 1, ItemIds::CORAL_FAN);
			case CoralType::BUBBLE()->id(): return new BID(Ids::CORAL_FAN_HANG2, 0, ItemIds::CORAL_FAN);
			case CoralType::FIRE()->id(): return new BID(Ids::CORAL_FAN_HANG2, 1, ItemIds::CORAL_FAN);
			case CoralType::HORN()->id(): return new BID(Ids::CORAL_FAN_HANG3, 0, ItemIds::CORAL_FAN);
		}
		throw new AssumptionFailedError("Switch should cover all coral types");
	}
}
