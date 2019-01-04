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

use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\utils\Color;
use function constant;
use function defined;
use function strtoupper;

class Effect{
	public const SPEED = 1;
	public const SLOWNESS = 2;
	public const HASTE = 3;
	public const FATIGUE = 4, MINING_FATIGUE = 4;
	public const STRENGTH = 5;
	public const INSTANT_HEALTH = 6, HEALING = 6;
	public const INSTANT_DAMAGE = 7, HARMING = 7;
	public const JUMP_BOOST = 8, JUMP = 8;
	public const NAUSEA = 9, CONFUSION = 9;
	public const REGENERATION = 10;
	public const RESISTANCE = 11, DAMAGE_RESISTANCE = 11;
	public const FIRE_RESISTANCE = 12;
	public const WATER_BREATHING = 13;
	public const INVISIBILITY = 14;
	public const BLINDNESS = 15;
	public const NIGHT_VISION = 16;
	public const HUNGER = 17;
	public const WEAKNESS = 18;
	public const POISON = 19;
	public const WITHER = 20;
	public const HEALTH_BOOST = 21;
	public const ABSORPTION = 22;
	public const SATURATION = 23;
	public const LEVITATION = 24; //TODO
	public const FATAL_POISON = 25;
	public const CONDUIT_POWER = 26;

	/** @var Effect[] */
	protected static $effects = [];

	public static function init() : void{
		self::registerEffect(new Effect(Effect::SPEED, "%potion.moveSpeed", new Color(0x7c, 0xaf, 0xc6)));
		self::registerEffect(new Effect(Effect::SLOWNESS, "%potion.moveSlowdown", new Color(0x5a, 0x6c, 0x81), true));
		self::registerEffect(new Effect(Effect::HASTE, "%potion.digSpeed", new Color(0xd9, 0xc0, 0x43)));
		self::registerEffect(new Effect(Effect::MINING_FATIGUE, "%potion.digSlowDown", new Color(0x4a, 0x42, 0x17), true));
		self::registerEffect(new Effect(Effect::STRENGTH, "%potion.damageBoost", new Color(0x93, 0x24, 0x23)));
		self::registerEffect(new Effect(Effect::INSTANT_HEALTH, "%potion.heal", new Color(0xf8, 0x24, 0x23), false, 1, false));
		self::registerEffect(new Effect(Effect::INSTANT_DAMAGE, "%potion.harm", new Color(0x43, 0x0a, 0x09), true, 1, false));
		self::registerEffect(new Effect(Effect::JUMP_BOOST, "%potion.jump", new Color(0x22, 0xff, 0x4c)));
		self::registerEffect(new Effect(Effect::NAUSEA, "%potion.confusion", new Color(0x55, 0x1d, 0x4a), true));
		self::registerEffect(new Effect(Effect::REGENERATION, "%potion.regeneration", new Color(0xcd, 0x5c, 0xab)));
		self::registerEffect(new Effect(Effect::RESISTANCE, "%potion.resistance", new Color(0x99, 0x45, 0x3a)));
		self::registerEffect(new Effect(Effect::FIRE_RESISTANCE, "%potion.fireResistance", new Color(0xe4, 0x9a, 0x3a)));
		self::registerEffect(new Effect(Effect::WATER_BREATHING, "%potion.waterBreathing", new Color(0x2e, 0x52, 0x99)));
		self::registerEffect(new Effect(Effect::INVISIBILITY, "%potion.invisibility", new Color(0x7f, 0x83, 0x92)));
		self::registerEffect(new Effect(Effect::BLINDNESS, "%potion.blindness", new Color(0x1f, 0x1f, 0x23), true));
		self::registerEffect(new Effect(Effect::NIGHT_VISION, "%potion.nightVision", new Color(0x1f, 0x1f, 0xa1)));
		self::registerEffect(new Effect(Effect::HUNGER, "%potion.hunger", new Color(0x58, 0x76, 0x53), true));
		self::registerEffect(new Effect(Effect::WEAKNESS, "%potion.weakness", new Color(0x48, 0x4d, 0x48), true));
		self::registerEffect(new Effect(Effect::POISON, "%potion.poison", new Color(0x4e, 0x93, 0x31), true));
		self::registerEffect(new Effect(Effect::WITHER, "%potion.wither", new Color(0x35, 0x2a, 0x27), true));
		self::registerEffect(new Effect(Effect::HEALTH_BOOST, "%potion.healthBoost", new Color(0xf8, 0x7d, 0x23)));
		self::registerEffect(new Effect(Effect::ABSORPTION, "%potion.absorption", new Color(0x25, 0x52, 0xa5)));
		self::registerEffect(new Effect(Effect::SATURATION, "%potion.saturation", new Color(0xf8, 0x24, 0x23), false, 1));
		self::registerEffect(new Effect(Effect::LEVITATION, "%potion.levitation", new Color(0xce, 0xff, 0xff)));
		self::registerEffect(new Effect(Effect::FATAL_POISON, "%potion.poison", new Color(0x4e, 0x93, 0x31), true));
		self::registerEffect(new Effect(Effect::CONDUIT_POWER, "%potion.conduitPower", new Color(0x1d, 0xc2, 0xd1)));
	}

