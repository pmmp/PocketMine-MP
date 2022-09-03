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

use pocketmine\color\Color;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\effect\EffectContainer;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\InstantEffect;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\item\PotionType;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\utils\Binary;
use pocketmine\world\particle\PotionSplashParticle;
use function array_map;
use function count;
use function round;

class AreaEffectCloud extends Entity{

	public const DURATION = 600;
	public const DURATION_ON_USE = 0;

	public const WAIT_TIME = 10;
	public const REAPPLICATION_DELAY = 40;

	public const RADIUS = 3.0;
	public const RADIUS_ON_USE = -0.5;
	public const RADIUS_PER_TICK = -0.005;

	public const TAG_POTION_ID = "PotionId"; //TAG_Short
	public const TAG_AGE = "Age"; //TAG_Int
	public const TAG_DURATION = "Duration"; //TAG_Int
	public const TAG_DURATION_ON_USE = "DurationOnUse"; //TAG_Int
	public const TAG_REAPPLICATION_DELAY = "ReapplicationDelay"; //TAG_Int
	public const TAG_RADIUS = "Radius"; //TAG_Float
	public const TAG_RADIUS_ON_USE = "RadiusOnUse"; //TAG_Float
	public const TAG_RADIUS_PER_TICK = "RadiusPerTick"; //TAG_Float
	public const TAG_EFFECTS = "mobEffects"; //TAG_List

	public static function getNetworkTypeId() : string{ return EntityIds::AREA_EFFECT_CLOUD; }

	protected int $age = 0;

	protected PotionType $potionType;
	protected EffectContainer $effectContainer;

	protected Color $bubbleColor;
	protected bool $onlyAmbientEffects = false;

	/** @var array<int, int> */
	protected array $victims = [];

	protected int $duration = self::DURATION;
	protected int $durationOnUse = self::DURATION_ON_USE;
	protected int $reapplicationDelay = self::REAPPLICATION_DELAY;

	protected float $radius = self::RADIUS;
	protected float $radiusOnUse = self::RADIUS_ON_USE;
	protected float $radiusPerTick = -0.005;

	public function __construct(Location $location, PotionType $potionType, ?CompoundTag $nbt = null){
		$this->potionType = $potionType;
		$this->bubbleColor = PotionSplashParticle::DEFAULT_COLOR();
		parent::__construct($location, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.5, $this->radius * 2); }

	protected function getInitialDragMultiplier() : float{ return 0.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->effectContainer = new EffectContainer();
		$this->effectContainer->getEffectAddHooks()->add(function() : void{ $this->recalculateEffectColor(); });
		$this->effectContainer->getEffectRemoveHooks()->add(function() : void{ $this->recalculateEffectColor(); });

		$this->age = $nbt->getInt(self::TAG_AGE, 0);
		$this->duration = $nbt->getInt(self::TAG_DURATION, self::DURATION);
		$this->durationOnUse = $nbt->getInt(self::TAG_DURATION_ON_USE, self::DURATION_ON_USE);
		$this->reapplicationDelay = $nbt->getInt(self::TAG_REAPPLICATION_DELAY, self::REAPPLICATION_DELAY);
		$this->radius = $nbt->getFloat(self::TAG_RADIUS, self::RADIUS);
		$this->radiusOnUse = $nbt->getFloat(self::TAG_RADIUS_ON_USE, self::RADIUS_ON_USE);
		$this->radiusPerTick = $nbt->getFloat(self::TAG_RADIUS_PER_TICK, self::RADIUS_PER_TICK);

		/** @var CompoundTag[]|ListTag|null $effectsTag */
		$effectsTag = $nbt->getListTag(self::TAG_EFFECTS);
		if($effectsTag !== null){
			foreach($effectsTag as $e){
				$effect = EffectIdMap::getInstance()->fromId($e->getByte("Id"));
				if($effect === null){
					continue;
				}

				$this->effectContainer->add(new EffectInstance(
					$effect,
					$e->getInt("Duration"),
					Binary::unsignByte($e->getByte("Amplifier")),
					$e->getByte("ShowParticles", 1) !== 0,
					$e->getByte("Ambient", 0) !== 0
				));
			}
		}
		$this->recalculateEffectColor();
	}

	public function isFireProof() : bool{
		return true;
	}
	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setInt(self::TAG_AGE, $this->age);
		$nbt->setShort(self::TAG_POTION_ID, PotionTypeIdMap::getInstance()->toId($this->potionType));
		$nbt->setInt(self::TAG_DURATION, $this->duration);
		$nbt->setInt(self::TAG_DURATION_ON_USE, $this->durationOnUse);
		$nbt->setInt(self::TAG_REAPPLICATION_DELAY, $this->reapplicationDelay);
		$nbt->setFloat(self::TAG_RADIUS, $this->radius);
		$nbt->setFloat(self::TAG_RADIUS_ON_USE, $this->radiusOnUse);
		$nbt->setFloat(self::TAG_RADIUS_PER_TICK, $this->radiusPerTick);

