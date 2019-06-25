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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\math\Vector3;
use pocketmine\network\mcpe\handler\PacketHandler;

class ContainerOpenPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_OPEN_PACKET;

	/** @var int */
	public $windowId;
	/** @var int */
	public $type;
	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $entityUniqueId = -1;

	public static function blockInv(int $windowId, int $windowType, int $x, int $y, int $z) : self{
		$result = new self;
		$result->windowId = $windowId;
		$result->type = $windowType;
		[$result->x, $result->y, $result->z] = [$x, $y, $z];
		return $result;
	}

	public static function blockInvVec3(int $windowId, int $windowType, Vector3 $vector3) : self{
		return self::blockInv($windowId, $windowType, $vector3->getFloorX(), $vector3->getFloorY(), $vector3->getFloorZ());
	}

	public static function entityInv(int $windowId, int $windowType, int $entityUniqueId) : self{
		$result = new self;
		$result->windowId = $windowId;
		$result->type = $windowType;
		$result->entityUniqueId = $entityUniqueId;
		return $result;
	}

	protected function decodePayload() : void{
		$this->windowId = $this->getByte();
		$this->type = $this->getByte();
		$this->getBlockPosition($this->x, $this->y, $this->z);
		$this->entityUniqueId = $this->getEntityUniqueId();
	}

	protected function encodePayload() : void{
		$this->putByte($this->windowId);
		$this->putByte($this->type);
		$this->putBlockPosition($this->x, $this->y, $this->z);
		$this->putEntityUniqueId($this->entityUniqueId);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleContainerOpen($this);
	}
}
