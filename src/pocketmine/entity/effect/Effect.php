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

namespace pocketmine\entity\effect;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\utils\Color;
use function constant;
use function defined;
use function strtoupper;

class Effect{

	//TODO: remove our dependence on these magic numbers
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
		self::registerEffect(new SpeedEffect(Effect::SPEED, "%potion.moveSpeed", new Color(0x7c, 0xaf, 0xc6)));
		self::registerEffect(new SlownessEffect(Effect::SLOWNESS, "%potion.moveSlowdown", new Color(0x5a, 0x6c, 0x81), true));
		self::registerEffect(new Effect(Effect::HASTE, "%potion.digSpeed", new Color(0xd9, 0xc0, 0x43)));
		self::registerEffect(new Effect(Effect::MINING_FATIGUE, "%potion.digSlowDown", new Color(0x4a, 0x42, 0x17), true));
		self::registerEffect(new Effect(Effect::STRENGTH, "%potion.damageBoost", new Color(0x93, 0x24, 0x23)));
		self::registerEffect(new InstantHealthEffect(Effect::INSTANT_HEALTH, "%potion.heal", new Color(0xf8, 0x24, 0x23), false, false));
		self::registerEffect(new InstantDamageEffect(Effect::INSTANT_DAMAGE, "%potion.harm", new Color(0x43, 0x0a, 0x09), true, false));
		self::registerEffect(new Effect(Effect::JUMP_BOOST, "%potion.jump", new Color(0x22, 0xff, 0x4c)));
		self::registerEffect(new Effect(Effect::NAUSEA, "%potion.confusion", new Color(0x55, 0x1d, 0x4a), true));
		self::registerEffect(new RegenerationEffect(Effect::REGENERATION, "%potion.regeneration", new Color(0xcd, 0x5c, 0xab)));
		self::registerEffect(new Effect(Effect::RESISTANCE, "%potion.resistance", new Color(0x99, 0x45, 0x3a)));
		self::registerEffect(new Effect(Effect::FIRE_RESISTANCE, "%potion.fireResistance", new Color(0xe4, 0x9a, 0x3a)));
		self::registerEffect(new Effect(Effect::WATER_BREATHING, "%potion.waterBreathing", new Color(0x2e, 0x52, 0x99)));
		self::registerEffect(new InvisibilityEffect(Effect::INVISIBILITY, "%potion.invisibility", new Color(0x7f, 0x83, 0x92)));
		self::registerEffect(new Effect(Effect::BLINDNESS, "%potion.blindness", new Color(0x1f, 0x1f, 0x23), true));
		self::registerEffect(new Effect(Effect::NIGHT_VISION, "%potion.nightVision", new Color(0x1f, 0x1f, 0xa1)));
		self::registerEffect(new HungerEffect(Effect::HUNGER, "%potion.hunger", new Color(0x58, 0x76, 0x53), true));
		self::registerEffect(new Effect(Effect::WEAKNESS, "%potion.weakness", new Color(0x48, 0x4d, 0x48), true));
		self::registerEffect(new PoisonEffect(Effect::POISON, "%potion.poison", new Color(0x4e, 0x93, 0x31), true));
		self::registerEffect(new WitherEffect(Effect::WITHER, "%potion.wither", new Color(0x35, 0x2a, 0x27), true));
		self::registerEffect(new HealthBoostEffect(Effect::HEALTH_BOOST, "%potion.healthBoost", new Color(0xf8, 0x7d, 0x23)));
		self::registerEffect(new AbsorptionEffect(Effect::ABSORPTION, "%potion.absorption", new Color(0x25, 0x52, 0xa5)));
		self::registerEffect(new SaturationEffect(Effect::SATURATION, "%potion.saturation", new Color(0xf8, 0x24, 0x23), false));
		self::registerEffect(new LevitationEffect(Effect::LEVITATION, "%potion.levitation", new Color(0xce, 0xff, 0xff)));
		self::registerEffect(new PoisonEffect(Effect::FATAL_POISON, "%potion.poison", new Color(0x4e, 0x93, 0x31), true, true, true));
		self::registerEffect(new Effect(Effect::CONDUIT_POWER, "%potion.conduitPower", new Color(0x1d, 0xc2, 0xd1)));
	}

	/* auto-generated code */

	public static function ABSORPTION() : Effect{
		return self::getEffect(Effect::ABSORPTION);
	}

	public static function BLINDNESS() : Effect{
		return self::getEffect(Effect::BLINDNESS);
	}

	public static function CONDUIT_POWER() : Effect{
		return self::getEffect(Effect::CONDUIT_POWER);
	}

	public static function FATAL_POISON() : Effect{
		return self::getEffect(Effect::FATAL_POISON);
	}

	public static function FIRE_RESISTANCE() : Effect{
		return self::getEffect(Effect::FIRE_RESISTANCE);
	}

	public static function HASTE() : Effect{
		return self::getEffect(Effect::HASTE);
	}

	public static function HEALTH_BOOST() : Effect{
		return self::getEffect(Effect::HEALTH_BOOST);
	}

	public static function HUNGER() : Effect{
		return self::getEffect(Effect::HUNGER);
	}

	public static function INSTANT_DAMAGE() : Effect{
		return self::getEffect(Effect::INSTANT_DAMAGE);
	}

	public static function INSTANT_HEALTH() : Effect{
		return self::getEffect(Effect::INSTANT_HEALTH);
	}

	public static function INVISIBILITY() : Effect{
		return self::getEffect(Effect::INVISIBILITY);
	}

	public static function JUMP_BOOST() : Effect{
		return self::getEffect(Effect::JUMP_BOOST);
	}

	public static function LEVITATION() : Effect{
		return self::getEffect(Effect::LEVITATION);
	}

	public static function MINING_FATIGUE() : Effect{
		return self::getEffect(Effect::MINING_FATIGUE);
	}

	public static function NAUSEA() : Effect{
		return self::getEffect(Effect::NAUSEA);
	}

	public static function NIGHT_VISION() : Effect{
		return self::getEffect(Effect::NIGHT_VISION);
	}

	public static function POISON() : Effect{
		return self::getEffect(Effect::POISON);
	}

	public static function REGENERATION() : Effect{
		return self::getEffect(Effect::REGENERATION);
	}

	public static function RESISTANCE() : Effect{
		return self::getEffect(Effect::RESISTANCE);
	}

	public static function SATURATION() : Effect{
		return self::getEffect(Effect::SATURATION);
	}

	public static function SLOWNESS() : Effect{
		return self::getEffect(Effect::SLOWNESS);
	}

	public static function SPEED() : Effect{
		return self::getEffect(Effect::SPEED);
	}

	public static function STRENGTH() : Effect{
		return self::getEffect(Effect::STRENGTH);
	}

	public static function WATER_BREATHING() : Effect{
		return self::getEffect(Effect::WATER_BREATHING);
	}

	public static function WEAKNESS() : Effect{
		return self::getEffect(Effect::WEAKNESS);
	}

	public static function WITHER() : Effect{
		return self::getEffect(Effect::WITHER);
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
	/** @var bool */
	protected $hasBubbles;

	/**
	 * @param int    $id Effect ID as per Minecraft PE
	 * @param string $name Translation key used for effect name
	 * @param Color  $color
	 * @param bool   $isBad Whether the effect is harmful
	 * @param bool   $hasBubbles Whether the effect has potion bubbles. Some do not (e.g. Instant Damage has its own particles instead of bubbles)
	 */
	public function __construct(int $id, string $name, Color $color, bool $isBad = false, bool $hasBubbles = true){
		$this->id = $id;
		$this->name = $name;
		$this->color = $color;
		$this->bad = $isBad;
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
	 * Returns the default duration this effect will apply for if a duration is not specified.
	 * @return int
	 */
	public function getDefaultDuration() : int{
		return 600;
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
		return false;
	}

	/**
	 * Applies effect results to an entity. This will not be called unless canTick() returns true.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 * @param float          $potency
	 * @param null|Entity    $source
	 */
	public function applyEffect(Living $entity, EffectInstance $instance, float $potency = 1.0, ?Entity $source = null) : void{

	}

	/**
	 * Applies effects to the entity when the effect is first added.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 */
	public function add(Living $entity, EffectInstance $instance) : void{

	}

	/**
	 * Removes the effect from the entity, resetting any changed values back to their original defaults.
	 *
	 * @param Living         $entity
	 * @param EffectInstance $instance
	 */
	public function remove(Living $entity, EffectInstance $instance) : void{

	}

	public function __clone(){
		$this->color = clone $this->color;
	}
}
