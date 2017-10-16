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

namespace pocketmine\tile;

use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\Player;

abstract class Spawnable extends Tile{

	public function createSpawnPacket() : BlockEntityDataPacket{
		$nbt = new NBT(NBT::LITTLE_ENDIAN);
		$nbt->setData($this->getSpawnCompound());
		$pk = new BlockEntityDataPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->namedtag = $nbt->write(true);

		return $pk;
	}

	public function spawnTo(Player $player) : bool{
		if($this->closed){
			return false;
		}

		$player->dataPacket($this->createSpawnPacket());

		return true;
	}

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->spawnToAll();
	}

	public function spawnToAll(){
		if($this->closed){
			return;
		}

		$pk = $this->createSpawnPacket();
		$this->level->addChunkPacket($this->chunk->getX(), $this->chunk->getZ(), $pk);
	}

	protected function onChanged() : void{
		$this->spawnToAll();

		if($this->chunk !== null){
			$this->chunk->setChanged();
			$this->level->clearChunkCache($this->chunk->getX(), $this->chunk->getZ());
		}
	}

	/**
	 * @return CompoundTag
	 */
	final public function getSpawnCompound() : CompoundTag{
		$nbt = new CompoundTag("", [
			$this->namedtag->id,
			$this->namedtag->x,
			$this->namedtag->y,
			$this->namedtag->z
		]);
		$this->addAdditionalSpawnData($nbt);
		return $nbt;
	}

	/**
	 * An extension to getSpawnCompound() for
	 * further modifying the generic tile NBT.
	 *
	 * @param CompoundTag $nbt
	 */
	abstract public function addAdditionalSpawnData(CompoundTag $nbt) : void;

	/**
	 * Called when a player updates a block entity's NBT data
	 * for example when writing on a sign.
	 *
	 * @param CompoundTag $nbt
	 * @param Player      $player
	 *
	 * @return bool indication of success, will respawn the tile to the player if false.
	 */
	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		return false;
	}
}
