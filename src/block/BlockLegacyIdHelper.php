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
use pocketmine\item\VanillaItems;
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
		});
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
		});
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
		});
	}

	public static function getLogIdentifier(TreeType $treeType) : BID{
		return match($treeType->id()){
			TreeType::OAK()->id() => new BID(Ids::OAK_LOG),
			TreeType::SPRUCE()->id() => new BID(Ids::SPRUCE_LOG),
			TreeType::BIRCH()->id() => new BID(Ids::BIRCH_LOG),
			TreeType::JUNGLE()->id() => new BID(Ids::JUNGLE_LOG),
			TreeType::ACACIA()->id() => new BID(Ids::ACACIA_LOG),
			TreeType::DARK_OAK()->id() => new BID(Ids::DARK_OAK_LOG),
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

	public static function getWoodenTrapdoorIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_TRAPDOOR);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_TRAPDOOR);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_TRAPDOOR);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_TRAPDOOR);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_TRAPDOOR);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_TRAPDOOR);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenButtonIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_BUTTON);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_BUTTON);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_BUTTON);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_BUTTON);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_BUTTON);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_BUTTON);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenPressurePlateIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_PRESSURE_PLATE);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_PRESSURE_PLATE);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_PRESSURE_PLATE);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_PRESSURE_PLATE);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_PRESSURE_PLATE);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_PRESSURE_PLATE);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenDoorIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_DOOR);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_DOOR);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_DOOR);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_DOOR);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_DOOR);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_DOOR);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenFenceGateIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_FENCE_GATE);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_FENCE_GATE);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_FENCE_GATE);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_FENCE_GATE);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_FENCE_GATE);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_FENCE_GATE);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}

	public static function getWoodenStairsIdentifier(TreeType $treeType) : BlockIdentifier{
		switch($treeType->id()){
			case TreeType::OAK()->id():
				return new BID(Ids::OAK_STAIRS);
			case TreeType::SPRUCE()->id():
				return new BID(Ids::SPRUCE_STAIRS);
			case TreeType::BIRCH()->id():
				return new BID(Ids::BIRCH_STAIRS);
			case TreeType::JUNGLE()->id():
				return new BID(Ids::JUNGLE_STAIRS);
			case TreeType::ACACIA()->id():
				return new BID(Ids::ACACIA_STAIRS);
			case TreeType::DARK_OAK()->id():
				return new BID(Ids::DARK_OAK_STAIRS);
		}
		throw new AssumptionFailedError("Switch should cover all wood types");
	}
}
