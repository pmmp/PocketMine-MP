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
		return new BID(match($type){
			WoodType::OAK => Ids::OAK_PLANKS,
			WoodType::SPRUCE => Ids::SPRUCE_PLANKS,
			WoodType::BIRCH => Ids::BIRCH_PLANKS,
			WoodType::JUNGLE => Ids::JUNGLE_PLANKS,
			WoodType::ACACIA => Ids::ACACIA_PLANKS,
			WoodType::DARK_OAK => Ids::DARK_OAK_PLANKS,
			WoodType::MANGROVE => Ids::MANGROVE_PLANKS,
			WoodType::CRIMSON => Ids::CRIMSON_PLANKS,
			WoodType::WARPED => Ids::WARPED_PLANKS,
			WoodType::CHERRY => Ids::CHERRY_PLANKS,
		});
	}

	public static function getFenceIdentifier(WoodType $type) : BID{
		return new BID(match($type){
			WoodType::OAK => Ids::OAK_FENCE,
			WoodType::SPRUCE => Ids::SPRUCE_FENCE,
			WoodType::BIRCH => Ids::BIRCH_FENCE,
			WoodType::JUNGLE => Ids::JUNGLE_FENCE,
			WoodType::ACACIA => Ids::ACACIA_FENCE,
			WoodType::DARK_OAK => Ids::DARK_OAK_FENCE,
			WoodType::MANGROVE => Ids::MANGROVE_FENCE,
			WoodType::CRIMSON => Ids::CRIMSON_FENCE,
			WoodType::WARPED => Ids::WARPED_FENCE,
			WoodType::CHERRY => Ids::CHERRY_FENCE,
		});
	}

	public static function getSlabIdentifier(WoodType $type) : BID{
		return new BID(match($type){
			WoodType::OAK => Ids::OAK_SLAB,
			WoodType::SPRUCE => Ids::SPRUCE_SLAB,
			WoodType::BIRCH => Ids::BIRCH_SLAB,
			WoodType::JUNGLE => Ids::JUNGLE_SLAB,
			WoodType::ACACIA => Ids::ACACIA_SLAB,
			WoodType::DARK_OAK => Ids::DARK_OAK_SLAB,
			WoodType::MANGROVE => Ids::MANGROVE_SLAB,
			WoodType::CRIMSON => Ids::CRIMSON_SLAB,
			WoodType::WARPED => Ids::WARPED_SLAB,
			WoodType::CHERRY => Ids::CHERRY_SLAB,
		});
	}

	public static function getLogIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType){
			WoodType::OAK => Ids::OAK_LOG,
			WoodType::SPRUCE => Ids::SPRUCE_LOG,
			WoodType::BIRCH => Ids::BIRCH_LOG,
			WoodType::JUNGLE => Ids::JUNGLE_LOG,
			WoodType::ACACIA => Ids::ACACIA_LOG,
			WoodType::DARK_OAK => Ids::DARK_OAK_LOG,
			WoodType::MANGROVE => Ids::MANGROVE_LOG,
			WoodType::CRIMSON => Ids::CRIMSON_STEM,
			WoodType::WARPED => Ids::WARPED_STEM,
			WoodType::CHERRY => Ids::CHERRY_LOG,
		});
	}

	public static function getAllSidedLogIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType){
			WoodType::OAK => Ids::OAK_WOOD,
			WoodType::SPRUCE => Ids::SPRUCE_WOOD,
			WoodType::BIRCH => Ids::BIRCH_WOOD,
			WoodType::JUNGLE => Ids::JUNGLE_WOOD,
			WoodType::ACACIA => Ids::ACACIA_WOOD,
			WoodType::DARK_OAK => Ids::DARK_OAK_WOOD,
			WoodType::MANGROVE => Ids::MANGROVE_WOOD,
			WoodType::CRIMSON => Ids::CRIMSON_HYPHAE,
			WoodType::WARPED => Ids::WARPED_HYPHAE,
			WoodType::CHERRY => Ids::CHERRY_WOOD,
		});
	}

	public static function getLeavesIdentifier(LeavesType $leavesType) : BID{
		return new BID(match($leavesType){
			LeavesType::OAK => Ids::OAK_LEAVES,
			LeavesType::SPRUCE => Ids::SPRUCE_LEAVES,
			LeavesType::BIRCH => Ids::BIRCH_LEAVES,
			LeavesType::JUNGLE => Ids::JUNGLE_LEAVES,
			LeavesType::ACACIA => Ids::ACACIA_LEAVES,
			LeavesType::DARK_OAK => Ids::DARK_OAK_LEAVES,
			LeavesType::MANGROVE => Ids::MANGROVE_LEAVES,
			LeavesType::AZALEA => Ids::AZALEA_LEAVES,
			LeavesType::FLOWERING_AZALEA => Ids::FLOWERING_AZALEA_LEAVES,
			LeavesType::CHERRY => Ids::CHERRY_LEAVES,
		});
	}

	public static function getSaplingIdentifier(SaplingType $treeType) : BID{
		return new BID(match($treeType){
			SaplingType::OAK => Ids::OAK_SAPLING,
			SaplingType::SPRUCE => Ids::SPRUCE_SAPLING,
			SaplingType::BIRCH => Ids::BIRCH_SAPLING,
			SaplingType::JUNGLE => Ids::JUNGLE_SAPLING,
			SaplingType::ACACIA => Ids::ACACIA_SAPLING,
			SaplingType::DARK_OAK => Ids::DARK_OAK_SAPLING,
		});
	}

	/**
	 * @return BID[]|\Closure[]
	 * @phpstan-return array{BID, BID, \Closure() : \pocketmine\item\Item}
	 */
	public static function getSignInfo(WoodType $treeType) : array{
		$make = fn(int $floorId, int $wallId, \Closure $getItem) => [
			new BID($floorId, TileSign::class),
			new BID($wallId, TileSign::class),
			$getItem
		];
		return match($treeType){
			WoodType::OAK => $make(Ids::OAK_SIGN, Ids::OAK_WALL_SIGN, fn() => VanillaItems::OAK_SIGN()),
			WoodType::SPRUCE => $make(Ids::SPRUCE_SIGN, Ids::SPRUCE_WALL_SIGN, fn() => VanillaItems::SPRUCE_SIGN()),
			WoodType::BIRCH => $make(Ids::BIRCH_SIGN, Ids::BIRCH_WALL_SIGN, fn() => VanillaItems::BIRCH_SIGN()),
			WoodType::JUNGLE => $make(Ids::JUNGLE_SIGN, Ids::JUNGLE_WALL_SIGN, fn() => VanillaItems::JUNGLE_SIGN()),
			WoodType::ACACIA => $make(Ids::ACACIA_SIGN, Ids::ACACIA_WALL_SIGN, fn() => VanillaItems::ACACIA_SIGN()),
			WoodType::DARK_OAK => $make(Ids::DARK_OAK_SIGN, Ids::DARK_OAK_WALL_SIGN, fn() => VanillaItems::DARK_OAK_SIGN()),
			WoodType::MANGROVE => $make(Ids::MANGROVE_SIGN, Ids::MANGROVE_WALL_SIGN, fn() => VanillaItems::MANGROVE_SIGN()),
			WoodType::CRIMSON => $make(Ids::CRIMSON_SIGN, Ids::CRIMSON_WALL_SIGN, fn() => VanillaItems::CRIMSON_SIGN()),
			WoodType::WARPED => $make(Ids::WARPED_SIGN, Ids::WARPED_WALL_SIGN, fn() => VanillaItems::WARPED_SIGN()),
			WoodType::CHERRY => $make(Ids::CHERRY_SIGN, Ids::CHERRY_WALL_SIGN, fn() => VanillaItems::CHERRY_SIGN()),
		};
	}

	public static function getTrapdoorIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType){
			WoodType::OAK => Ids::OAK_TRAPDOOR,
			WoodType::SPRUCE => Ids::SPRUCE_TRAPDOOR,
			WoodType::BIRCH => Ids::BIRCH_TRAPDOOR,
			WoodType::JUNGLE => Ids::JUNGLE_TRAPDOOR,
			WoodType::ACACIA => Ids::ACACIA_TRAPDOOR,
			WoodType::DARK_OAK => Ids::DARK_OAK_TRAPDOOR,
			WoodType::MANGROVE => Ids::MANGROVE_TRAPDOOR,
			WoodType::CRIMSON => Ids::CRIMSON_TRAPDOOR,
			WoodType::WARPED => Ids::WARPED_TRAPDOOR,
			WoodType::CHERRY => Ids::CHERRY_TRAPDOOR,
		});
	}

	public static function getButtonIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType){
			WoodType::OAK => Ids::OAK_BUTTON,
			WoodType::SPRUCE => Ids::SPRUCE_BUTTON,
			WoodType::BIRCH => Ids::BIRCH_BUTTON,
			WoodType::JUNGLE => Ids::JUNGLE_BUTTON,
			WoodType::ACACIA => Ids::ACACIA_BUTTON,
			WoodType::DARK_OAK => Ids::DARK_OAK_BUTTON,
			WoodType::MANGROVE => Ids::MANGROVE_BUTTON,
			WoodType::CRIMSON => Ids::CRIMSON_BUTTON,
			WoodType::WARPED => Ids::WARPED_BUTTON,
			WoodType::CHERRY => Ids::CHERRY_BUTTON,
		});
	}

	public static function getPressurePlateIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType){
			WoodType::OAK => Ids::OAK_PRESSURE_PLATE,
			WoodType::SPRUCE => Ids::SPRUCE_PRESSURE_PLATE,
			WoodType::BIRCH => Ids::BIRCH_PRESSURE_PLATE,
			WoodType::JUNGLE => Ids::JUNGLE_PRESSURE_PLATE,
			WoodType::ACACIA => Ids::ACACIA_PRESSURE_PLATE,
			WoodType::DARK_OAK => Ids::DARK_OAK_PRESSURE_PLATE,
			WoodType::MANGROVE => Ids::MANGROVE_PRESSURE_PLATE,
			WoodType::CRIMSON => Ids::CRIMSON_PRESSURE_PLATE,
			WoodType::WARPED => Ids::WARPED_PRESSURE_PLATE,
			WoodType::CHERRY => Ids::CHERRY_PRESSURE_PLATE,
		});
	}

	public static function getDoorIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType){
			WoodType::OAK => Ids::OAK_DOOR,
			WoodType::SPRUCE => Ids::SPRUCE_DOOR,
			WoodType::BIRCH => Ids::BIRCH_DOOR,
			WoodType::JUNGLE => Ids::JUNGLE_DOOR,
			WoodType::ACACIA => Ids::ACACIA_DOOR,
			WoodType::DARK_OAK => Ids::DARK_OAK_DOOR,
			WoodType::MANGROVE => Ids::MANGROVE_DOOR,
			WoodType::CRIMSON => Ids::CRIMSON_DOOR,
			WoodType::WARPED => Ids::WARPED_DOOR,
			WoodType::CHERRY => Ids::CHERRY_DOOR,
		});
	}

	public static function getFenceGateIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType){
			WoodType::OAK => Ids::OAK_FENCE_GATE,
			WoodType::SPRUCE => Ids::SPRUCE_FENCE_GATE,
			WoodType::BIRCH => Ids::BIRCH_FENCE_GATE,
			WoodType::JUNGLE => Ids::JUNGLE_FENCE_GATE,
			WoodType::ACACIA => Ids::ACACIA_FENCE_GATE,
			WoodType::DARK_OAK => Ids::DARK_OAK_FENCE_GATE,
			WoodType::MANGROVE => Ids::MANGROVE_FENCE_GATE,
			WoodType::CRIMSON => Ids::CRIMSON_FENCE_GATE,
			WoodType::WARPED => Ids::WARPED_FENCE_GATE,
			WoodType::CHERRY => Ids::CHERRY_FENCE_GATE,
		});
	}

	public static function getStairsIdentifier(WoodType $treeType) : BID{
		return new BID(match($treeType){
			WoodType::OAK => Ids::OAK_STAIRS,
			WoodType::SPRUCE => Ids::SPRUCE_STAIRS,
			WoodType::BIRCH => Ids::BIRCH_STAIRS,
			WoodType::JUNGLE => Ids::JUNGLE_STAIRS,
			WoodType::ACACIA => Ids::ACACIA_STAIRS,
			WoodType::DARK_OAK => Ids::DARK_OAK_STAIRS,
			WoodType::MANGROVE => Ids::MANGROVE_STAIRS,
			WoodType::CRIMSON => Ids::CRIMSON_STAIRS,
			WoodType::WARPED => Ids::WARPED_STAIRS,
			WoodType::CHERRY => Ids::CHERRY_STAIRS,
		});
	}
}
