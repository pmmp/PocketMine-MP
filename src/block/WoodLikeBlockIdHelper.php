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
use pocketmine\block\utils\LeavesType;
use pocketmine\block\utils\SaplingType;
use pocketmine\block\utils\WoodType;
use pocketmine\item\VanillaItems;
use pocketmine\utils\AssumptionFailedError;

/**
 * All wood-like blocks have different IDs for different wood types.
 *
 * We can't make these dynamic, because some types of wood have different type properties (e.g. crimson and warped planks
 * are not flammable, but all other planks are).
 *
 * In the future, it's entirely within the realm of reason that the other types of wood may differ in other ways, such
 * as flammability, hardness, required tool tier, etc.
 * Therefore, to stay on the safe side of Mojang, wood-like blocks have static types. This does unfortunately generate
 * a lot of ugly code.
 *
 * @internal
 */
final class WoodLikeBlockIdHelper{

	public static function getPlanksIdentifier(WoodType $type) : BID{
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

	public static function getFenceIdentifier(WoodType $type) : BID{
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

	public static function getSlabIdentifier(WoodType $type) : BID{
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
		return new BID(match($treeType->id()){
			WoodType::OAK()->id() => Ids::OAK_LOG,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_LOG,
			WoodType::BIRCH()->id() => Ids::BIRCH_LOG,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_LOG,
			WoodType::ACACIA()->id() => Ids::ACACIA_LOG,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_LOG,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_LOG,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_STEM,
			WoodType::WARPED()->id() => Ids::WARPED_STEM,
			default => throw new AssumptionFailedError("All tree types should be covered")
		});
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

	public static function getLeavesIdentifier(LeavesType $leavesType) : BID{
		return new BID(match($leavesType->id()){
			LeavesType::OAK()->id() => Ids::OAK_LEAVES,
			LeavesType::SPRUCE()->id() => Ids::SPRUCE_LEAVES,
			LeavesType::BIRCH()->id() => Ids::BIRCH_LEAVES,
			LeavesType::JUNGLE()->id() => Ids::JUNGLE_LEAVES,
			LeavesType::ACACIA()->id() => Ids::ACACIA_LEAVES,
			LeavesType::DARK_OAK()->id() => Ids::DARK_OAK_LEAVES,
			LeavesType::MANGROVE()->id() => Ids::MANGROVE_LEAVES,
			LeavesType::AZALEA()->id() => Ids::AZALEA_LEAVES,
			LeavesType::FLOWERING_AZALEA()->id() => Ids::FLOWERING_AZALEA_LEAVES,
			default => throw new AssumptionFailedError("All leaves types should be covered")
		});
	}

	public static function getSaplingIdentifier(SaplingType $treeType) : BID{
		return new BID(match($treeType->id()){
			SaplingType::OAK()->id() => Ids::OAK_SAPLING,
			SaplingType::SPRUCE()->id() => Ids::SPRUCE_SAPLING,
			SaplingType::BIRCH()->id() => Ids::BIRCH_SAPLING,
			SaplingType::JUNGLE()->id() => Ids::JUNGLE_SAPLING,
			SaplingType::ACACIA()->id() => Ids::ACACIA_SAPLING,
			SaplingType::DARK_OAK()->id() => Ids::DARK_OAK_SAPLING,
			default => throw new AssumptionFailedError("All tree types should be covered")
		});
	}

	/**
	 * @return BID[]|\Closure[]
	 * @phpstan-return array{BID, BID, \Closure() : \pocketmine\item\Item}
	 */
	public static function getSignInfo(WoodType $treeType) : array{
		switch($treeType->id()){
			case WoodType::OAK()->id():
				return [
					new BID(Ids::OAK_SIGN, TileSign::class),
					new BID(Ids::OAK_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::OAK_SIGN()
				];
			case WoodType::SPRUCE()->id():
				return [
					new BID(Ids::SPRUCE_SIGN, TileSign::class),
					new BID(Ids::SPRUCE_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::SPRUCE_SIGN()
				];
			case WoodType::BIRCH()->id():
				return [
					new BID(Ids::BIRCH_SIGN, TileSign::class),
					new BID(Ids::BIRCH_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::BIRCH_SIGN()
				];
			case WoodType::JUNGLE()->id():
				return [
					new BID(Ids::JUNGLE_SIGN, TileSign::class),
					new BID(Ids::JUNGLE_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::JUNGLE_SIGN()
				];
			case WoodType::ACACIA()->id():
				return [
					new BID(Ids::ACACIA_SIGN, TileSign::class),
					new BID(Ids::ACACIA_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::ACACIA_SIGN()
				];
			case WoodType::DARK_OAK()->id():
				return [
					new BID(Ids::DARK_OAK_SIGN, TileSign::class),
					new BID(Ids::DARK_OAK_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::DARK_OAK_SIGN()
				];
			case WoodType::MANGROVE()->id():
				return [
					new BID(Ids::MANGROVE_SIGN, TileSign::class),
					new BID(Ids::MANGROVE_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::MANGROVE_SIGN()
				];
			case WoodType::CRIMSON()->id():
				return [
					new BID(Ids::CRIMSON_SIGN, TileSign::class),
					new BID(Ids::CRIMSON_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::CRIMSON_SIGN()
				];
			case WoodType::WARPED()->id():
				return [
					new BID(Ids::WARPED_SIGN, TileSign::class),
					new BID(Ids::WARPED_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::WARPED_SIGN()
				];

		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getTrapdoorIdentifier(WoodType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			WoodType::OAK()->id() => Ids::OAK_TRAPDOOR,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_TRAPDOOR,
			WoodType::BIRCH()->id() => Ids::BIRCH_TRAPDOOR,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_TRAPDOOR,
			WoodType::ACACIA()->id() => Ids::ACACIA_TRAPDOOR,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_TRAPDOOR,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_TRAPDOOR,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_TRAPDOOR,
			WoodType::WARPED()->id() => Ids::WARPED_TRAPDOOR,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getButtonIdentifier(WoodType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			WoodType::OAK()->id() => Ids::OAK_BUTTON,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_BUTTON,
			WoodType::BIRCH()->id() => Ids::BIRCH_BUTTON,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_BUTTON,
			WoodType::ACACIA()->id() => Ids::ACACIA_BUTTON,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_BUTTON,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_BUTTON,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_BUTTON,
			WoodType::WARPED()->id() => Ids::WARPED_BUTTON,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getPressurePlateIdentifier(WoodType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			WoodType::OAK()->id() => Ids::OAK_PRESSURE_PLATE,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_PRESSURE_PLATE,
			WoodType::BIRCH()->id() => Ids::BIRCH_PRESSURE_PLATE,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_PRESSURE_PLATE,
			WoodType::ACACIA()->id() => Ids::ACACIA_PRESSURE_PLATE,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_PRESSURE_PLATE,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_PRESSURE_PLATE,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_PRESSURE_PLATE,
			WoodType::WARPED()->id() => Ids::WARPED_PRESSURE_PLATE,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getDoorIdentifier(WoodType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			WoodType::OAK()->id() => Ids::OAK_DOOR,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_DOOR,
			WoodType::BIRCH()->id() => Ids::BIRCH_DOOR,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_DOOR,
			WoodType::ACACIA()->id() => Ids::ACACIA_DOOR,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_DOOR,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_DOOR,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_DOOR,
			WoodType::WARPED()->id() => Ids::WARPED_DOOR,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getFenceGateIdentifier(WoodType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			WoodType::OAK()->id() => Ids::OAK_FENCE_GATE,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_FENCE_GATE,
			WoodType::BIRCH()->id() => Ids::BIRCH_FENCE_GATE,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_FENCE_GATE,
			WoodType::ACACIA()->id() => Ids::ACACIA_FENCE_GATE,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_FENCE_GATE,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_FENCE_GATE,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_FENCE_GATE,
			WoodType::WARPED()->id() => Ids::WARPED_FENCE_GATE,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getStairsIdentifier(WoodType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			WoodType::OAK()->id() => Ids::OAK_STAIRS,
			WoodType::SPRUCE()->id() => Ids::SPRUCE_STAIRS,
			WoodType::BIRCH()->id() => Ids::BIRCH_STAIRS,
			WoodType::JUNGLE()->id() => Ids::JUNGLE_STAIRS,
			WoodType::ACACIA()->id() => Ids::ACACIA_STAIRS,
			WoodType::DARK_OAK()->id() => Ids::DARK_OAK_STAIRS,
			WoodType::MANGROVE()->id() => Ids::MANGROVE_STAIRS,
			WoodType::CRIMSON()->id() => Ids::CRIMSON_STAIRS,
			WoodType::WARPED()->id() => Ids::WARPED_STAIRS,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}
}
