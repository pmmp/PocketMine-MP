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
use pocketmine\world\sound\TridentHitBlockSound;
use pocketmine\world\sound\TridentHitEntitySound;
use pocketmine\world\sound\TridentReturnSound;
use pocketmine\block\Water;

class Trident extends Projectile{

	public const TAG_ITEM = "Trident"; //TAG_Compound
	protected const TAG_SPAWNED_IN_CREATIVE = "isCreative"; //TAG_Byte

	public static function getNetworkTypeId() : string{ return EntityIds::THROWN_TRIDENT; }

	protected Item $item;

	protected float $damage = 8.0;

	protected bool $canCollide = true;

	protected bool $spawnedInCreative = false;
	/** If true, the trident is currently returning to its owner due to Loyalty enchant */
	protected bool $returning = false;

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

	/**
	 * Trident should not be stopped by water blocks when thrown.
	 */
	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult{
		if($block instanceof Water){
			return null;
		}

		return parent::calculateInterceptWithBlock($block, $start, $end);
	}

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
		$hasUpdate = parent::entityBaseTick($tickDiff);

		// Loyalty: if the trident is stuck and has Loyalty, return to owner
		$owner = $this->getOwningEntity();
		$loyaltyLevel = $this->item->getEnchantmentLevel(\pocketmine\item\enchantment\VanillaEnchantments::TRIDENT_LOYALTY());

		// If the trident is essentially stopped (for example stuck in water where block collisions are ignored),
		// treat it as stuck so Loyalty will trigger.
		if (!$this->returning && $loyaltyLevel > 0 && $owner instanceof Player) {
			// ticksLived threshold avoids immediate returns right after throw
			if ($this->ticksLived > 5 && $this->motion->lengthSquared() < 0.0001) {
				// begin returning
				$this->returning = true;
				$this->blockHit = null;
				$this->isCollided = false;
				$this->onGround = false;
				$this->canCollide = false;
				$this->setHasGravity(false);
				$this->setTargetEntity($owner);
				// sound for starting return
				$this->broadcastSound(new TridentReturnSound());
			}
		}

		if(($this->blockHit !== null || $this->onGround) && $loyaltyLevel > 0 && $owner instanceof Player && !$this->returning){
			// begin returning
			$this->returning = true;
			// free from being stuck
			$this->blockHit = null;
			$this->isCollided = false;
			$this->onGround = false;
			// make it non-collectible while returning
			$this->canCollide = false;
			$this->setHasGravity(false);
			$this->setTargetEntity($owner);
			// sound for starting return
			$this->broadcastSound(new TridentReturnSound());
		}

		if($this->returning){
			if(!($owner instanceof Player) || !$owner->isAlive() || $owner->isClosed()){
				// owner gone -> drop trident as item
				$this->returning = false;
				$this->canCollide = true;
				$this->setHasGravity(true);
			}else{
				// move towards owner
				$to = $owner->getLocation()->asVector3();
				$from = $this->location->asVector3();
				$dir = $to->subtractVector($from);
				$distance = $dir->length();
				if($distance <= 2.0){
					// close enough to give it back
					if($owner instanceof Player){
						$this->pickup($owner);
					}else{
						$this->flagForDespawn();
					}
				}else{
					// interpolate velocity towards owner; stronger with higher loyalty
					$speed = 1.0 + 0.5 * $loyaltyLevel;
					$newMotion = $dir->normalize()->multiply($speed);
					$this->setMotion($newMotion);
				}
			}
		}

	return $hasUpdate;
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
		$this->broadcastSound(new TridentHitBlockSound());
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
		if($this->blockHit !== null || $this->returning){
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