	/**
	 * @param Effect $effect
	 */
	public static function registerEffect(Effect $effect) : void{
		self::$effects[$effect->getId()] = $effect;
	}

	/**
	 * @param int $id
	 *
	 * @return Effect|null
	 */
	public static function getEffect(int $id) : ?Effect{
		return self::$effects[$id] ?? null;
	}

	/**
	 * @param string $name
	 *
	 * @return Effect|null
	 */
	public static function getEffectByName(string $name) : ?Effect{
		$const = self::class . "::" . strtoupper($name);
		if(defined($const)){
			return self::getEffect(constant($const));
		}
		return null;
	}

	/** @var int */
	protected $id;
	/** @var string */
	protected $name;
	/** @var Color */
	protected $color;
	/** @var bool */
	protected $bad;
	/** @var int */
	protected $defaultDuration;
	/** @var bool */
	protected $hasBubbles;

	/**
	 * @param int    $id Effect ID as per Minecraft PE
	 * @param string $name Translation key used for effect name
	 * @param Color  $color
	 * @param bool   $isBad Whether the effect is harmful
	 * @param int    $defaultDuration Duration in ticks the effect will last for by default if applied without a duration.
	 * @param bool   $hasBubbles Whether the effect has potion bubbles. Some do not (e.g. Instant Damage has its own particles instead of bubbles)
	 */
	public function __construct(int $id, string $name, Color $color, bool $isBad = false, int $defaultDuration = 300 * 20, bool $hasBubbles = true){
		$this->id = $id;
		$this->name = $name;
		$this->color = $color;
		$this->bad = $isBad;
		$this->defaultDuration = $defaultDuration;
		$this->hasBubbles = $hasBubbles;
	}

	/**
	 * Returns the effect ID as per Minecraft PE
	 * @return int
	 */
	public function getId() : int{
		return $this->id;
	}

	/**
	 * Returns the translation key used to translate this effect's name.
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * Returns a Color object representing this effect's particle colour.
	 * @return Color
	 */
	public function getColor() : Color{
		return clone $this->color;
	}

	/**
	 * Returns whether this effect is harmful.
	 * TODO: implement inverse effect results for undead mobs
	 *
	 * @return bool
	 */
	public function isBad() : bool{
		return $this->bad;
	}

	/**
	 * Returns whether the effect is by default an instant effect.
	 * @return bool
	 */
	public function isInstantEffect() : bool{
		return $this->defaultDuration <= 1;
	}

	/**
	 * Returns the default duration this effect will apply for if a duration is not specified.
	 * @return int
	 */
	public function getDefaultDuration() : int{
		return $this->defaultDuration;
	}

	/**
	 * Returns whether this effect will give the subject potion bubbles.
	 * @return bool
	 */
	public function hasBubbles() : bool{
		return $this->hasBubbles;
	}

	/**
	 * Returns whether the effect will do something on the current tick.
	 *
	 * @param EffectInstance $instance
	 *
	 * @return bool
	 */
	public function canTick(EffectInstance $instance) : bool{
		switch($this->id){
			case Effect::POISON:
			case Effect::FATAL_POISON:
				if(($interval = (25 >> $instance->getAmplifier())) > 0){
					return ($instance->getDuration() % $interval) === 0;
				}
				return true;
			case Effect::WITHER:
				if(($interval = (50 >> $instance->getAmplifier())) > 0){
					return ($instance->getDuration() % $interval) === 0;
				}
				return true;
			case Effect::REGENERATION:
				if(($interval = (40 >> $instance->getAmplifier())) > 0){
					return ($instance->getDuration() % $interval) === 0;
				}
				return true;
			case Effect::HUNGER:
				return true;
			case Effect::INSTANT_DAMAGE:
			case Effect::INSTANT_HEALTH:
			case Effect::SATURATION:
				//If forced to last longer than 1 tick, these apply every tick.
				return true;
		}
		return false;
	}

