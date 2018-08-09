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

use pocketmine\block\Hopper as BlockHopper;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\inventory\HopperInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\Level;
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
	/** @var bool */
	protected $isPowered = false;

	public const TAG_TRANSFER_COOLDOWN = "TransferCooldown";

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);

		$this->scheduleUpdate();
	}

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->transferCooldown = $nbt->getInt(self::TAG_TRANSFER_COOLDOWN, 8);

		$this->loadName($nbt);

		$this->inventory = new HopperInventory($this);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->transferCooldown);

		$this->saveItems($nbt);
		$this->saveName($nbt);
	}

	public function close(): void{
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

	public function getDefaultName(): string{
		return "Hopper";
	}

	protected  function addAdditionalSpawnData(CompoundTag $nbt): void{
		$nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->transferCooldown);

		$this->addNameSpawnData($nbt);
	}

	public function onUpdate(): bool{
		$block = $this->getBlock();
		if(!($block instanceof BlockHopper)){
			return false;
		}

		$area = clone $block->getBoundingBox(); //Area above hopper to draw items from
		$area->maxY = ceil($area->maxY) + 1; //Account for full block above, not just 1 + 5/8

		$chunkEntities = array_filter($this->getLevel()->getChunkEntities($this->x >> 4, $this->z >> 4),
			function(Entity $entity):bool{
				return $entity instanceof ItemEntity and !$entity->isFlaggedForDespawn();
			});

		/** @var ItemEntity $entity */
		foreach($chunkEntities as $entity){
			if(!$entity->boundingBox->intersectsWith($area)){
				continue;
			}

			$item = $entity->getItem();
			if(!($item instanceof Item) or $item->isNull()){
				$entity->flagForDespawn();
				continue;
			}

			if($this->inventory->canAddItem($item)){
				$this->inventory->addItem($item);
				$entity->flagForDespawn();
			}
		}

		if($this->isPowered){
			return true;
		}

		if($this->transferCooldown !== 0){
			$this->transferCooldown--;
			return true;
		}

		$pos = $this->asVector3()->getSide($block->getDamage());
		$tile = $this->level->getTileAt($pos->x, $pos->y, $pos->z);
		if($tile instanceof Tile and $tile instanceof InventoryHolder){
			$item = $this->inventory->firstItem();
			if($item != null){
				$this->transferItem($item, $tile);
			}
		}

		return true;
	}

	public function transferItem(Item $trItem, InventoryHolder $inventoryHolder) : bool{
		$item = clone $trItem;
		$item->setCount(1);
		$inv = $inventoryHolder->getInventory();
		if($inv->canAddItem($item)){
			$inv->addItem($item);
			$this->inventory->removeItem($item);
			$this->resetTransferCooldown();
			if($inventoryHolder instanceof Hopper){
				$inventoryHolder->resetTransferCooldown();
			}
			return true;
		}
		return false;
	}

	public function resetTransferCooldown(){
		$this->transferCooldown = 8;
	}

	public function isPowered(): bool{
		return $this->isPowered;
	}

	public function setPowered(bool $value): void{
		$this->isPowered = $value;
	}
}