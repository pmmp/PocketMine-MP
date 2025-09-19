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
use pocketmine\item\Item;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\EntityEventBroadcaster;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\world\sound\TridentHitEntitySound;
use pocketmine\world\sound\TridentHitGroundSound;

class Trident extends Projectile{

	public const TAG_ITEM = "Trident"; //TAG_Compound
	protected const TAG_SPAWNED_IN_CREATIVE = "isCreative"; //TAG_Byte

	public static function getNetworkTypeId() : string{ return EntityIds::THROWN_TRIDENT; }

	protected Item $item;

	protected float $damage = 8.0;

	protected bool $canCollide = true;

	protected bool $spawnedInCreative = false;

	public function __construct(
		Location $location,
		Item $item,
		?Entity $shootingEntity,
		?CompoundTag $nbt = null
	){
		if($item->isNull()){
			throw new \InvalidArgumentException("Trident must have a count of at least 1");
		}
		$this->item = clone $item;
		parent::__construct($location, $shootingEntity, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.35, 0.25); }

	protected function getInitialDragMultiplier() : float{ return 0.01; }

	protected function getInitialGravity() : float{ return 0.1; }

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->spawnedInCreative = $nbt->getByte(self::TAG_SPAWNED_IN_CREATIVE, 0) === 1;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setTag(self::TAG_ITEM, $this->item->nbtSerialize());
		$nbt->setByte(self::TAG_SPAWNED_IN_CREATIVE, $this->spawnedInCreative ? 1 : 0);
		return $nbt;
	}

	protected function onFirstUpdate(int $currentTick) : void{
		$owner = $this->getOwningEntity();
		$this->spawnedInCreative = $owner instanceof Player && $owner->isCreative();

		parent::onFirstUpdate($currentTick);
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}
		//TODO: Loyalty enchantment.

		return parent::entityBaseTick($tickDiff);
	}

	protected function despawnsOnEntityHit() : bool{
		return false;
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		parent::onHitEntity($entityHit, $hitResult);

		$this->canCollide = false;
		$this->broadcastSound(new TridentHitEntitySound());
		$this->setMotion(new Vector3($this->motion->x * -0.01, $this->motion->y * -0.1, $this->motion->z * -0.01));
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$this->canCollide = true;
		$this->broadcastSound(new TridentHitGroundSound());
	}

	public function getItem() : Item{
		return clone $this->item;
	}

	public function setItem(Item $item) : void{
		if($item->isNull()){
			throw new \InvalidArgumentException("Trident must have a count of at least 1");
		}
		if($this->item->hasEnchantments() !== $item->hasEnchantments()){
			$this->networkPropertiesDirty = true;
		}
		$this->item = clone $item;
	}

	public function canCollideWith(Entity $entity) : bool{
		return $this->canCollide && $entity->getId() !== $this->ownerId && parent::canCollideWith($entity);
	}

	public function onCollideWithPlayer(Player $player) : void{
		if($this->blockHit !== null){
			$this->pickup($player);
		}
	}

	private function pickup(Player $player) : void{
		$shouldDespawn = false;

		$playerInventory = $player->getInventory();
		$ev = new EntityItemPickupEvent($player, $this, $this->getItem(), $playerInventory);
		if($player->hasFiniteResources() && !$playerInventory->canAddItem($ev->getItem())){
			$ev->cancel();
		}
		if($this->spawnedInCreative){
			$ev->cancel();
			$shouldDespawn = true;
		}

		$ev->call();
		if(!$ev->isCancelled()){
			$ev->getInventory()?->addItem($ev->getItem());
			$shouldDespawn = true;
		}

		if($shouldDespawn){
			//even if the item was not actually picked up, the animation must be displayed.
			NetworkBroadcastUtils::broadcastEntityEvent(
				$this->getViewers(),
				fn(EntityEventBroadcaster $broadcaster, array $recipients) => $broadcaster->onPickUpItem($recipients, $player, $this)
			);
			$this->flagForDespawn();
		}
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::ENCHANTED, $this->item->hasEnchantments());
	}
}
