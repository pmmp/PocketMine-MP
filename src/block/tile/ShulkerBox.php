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

use pocketmine\block\inventory\ShulkerBoxInventory;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class ShulkerBox extends Spawnable implements Container, Nameable{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_FACING = "facing";

	/** @var int */
	protected $facing = Facing::NORTH;

	/** @var ShulkerBoxInventory */
	protected $inventory;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new ShulkerBoxInventory($this->position);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->loadName($nbt);
		$this->loadItems($nbt);
		$this->facing = $nbt->getByte(self::TAG_FACING, $this->facing);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveName($nbt);
		$this->saveItems($nbt);
		$nbt->setByte(self::TAG_FACING, $this->facing);
	}

	public function copyDataFromItem(Item $item) : void{
		$this->readSaveData($item->getNamedTag());
		if($item->hasCustomName()){
			$this->setName($item->getCustomName());
		}
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();
			parent::close();
		}
	}

	protected function onBlockDestroyedHook() : void{
		//NOOP override of ContainerTrait - shulker boxes retain their contents when destroyed
	}

	public function getCleanedNBT() : ?CompoundTag{
		$nbt = parent::getCleanedNBT();
		if($nbt !== null){
			$nbt->removeTag(self::TAG_FACING);
		}
		return $nbt;
	}

	public function getFacing() : int{
		return $this->facing;
	}

	public function setFacing(int $facing) : void{
		$this->facing = $facing;
	}

	/**
	 * @return ShulkerBoxInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	/**
	 * @return ShulkerBoxInventory
	 */
	public function getRealInventory(){
		return $this->inventory;
	}

	public function getDefaultName() : string{
		return "Shulker Box";
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setByte(self::TAG_FACING, $this->facing);
		$this->addNameSpawnData($nbt);
	}
}
