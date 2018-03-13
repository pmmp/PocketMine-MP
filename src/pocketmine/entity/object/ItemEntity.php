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

namespace pocketmine\entity\object;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ItemDespawnEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\AddItemEntityPacket;
use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;
use pocketmine\Player;

class ItemEntity extends Entity{
	public const NETWORK_ID = self::ITEM;

	/** @var string */
	protected $owner = "";
	/** @var string */
	protected $thrower = "";
	/** @var int */
	protected $pickupDelay = 0;
	/** @var Item */
	protected $item;

	public $width = 0.25;
	public $height = 0.25;
	protected $baseOffset = 0.125;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	public $canCollide = false;

	protected function initEntity(){
		parent::initEntity();

		$this->setMaxHealth(5);
		$this->setHealth($this->namedtag->getShort("Health", (int) $this->getHealth()));
		$this->age = $this->namedtag->getShort("Age", $this->age);
		$this->pickupDelay = $this->namedtag->getShort("PickupDelay", $this->pickupDelay);
		$this->owner = $this->namedtag->getString("Owner", $this->owner);
		$this->thrower = $this->namedtag->getString("Thrower", $this->thrower);


		$itemTag = $this->namedtag->getCompoundTag("Item");
		if($itemTag === null){
			$this->close();
			return;
		}

		$this->item = Item::nbtDeserialize($itemTag);


		$this->server->getPluginManager()->callEvent(new ItemSpawnEvent($this));
	}

	public function attack(EntityDamageEvent $source){
		if(
			$source->getCause() === EntityDamageEvent::CAUSE_VOID or
			$source->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK or
			$source->getCause() === EntityDamageEvent::CAUSE_LAVA or
			$source->getCause() === EntityDamageEvent::CAUSE_ENTITY_EXPLOSION or
			$source->getCause() === EntityDamageEvent::CAUSE_BLOCK_EXPLOSION
		){
			parent::attack($source);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->isFlaggedForDespawn()){
			if($this->pickupDelay > 0 and $this->pickupDelay < 32767){ //Infinite delay
				$this->pickupDelay -= $tickDiff;
				if($this->pickupDelay < 0){
					$this->pickupDelay = 0;
				}
			}

			if($this->age > 6000){
				$this->server->getPluginManager()->callEvent($ev = new ItemDespawnEvent($this));
				if($ev->isCancelled()){
					$this->age = 0;
				}else{
					$this->flagForDespawn();
					$hasUpdate = true;
				}
			}

		}

		return $hasUpdate;
	}

	protected function tryChangeMovement(){
		$this->checkObstruction($this->x, $this->y, $this->z);
		parent::tryChangeMovement();
	}

	protected function applyDragBeforeGravity() : bool{
		return true;
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->setTag($this->item->nbtSerialize(-1, "Item"));
		$this->namedtag->setShort("Health", (int) $this->getHealth());
		$this->namedtag->setShort("Age", $this->age);
		$this->namedtag->setShort("PickupDelay", $this->pickupDelay);
		if($this->owner !== null){
			$this->namedtag->setString("Owner", $this->owner);
		}
		if($this->thrower !== null){
			$this->namedtag->setString("Thrower", $this->thrower);
		}
	}

	/**
	 * @return Item
	 */
	public function getItem() : Item{
		return $this->item;
	}

	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}

	/**
	 * @return int
	 */
	public function getPickupDelay() : int{
		return $this->pickupDelay;
	}

	/**
	 * @param int $delay
	 */
	public function setPickupDelay(int $delay){
		$this->pickupDelay = $delay;
	}

	/**
	 * @return string
	 */
	public function getOwner() : string{
		return $this->owner;
	}

	/**
	 * @param string $owner
	 */
	public function setOwner(string $owner){
		$this->owner = $owner;
	}

	/**
	 * @return string
	 */
	public function getThrower() : string{
		return $this->thrower;
	}

	/**
	 * @param string $thrower
	 */
	public function setThrower(string $thrower){
		$this->thrower = $thrower;
	}

	protected function sendSpawnPacket(Player $player) : void{
		$pk = new AddItemEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->asVector3();
		$pk->motion = $this->getMotion();
		$pk->item = $this->getItem();
		$pk->metadata = $this->propertyManager->getAll();

		$player->dataPacket($pk);
	}

	public function onCollideWithPlayer(Player $player){
		if($this->getPickupDelay() > 0){
			return;
		}

		$item = $this->getItem();
		$playerInventory = $player->getInventory();

		if(!($item instanceof Item) or ($player->isSurvival() and !$playerInventory->canAddItem($item))){
			return;
		}

		$this->server->getPluginManager()->callEvent($ev = new InventoryPickupItemEvent($playerInventory, $this));
		if($ev->isCancelled()){
			return;
		}

		switch($item->getId()){
			case Item::WOOD:
				$player->awardAchievement("mineWood");
				break;
			case Item::DIAMOND:
				$player->awardAchievement("diamond");
				break;
		}

		$pk = new TakeItemEntityPacket();
		$pk->eid = $player->getId();
		$pk->target = $this->getId();
		$this->server->broadcastPacket($this->getViewers(), $pk);

		$playerInventory->addItem(clone $item);
		$this->flagForDespawn();
	}
}
