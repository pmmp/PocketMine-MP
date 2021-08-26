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
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
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
 */
final class PotionType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	private string $displayName;

	protected static function setup() : void{
		self::registerAll(
			new self("water", "Water", fn() => []),
			new self("mundane", "Mundane", fn() => []),
			new self("long_mundane", "Long Mundane", fn() => []),
			new self("thick", "Thick", fn() => []),
			new self("awkward", "Awkward", fn() => []),
			new self("night_vision", "Night Vision", fn() => [
				new EffectInstance(VanillaEffects::NIGHT_VISION(), 3600)
			]),
			new self("long_night_vision", "Long Night Vision", fn() => [
				new EffectInstance(VanillaEffects::NIGHT_VISION(), 9600)
			]),
			new self("invisibility", "Invisibility", fn() => [
				new EffectInstance(VanillaEffects::INVISIBILITY(), 3600)
			]),
			new self("long_invisibility", "Long Invisibility", fn() => [
				new EffectInstance(VanillaEffects::INVISIBILITY(), 9600)
			]),
			new self("leaping", "Leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 3600)
			]),
			new self("long_leaping", "Long Leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 9600)
			]),
			new self("strong_leaping", "Strong Leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 1800, 1)
			]),
			new self("fire_resistance", "Fire Resistance", fn() => [
				new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 3600)
			]),
			new self("long_fire_resistance", "Long Fire Resistance", fn() => [
				new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 9600)
			]),
			new self("swiftness", "Swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 3600)
			]),
			new self("long_swiftness", "Long Swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 9600)
			]),
			new self("strong_swiftness", "Strong Swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 1800, 1)
			]),
			new self("slowness", "Slowness", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 1800)
			]),
			new self("long_slowness", "Long Slowness", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 4800)
			]),
			new self("water_breathing", "Water Breathing", fn() => [
				new EffectInstance(VanillaEffects::WATER_BREATHING(), 3600)
			]),
			new self("long_water_breathing", "Long Water Breathing", fn() => [
				new EffectInstance(VanillaEffects::WATER_BREATHING(), 9600)
			]),
			new self("healing", "Healing", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_HEALTH())
			]),
			new self("strong_healing", "Strong Healing", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_HEALTH(), null, 1)
			]),
			new self("harming", "Harming", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_DAMAGE())
			]),
			new self("strong_harming", "Strong Harming", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_DAMAGE(), null, 1)
			]),
			new self("poison", "Poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 900)
			]),
			new self("long_poison", "Long Poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 2400)
			]),
			new self("strong_poison", "Strong Poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 440, 1)
			]),
			new self("regeneration", "Regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 900)
			]),
			new self("long_regeneration", "Long Regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 2400)
			]),
			new self("strong_regeneration", "Strong Regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 440, 1)
			]),
			new self("strength", "Strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 3600)
			]),
			new self("long_strength", "Long Strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 9600)
			]),
			new self("strong_strength", "Strong Strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 1800, 1)
			]),
			new self("weakness", "Weakness", fn() => [
				new EffectInstance(VanillaEffects::WEAKNESS(), 1800)
			]),
			new self("long_weakness", "Long Weakness", fn() => [
				new EffectInstance(VanillaEffects::WEAKNESS(), 4800)
			]),
			new self("wither", "Wither", fn() => [
				new EffectInstance(VanillaEffects::WITHER(), 800, 1)
			]),
			new self("turtle_master", "Turtle Master", fn() => [
				//TODO
			]),
			new self("long_turtle_master", "Long Turtle Master", fn() => [
				//TODO
			]),
			new self("strong_turtle_master", "Strong Turtle Master", fn() => [
				//TODO
			]),
			new self("slow_falling", "Slow Falling", fn() => [
				//TODO
			]),
			new self("long_slow_falling", "Long Slow Falling", fn() => [
				//TODO
			])
		);
	}

	/** @phpstan-var \Closure() : list<EffectInstance>  */
	private \Closure $effectsGetter;

	/**
	 * @phpstan-param \Closure() : list<EffectInstance> $effectsGetter
	 */
	private function __construct(string $enumName, string $displayName, \Closure $effectsGetter){
		$this->Enum___construct($enumName);
		$this->displayName = $displayName;
		$this->effectsGetter = $effectsGetter;
	}

	public function getDisplayName() : string{ return $this->displayName; }

	/**
	 * @return EffectInstance[]
	 * @phpstan-return list<EffectInstance>
	 */
	public function getEffects() : array{
		return ($this->effectsGetter)();
	}
}
