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

class PlayerActionPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::PLAYER_ACTION_PACKET;

	public const ACTION_START_BREAK = 0;
	public const ACTION_ABORT_BREAK = 1;
	public const ACTION_STOP_BREAK = 2;
	public const ACTION_GET_UPDATED_BLOCK = 3;
	public const ACTION_DROP_ITEM = 4;
	public const ACTION_START_SLEEPING = 5;
	public const ACTION_STOP_SLEEPING = 6;
	public const ACTION_RESPAWN = 7;
	public const ACTION_JUMP = 8;
	public const ACTION_START_SPRINT = 9;
	public const ACTION_STOP_SPRINT = 10;
	public const ACTION_START_SNEAK = 11;
	public const ACTION_STOP_SNEAK = 12;
	public const ACTION_CREATIVE_PLAYER_DESTROY_BLOCK = 13;
	public const ACTION_DIMENSION_CHANGE_ACK = 14; //sent when spawning in a different dimension to tell the server we spawned
	public const ACTION_START_GLIDE = 15;
	public const ACTION_STOP_GLIDE = 16;
	public const ACTION_BUILD_DENIED = 17;
	public const ACTION_CONTINUE_BREAK = 18;
	public const ACTION_CHANGE_SKIN = 19;
	public const ACTION_SET_ENCHANTMENT_SEED = 20; //no longer used
	public const ACTION_START_SWIMMING = 21;
	public const ACTION_STOP_SWIMMING = 22;
	public const ACTION_START_SPIN_ATTACK = 23;
	public const ACTION_STOP_SPIN_ATTACK = 24;
	public const ACTION_INTERACT_BLOCK = 25;

	/** @var int */
	public $entityRuntimeId;
	/** @var int */
	public $action;
	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $face;

	protected function decodePayload(PacketSerializer $in) : void{
		$this->entityRuntimeId = $in->getEntityRuntimeId();
		$this->action = $in->getVarInt();
		$in->getBlockPosition($this->x, $this->y, $this->z);
		$this->face = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putEntityRuntimeId($this->entityRuntimeId);
		$out->putVarInt($this->action);
		$out->putBlockPosition($this->x, $this->y, $this->z);
		$out->putVarInt($this->face);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handlePlayerAction($this);
	}
}
