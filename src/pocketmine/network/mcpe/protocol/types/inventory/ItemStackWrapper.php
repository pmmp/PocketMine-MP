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

namespace pocketmine\network\mcpe\protocol\types\inventory;

use pocketmine\item\Item;
use pocketmine\network\mcpe\NetworkBinaryStream;

final class ItemStackWrapper{

	/** @var int */
	private $stackId;
	/** @var Item */
	private $itemStack;

	public function __construct(int $stackId, Item $itemStack){
		$this->stackId = $stackId;
		$this->itemStack = $itemStack;
	}

	public static function legacy(Item $itemStack) : self{
		return new self($itemStack->isNull() ? 0 : 1, $itemStack);
	}

	public function getStackId() : int{ return $this->stackId; }

	public function getItemStack() : Item{ return $this->itemStack; }

	public static function read(NetworkBinaryStream $in) : self{
		$stackId = $in->readGenericTypeNetworkId();
		$stack = $in->getSlot();
		return new self($stackId, $stack);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->writeGenericTypeNetworkId($this->stackId);
		$out->putSlot($this->itemStack);
	}
}
