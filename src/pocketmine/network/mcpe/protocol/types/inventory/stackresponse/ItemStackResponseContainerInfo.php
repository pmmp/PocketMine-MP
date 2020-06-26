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

use pocketmine\network\mcpe\NetworkBinaryStream;
use function count;

final class ItemStackResponseContainerInfo{

	/** @var int */
	private $containerId;
	/** @var ItemStackResponseSlotInfo[] */
	private $slots;

	/**
	 * @param ItemStackResponseSlotInfo[] $slots
	 */
	public function __construct(int $containerId, array $slots){
		$this->containerId = $containerId;
		$this->slots = $slots;
	}

	public function getContainerId() : int{ return $this->containerId; }

	/** @return ItemStackResponseSlotInfo[] */
	public function getSlots() : array{ return $this->slots; }

	public static function read(NetworkBinaryStream $in) : self{
		$containerId = $in->getByte();
		$slots = [];
		for($i = 0, $len = $in->getUnsignedVarInt(); $i < $len; ++$i){
			$slots[] = ItemStackResponseSlotInfo::read($in);
		}
		return new self($containerId, $slots);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->containerId);
		$out->putUnsignedVarInt(count($this->slots));
		foreach($this->slots as $slot){
			$slot->write($out);
		}
	}
}
