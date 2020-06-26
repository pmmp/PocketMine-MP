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
 * Swaps two stacks. These don't have to be in the same inventory. This action does not modify the stacks themselves.
 */
final class SwapStackRequestAction extends ItemStackRequestAction{

	/** @var ItemStackRequestSlotInfo */
	private $slot1;
	/** @var ItemStackRequestSlotInfo */
	private $slot2;

	public function __construct(ItemStackRequestSlotInfo $slot1, ItemStackRequestSlotInfo $slot2){
		$this->slot1 = $slot1;
		$this->slot2 = $slot2;
	}

	public function getSlot1() : ItemStackRequestSlotInfo{ return $this->slot1; }

	public function getSlot2() : ItemStackRequestSlotInfo{ return $this->slot2; }

	public static function getTypeId() : int{ return ItemStackRequestActionType::SWAP; }

	public static function read(NetworkBinaryStream $in) : self{
		$slot1 = ItemStackRequestSlotInfo::read($in);
		$slot2 = ItemStackRequestSlotInfo::read($in);
		return new self($slot1, $slot2);
	}

	public function write(NetworkBinaryStream $out) : void{
		$this->slot1->write($out);
		$this->slot2->write($out);
	}
}
