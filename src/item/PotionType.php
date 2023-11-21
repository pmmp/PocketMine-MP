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

namespace pocketmine\item;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\utils\LegacyEnumShimTrait;
use function spl_object_id;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static PotionType AWKWARD()
 * @method static PotionType FIRE_RESISTANCE()
 * @method static PotionType HARMING()
 * @method static PotionType HEALING()
 * @method static PotionType INVISIBILITY()
 * @method static PotionType LEAPING()
 * @method static PotionType LONG_FIRE_RESISTANCE()
 * @method static PotionType LONG_INVISIBILITY()
 * @method static PotionType LONG_LEAPING()
 * @method static PotionType LONG_MUNDANE()
 * @method static PotionType LONG_NIGHT_VISION()
 * @method static PotionType LONG_POISON()
 * @method static PotionType LONG_REGENERATION()
 * @method static PotionType LONG_SLOWNESS()
 * @method static PotionType LONG_SLOW_FALLING()
 * @method static PotionType LONG_STRENGTH()
 * @method static PotionType LONG_SWIFTNESS()
 * @method static PotionType LONG_TURTLE_MASTER()
 * @method static PotionType LONG_WATER_BREATHING()
 * @method static PotionType LONG_WEAKNESS()
 * @method static PotionType MUNDANE()
 * @method static PotionType NIGHT_VISION()
 * @method static PotionType POISON()
 * @method static PotionType REGENERATION()
 * @method static PotionType SLOWNESS()
 * @method static PotionType SLOW_FALLING()
 * @method static PotionType STRENGTH()
 * @method static PotionType STRONG_HARMING()
 * @method static PotionType STRONG_HEALING()
 * @method static PotionType STRONG_LEAPING()
 * @method static PotionType STRONG_POISON()
 * @method static PotionType STRONG_REGENERATION()
 * @method static PotionType STRONG_SLOWNESS()
 * @method static PotionType STRONG_STRENGTH()
 * @method static PotionType STRONG_SWIFTNESS()
 * @method static PotionType STRONG_TURTLE_MASTER()
 * @method static PotionType SWIFTNESS()
 * @method static PotionType THICK()
 * @method static PotionType TURTLE_MASTER()
 * @method static PotionType WATER()
 * @method static PotionType WATER_BREATHING()
 * @method static PotionType WEAKNESS()
 * @method static PotionType WITHER()
 *
 * @phpstan-type TMetadata array{0: string, 1: \Closure() : list<EffectInstance>}
 */
enum PotionType{
	use LegacyEnumShimTrait;

	case WATER;
	case MUNDANE;
	case LONG_MUNDANE;
	case THICK;
	case AWKWARD;
	case NIGHT_VISION;
	case LONG_NIGHT_VISION;
	case INVISIBILITY;
	case LONG_INVISIBILITY;
	case LEAPING;
	case LONG_LEAPING;
	case STRONG_LEAPING;
	case FIRE_RESISTANCE;
	case LONG_FIRE_RESISTANCE;
	case SWIFTNESS;
	case LONG_SWIFTNESS;
	case STRONG_SWIFTNESS;
	case SLOWNESS;
	case LONG_SLOWNESS;
	case WATER_BREATHING;
	case LONG_WATER_BREATHING;
	case HEALING;
	case STRONG_HEALING;
	case HARMING;
	case STRONG_HARMING;
	case POISON;
	case LONG_POISON;
	case STRONG_POISON;
	case REGENERATION;
	case LONG_REGENERATION;
	case STRONG_REGENERATION;
	case STRENGTH;
	case LONG_STRENGTH;
	case STRONG_STRENGTH;
	case WEAKNESS;
	case LONG_WEAKNESS;
	case WITHER;
	case TURTLE_MASTER;
	case LONG_TURTLE_MASTER;
	case STRONG_TURTLE_MASTER;
	case SLOW_FALLING;
	case LONG_SLOW_FALLING;
	case STRONG_SLOWNESS;

