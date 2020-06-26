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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackresponse;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class ItemStackResponseSlotInfo{

	/** @var int */
	private $slot;
	/** @var int */
	private $hotbarSlot;
	/** @var int */
	private $count;
	/** @var int */
	private $itemStackId;

	public function __construct(int $slot, int $hotbarSlot, int $count, int $itemStackId){
		$this->slot = $slot;
		$this->hotbarSlot = $hotbarSlot;
		$this->count = $count;
		$this->itemStackId = $itemStackId;
	}

	public function getSlot() : int{ return $this->slot; }

	public function getHotbarSlot() : int{ return $this->hotbarSlot; }

	public function getCount() : int{ return $this->count; }

	public function getItemStackId() : int{ return $this->itemStackId; }

	public static function read(PacketSerializer $in) : self{
		$slot = $in->getByte();
		$hotbarSlot = $in->getByte();
		$count = $in->getByte();
		$itemStackId = $in->readGenericTypeNetworkId();
		return new self($slot, $hotbarSlot, $count, $itemStackId);
	}

	public function write(PacketSerializer $out) : void{
		$out->putByte($this->slot);
		$out->putByte($this->hotbarSlot);
		$out->putByte($this->count);
		$out->writeGenericTypeNetworkId($this->itemStackId);
	}
}
