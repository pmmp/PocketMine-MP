<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\inventory\ShulkerBoxInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class ShulkerBox extends Spawnable implements InventoryHolder, Container, Nameable {
	use NameableTrait{
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_FACING = "facing";
	public const TAG_UNDYED = "isUndyed";

	/** @var int */
	protected $facing = Vector3::SIDE_UP;
	/** @var bool */
	protected $isUndyed = true;

	/** @var ShulkerBoxInventory */
	protected $inventory;

	/**
	 * @param int $facing
	 */
	public function setFacing(int $facing) : void{
		if($facing < 0 or $facing > 5){
			throw new \InvalidArgumentException("Invalid shulkerbox facing: $facing");
		}

		$this->facing = $facing;
		$this->onChanged();
	}

	/**
	 * @return int
	 */
	public function getFacing() : int{
		return $this->facing;
	}

	/**
	 * @return int
	 */
	public function getSize(){
		return 27;
	}

	/**
	 * @return string
	 */
	public function getDefaultName(): string{
		return "Shulker Box";
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

	public function readSaveData(CompoundTag $nbt) : void{
		$this->facing = $nbt->getByte(self::TAG_FACING, Vector3::SIDE_UP);
		$this->isUndyed = $nbt->getByte(self::TAG_UNDYED, 1) == 1;

		$this->loadName($nbt);

		$this->inventory = new ShulkerBoxInventory($this);
		$this->loadItems($nbt);
	}

	public function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setTag(new ByteTag(self::TAG_FACING, $this->facing));
		$nbt->setTag(new ByteTag(self::TAG_UNDYED, $this->isUndyed ? 1 : 0));

		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setTag(new ByteTag(self::TAG_FACING, $this->facing));
		$nbt->setTag(new ByteTag(self::TAG_UNDYED, $this->isUndyed ? 1 : 0));

		$this->addNameSpawnData($nbt);
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->setByte(self::TAG_FACING, $face ?? Vector3::SIDE_UP);
		if($item !== null){
			$nbt->setByte(self::TAG_UNDYED, $item->getId() == Block::UNDYED_SHULKER_BOX ? 1 : 0);
		}
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;

			parent::close();
		}
	}
}