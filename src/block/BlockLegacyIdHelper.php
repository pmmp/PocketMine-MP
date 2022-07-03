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
use pocketmine\block\utils\TreeType;
use pocketmine\block\utils\WoodType;
use pocketmine\item\VanillaItems;
use pocketmine\utils\AssumptionFailedError;

final class BlockLegacyIdHelper{

	public static function getWoodenPlanksIdentifier(WoodType $type) : BID{
		return new BID(match($type->id()){
			WoodType::OAK()->id() => Ids::OAK_PLANKS,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_PLANKS,
			WoodType::BIRCH()->id() => Ids::BIRCH_PLANKS,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_PLANKS,
			WoodType::ACACIA()->id() => Ids::ACACIA_PLANKS,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_PLANKS,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_PLANKS,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_PLANKS,
			WoodType::WARPED()->id() => Ids::WARPED_PLANKS,
			default => throw new AssumptionFailedError("All tree types should be covered")
		});
	}

	public static function getWoodenFenceIdentifier(WoodType $type) : BID{
		return new BID(match($type->id()){
			WoodType::OAK()->id() => Ids::OAK_FENCE,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_FENCE,
			WoodType::BIRCH()->id() => Ids::BIRCH_FENCE,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_FENCE,
			WoodType::ACACIA()->id() => Ids::ACACIA_FENCE,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_FENCE,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_FENCE,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_FENCE,
			WoodType::WARPED()->id() => Ids::WARPED_FENCE,
			default => throw new AssumptionFailedError("All tree types should be covered")
		});
	}

	public static function getWoodenSlabIdentifier(WoodType $type) : BID{
		return new BID(match($type->id()){
			WoodType::OAK()->id() => Ids::OAK_SLAB,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_SLAB,
			WoodType::BIRCH()->id() => Ids::BIRCH_SLAB,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_SLAB,
			WoodType::ACACIA()->id() => Ids::ACACIA_SLAB,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_SLAB,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_SLAB,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_SLAB,
			WoodType::WARPED()->id() => Ids::WARPED_SLAB,
			default => throw new AssumptionFailedError("All tree types should be covered")
		});
	}

