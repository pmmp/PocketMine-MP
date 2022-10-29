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

namespace pocketmine\entity\projectile;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\item\Trident as TridentItem;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\world\sound\TridentHitGroundSound;
use pocketmine\world\sound\TridentHitSound;

class Trident extends Projectile{

	public static function getNetworkTypeId() : string{ return EntityIds::THROWN_TRIDENT; }

	public const PICKUP_NONE = 0;
	public const PICKUP_ANY = 1;
	public const PICKUP_CREATIVE = 2;

	private const TAG_PICKUP = "pickup"; //TAG_Byte

	protected TridentItem $item;

	protected float $damage = 8.0;

	protected int $pickupMode = self::PICKUP_ANY;

	protected bool $canHitEntity = true;

	public function __construct(
		Location $location,
		TridentItem $item,
		?Entity $shootingEntity,
		?CompoundTag $nbt = null
	){
		if($item->isNull()){
			throw new \InvalidArgumentException("Trident must have a count of at least 1");
		}
		$this->setItem($item);
		parent::__construct($location, $shootingEntity, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.25, 0.25); }

	protected function getInitialDragMultiplier() : float{ return 0.01; }

	protected function getInitialGravity() : float{ return 0.05; }

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->pickupMode = $nbt->getByte(self::TAG_PICKUP, self::PICKUP_ANY);
		$this->canHitEntity = $nbt->getByte("canHitEntity", 1) === 1;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setTag("Trident", $this->item->nbtSerialize());
		$nbt->setByte(self::TAG_PICKUP, $this->pickupMode);
		$nbt->setByte("canHitEntity", $this->canHitEntity ? 1 : 0);
		return $nbt;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		return parent::entityBaseTick($tickDiff);
	}

	public function move(float $dx, float $dy, float $dz) : void{
		$motion = $this->motion;
		parent::move($dx, $dy, $dz);
		if($this->isCollided && !$this->canHitEntity){
			$this->motion = $motion;
		}
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		if(!$this->canHitEntity){
			return;
		}
		if($entityHit->getId() === $this->ownerId){
			if($entityHit instanceof Player){
				$this->pickup($entityHit); //tridents cannot hit their thrower
				return;
			}
		}
		parent::onHitEntity($entityHit, $hitResult);
		$this->canHitEntity = false;
		$this->item->applyDamage(1);
		$newTrident = new self($this->location, $this->item, $this->getOwningEntity(), $this->saveNBT());
		$newTrident->spawnToAll();
		$motion = new Vector3($this->motion->x * -0.01, $this->motion->y * -0.1, $this->motion->z * -0.01);
		$newTrident->setMotion($motion);
		$this->broadcastSound(new TridentHitSound());
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->canHitEntity = true;
		$this->broadcastSound(new TridentHitGroundSound());
	}

	public function getItem() : TridentItem{
		return clone $this->item;
	}

	public function setItem(TridentItem $item) : void{
		if($item->isNull()){
			throw new \InvalidArgumentException("Trident must have a count of at least 1");
		}
		$this->item = clone $item;
		$this->networkPropertiesDirty = true;
	}

	public function getPickupMode() : int{
		return $this->pickupMode;
	}

	public function setPickupMode(int $pickupMode) : void{
		$this->pickupMode = $pickupMode;
	}

	public function onCollideWithPlayer(Player $player) : void{
		if($this->blockHit === null){
			return;
		}

		$this->pickup($player);
	}

	private function pickup(Player $player) : void{
		$item = $this->getItem();
		$shouldDespawn = false;

		$playerInventory = match(true){
			$player->getInventory()->getAddableItemQuantity($item) > 0 => $player->getInventory(),
			default => null
		};

		$ev = new EntityItemPickupEvent($player, $this, $item, $playerInventory);
		if($player->hasFiniteResources() && $playerInventory === null){
			$ev->cancel();
		}
		if($this->pickupMode === self::PICKUP_NONE || ($this->pickupMode === self::PICKUP_CREATIVE && !$player->isCreative())){
			$ev->cancel();
			$shouldDespawn = true;
		}

		$ev->call();
		if(!$ev->isCancelled()){
			foreach($this->getViewers() as $viewer){
				$viewer->getNetworkSession()->onPlayerPickUpItem($player, $this);
			}
			$ev->getInventory()?->addItem($ev->getItem());
			$shouldDespawn = true;
		}

		if($shouldDespawn){
			$this->flagForDespawn();
		}
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::ENCHANTED, $this->item->hasEnchantments());
	}
}
