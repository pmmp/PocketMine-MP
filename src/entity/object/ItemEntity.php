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
use pocketmine\event\entity\ItemDespawnEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\player\Player;
use function get_class;
use function max;

class ItemEntity extends Entity{
	public const NETWORK_ID = EntityLegacyIds::ITEM;

	public const DEFAULT_DESPAWN_DELAY = 6000; //5 minutes
	public const NEVER_DESPAWN = -1;
	public const MAX_DESPAWN_DELAY = 32767 + self::DEFAULT_DESPAWN_DELAY; //max value storable by mojang NBT :(

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

	/** @var int */
	protected $despawnDelay = self::DEFAULT_DESPAWN_DELAY;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->setMaxHealth(5);
		$this->setHealth($nbt->getShort("Health", (int) $this->getHealth()));

		$age = $nbt->getShort("Age", 0);
		if($age === -32768){
			$this->despawnDelay = self::NEVER_DESPAWN;
		}else{
			$this->despawnDelay = max(0, self::DEFAULT_DESPAWN_DELAY - $age);
		}
		$this->pickupDelay = $nbt->getShort("PickupDelay", $this->pickupDelay);
		$this->owner = $nbt->getString("Owner", $this->owner);
		$this->thrower = $nbt->getString("Thrower", $this->thrower);


		$itemTag = $nbt->getCompoundTag("Item");
		if($itemTag === null){
			throw new \UnexpectedValueException("Invalid " . get_class($this) . " entity: expected \"Item\" NBT tag not found");
		}

		$this->item = Item::nbtDeserialize($itemTag);
		if($this->item->isNull()){
			throw new \UnexpectedValueException("Item for " . get_class($this) . " is invalid");
		}


		(new ItemSpawnEvent($this))->call();
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if(!$this->isFlaggedForDespawn() and $this->pickupDelay !== self::NEVER_DESPAWN){ //Infinite delay
			$this->pickupDelay -= $tickDiff;
			if($this->pickupDelay < 0){
				$this->pickupDelay = 0;
			}

			$this->despawnDelay -= $tickDiff;
			if($this->despawnDelay <= 0){
				$ev = new ItemDespawnEvent($this);
				$ev->call();
				if($ev->isCancelled()){
					$this->despawnDelay = self::DEFAULT_DESPAWN_DELAY;
				}else{
					$this->flagForDespawn();
					$hasUpdate = true;
				}
			}
		}

		return $hasUpdate;
	}

	protected function tryChangeMovement() : void{
		$this->checkObstruction($this->location->x, $this->location->y, $this->location->z);
		parent::tryChangeMovement();
	}

	protected function applyDragBeforeGravity() : bool{
		return true;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setTag("Item", $this->item->nbtSerialize());
		$nbt->setShort("Health", (int) $this->getHealth());
		if($this->despawnDelay === self::NEVER_DESPAWN){
			$age = -32768;
		}else{
			$age = self::DEFAULT_DESPAWN_DELAY - $this->despawnDelay;
		}
		$nbt->setShort("Age", $age);
		$nbt->setShort("PickupDelay", $this->pickupDelay);
		if($this->owner !== null){
			$nbt->setString("Owner", $this->owner);
		}
		if($this->thrower !== null){
			$nbt->setString("Thrower", $this->thrower);
		}

		return $nbt;
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
	public function setPickupDelay(int $delay) : void{
		$this->pickupDelay = $delay;
	}

	/**
	 * Returns the number of ticks left before this item will despawn. If -1, the item will never despawn.
	 *
	 * @return int
	 */
	public function getDespawnDelay() : int{
		return $this->despawnDelay;
	}

	/**
	 * @param int $despawnDelay
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setDespawnDelay(int $despawnDelay) : void{
		if(($despawnDelay < 0 or $despawnDelay > self::MAX_DESPAWN_DELAY) and $despawnDelay !== self::NEVER_DESPAWN){
			throw new \InvalidArgumentException("Despawn ticker must be in range 0 ... " . self::MAX_DESPAWN_DELAY . " or " . self::NEVER_DESPAWN . ", got $despawnDelay");
		}
		$this->despawnDelay = $despawnDelay;
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
	public function setOwner(string $owner) : void{
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
	public function setThrower(string $thrower) : void{
		$this->thrower = $thrower;
	}

	protected function sendSpawnPacket(Player $player) : void{
		$pk = new AddItemActorPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->location->asVector3();
		$pk->motion = $this->getMotion();
		$pk->item = $this->getItem();
		$pk->metadata = $this->getSyncedNetworkData(false);

		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public function onCollideWithPlayer(Player $player) : void{
		if($this->getPickupDelay() !== 0){
			return;
		}

		$item = $this->getItem();
		$playerInventory = $player->getInventory();

		if($player->hasFiniteResources() and !$playerInventory->canAddItem($item)){
			return;
		}

		$ev = new InventoryPickupItemEvent($playerInventory, $this);
		$ev->call();
		if($ev->isCancelled()){
			return;
		}

		$this->server->broadcastPackets($this->getViewers(), [TakeItemActorPacket::create($player->getId(), $this->getId())]);

		$playerInventory->addItem(clone $item);
		$this->flagForDespawn();
	}
}
