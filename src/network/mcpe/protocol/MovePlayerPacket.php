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

class MovePlayerPacket extends DataPacket implements ClientboundPacket, ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::MOVE_PLAYER_PACKET;

	public const MODE_NORMAL = 0;
	public const MODE_RESET = 1;
	public const MODE_TELEPORT = 2;
	public const MODE_PITCH = 3; //facepalm Mojang

	/** @var int */
	public $entityRuntimeId;
	/** @var Vector3 */
	public $position;
	/** @var float */
	public $pitch;
	/** @var float */
	public $yaw;
	/** @var float */
	public $headYaw;
	/** @var int */
	public $mode = self::MODE_NORMAL;
	/** @var bool */
	public $onGround = false; //TODO
	/** @var int */
	public $ridingEid = 0;
	/** @var int */
	public $teleportCause = 0;
	/** @var int */
	public $teleportItem = 0;

	protected function decodePayload() : void{
		$this->entityRuntimeId = $this->buf->getEntityRuntimeId();
		$this->position = $this->buf->getVector3();
		$this->pitch = $this->buf->getLFloat();
		$this->yaw = $this->buf->getLFloat();
		$this->headYaw = $this->buf->getLFloat();
		$this->mode = $this->buf->getByte();
		$this->onGround = $this->buf->getBool();
		$this->ridingEid = $this->buf->getEntityRuntimeId();
		if($this->mode === MovePlayerPacket::MODE_TELEPORT){
			$this->teleportCause = $this->buf->getLInt();
			$this->teleportItem = $this->buf->getLInt();
		}
	}

	protected function encodePayload() : void{
		$this->buf->putEntityRuntimeId($this->entityRuntimeId);
		$this->buf->putVector3($this->position);
		$this->buf->putLFloat($this->pitch);
		$this->buf->putLFloat($this->yaw);
		$this->buf->putLFloat($this->headYaw); //TODO
		$this->buf->putByte($this->mode);
		$this->buf->putBool($this->onGround);
		$this->buf->putEntityRuntimeId($this->ridingEid);
		if($this->mode === MovePlayerPacket::MODE_TELEPORT){
			$this->buf->putLInt($this->teleportCause);
			$this->buf->putLInt($this->teleportItem);
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleMovePlayer($this);
	}
}