	/**
	 * Applies effect results to an entity. This will not be called unless canTick() returns true.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 * @param float          $potency
	 * @param null|Entity    $source
	 * @param null|Entity    $sourceOwner
	 */
	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null, ?Entity $sourceOwner = null) : void{
		switch($this->id){
			/** @noinspection PhpMissingBreakStatementInspection */
			case Effect::POISON:
				if($entity->getHealth() <= 1){
					break;
				}
			case Effect::FATAL_POISON:
				$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
				$entity->attack($ev);
				break;

			case Effect::WITHER:
				$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
				$entity->attack($ev);
				break;

			case Effect::REGENERATION:
				if($entity->getHealth() < $entity->getMaxHealth()){
					$ev = new EntityRegainHealthEvent($entity, 1, EntityRegainHealthEvent::CAUSE_MAGIC);
					$entity->heal($ev);
				}
				break;

			case Effect::HUNGER:
				if($entity instanceof Human){
					$entity->exhaust(0.025 * $instance->getEffectLevel(), PlayerExhaustEvent::CAUSE_POTION);
				}
				break;
			case Effect::INSTANT_HEALTH:
				//TODO: add particles (witch spell)
				if($entity->getHealth() < $entity->getMaxHealth()){
					$entity->heal(new EntityRegainHealthEvent($entity, (4 << $instance->getAmplifier()) * $potency, EntityRegainHealthEvent::CAUSE_MAGIC));
				}
				break;
			case Effect::INSTANT_DAMAGE:
				//TODO: add particles (witch spell)
				$damage = (4 << $instance->getAmplifier()) * $potency;
				if($source !== null and $sourceOwner !== null){
					$ev = new EntityDamageByChildEntityEvent($sourceOwner, $source, $entity, EntityDamageEvent::CAUSE_MAGIC, $damage);
				}elseif($source !== null){
					$ev = new EntityDamageByEntityEvent($source, $entity, EntityDamageEvent::CAUSE_MAGIC, $damage);
				}else{
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, $damage);
				}
				$entity->attack($ev);

				break;
			case Effect::SATURATION:
				if($entity instanceof Human){
					$entity->addFood($instance->getEffectLevel());
					$entity->addSaturation($instance->getEffectLevel() * 2);
				}
				break;
		}
	}

	/**
	 * Applies effects to the entity when the effect is first added.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 */
	public function add(Living $entity, EffectInstance $instance) : void{
		switch($this->id){
			case Effect::INVISIBILITY:
				$entity->setInvisible();
				$entity->setNameTagVisible(false);
				break;
			case Effect::SPEED:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() * (1 + 0.2 * $instance->getEffectLevel()));
				break;
			case Effect::SLOWNESS:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() * (1 - 0.15 * $instance->getEffectLevel()), true);
				break;

			case Effect::HEALTH_BOOST:
				$entity->setMaxHealth($entity->getMaxHealth() + 4 * $instance->getEffectLevel());
				break;
			case Effect::ABSORPTION:
				$new = (4 * $instance->getEffectLevel());
				if($new > $entity->getAbsorption()){
					$entity->setAbsorption($new);
				}
				break;
		}
	}

	/**
	 * Removes the effect from the entity, resetting any changed values back to their original defaults.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 */
	public function remove(Living $entity, EffectInstance $instance) : void{
		switch($this->id){
			case Effect::INVISIBILITY:
				$entity->setInvisible(false);
				$entity->setNameTagVisible(true);
				break;
			case Effect::SPEED:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() / (1 + 0.2 * $instance->getEffectLevel()));
				break;
			case Effect::SLOWNESS:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() / (1 - 0.15 * $instance->getEffectLevel()));
				break;
			case Effect::HEALTH_BOOST:
				$entity->setMaxHealth($entity->getMaxHealth() - 4 * $instance->getEffectLevel());
				break;
			case Effect::ABSORPTION:
				$entity->setAbsorption(0);
				break;
		}
	}

	public function __clone(){
		$this->color = clone $this->color;
	}
}
