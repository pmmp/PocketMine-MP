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

use pocketmine\block\BlockTypeIds as Ids;
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

	/**
	 * @phpstan-return \Closure() : \pocketmine\item\Item
	 */
	public static function getSignItem(WoodType $treeType) : \Closure{
		return match($treeType){
			WoodType::OAK => VanillaItems::OAK_SIGN(...),
			WoodType::SPRUCE => VanillaItems::SPRUCE_SIGN(...),
			WoodType::BIRCH => VanillaItems::BIRCH_SIGN(...),
			WoodType::JUNGLE => VanillaItems::JUNGLE_SIGN(...),
			WoodType::ACACIA => VanillaItems::ACACIA_SIGN(...),
			WoodType::DARK_OAK => VanillaItems::DARK_OAK_SIGN(...),
			WoodType::MANGROVE => VanillaItems::MANGROVE_SIGN(...),
			WoodType::CRIMSON => VanillaItems::CRIMSON_SIGN(...),
			WoodType::WARPED => VanillaItems::WARPED_SIGN(...),
			WoodType::CHERRY => VanillaItems::CHERRY_SIGN(...),
		};
	}
}
