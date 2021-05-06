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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

final class CreativeContentEntry{

	/** @var int */
	private $entryId;
	/** @var ItemStack */
	private $item;

	public function __construct(int $entryId, ItemStack $item){
		$this->entryId = $entryId;
		$this->item = $item;
	}

	public function getEntryId() : int{ return $this->entryId; }

	public function getItem() : ItemStack{ return $this->item; }

	public static function read(PacketSerializer $in) : self{
		$entryId = $in->readGenericTypeNetworkId();
		$item = $in->getItemStackWithoutStackId();
		return new self($entryId, $item);
	}

	public function write(PacketSerializer $out) : void{
		$out->writeGenericTypeNetworkId($this->entryId);
		$out->putItemStackWithoutStackId($this->item);
	}
}
