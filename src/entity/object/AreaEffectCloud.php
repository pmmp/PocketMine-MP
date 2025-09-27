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

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\entity\effect\EffectCollection;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\InstantEffect;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\AreaEffectCloudApplyEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\utils\Binary;
use pocketmine\world\particle\PotionSplashParticle;
use function count;
use function max;
use function round;

class AreaEffectCloud extends Entity{

	public const DEFAULT_DURATION = 600; // in ticks
	public const DEFAULT_DURATION_CHANGE_ON_USE = 0; // in ticks

	public const UPDATE_DELAY = 10; // in ticks
	public const REAPPLICATION_DELAY = 40; // in ticks

	public const DEFAULT_RADIUS = 3.0; // in blocks
	public const DEFAULT_RADIUS_CHANGE_ON_PICKUP = -0.5; // in blocks
	public const DEFAULT_RADIUS_CHANGE_ON_USE = -0.5; // in blocks
	public const DEFAULT_RADIUS_CHANGE_PER_TICK = -(self::DEFAULT_RADIUS / self::DEFAULT_DURATION); // in blocks

	protected const TAG_POTION_ID = "PotionId"; //TAG_Short
	protected const TAG_SPAWN_TICK = "SpawnTick"; //TAG_Long
	protected const TAG_DURATION = "Duration"; //TAG_Int
	protected const TAG_PICKUP_COUNT = "PickupCount"; //TAG_Int
	protected const TAG_DURATION_ON_USE = "DurationOnUse"; //TAG_Int
	protected const TAG_REAPPLICATION_DELAY = "ReapplicationDelay"; //TAG_Int
	protected const TAG_INITIAL_RADIUS = "InitialRadius"; //TAG_Float
	protected const TAG_RADIUS = "Radius"; //TAG_Float
	protected const TAG_RADIUS_CHANGE_ON_PICKUP = "RadiusChangeOnPickup"; //TAG_Float
	protected const TAG_RADIUS_ON_USE = "RadiusOnUse"; //TAG_Float
	protected const TAG_RADIUS_PER_TICK = "RadiusPerTick"; //TAG_Float
	protected const TAG_EFFECTS = "mobEffects"; //TAG_List

	public static function getNetworkTypeId() : string{ return EntityIds::AREA_EFFECT_CLOUD; }

	protected int $age = 0;

	protected EffectCollection $effectCollection;

	/** @var array<int, int> entity ID => expiration */
	protected array $victims = [];

	protected int $maxAge = self::DEFAULT_DURATION;
	protected int $maxAgeChangeOnUse = self::DEFAULT_DURATION_CHANGE_ON_USE;

	protected int $reapplicationDelay = self::REAPPLICATION_DELAY;

	protected int $pickupCount = 0;
	protected float $radiusChangeOnPickup = self::DEFAULT_RADIUS_CHANGE_ON_PICKUP;

	protected float $initialRadius = self::DEFAULT_RADIUS;
	protected float $radius = self::DEFAULT_RADIUS;
	protected float $radiusChangeOnUse = self::DEFAULT_RADIUS_CHANGE_ON_USE;
	protected float $radiusChangePerTick = self::DEFAULT_RADIUS_CHANGE_PER_TICK;

