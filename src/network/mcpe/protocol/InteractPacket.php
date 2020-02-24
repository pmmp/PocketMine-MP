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

use pocketmine\network\mcpe\handler\PacketHandler;

class InteractPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::INTERACT_PACKET;

	public const ACTION_LEAVE_VEHICLE = 3;
	public const ACTION_MOUSEOVER = 4;

	public const ACTION_OPEN_INVENTORY = 6;

	/** @var int */
	public $action;
	/** @var int */
	public $target;

	/** @var float */
	public $x;
	/** @var float */
	public $y;
	/** @var float */
	public $z;

	protected function decodePayload() : void{
		$this->action = $this->buf->getByte();
		$this->target = $this->buf->getEntityRuntimeId();

		if($this->action === self::ACTION_MOUSEOVER){
			//TODO: should this be a vector3?
			$this->x = $this->buf->getLFloat();
			$this->y = $this->buf->getLFloat();
			$this->z = $this->buf->getLFloat();
		}
	}

	protected function encodePayload() : void{
		$this->buf->putByte($this->action);
		$this->buf->putEntityRuntimeId($this->target);

		if($this->action === self::ACTION_MOUSEOVER){
			$this->buf->putLFloat($this->x);
			$this->buf->putLFloat($this->y);
			$this->buf->putLFloat($this->z);
		}
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleInteract($this);
	}
}
