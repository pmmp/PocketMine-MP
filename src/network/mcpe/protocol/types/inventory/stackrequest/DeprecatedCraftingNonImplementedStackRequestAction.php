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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

/**
 * Tells that the current transaction involves crafting an item in a way that isn't supported by the current system.
 * At the time of writing, this includes using anvils.
 */
final class DeprecatedCraftingNonImplementedStackRequestAction extends ItemStackRequestAction{

	public static function getTypeId() : int{
		return ItemStackRequestActionType::CRAFTING_NON_IMPLEMENTED_DEPRECATED_ASK_TY_LAING;
	}

	public static function read(PacketSerializer $in) : self{
		return new self;
	}

	public function write(PacketSerializer $out) : void{
		//NOOP
	}
}
