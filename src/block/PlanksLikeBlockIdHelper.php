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
use pocketmine\block\utils\PlanksType;
use pocketmine\utils\AssumptionFailedError;

/**
 * All planks-like blocks have different IDs for different plank types.
 *
 * We can't make these dynamic, because some types of planks have different type properties (e.g. crimson and warped planks
 * are not flammable, but all other planks are).
 *
 * In the future, it's entirely within the realm of reason that the other types of planks may differ in other ways, such
 * as flammability, hardness, required tool tier, etc.
 * Therefore, to stay on the safe side of Mojang, planks-like blocks have static types. This does unfortunately generate
 * a lot of ugly code.
 *
 * @internal
 */
final class PlanksLikeBlockIdHelper{
	public static function getPlanksIdentifier(PlanksType $type) : BID{
		return new BID(match($type->id()){
			PlanksType::OAK()->id() => Ids::OAK_PLANKS,
			PlanksType::SPRUCE()->id() => Ids::SPRUCE_PLANKS,
			PlanksType::BIRCH()->id() => Ids::BIRCH_PLANKS,
			PlanksType::JUNGLE()->id() => Ids::JUNGLE_PLANKS,
			PlanksType::ACACIA()->id() => Ids::ACACIA_PLANKS,
			PlanksType::DARK_OAK()->id() => Ids::DARK_OAK_PLANKS,
			PlanksType::MANGROVE()->id() => Ids::MANGROVE_PLANKS,
			PlanksType::CRIMSON()->id() => Ids::CRIMSON_PLANKS,
			PlanksType::WARPED()->id() => Ids::WARPED_PLANKS,
			PlanksType::CHERRY()->id() => Ids::CHERRY_PLANKS,
			default => throw new AssumptionFailedError("All planks types should be covered")
		});
	}

	public static function getSlabIdentifier(PlanksType $type) : BID{
		return new BID(match($type->id()){
			PlanksType::OAK()->id() => Ids::OAK_SLAB,
			PlanksType::SPRUCE()->id() => Ids::SPRUCE_SLAB,
			PlanksType::BIRCH()->id() => Ids::BIRCH_SLAB,
			PlanksType::JUNGLE()->id() => Ids::JUNGLE_SLAB,
			PlanksType::ACACIA()->id() => Ids::ACACIA_SLAB,
			PlanksType::DARK_OAK()->id() => Ids::DARK_OAK_SLAB,
			PlanksType::MANGROVE()->id() => Ids::MANGROVE_SLAB,
			PlanksType::CRIMSON()->id() => Ids::CRIMSON_SLAB,
			PlanksType::WARPED()->id() => Ids::WARPED_SLAB,
			PlanksType::CHERRY()->id() => Ids::CHERRY_SLAB,
			default => throw new AssumptionFailedError("All planks types should be covered")
		});
	}

	public static function getStairsIdentifier(PlanksType $treeType) : BlockIdentifier{
		return new BID(match($treeType->id()){
			PlanksType::OAK()->id() => Ids::OAK_STAIRS,
			PlanksType::SPRUCE()->id() => Ids::SPRUCE_STAIRS,
			PlanksType::BIRCH()->id() => Ids::BIRCH_STAIRS,
			PlanksType::JUNGLE()->id() => Ids::JUNGLE_STAIRS,
			PlanksType::ACACIA()->id() => Ids::ACACIA_STAIRS,
			PlanksType::DARK_OAK()->id() => Ids::DARK_OAK_STAIRS,
			PlanksType::MANGROVE()->id() => Ids::MANGROVE_STAIRS,
			PlanksType::CRIMSON()->id() => Ids::CRIMSON_STAIRS,
			PlanksType::WARPED()->id() => Ids::WARPED_STAIRS,
			PlanksType::CHERRY()->id() => Ids::CHERRY_STAIRS,
			default => throw new AssumptionFailedError("All planks types should be covered")
		});
	}
}
