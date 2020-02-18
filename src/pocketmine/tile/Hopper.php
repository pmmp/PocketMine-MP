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

use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\inventory\HopperInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\CompoundTag;

class Hopper extends Spawnable implements Container, Nameable, InventoryHolder{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	/** @var HopperInventory */
	protected $inventory;
	/** @var int */
	protected $transferCooldown = 8;
	/** @var AxisAlignedBB */
	protected $pullBox;

	public const TAG_TRANSFER_COOLDOWN = "TransferCooldown";

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);

		$this->pullBox = new AxisAlignedBB($this->x, $this->y, $this->z, $this->x + 1, $this->y + 1.5, $this->z + 1);

		$this->scheduleUpdate();
	}

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->transferCooldown = $nbt->getInt(self::TAG_TRANSFER_COOLDOWN, 8);

		$this->inventory = new HopperInventory($this);

		$this->loadName($nbt);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->transferCooldown);

		$this->saveItems($nbt);
		$this->saveName($nbt);
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;
			parent::close();
		}
	}

	public function getInventory(){
		return $this->inventory;
	}

	public function getRealInventory(){
		return $this->inventory;
	}

	public function getDefaultName() : string{
		return "Hopper";
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$this->addNameSpawnData($nbt);
	}

	public function onUpdate() : bool{
		if($this->closed){
			return false;
		}
		
		if($this->isOnTransferCooldown()){
			$this->transferCooldown--;
		}else{
			$transfer = false;

			if(!$this->isEmpty()){
				$transfer = $this->transferItemOut();
			}

			if(!$this->isFull()){
				$transfer = $this->pullItemFromTop() or $transfer;
			}

			if($transfer){
				$this->setTransferCooldown(8);
			}
		}

		return true;
	}

	public function isEmpty() : bool{
		return count($this->inventory->getContents()) === 0;
	}

	public function isFull() : bool{
		return count($this->inventory->getContents()) === $this->inventory->getSize();
	}

	public function transferItemOut() : bool{
		$tile = $this->level->getTile($this->getSide($this->getBlock()->getDamage()));

		if($tile instanceof InventoryHolder){
			$targetInventory = $tile->getInventory();

			foreach($this->inventory->getContents() as $slot => $item){
				$item->setCount(1);

				if($targetInventory->canAddItem($item)){
					$targetInventory->addItem($item);
					$this->inventory->removeItem($item);

					if($tile instanceof Hopper){
						$tile->setTransferCooldown(8);
					}

					return true;
				}
			}
		}

		return false;
	}

	public function pullItemFromTop() : bool{
		$tile = $this->level->getTile($this->up());

		if($tile instanceof InventoryHolder){
			$inv = $tile->getInventory();
			foreach($inv->getContents() as $slot => $item){
				$item->setCount(1);

				if($this->inventory->canAddItem($item)){
					$this->inventory->addItem($item);
					$inv->removeItem($item);

					return true;
				}
			}
		}else{
			/** @var ItemEntity $entity */
			foreach(array_filter($this->level->getNearbyEntities($this->pullBox), function(Entity $entity) : bool{
				return $entity instanceof ItemEntity and !$entity->isFlaggedForDespawn();
			}) as $entity){
				$item = $entity->getItem();
				if($this->inventory->canAddItem($item)){
					$this->inventory->addItem($item);

					$entity->flagForDespawn();

					return true;
				}
			}
		}

		return false;
	}

	public function isOnTransferCooldown() : bool{
		return $this->transferCooldown > 0;
	}

	public function setTransferCooldown(int $cooldown){
		$this->transferCooldown = $cooldown;
	}
}