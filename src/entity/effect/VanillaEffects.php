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

use pocketmine\color\Color;
use pocketmine\utils\RegistryTrait;
use function assert;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see RegistryTrait::_generateMethodAnnotations()
 *
 * @method static AbsorptionEffect ABSORPTION()
 * @method static Effect BLINDNESS()
 * @method static Effect CONDUIT_POWER()
 * @method static PoisonEffect FATAL_POISON()
 * @method static Effect FIRE_RESISTANCE()
 * @method static Effect HASTE()
 * @method static HealthBoostEffect HEALTH_BOOST()
 * @method static HungerEffect HUNGER()
 * @method static InstantDamageEffect INSTANT_DAMAGE()
 * @method static InstantHealthEffect INSTANT_HEALTH()
 * @method static InvisibilityEffect INVISIBILITY()
 * @method static Effect JUMP_BOOST()
 * @method static LevitationEffect LEVITATION()
 * @method static Effect MINING_FATIGUE()
 * @method static Effect NAUSEA()
 * @method static Effect NIGHT_VISION()
 * @method static PoisonEffect POISON()
 * @method static RegenerationEffect REGENERATION()
 * @method static Effect RESISTANCE()
 * @method static SaturationEffect SATURATION()
 * @method static SlownessEffect SLOWNESS()
 * @method static SpeedEffect SPEED()
 * @method static Effect STRENGTH()
 * @method static Effect WATER_BREATHING()
 * @method static Effect WEAKNESS()
 * @method static WitherEffect WITHER()
 */
final class VanillaEffects{
	use RegistryTrait;

	protected static function setup() : void{
		self::register("absorption", new AbsorptionEffect(22, "%potion.absorption", new Color(0x25, 0x52, 0xa5)));
		//TODO: bad_omen
		self::register("blindness", new Effect(15, "%potion.blindness", new Color(0x1f, 0x1f, 0x23), true));
		self::register("conduit_power", new Effect(26, "%potion.conduitPower", new Color(0x1d, 0xc2, 0xd1)));
		self::register("fatal_poison", new PoisonEffect(25, "%potion.poison", new Color(0x4e, 0x93, 0x31), true, true, true));
		self::register("fire_resistance", new Effect(12, "%potion.fireResistance", new Color(0xe4, 0x9a, 0x3a)));
		self::register("haste", new Effect(3, "%potion.digSpeed", new Color(0xd9, 0xc0, 0x43)));
		self::register("health_boost", new HealthBoostEffect(21, "%potion.healthBoost", new Color(0xf8, 0x7d, 0x23)));
		self::register("hunger", new HungerEffect(17, "%potion.hunger", new Color(0x58, 0x76, 0x53), true));
		self::register("instant_damage", new InstantDamageEffect(7, "%potion.harm", new Color(0x43, 0x0a, 0x09), true, false));
		self::register("instant_health", new InstantHealthEffect(6, "%potion.heal", new Color(0xf8, 0x24, 0x23), false, false));
		self::register("invisibility", new InvisibilityEffect(14, "%potion.invisibility", new Color(0x7f, 0x83, 0x92)));
		self::register("jump_boost", new Effect(8, "%potion.jump", new Color(0x22, 0xff, 0x4c)));
		self::register("levitation", new LevitationEffect(24, "%potion.levitation", new Color(0xce, 0xff, 0xff)));
		self::register("mining_fatigue", new Effect(4, "%potion.digSlowDown", new Color(0x4a, 0x42, 0x17), true));
		self::register("nausea", new Effect(9, "%potion.confusion", new Color(0x55, 0x1d, 0x4a), true));
		self::register("night_vision", new Effect(16, "%potion.nightVision", new Color(0x1f, 0x1f, 0xa1)));
		self::register("poison", new PoisonEffect(19, "%potion.poison", new Color(0x4e, 0x93, 0x31), true));
		self::register("regeneration", new RegenerationEffect(10, "%potion.regeneration", new Color(0xcd, 0x5c, 0xab)));
		self::register("resistance", new Effect(11, "%potion.resistance", new Color(0x99, 0x45, 0x3a)));
		self::register("saturation", new SaturationEffect(23, "%potion.saturation", new Color(0xf8, 0x24, 0x23), false));
		//TODO: slow_falling
		self::register("slowness", new SlownessEffect(2, "%potion.moveSlowdown", new Color(0x5a, 0x6c, 0x81), true));
		self::register("speed", new SpeedEffect(1, "%potion.moveSpeed", new Color(0x7c, 0xaf, 0xc6)));
		self::register("strength", new Effect(5, "%potion.damageBoost", new Color(0x93, 0x24, 0x23)));
		//TODO: village_hero
		self::register("water_breathing", new Effect(13, "%potion.waterBreathing", new Color(0x2e, 0x52, 0x99)));
		self::register("weakness", new Effect(18, "%potion.weakness", new Color(0x48, 0x4d, 0x48), true));
		self::register("wither", new WitherEffect(20, "%potion.wither", new Color(0x35, 0x2a, 0x27), true));
	}

	protected static function register(string $name, Effect $member) : void{
		self::_registryRegister($name, $member);
	}

	/**
	 * @return Effect[]
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var Effect[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	public static function fromString(string $name) : Effect{
		$result = self::_registryFromString($name);
		assert($result instanceof Effect);
		return $result;
	}
}