	public function __construct(
		Location $location,
		?CompoundTag $nbt = null
	){
		parent::__construct($location, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.5, $this->radius * 2); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->effectCollection = new EffectCollection();
		$this->effectCollection->getEffectAddHooks()->add(function() : void{ $this->networkPropertiesDirty = true; });
		$this->effectCollection->getEffectRemoveHooks()->add(function() : void{ $this->networkPropertiesDirty = true; });
		$this->effectCollection->setEffectFilterForBubbles(static fn(EffectInstance $e) : bool => $e->isVisible());

		$worldTime = $this->getWorld()->getTime();
		$this->age = max($worldTime - $nbt->getLong(self::TAG_SPAWN_TICK, $worldTime), 0);
		$this->maxAge = $nbt->getInt(self::TAG_DURATION, self::DEFAULT_DURATION);
		$this->maxAgeChangeOnUse = $nbt->getInt(self::TAG_DURATION_ON_USE, self::DEFAULT_DURATION_CHANGE_ON_USE);
		$this->pickupCount = $nbt->getInt(self::TAG_PICKUP_COUNT, 0);
		$this->reapplicationDelay = $nbt->getInt(self::TAG_REAPPLICATION_DELAY, self::REAPPLICATION_DELAY);

		$this->initialRadius = $nbt->getFloat(self::TAG_INITIAL_RADIUS, self::DEFAULT_RADIUS);
		$this->setRadius($nbt->getFloat(self::TAG_RADIUS, $this->initialRadius));
		$this->radiusChangeOnPickup = $nbt->getFloat(self::TAG_RADIUS_CHANGE_ON_PICKUP, self::DEFAULT_RADIUS_CHANGE_ON_PICKUP);
		$this->radiusChangeOnUse = $nbt->getFloat(self::TAG_RADIUS_ON_USE, self::DEFAULT_RADIUS_CHANGE_ON_USE);
		$this->radiusChangePerTick = $nbt->getFloat(self::TAG_RADIUS_PER_TICK, self::DEFAULT_RADIUS_CHANGE_PER_TICK);

		$effectsTag = $nbt->getListTag(self::TAG_EFFECTS, CompoundTag::class);
		if($effectsTag !== null){
			foreach($effectsTag as $e){
				$effect = EffectIdMap::getInstance()->fromId($e->getByte("Id"));
				if($effect === null){
					continue;
				}

				$this->effectCollection->add(new EffectInstance(
					$effect,
					$e->getInt("Duration"),
					Binary::unsignByte($e->getByte("Amplifier")),
					$e->getByte("ShowParticles", 1) !== 0,
					$e->getByte("Ambient", 0) !== 0
				));
			}
		}
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setLong(self::TAG_SPAWN_TICK, $this->getWorld()->getTime() - $this->age);
		$nbt->setShort(self::TAG_POTION_ID, PotionTypeIds::WATER); //not used, mobEffects is used exclusively in Bedrock
		$nbt->setInt(self::TAG_DURATION, $this->maxAge);
		$nbt->setInt(self::TAG_DURATION_ON_USE, $this->maxAgeChangeOnUse);
		$nbt->setInt(self::TAG_PICKUP_COUNT, $this->pickupCount);
		$nbt->setInt(self::TAG_REAPPLICATION_DELAY, $this->reapplicationDelay);
		$nbt->setFloat(self::TAG_INITIAL_RADIUS, $this->initialRadius);
		$nbt->setFloat(self::TAG_RADIUS, $this->radius);
		$nbt->setFloat(self::TAG_RADIUS_CHANGE_ON_PICKUP, $this->radiusChangeOnPickup);
		$nbt->setFloat(self::TAG_RADIUS_ON_USE, $this->radiusChangeOnUse);
		$nbt->setFloat(self::TAG_RADIUS_PER_TICK, $this->radiusChangePerTick);

		if(count($this->effectCollection->all()) > 0){
			$effects = [];
			foreach($this->effectCollection->all() as $effect){
				$effects[] = CompoundTag::create()
					->setByte("Id", EffectIdMap::getInstance()->toId($effect->getType()))
					->setByte("Amplifier", Binary::signByte($effect->getAmplifier()))
					->setInt("Duration", $effect->getDuration())
					->setByte("Ambient", $effect->isAmbient() ? 1 : 0)
					->setByte("ShowParticles", $effect->isVisible() ? 1 : 0);
			}
			$nbt->setTag(self::TAG_EFFECTS, new ListTag($effects));
		}

		return $nbt;
	}

	public function isFireProof() : bool{
		return true;
	}

	public function canBeCollidedWith() : bool{
		return false;
	}

	/**
	 * Returns the current age of the cloud (in ticks).
	 */
	public function getAge() : int{
		return $this->age;
	}

	public function getEffects() : EffectCollection{
		return $this->effectCollection;
	}

	/**
	 * Returns the initial radius (in blocks).
	 */
	public function getInitialRadius() : float{
		return $this->initialRadius;
	}

	/**
	 * Returns the current radius (in blocks).
	 */
	public function getRadius() : float{
		return $this->radius;
	}

	/**
	 * Sets the current radius (in blocks).
	 */
	protected function setRadius(float $radius) : void{
		$this->radius = $radius;
		$this->setSize($this->getInitialSizeInfo());
		$this->networkPropertiesDirty = true;
	}

	/**
	 * Returns the amount that the radius of this cloud will add by when it is
	 * picked up (in blocks). Usually negative resulting in a radius reduction.
	 *
	 * Applied when getting dragon breath bottle.
	 */
	public function getRadiusChangeOnPickup() : float{
		return $this->radiusChangeOnPickup;
	}

	/**
	 * Sets the amount that the radius of this cloud will add by when it is
	 * picked up (in blocks). Usually negative resulting in a radius reduction.
	 *
	 * Applied when getting dragon breath bottle.
	 */
	public function setRadiusChangeOnPickup(float $radiusChangeOnPickup) : void{
		$this->radiusChangeOnPickup = $radiusChangeOnPickup;
	}

	/**
	 * Returns the amount that the radius of this cloud will add by when it
	 * applies an effect to an entity (in blocks). Usually negative resulting in a radius reduction.
	 */
	public function getRadiusChangeOnUse() : float{
		return $this->radiusChangeOnUse;
	}

	/**
	 * Sets the amount that the radius of this cloud will add by when it
	 * applies an effect to an entity (in blocks).
	 */
	public function setRadiusChangeOnUse(float $radiusChangeOnUse) : void{
		$this->radiusChangeOnUse = $radiusChangeOnUse;
	}

	/**
	 * Returns the amount that the radius of this cloud will add by when an update
	 * is performed (in blocks). Usually negative resulting in a radius reduction.
	 */
	public function getRadiusChangePerTick() : float{
		return $this->radiusChangePerTick;
	}

