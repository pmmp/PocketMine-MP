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

class SetSpawnPositionPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::SET_SPAWN_POSITION_PACKET;

	public const TYPE_PLAYER_SPAWN = 0;
	public const TYPE_WORLD_SPAWN = 1;

	/** @var int */
	public $spawnType;
	/** @var int */
	public $x;
	/** @var int */
	public $y;
	/** @var int */
	public $z;
	/** @var int */
	public $dimension;
	/** @var int */
	public $x2;
	/** @var int */
	public $y2;
	/** @var int */
	public $z2;

	public static function playerSpawn(int $x, int $y, int $z, int $dimension, int $x2, int $y2, int $z2) : self{
		$result = new self;
		$result->spawnType = self::TYPE_PLAYER_SPAWN;
		[$result->x, $result->y, $result->z] = [$x, $y, $z];
		[$result->x2, $result->y2, $result->z2] = [$x2, $y2, $z2];
		$result->dimension = $dimension;
		return $result;
	}

	public static function worldSpawn(int $x, int $y, int $z, int $dimension) : self{
		$result = new self;
		$result->spawnType = self::TYPE_WORLD_SPAWN;
		[$result->x, $result->y, $result->z] = [$x, $y, $z];
		[$result->x2, $result->y2, $result->z2] = [$x, $y, $z];
		$result->dimension = $dimension;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void{
		$this->spawnType = $in->getVarInt();
		$in->getBlockPosition($this->x, $this->y, $this->z);
		$this->dimension = $in->getVarInt();
		$in->getBlockPosition($this->x2, $this->y2, $this->z2);
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putVarInt($this->spawnType);
		$out->putBlockPosition($this->x, $this->y, $this->z);
		$out->putVarInt($this->dimension);
		$out->putBlockPosition($this->x2, $this->y2, $this->z2);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleSetSpawnPosition($this);
	}
}
