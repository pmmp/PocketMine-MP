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
use pocketmine\block\utils\LogType;
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

	public static function getFenceIdentifier(LogType $type) : BID{
		return new BID(match($type->id()){
			LogType::OAK()->id() => Ids::OAK_FENCE,
			LogType::SPRUCE()->id() => Ids::SPRUCE_FENCE,
			LogType::BIRCH()->id() => Ids::BIRCH_FENCE,
			LogType::JUNGLE()->id() => Ids::JUNGLE_FENCE,
			LogType::ACACIA()->id() => Ids::ACACIA_FENCE,
			LogType::DARK_OAK()->id() => Ids::DARK_OAK_FENCE,
			LogType::MANGROVE()->id() => Ids::MANGROVE_FENCE,
			LogType::CRIMSON()->id() => Ids::CRIMSON_FENCE,
			LogType::WARPED()->id() => Ids::WARPED_FENCE,
			LogType::CHERRY()->id() => Ids::CHERRY_FENCE,
			default => throw new AssumptionFailedError("All tree types should be covered")
		});
	}

	public static function getLogIdentifier(LogType $treeType) : BID{
		return new BID(match($treeType->id()){
			LogType::OAK()->id() => Ids::OAK_LOG,
			LogType::SPRUCE()->id() => Ids::SPRUCE_LOG,
			LogType::BIRCH()->id() => Ids::BIRCH_LOG,
			LogType::JUNGLE()->id() => Ids::JUNGLE_LOG,
			LogType::ACACIA()->id() => Ids::ACACIA_LOG,
			LogType::DARK_OAK()->id() => Ids::DARK_OAK_LOG,
			LogType::MANGROVE()->id() => Ids::MANGROVE_LOG,
			LogType::CRIMSON()->id() => Ids::CRIMSON_STEM,
			LogType::WARPED()->id() => Ids::WARPED_STEM,
			LogType::CHERRY()->id() => Ids::CHERRY_LOG,
			default => throw new AssumptionFailedError("All tree types should be covered")
		});
	}

	public static function getAllSidedLogIdentifier(LogType $treeType) : BID{
		return new BID(match($treeType->id()){
			LogType::OAK()->id() => Ids::OAK_WOOD,
			LogType::SPRUCE()->id() => Ids::SPRUCE_WOOD,
			LogType::BIRCH()->id() => Ids::BIRCH_WOOD,
			LogType::JUNGLE()->id() => Ids::JUNGLE_WOOD,
			LogType::ACACIA()->id() => Ids::ACACIA_WOOD,
			LogType::DARK_OAK()->id() => Ids::DARK_OAK_WOOD,
			LogType::MANGROVE()->id() => Ids::MANGROVE_WOOD,
			LogType::CRIMSON()->id() => Ids::CRIMSON_HYPHAE,
			LogType::WARPED()->id() => Ids::WARPED_HYPHAE,
			LogType::CHERRY()->id() => Ids::CHERRY_WOOD,
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
			LeavesType::CHERRY()->id() => Ids::CHERRY_LEAVES,
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
	public static function getSignInfo(LogType $treeType) : array{
		switch($treeType->id()){
			case LogType::OAK()->id():
				return [
					new BID(Ids::OAK_SIGN, TileSign::class),
					new BID(Ids::OAK_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::OAK_SIGN()
				];
			case LogType::SPRUCE()->id():
				return [
					new BID(Ids::SPRUCE_SIGN, TileSign::class),
					new BID(Ids::SPRUCE_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::SPRUCE_SIGN()
				];
			case LogType::BIRCH()->id():
				return [
					new BID(Ids::BIRCH_SIGN, TileSign::class),
					new BID(Ids::BIRCH_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::BIRCH_SIGN()
				];
			case LogType::JUNGLE()->id():
				return [
					new BID(Ids::JUNGLE_SIGN, TileSign::class),
					new BID(Ids::JUNGLE_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::JUNGLE_SIGN()
				];
			case LogType::ACACIA()->id():
				return [
					new BID(Ids::ACACIA_SIGN, TileSign::class),
					new BID(Ids::ACACIA_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::ACACIA_SIGN()
				];
			case LogType::DARK_OAK()->id():
				return [
					new BID(Ids::DARK_OAK_SIGN, TileSign::class),
					new BID(Ids::DARK_OAK_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::DARK_OAK_SIGN()
				];
			case LogType::MANGROVE()->id():
				return [
					new BID(Ids::MANGROVE_SIGN, TileSign::class),
					new BID(Ids::MANGROVE_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::MANGROVE_SIGN()
				];
			case LogType::CRIMSON()->id():
				return [
					new BID(Ids::CRIMSON_SIGN, TileSign::class),
					new BID(Ids::CRIMSON_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::CRIMSON_SIGN()
				];
			case LogType::WARPED()->id():
				return [
					new BID(Ids::WARPED_SIGN, TileSign::class),
					new BID(Ids::WARPED_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::WARPED_SIGN()
				];
			case LogType::CHERRY()->id():
				return [
					new BID(Ids::CHERRY_SIGN, TileSign::class),
					new BID(Ids::CHERRY_WALL_SIGN, TileSign::class),
					fn() => VanillaItems::CHERRY_SIGN()
				];
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getTrapdoorIdentifier(LogType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			LogType::OAK()->id() => Ids::OAK_TRAPDOOR,
			LogType::SPRUCE()->id() => Ids::SPRUCE_TRAPDOOR,
			LogType::BIRCH()->id() => Ids::BIRCH_TRAPDOOR,
			LogType::JUNGLE()->id() => Ids::JUNGLE_TRAPDOOR,
			LogType::ACACIA()->id() => Ids::ACACIA_TRAPDOOR,
			LogType::DARK_OAK()->id() => Ids::DARK_OAK_TRAPDOOR,
			LogType::MANGROVE()->id() => Ids::MANGROVE_TRAPDOOR,
			LogType::CRIMSON()->id() => Ids::CRIMSON_TRAPDOOR,
			LogType::WARPED()->id() => Ids::WARPED_TRAPDOOR,
			LogType::CHERRY()->id() => Ids::CHERRY_TRAPDOOR,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getButtonIdentifier(LogType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			LogType::OAK()->id() => Ids::OAK_BUTTON,
			LogType::SPRUCE()->id() => Ids::SPRUCE_BUTTON,
			LogType::BIRCH()->id() => Ids::BIRCH_BUTTON,
			LogType::JUNGLE()->id() => Ids::JUNGLE_BUTTON,
			LogType::ACACIA()->id() => Ids::ACACIA_BUTTON,
			LogType::DARK_OAK()->id() => Ids::DARK_OAK_BUTTON,
			LogType::MANGROVE()->id() => Ids::MANGROVE_BUTTON,
			LogType::CRIMSON()->id() => Ids::CRIMSON_BUTTON,
			LogType::WARPED()->id() => Ids::WARPED_BUTTON,
			LogType::CHERRY()->id() => Ids::CHERRY_BUTTON,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getPressurePlateIdentifier(LogType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			LogType::OAK()->id() => Ids::OAK_PRESSURE_PLATE,
			LogType::SPRUCE()->id() => Ids::SPRUCE_PRESSURE_PLATE,
			LogType::BIRCH()->id() => Ids::BIRCH_PRESSURE_PLATE,
			LogType::JUNGLE()->id() => Ids::JUNGLE_PRESSURE_PLATE,
			LogType::ACACIA()->id() => Ids::ACACIA_PRESSURE_PLATE,
			LogType::DARK_OAK()->id() => Ids::DARK_OAK_PRESSURE_PLATE,
			LogType::MANGROVE()->id() => Ids::MANGROVE_PRESSURE_PLATE,
			LogType::CRIMSON()->id() => Ids::CRIMSON_PRESSURE_PLATE,
			LogType::WARPED()->id() => Ids::WARPED_PRESSURE_PLATE,
			LogType::CHERRY()->id() => Ids::CHERRY_PRESSURE_PLATE,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getDoorIdentifier(LogType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			LogType::OAK()->id() => Ids::OAK_DOOR,
			LogType::SPRUCE()->id() => Ids::SPRUCE_DOOR,
			LogType::BIRCH()->id() => Ids::BIRCH_DOOR,
			LogType::JUNGLE()->id() => Ids::JUNGLE_DOOR,
			LogType::ACACIA()->id() => Ids::ACACIA_DOOR,
			LogType::DARK_OAK()->id() => Ids::DARK_OAK_DOOR,
			LogType::MANGROVE()->id() => Ids::MANGROVE_DOOR,
			LogType::CRIMSON()->id() => Ids::CRIMSON_DOOR,
			LogType::WARPED()->id() => Ids::WARPED_DOOR,
			LogType::CHERRY()->id() => Ids::CHERRY_DOOR,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}

	public static function getFenceGateIdentifier(LogType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			LogType::OAK()->id() => Ids::OAK_FENCE_GATE,
			LogType::SPRUCE()->id() => Ids::SPRUCE_FENCE_GATE,
			LogType::BIRCH()->id() => Ids::BIRCH_FENCE_GATE,
			LogType::JUNGLE()->id() => Ids::JUNGLE_FENCE_GATE,
			LogType::ACACIA()->id() => Ids::ACACIA_FENCE_GATE,
			LogType::DARK_OAK()->id() => Ids::DARK_OAK_FENCE_GATE,
			LogType::MANGROVE()->id() => Ids::MANGROVE_FENCE_GATE,
			LogType::CRIMSON()->id() => Ids::CRIMSON_FENCE_GATE,
			LogType::WARPED()->id() => Ids::WARPED_FENCE_GATE,
			LogType::CHERRY()->id() => Ids::CHERRY_FENCE_GATE,
			default => throw new AssumptionFailedError("All wood types should be covered")
		});
	}
}