	/**
	 * Sets the amount that the radius of this cloud will add by when an update is performed (in blocks).
	 */
	public function setRadiusChangePerTick(float $radiusChangePerTick) : void{
		$this->radiusChangePerTick = $radiusChangePerTick;
	}

	/**
	 * Returns the age at which the cloud will despawn.
	 */
	public function getMaxAge() : int{
		return $this->maxAge;
	}

	/**
	 * Sets the age at which the cloud will despawn.
	 */
	public function setMaxAge(int $maxAge) : void{
		$this->maxAge = $maxAge;
	}

	/**
	 * Returns the amount that the max age of this cloud will change by when it
	 * applies an effect to an entity (in ticks).
	 */
	public function getMaxAgeChangeOnUse() : int{
		return $this->maxAgeChangeOnUse;
	}

	/**
	 * Sets the amount that the max age of this cloud will change by when it
	 * applies an effect to an entity (in ticks).
	 */
	public function setMaxAgeChangeOnUse(int $maxAgeChangeOnUse) : void{
		$this->maxAgeChangeOnUse = $maxAgeChangeOnUse;
	}

	/**
	 * Returns the time that an entity will be immune from subsequent exposure (in ticks).
	 */
	public function getReapplicationDelay() : int{
		return $this->reapplicationDelay;
	}

	/**
	 * Sets the time that an entity will be immune from subsequent exposure (in ticks).
	 */
	public function setReapplicationDelay(int $delay) : void{
		$this->reapplicationDelay = $delay;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		$this->age += $tickDiff;
		$radius = $this->radius + ($this->radiusChangePerTick * $tickDiff);
		if($radius < 0.5){
			$this->flagForDespawn();
			return true;
		}
		$this->setRadius($radius);
		if($this->age >= self::UPDATE_DELAY && ($this->age % self::UPDATE_DELAY) === 0){
			if($this->age > $this->maxAge){
				$this->flagForDespawn();
				return true;
			}

			foreach($this->victims as $entityId => $expiration){
				if($this->age >= $expiration){
					unset($this->victims[$entityId]);
				}
			}

			$entities = [];
			$radiusChange = 0.0;
			$maxAgeChange = 0;
			foreach($this->getWorld()->getCollidingEntities($this->getBoundingBox(), $this) as $entity){
				if(!$entity instanceof Living || isset($this->victims[$entity->getId()])){
					continue;
				}
				$entityPosition = $entity->getPosition();
				$xDiff = $entityPosition->getX() - $this->location->getX();
				$zDiff = $entityPosition->getZ() - $this->location->getZ();
				if(($xDiff ** 2 + $zDiff ** 2) > $this->radius ** 2){
					continue;
				}
				$entities[] = $entity;
				if($this->radiusChangeOnUse !== 0.0){
					$radiusChange += $this->radiusChangeOnUse;
					if($this->radius + $radiusChange <= 0){
						break;
					}
				}
				if($this->maxAgeChangeOnUse !== 0){
					$maxAgeChange += $this->maxAgeChangeOnUse;
					if($this->maxAge + $maxAgeChange <= 0){
						break;
					}
				}
			}
			if(count($entities) === 0){
				return $hasUpdate;
			}

			$ev = new AreaEffectCloudApplyEvent($this, $entities);
			$ev->call();
			if($ev->isCancelled()){
				return $hasUpdate;
			}

			foreach($ev->getAffectedEntities() as $entity){
				foreach($this->effectCollection->all() as $effect){
					$effect = clone $effect; //avoid accidental modification
					if($effect->getType() instanceof InstantEffect){
						$effect->getType()->applyEffect($entity, $effect, 0.5, $this);
					}else{
						$entity->getEffects()->add($effect->setDuration((int) round($effect->getDuration() / 4)));
					}
				}
				if($this->reapplicationDelay !== 0){
					$this->victims[$entity->getId()] = $this->age + $this->reapplicationDelay;
				}
			}

			$radius = $this->radius + $radiusChange;
			$maxAge = $this->maxAge + $maxAgeChange;
			if($radius <= 0 || $maxAge <= 0){
				$this->flagForDespawn();
				return true;
			}
			$this->setRadius($radius);
			$this->setMaxAge($maxAge);
			$hasUpdate = true;
		}

		return $hasUpdate;
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		//visual properties
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS, $this->radius);
		$properties->setInt(EntityMetadataProperties::POTION_COLOR, Binary::signInt((
			count($this->effectCollection->all()) === 0 ? PotionSplashParticle::DEFAULT_COLOR() : $this->effectCollection->getBubbleColor()
		)->toARGB()));

		//these are properties the client expects, and are used for client-sided logic, which we don't want
		$properties->setByte(EntityMetadataProperties::POTION_AMBIENT, 0);
		$properties->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_DURATION, -1);
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS_CHANGE_ON_PICKUP, 0);
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS_PER_TICK, 0);
		$properties->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_SPAWN_TIME, 0);
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_PICKUP_COUNT, 0);
		$properties->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_WAITING, 0);
	}

	protected function destroyCycles() : void{
		//wipe out callback refs
		$this->effectCollection = new EffectCollection();
		parent::destroyCycles();
	}
}