		if(count($this->effectContainer->all()) > 0){
			$effects = [];
			foreach($this->effectContainer->all() as $effect){
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

	/**
	 * Recalculates the area entity's potion bubbles colour based on the active effects.
	 */
	protected function recalculateEffectColor() : void{
		/** @var Color[] $colors */
		$colors = [];
		$ambient = true;
		foreach($this->getCloudEffects() as $effect){
			if($effect->isVisible()){
				$level = $effect->getEffectLevel();
				$color = $effect->getColor();
				for($i = 0; $i < $level; ++$i){
					$colors[] = $color;
				}

				if(!$effect->isAmbient()){
					$ambient = false;
				}
			}
		}

		if(count($colors) > 0){
			$this->bubbleColor = Color::mix(...$colors);
			$this->onlyAmbientEffects = $ambient;
		}else{
			$this->bubbleColor = PotionSplashParticle::DEFAULT_COLOR();
			$this->onlyAmbientEffects = false;
		}
		$this->networkPropertiesDirty = true;
	}

	public function getAge() : int{
		return $this->age;
	}

	public function getPotionType() : PotionType{
		return $this->potionType;
	}

	public function getEffects() : EffectContainer{
		return $this->effectContainer;
	}

	public function getBubbleColor() : Color{
		return $this->bubbleColor;
	}

	public function hasOnlyAmbientEffects() : bool{
		return $this->onlyAmbientEffects;
	}

	public function getRadius() : float{
		return $this->radius;
	}

	public function setRadius(float $radius) : void{
		$this->radius = $radius;
		$this->setSize($this->getInitialSizeInfo());
		$this->networkPropertiesDirty = true;
	}

	public function getRadiusOnUse() : float{
		return $this->radiusOnUse;
	}

	public function setRadiusOnUse(float $radiusOnUse) : void{
		$this->radiusOnUse = $radiusOnUse;
		$this->networkPropertiesDirty = true;
	}

	public function getRadiusPerTick() : float{
		return $this->radiusPerTick;
	}

	public function setRadiusPerTick(float $radiusPerTick) : void{
		$this->radiusPerTick = $radiusPerTick;
		$this->networkPropertiesDirty = true;
	}

	public function getDuration() : int{
		return $this->duration;
	}

	public function setDuration(int $duration) : void{
		$this->duration = $duration;
		$this->networkPropertiesDirty = true;
	}

	public function getDurationOnUse() : int{
		return $this->durationOnUse;
	}

	public function setDurationOnUse(int $durationOnUse) : void{
		$this->durationOnUse = $durationOnUse;
		$this->networkPropertiesDirty = true;
	}

	public function getReapplicationDelay() : int{
		return $this->reapplicationDelay;
	}

	public function setReapplicationDelay(int $delay) : void{
		$this->reapplicationDelay = $delay;
		$this->networkPropertiesDirty = true;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		$this->age += $tickDiff;
		if($this->age > $this->duration){
			$this->flagForDespawn();
			return true;
		}
		//Area effect clouds only trigger updates every ten ticks.
		if($this->age >= self::WAIT_TIME && $this->age % self::WAIT_TIME === 0){
			$radius = $this->radius + ($this->radiusPerTick * $tickDiff);
			if($this->radius < 0.5){
				$this->flagForDespawn();
				return true;
			}
			$this->setRadius($radius);
			foreach($this->victims as $entityId => $expiration){
				if($this->age >= $expiration){
					unset($this->victims[$entityId]);
				}
			}
			foreach($this->getWorld()->getNearbyEntities($this->getBoundingBox(), $this) as $entity){
				if(!$entity instanceof Living || !$entity->isAlive() || isset($this->victims[$entity->getId()])){
					continue;
				}

				$entityPosition = $entity->getPosition();
				$xDiff = $entityPosition->getX() - $this->location->getX();
				$zDiff = $entityPosition->getZ() - $this->location->getZ();
				if(($xDiff ** 2 + $zDiff ** 2) > $this->radius ** 2){
					continue;
				}

				foreach($this->getCloudEffects() as $effect){
					if($effect->getType() instanceof InstantEffect){
						$effect->getType()->applyEffect($entity, $effect, 0.5, $this);
					}else{
						$entity->getEffects()->add($effect->setDuration((int) round($effect->getDuration() / 4)));
					}
				}
				if($this->reapplicationDelay !== 0){
					$this->victims[$entity->getId()] = $this->age + $this->reapplicationDelay;
				}
				if($this->radiusOnUse !== 0.0){
					$radius = $this->radius + $this->radiusOnUse;
					if($radius <= 0){
						$this->flagForDespawn();
						return true;
					}
					$this->setRadius($radius);
				}
				if($this->durationOnUse !== 0){
					$duration = $this->duration + $this->durationOnUse;
					if($duration <= 0){
						$this->flagForDespawn();
						return true;
					}
					$this->setDuration($duration);
				}
			}
		}

		return $hasUpdate;
	}

	/**
	 * Returns the effects the area effect cloud provides.
	 *
	 * Used to get COPIES to avoid accidentally modifying the same effect instance
	 * already applied to another entity.
	 *
	 * @return EffectInstance[]
	 */
	public function getCloudEffects() : array{
		return array_map(function(EffectInstance $effect) : EffectInstance{
			return clone $effect;
		}, $this->effectContainer->all());
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setInt(EntityMetadataProperties::AREA_EFFECT_CLOUD_DURATION, $this->duration);
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS, $this->radius);
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS_PER_TICK, $this->radiusPerTick);
		$properties->setFloat(EntityMetadataProperties::AREA_EFFECT_CLOUD_RADIUS_CHANGE_ON_PICKUP, $this->radiusOnUse);
		$properties->setInt(EntityMetadataProperties::POTION_COLOR, Binary::signInt($this->bubbleColor->toARGB()));
		$properties->setByte(EntityMetadataProperties::POTION_AMBIENT, $this->onlyAmbientEffects ? 1 : 0);
	}
}