	/**
	 * @phpstan-return TMetadata
	 */
	private function getMetadata() : array{
		/** @phpstan-var array<int, TMetadata> $cache */
		static $cache = [];

		return $cache[spl_object_id($this)] ??= match($this){
			self::WATER => ["Water", fn() => []],
			self::MUNDANE => ["Mundane", fn() => []],
			self::LONG_MUNDANE => ["Long Mundane", fn() => []],
			self::THICK => ["Thick", fn() => []],
			self::AWKWARD => ["Awkward", fn() => []],
			self::NIGHT_VISION => ["Night Vision", fn() => [
				new EffectInstance(VanillaEffects::NIGHT_VISION(), 3600)
			]],
			self::LONG_NIGHT_VISION => ["Long Night Vision", fn() => [
				new EffectInstance(VanillaEffects::NIGHT_VISION(), 9600)
			]],
			self::INVISIBILITY => ["Invisibility", fn() => [
				new EffectInstance(VanillaEffects::INVISIBILITY(), 3600)
			]],
			self::LONG_INVISIBILITY => ["Long Invisibility", fn() => [
				new EffectInstance(VanillaEffects::INVISIBILITY(), 9600)
			]],
			self::LEAPING => ["Leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 3600)
			]],
			self::LONG_LEAPING => ["Long Leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 9600)
			]],
			self::STRONG_LEAPING => ["Strong Leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 1800, 1)
			]],
			self::FIRE_RESISTANCE => ["Fire Resistance", fn() => [
				new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 3600)
			]],
			self::LONG_FIRE_RESISTANCE => ["Long Fire Resistance", fn() => [
				new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 9600)
			]],
			self::SWIFTNESS => ["Swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 3600)
			]],
			self::LONG_SWIFTNESS => ["Long Swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 9600)
			]],
			self::STRONG_SWIFTNESS => ["Strong Swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 1800, 1)
			]],
			self::SLOWNESS => ["Slowness", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 1800)
			]],
			self::LONG_SLOWNESS => ["Long Slowness", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 4800)
			]],
			self::WATER_BREATHING => ["Water Breathing", fn() => [
				new EffectInstance(VanillaEffects::WATER_BREATHING(), 3600)
			]],
			self::LONG_WATER_BREATHING => ["Long Water Breathing", fn() => [
				new EffectInstance(VanillaEffects::WATER_BREATHING(), 9600)
			]],
			self::HEALING => ["Healing", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_HEALTH())
			]],
			self::STRONG_HEALING => ["Strong Healing", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_HEALTH(), null, 1)
			]],
			self::HARMING => ["Harming", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_DAMAGE())
			]],
			self::STRONG_HARMING => ["Strong Harming", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_DAMAGE(), null, 1)
			]],
			self::POISON => ["Poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 900)
			]],
			self::LONG_POISON => ["Long Poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 2400)
			]],
			self::STRONG_POISON => ["Strong Poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 440, 1)
			]],
			self::REGENERATION => ["Regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 900)
			]],
			self::LONG_REGENERATION => ["Long Regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 2400)
			]],
			self::STRONG_REGENERATION => ["Strong Regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 440, 1)
			]],
			self::STRENGTH => ["Strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 3600)
			]],
			self::LONG_STRENGTH => ["Long Strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 9600)
			]],
			self::STRONG_STRENGTH => ["Strong Strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 1800, 1)
			]],
			self::WEAKNESS => ["Weakness", fn() => [
				new EffectInstance(VanillaEffects::WEAKNESS(), 1800)
			]],
			self::LONG_WEAKNESS => ["Long Weakness", fn() => [
				new EffectInstance(VanillaEffects::WEAKNESS(), 4800)
			]],
			self::WITHER => ["Wither", fn() => [
				new EffectInstance(VanillaEffects::WITHER(), 800, 1)
			]],
			self::TURTLE_MASTER => ["Turtle Master", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 20, 3),
				new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 20, 2),
			]],
			self::LONG_TURTLE_MASTER => ["Long Turtle Master", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 40 * 20, 3),
				new EffectInstance(VanillaEffects::RESISTANCE(), 40 * 20, 2),
			]],
			self::STRONG_TURTLE_MASTER => ["Strong Turtle Master", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 20, 5),
				new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 20, 3),
			]],
			self::SLOW_FALLING => ["Slow Falling", fn() => [
				//TODO
			]],
			self::LONG_SLOW_FALLING => ["Long Slow Falling", fn() => [
				//TODO
			]],
			self::STRONG_SLOWNESS => ["Strong Slowness", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 20 * 20, 3)
			]]
		};
	}

	public function getDisplayName() : string{ return $this->getMetadata()[0]; }

	/**
	 * @return EffectInstance[]
	 * @phpstan-return list<EffectInstance>
	 */
	public function getEffects() : array{
		return ($this->getMetadata()[1])();
	}
}
