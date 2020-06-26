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

use pocketmine\network\mcpe\NetworkBinaryStream;

/**
 * I have no clear idea what this does. It seems to be the client hinting to the server "hey, put a secondary output in
 * X crafting grid slot". This is used for things like buckets.
 */
final class CraftingMarkSecondaryResultStackRequestAction extends ItemStackRequestAction{

	/** @var int */
	private $craftingGridSlot;

	public function __construct(int $craftingGridSlot){
		$this->craftingGridSlot = $craftingGridSlot;
	}

	public function getCraftingGridSlot() : int{ return $this->craftingGridSlot; }

	public static function getTypeId() : int{ return ItemStackRequestActionType::CRAFTING_MARK_SECONDARY_RESULT_SLOT; }

	public static function read(NetworkBinaryStream $in) : self{
		$slot = $in->getByte();
		return new self($slot);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->craftingGridSlot);
	}
}
