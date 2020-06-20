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

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\InstantEffect;
use pocketmine\entity\Living;
use pocketmine\event\entity\ProjectileHitBlockEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\Potion;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\sound\PotionSplashSound;
use function count;
use function round;
use function sqrt;

class SplashPotion extends Throwable{

	public static function getNetworkTypeId() : string{ return EntityIds::SPLASH_POTION; }

	protected $gravity = 0.05;
	protected $drag = 0.01;

	/** @var bool */
	protected $linger = false;
	/** @var int */
	protected $potionId = Potion::WATER;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->setPotionId($nbt->getShort("PotionId", Potion::WATER));
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setShort("PotionId", $this->getPotionId());

		return $nbt;
	}

	public function getResultDamage() : int{
		return -1; //no damage
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$effects = $this->getPotionEffects();
		$hasEffects = true;

		if(count($effects) === 0){
			$particle = new PotionSplashParticle(PotionSplashParticle::DEFAULT_COLOR());
			$hasEffects = false;
		}else{
			$colors = [];
			foreach($effects as $effect){
				$level = $effect->getEffectLevel();
				for($j = 0; $j < $level; ++$j){
					$colors[] = $effect->getColor();
				}
			}
			$particle = new PotionSplashParticle(Color::mix(...$colors));
		}

		$this->getWorld()->addParticle($this->location, $particle);
		$this->getWorld()->addSound($this->location, new PotionSplashSound());

		if($hasEffects){
			if(!$this->willLinger()){
				foreach($this->getWorld()->getNearbyEntities($this->boundingBox->expandedCopy(4.125, 2.125, 4.125), $this) as $entity){
					if($entity instanceof Living and $entity->isAlive()){
						$distanceSquared = $entity->getEyePos()->distanceSquared($this->location);
						if($distanceSquared > 16){ //4 blocks
							continue;
						}

						$distanceMultiplier = 1 - (sqrt($distanceSquared) / 4);
						if($event instanceof ProjectileHitEntityEvent and $entity === $event->getEntityHit()){
							$distanceMultiplier = 1.0;
						}

						foreach($this->getPotionEffects() as $effect){
							//getPotionEffects() is used to get COPIES to avoid accidentally modifying the same effect instance already applied to another entity

							if(!($effect->getType() instanceof InstantEffect)){
								$newDuration = (int) round($effect->getDuration() * 0.75 * $distanceMultiplier);
								if($newDuration < 20){
									continue;
								}
								$effect->setDuration($newDuration);
								$entity->getEffects()->add($effect);
							}else{
								$effect->getType()->applyEffect($entity, $effect, $distanceMultiplier, $this);
							}
						}
					}
				}
			}else{
				//TODO: lingering potions
			}
		}elseif($event instanceof ProjectileHitBlockEvent and $this->getPotionId() === Potion::WATER){
			$blockIn = $event->getBlockHit()->getSide($event->getRayTraceResult()->getHitFace());

			if($blockIn->getId() === BlockLegacyIds::FIRE){
				$this->getWorld()->setBlock($blockIn->getPos(), VanillaBlocks::AIR());
			}
			foreach($blockIn->getHorizontalSides() as $horizontalSide){
				if($horizontalSide->getId() === BlockLegacyIds::FIRE){
					$this->getWorld()->setBlock($horizontalSide->getPos(), VanillaBlocks::AIR());
				}
			}
		}
	}

	/**
	 * Returns the meta value of the potion item that this splash potion corresponds to. This decides what effects will be applied to the entity when it collides with its target.
	 */
	public function getPotionId() : int{
		return $this->potionId;
	}

	public function setPotionId(int $id) : void{
		$this->potionId = $id; //TODO: validation
	}

	/**
	 * Returns whether this splash potion will create an area-effect cloud when it lands.
	 */
	public function willLinger() : bool{
		return $this->linger;
	}

	/**
	 * Sets whether this splash potion will create an area-effect-cloud when it lands.
	 */
	public function setLinger(bool $value = true) : void{
		$this->linger = $value;
	}

	/**
	 * @return EffectInstance[]
	 */
	public function getPotionEffects() : array{
		return Potion::getPotionEffectsById($this->getPotionId());
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setShort(EntityMetadataProperties::POTION_AUX_VALUE, $this->potionId);
		$properties->setGenericFlag(EntityMetadataFlags::LINGER, $this->linger);
	}
}
