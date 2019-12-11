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

class AnvilDamagePacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::ANVIL_DAMAGE_PACKET;

	/** @var int */
	private $x;
	/** @var int */
	private $y;
	/** @var int */
	private $z;
	/** @var int */
	private $damageAmount;

	public static function create(int $x, int $y, int $z, int $damageAmount) : self{
		$result = new self;
		[$result->x, $result->y, $result->z] = [$x, $y, $z];
		$result->damageAmount = $damageAmount;
		return $result;
	}

	public function getDamageAmount() : int{
		return $this->damageAmount;
	}

	public function getX() : int{
		return $this->x;
	}

	public function getY() : int{
		return $this->y;
	}

	public function getZ() : int{
		return $this->z;
	}

	protected function decodePayload() : void{
		$this->damageAmount = $this->getByte();
		$this->getBlockPosition($this->x, $this->y, $this->z);
	}

	protected function encodePayload() : void{
		$this->putByte($this->damageAmount);
		$this->putBlockPosition($this->x, $this->y, $this->z);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleAnvilDamage($this);
	}
}