	public static function getLogIdentifier(WoodType $treeType) : BID{
		return match($treeType->id()){
			WoodType::OAK()->id() => new BID(Ids::OAK_LOG),
			WoodType::SPRUCE()->id() => new BID(Ids::SPRUCE_LOG),
			WoodType::BIRCH()->id() => new BID(Ids::BIRCH_LOG),
			WoodType::JUNGLE()->id() => new BID(Ids::JUNGLE_LOG),
			WoodType::ACACIA()->id() => new BID(Ids::ACACIA_LOG),
			WoodType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_LOG),
			WoodType::MANGROVE()->id() => new BID(Ids::MANGROVE_LOG),
			WoodType::CRIMSON()->id() => new BID(Ids::CRIMSON_STEM),
			WoodType::WARPED()->id() => new BID(Ids::WARPED_STEM),
			default => throw new AssumptionFailedError("All tree types should be covered")
		};
	}

	public static function getAllSidedLogIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType->id()){
			WoodType::OAK()->id() => Ids::OAK_WOOD,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_WOOD,
			WoodType::BIRCH()->id() => Ids::BIRCH_WOOD,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_WOOD,
			WoodType::ACACIA()->id() => Ids::ACACIA_WOOD,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_WOOD,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_WOOD,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_HYPHAE,
			WoodType::WARPED()->id() => Ids::WARPED_HYPHAE,
			default => throw new AssumptionFailedError("All tree types should be covered")
		});
	}

	public static function getLeavesIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_LEAVES),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_LEAVES),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_LEAVES),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_LEAVES),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_LEAVES),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_LEAVES),
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
		});
	}

	/**
	 * @return BID[]|\Closure[]
	 * @phpstan-return array{BID, BID, \Closure() : \pocketmine\item\Item}
	 */
	public static function getWoodenSignInfo(TreeType $treeType) : array{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return [
					new BID(Ids::OAK_SIGN, TileSign::class),
					new BID(Ids::OAK_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::OAK_SIGN()
				];
			case TreeType::SPRUCE()->id():
				return [
					new BID(Ids::SPRUCE_SIGN, TileSign::class),
					new BID(Ids::SPRUCE_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::SPRUCE_SIGN()
				];
			case TreeType::BIRCH()->id():
				return [
					new BID(Ids::BIRCH_SIGN, TileSign::class),
					new BID(Ids::BIRCH_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::BIRCH_SIGN()
				];
			case TreeType::JUNGLE()->id():
				return [
					new BID(Ids::JUNGLE_SIGN, TileSign::class),
					new BID(Ids::JUNGLE_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::JUNGLE_SIGN()
				];
			case TreeType::ACACIA()->id():
				return [
					new BID(Ids::ACACIA_SIGN, TileSign::class),
					new BID(Ids::ACACIA_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::ACACIA_SIGN()
				];
			case TreeType::DARK_OAK()->id():
				return [
					new BID(Ids::DARK_OAK_SIGN, TileSign::class),
					new BID(Ids::DARK_OAK_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::DARK_OAK_SIGN()
				];
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenTrapdoorIdentifier(WoodType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case WoodType::OAK()->id():
				return new BID(Ids::OAK_TRAPDOOR);
			case WoodType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_TRAPDOOR);
			case WoodType::BIRCH()->id():
				return new BID(Ids::BIRCH_TRAPDOOR);
			case WoodType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_TRAPDOOR);
			case WoodType::ACACIA()->id():
				return new BID(Ids::ACACIA_TRAPDOOR);
			case WoodType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_TRAPDOOR);
			case WoodType::MANGROVE()->id():
				return new BID(Ids::MANGROVE_TRAPDOOR);
			case WoodType::CRIMSON()->id():
				return new BID(Ids::CRIMSON_TRAPDOOR);
			case WoodType::WARPED()->id():
				return new BID(Ids::WARPED_TRAPDOOR);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenButtonIdentifier(WoodType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case WoodType::OAK()->id():
				return new BID(Ids::OAK_BUTTON);
			case WoodType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_BUTTON);
			case WoodType::BIRCH()->id():
				return new BID(Ids::BIRCH_BUTTON);
			case WoodType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_BUTTON);
			case WoodType::ACACIA()->id():
				return new BID(Ids::ACACIA_BUTTON);
			case WoodType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_BUTTON);
			case WoodType::MANGROVE()->id():
				return new BID(Ids::MANGROVE_BUTTON);
			case WoodType::CRIMSON()->id():
				return new BID(Ids::CRIMSON_BUTTON);
			case WoodType::WARPED()->id():
				return new BID(Ids::WARPED_BUTTON);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenPressurePlateIdentifier(WoodType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case WoodType::OAK()->id():
				return new BID(Ids::OAK_PRESSURE_PLATE);
			case WoodType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_PRESSURE_PLATE);
			case WoodType::BIRCH()->id():
				return new BID(Ids::BIRCH_PRESSURE_PLATE);
			case WoodType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_PRESSURE_PLATE);
			case WoodType::ACACIA()->id():
				return new BID(Ids::ACACIA_PRESSURE_PLATE);
			case WoodType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_PRESSURE_PLATE);
			case WoodType::MANGROVE()->id():
				return new BID(Ids::MANGROVE_PRESSURE_PLATE);
			case WoodType::CRIMSON()->id():
				return new BID(Ids::CRIMSON_PRESSURE_PLATE);
			case WoodType::WARPED()->id():
				return new BID(Ids::WARPED_PRESSURE_PLATE);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenDoorIdentifier(WoodType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case WoodType::OAK()->id():
				return new BID(Ids::OAK_DOOR);
			case WoodType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_DOOR);
			case WoodType::BIRCH()->id():
				return new BID(Ids::BIRCH_DOOR);
			case WoodType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_DOOR);
			case WoodType::ACACIA()->id():
				return new BID(Ids::ACACIA_DOOR);
			case WoodType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_DOOR);
			case WoodType::MANGROVE()->id():
				return new BID(Ids::MANGROVE_DOOR);
			case WoodType::CRIMSON()->id():
				return new BID(Ids::CRIMSON_DOOR);
			case WoodType::WARPED()->id():
				return new BID(Ids::WARPED_DOOR);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenFenceGateIdentifier(WoodType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case WoodType::OAK()->id():
				return new BID(Ids::OAK_FENCE_GATE);
			case WoodType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_FENCE_GATE);
			case WoodType::BIRCH()->id():
				return new BID(Ids::BIRCH_FENCE_GATE);
			case WoodType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_FENCE_GATE);
			case WoodType::ACACIA()->id():
				return new BID(Ids::ACACIA_FENCE_GATE);
			case WoodType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_FENCE_GATE);
			case WoodType::MANGROVE()->id():
				return new BID(Ids::MANGROVE_FENCE_GATE);
			case WoodType::CRIMSON()->id():
				return new BID(Ids::CRIMSON_FENCE_GATE);
			case WoodType::WARPED()->id():
				return new BID(Ids::WARPED_FENCE_GATE);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenStairsIdentifier(WoodType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case WoodType::OAK()->id():
				return new BID(Ids::OAK_STAIRS);
			case WoodType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_STAIRS);
			case WoodType::BIRCH()->id():
				return new BID(Ids::BIRCH_STAIRS);
			case WoodType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_STAIRS);
			case WoodType::ACACIA()->id():
				return new BID(Ids::ACACIA_STAIRS);
			case WoodType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_STAIRS);
			case WoodType::MANGROVE()->id():
				return new BID(Ids::MANGROVE_STAIRS);
			case WoodType::CRIMSON()->id():
				return new BID(Ids::CRIMSON_STAIRS);
			case WoodType::WARPED()->id():
				return new BID(Ids::WARPED_STAIRS);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}
}
