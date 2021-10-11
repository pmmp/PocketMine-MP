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

namespace pocketmine\block\tile;

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;

final class Bell extends Spawnable{
	public const TAG_DIRECTION = "Direction"; //TAG_Int
	public const TAG_RINGING = "Ringing"; //TAG_Byte
	public const TAG_TICKS = "Ticks"; //TAG_Int

	private bool $ringing = false;
	private int $facing = Facing::NORTH;
	private int $ticks = 0;

	public function isRinging() : bool{ return $this->ringing; }

	public function setRinging(bool $ringing) : void{ $this->ringing = $ringing; }

	public function getFacing() : int{ return $this->facing; }

	public function setFacing(int $facing) : void{ $this->facing = $facing; }

	public function getTicks() : int{ return $this->ticks; }

	public function setTicks(int $ticks) : void{ $this->ticks = $ticks; }

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_RINGING, $this->ringing ? 1 : 0);
		$nbt->setInt(self::TAG_DIRECTION, $this->facing);
		$nbt->setInt(self::TAG_TICKS, $this->ticks);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->ringing = $nbt->getByte(self::TAG_RINGING, 0) !== 0;
		$this->facing = $nbt->getInt(self::TAG_DIRECTION, Facing::NORTH);
		$this->ticks = $nbt->getInt(self::TAG_TICKS, 0);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_RINGING, $this->ringing ? 1 : 0);
		$nbt->setInt(self::TAG_DIRECTION, $this->facing);
		$nbt->setInt(self::TAG_TICKS, $this->ticks);
	}

	/**
	 * TODO: HACK!
	 * Creates a BlockActorDataPacket that triggers the ringing animation on a bell block.
	 *
	 * Bedrock team overcomplicated making bells ring when they implemented this; this would have been better and much
	 * simpler as a BlockEventPacket. It's simpler to implement bells with this hack than to follow Mojang's complicated
	 * mess.
	 */
	public function createFakeUpdatePacket(int $bellHitFace) : BlockActorDataPacket{
		$nbt = $this->getSpawnCompound();
		$nbt->setByte(self::TAG_RINGING, 1);
		$nbt->setInt(self::TAG_DIRECTION, BlockDataSerializer::writeLegacyHorizontalFacing($bellHitFace));
		$nbt->setInt(self::TAG_TICKS, 0);
		return BlockActorDataPacket::create($this->position->getFloorX(), $this->position->getFloorY(), $this->position->getFloorZ(), new CacheableNbt($nbt));
	}
}
