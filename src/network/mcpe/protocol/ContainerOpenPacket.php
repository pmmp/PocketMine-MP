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
use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

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
		$result->x = $result->y = $result->z = 0; //these have to be set even if they aren't used
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->windowId = $in->getByte();
		$this->type = $in->getByte();
		$in->getBlockPosition($this->x, $this->y, $this->z);
		$this->entityUniqueId = $in->getEntityUniqueId();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putByte($this->windowId);
		$out->putByte($this->type);
		$out->putBlockPosition($this->x, $this->y, $this->z);
		$out->putEntityUniqueId($this->entityUniqueId);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleContainerOpen($this);
	}
}
