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

final class ItemStackRequestSlotInfo{

	/** @var int */
	private $containerId;
	/** @var int */
	private $slotId;
	/** @var int */
	private $stackId;

	public function __construct(int $containerId, int $slotId, int $stackId){
		$this->containerId = $containerId;
		$this->slotId = $slotId;
		$this->stackId = $stackId;
	}

	public function getContainerId() : int{ return $this->containerId; }

	public function getSlotId() : int{ return $this->slotId; }

	public function getStackId() : int{ return $this->stackId; }

	public static function read(PacketSerializer $in) : self{
		$containerId = $in->getByte();
		$slotId = $in->getByte();
		$stackId = $in->readGenericTypeNetworkId();
		return new self($containerId, $slotId, $stackId);
	}

	public function write(PacketSerializer $out) : void{
		$out->putByte($this->containerId);
		$out->putByte($this->slotId);
		$out->writeGenericTypeNetworkId($this->stackId);
	}
}
