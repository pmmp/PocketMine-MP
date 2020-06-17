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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\utils\BinaryDataException;

class MoveActorDeltaPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::MOVE_ACTOR_DELTA_PACKET;

	public const FLAG_HAS_X = 0x01;
	public const FLAG_HAS_Y = 0x02;
	public const FLAG_HAS_Z = 0x04;
	public const FLAG_HAS_ROT_X = 0x08;
	public const FLAG_HAS_ROT_Y = 0x10;
	public const FLAG_HAS_ROT_Z = 0x20;
	public const FLAG_GROUND = 0x40;
	public const FLAG_TELEPORT = 0x80;
	public const FLAG_FORCE_MOVE_LOCAL_ENTITY = 0x100;

	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $flags;
	/** @var int */
	public $xDiff = 0;
	/** @var int */
	public $yDiff = 0;
	/** @var int */
	public $zDiff = 0;
	/** @var float */
	public $xRot = 0.0;
	/** @var float */
	public $yRot = 0.0;
	/** @var float */
	public $zRot = 0.0;

	/**
	 * @throws BinaryDataException
	 */
	private function maybeReadCoord(int $flag, PacketSerializer $in) : int{
		if(($this->flags & $flag) !== 0){
			return $in->getVarInt();
		}
		return 0;
	}

	/**
	 * @throws BinaryDataException
	 */
	private function maybeReadRotation(int $flag, PacketSerializer $in) : float{
		if(($this->flags & $flag) !== 0){
			return $in->getByteRotation();
		}
		return 0.0;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$this->flags = $in->getLShort();
		$this->xDiff = $this->maybeReadCoord(self::FLAG_HAS_X, $in);
		$this->yDiff = $this->maybeReadCoord(self::FLAG_HAS_Y, $in);
		$this->zDiff = $this->maybeReadCoord(self::FLAG_HAS_Z, $in);
		$this->xRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_X, $in);
		$this->yRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_Y, $in);
		$this->zRot = $this->maybeReadRotation(self::FLAG_HAS_ROT_Z, $in);
	}

	private function maybeWriteCoord(int $flag, int $val, PacketSerializer $out) : void{
		if(($this->flags & $flag) !== 0){
			$out->putVarInt($val);
		}
	}

	private function maybeWriteRotation(int $flag, float $val, PacketSerializer $out) : void{
		if(($this->flags & $flag) !== 0){
			$out->putByteRotation($val);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$out->putLShort($this->flags);
		$this->maybeWriteCoord(self::FLAG_HAS_X, $this->xDiff, $out);
		$this->maybeWriteCoord(self::FLAG_HAS_Y, $this->yDiff, $out);
		$this->maybeWriteCoord(self::FLAG_HAS_Z, $this->zDiff, $out);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_X, $this->xRot, $out);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_Y, $this->yRot, $out);
		$this->maybeWriteRotation(self::FLAG_HAS_ROT_Z, $this->zRot, $out);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleMoveActorDelta($this);
	}
}
