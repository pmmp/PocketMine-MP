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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;

final class UpdateSubChunkBlocksPacketEntry{

	private int $x;
	private int $y;
	private int $z;
	private int $blockRuntimeId;

	private int $flags;

	//These two fields are useless 99.9% of the time; they are here to allow this packet to provide UpdateBlockSyncedPacket functionality.
	private int $syncedUpdateEntityUniqueId;
	private int $syncedUpdateType;

	public function __construct(int $x, int $y, int $z, int $blockRuntimeId, int $flags, int $syncedUpdateEntityUniqueId, int $syncedUpdateType){
		$this->x = $x;
		$this->y = $y;
		$this->z = $z;
		$this->blockRuntimeId = $blockRuntimeId;
		$this->flags = $flags;
		$this->syncedUpdateEntityUniqueId = $syncedUpdateEntityUniqueId;
		$this->syncedUpdateType = $syncedUpdateType;
	}

	public static function simple(int $x, int $y, int $z, int $blockRuntimeId) : self{
		return new self($x, $y, $z, $blockRuntimeId, UpdateBlockPacket::FLAG_NETWORK, 0, 0);
	}

	public function getX() : int{ return $this->x; }

	public function getY() : int{ return $this->y; }

	public function getZ() : int{ return $this->z; }

	public function getBlockRuntimeId() : int{ return $this->blockRuntimeId; }

	public function getFlags() : int{ return $this->flags; }

	public function getSyncedUpdateEntityUniqueId() : int{ return $this->syncedUpdateEntityUniqueId; }

	public function getSyncedUpdateType() : int{ return $this->syncedUpdateType; }

	public static function read(NetworkBinaryStream $in) : self{
		$x = $y = $z = 0;
		$in->getBlockPosition($x, $y, $z);
		$blockRuntimeId = $in->getUnsignedVarInt();
		$updateFlags = $in->getUnsignedVarInt();
		$syncedUpdateEntityUniqueId = $in->getUnsignedVarLong(); //this can't use the standard method because it's unsigned as opposed to the usual signed... !!!!!!
		$syncedUpdateType = $in->getUnsignedVarInt(); //this isn't even consistent with UpdateBlockSyncedPacket?!

		return new self($x, $y, $z, $blockRuntimeId, $updateFlags, $syncedUpdateEntityUniqueId, $syncedUpdateType);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putBlockPosition($this->x, $this->y, $this->z);
		$out->putUnsignedVarInt($this->blockRuntimeId);
		$out->putUnsignedVarInt($this->flags);
		$out->putUnsignedVarLong($this->syncedUpdateEntityUniqueId);
		$out->putUnsignedVarInt($this->syncedUpdateType);
	}
}
