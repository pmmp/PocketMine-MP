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

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ItemDespawnEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddItemEntityPacket;
use pocketmine\Player;

class Item extends Entity{
	const NETWORK_ID = 64;

	/** @var string */
	protected $owner = "";
	/** @var string */
	protected $thrower = "";
	/** @var int */
	protected $pickupDelay = 0;
	/** @var ItemItem */
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
		$this->setHealth((int) $this->namedtag["Health"]);
		if(isset($this->namedtag->Age)){
			$this->age = $this->namedtag["Age"];
		}
		if(isset($this->namedtag->PickupDelay)){
			$this->pickupDelay = $this->namedtag["PickupDelay"];
		}
		if(isset($this->namedtag->Owner)){
			$this->owner = $this->namedtag["Owner"];
		}
		if(isset($this->namedtag->Thrower)){
			$this->thrower = $this->namedtag["Thrower"];
		}


		if(!isset($this->namedtag->Item)){
			$this->close();
			return;
		}

		assert($this->namedtag->Item instanceof CompoundTag);

		$this->item = ItemItem::nbtDeserialize($this->namedtag->Item);


		$this->server->getPluginManager()->callEvent(new ItemSpawnEvent($this));
	}

	public function attack(EntityDamageEvent $source){
		if(
			$source->getCause() === EntityDamageEvent::CAUSE_VOID or
			$source->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK or
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

		if($this->isAlive()){
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
					$this->kill();
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
		$this->namedtag->Item = $this->item->nbtSerialize(-1, "Item");
		$this->namedtag->Health = new ShortTag("Health", (int) $this->getHealth());
		$this->namedtag->Age = new ShortTag("Age", $this->age);
		$this->namedtag->PickupDelay = new ShortTag("PickupDelay", $this->pickupDelay);
		if($this->owner !== null){
			$this->namedtag->Owner = new StringTag("Owner", $this->owner);
		}
		if($this->thrower !== null){
			$this->namedtag->Thrower = new StringTag("Thrower", $this->thrower);
		}
	}

	/**
	 * @return ItemItem
	 */
	public function getItem() : ItemItem{
		return $this->item;
	}

	public function canCollideWith(Entity $entity) : bool{
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

	public function spawnTo(Player $player){
		$pk = new AddItemEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->asVector3();
		$pk->motion = $this->getMotion();
		$pk->item = $this->getItem();
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}
