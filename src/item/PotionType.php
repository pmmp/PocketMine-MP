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
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
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

	protected static function setup() : void{
		self::registerAll(
			new self("water", fn() => []),
			new self("mundane", fn() => []),
			new self("long_mundane", fn() => []),
			new self("thick", fn() => []),
			new self("awkward", fn() => []),
			new self("night_vision", fn() => [
				new EffectInstance(VanillaEffects::NIGHT_VISION(), 3600)
			]),
			new self("long_night_vision", fn() => [
				new EffectInstance(VanillaEffects::NIGHT_VISION(), 9600)
			]),
			new self("invisibility", fn() => [
				new EffectInstance(VanillaEffects::INVISIBILITY(), 3600)
			]),
			new self("long_invisibility", fn() => [
				new EffectInstance(VanillaEffects::INVISIBILITY(), 9600)
			]),
			new self("leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 3600)
			]),
			new self("long_leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 9600)
			]),
			new self("strong_leaping", fn() => [
				new EffectInstance(VanillaEffects::JUMP_BOOST(), 1800, 1)
			]),
			new self("fire_resistance", fn() => [
				new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 3600)
			]),
			new self("long_fire_resistance", fn() => [
				new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 9600)
			]),
			new self("swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 3600)
			]),
			new self("long_swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 9600)
			]),
			new self("strong_swiftness", fn() => [
				new EffectInstance(VanillaEffects::SPEED(), 1800, 1)
			]),
			new self("slowness", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 1800)
			]),
			new self("long_slowness", fn() => [
				new EffectInstance(VanillaEffects::SLOWNESS(), 4800)
			]),
			new self("water_breathing", fn() => [
				new EffectInstance(VanillaEffects::WATER_BREATHING(), 3600)
			]),
			new self("long_water_breathing", fn() => [
				new EffectInstance(VanillaEffects::WATER_BREATHING(), 9600)
			]),
			new self("healing", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_HEALTH())
			]),
			new self("strong_healing", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_HEALTH(), null, 1)
			]),
			new self("harming", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_DAMAGE())
			]),
			new self("strong_harming", fn() => [
				new EffectInstance(VanillaEffects::INSTANT_DAMAGE(), null, 1)
			]),
			new self("poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 900)
			]),
			new self("long_poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 2400)
			]),
			new self("strong_poison", fn() => [
				new EffectInstance(VanillaEffects::POISON(), 440, 1)
			]),
			new self("regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 900)
			]),
			new self("long_regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 2400)
			]),
			new self("strong_regeneration", fn() => [
				new EffectInstance(VanillaEffects::REGENERATION(), 440, 1)
			]),
			new self("strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 3600)
			]),
			new self("long_strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 9600)
			]),
			new self("strong_strength", fn() => [
				new EffectInstance(VanillaEffects::STRENGTH(), 1800, 1)
			]),
			new self("weakness", fn() => [
				new EffectInstance(VanillaEffects::WEAKNESS(), 1800)
			]),
			new self("long_weakness", fn() => [
				new EffectInstance(VanillaEffects::WEAKNESS(), 4800)
			]),
			new self("wither", fn() => [
				new EffectInstance(VanillaEffects::WITHER(), 800, 1)
			]),
			new self("turtle_master", fn() => [
				//TODO
			]),
			new self("long_turtle_master", fn() => [
				//TODO
			]),
			new self("strong_turtle_master", fn() => [
				//TODO
			]),
			new self("slow_falling", fn() => [
				//TODO
			]),
			new self("long_slow_falling", fn() => [
				//TODO
			])
		);
	}

	/** @phpstan-var \Closure() : list<EffectInstance>  */
	private \Closure $effectsGetter;

	/**
	 * @phpstan-param \Closure() : list<EffectInstance> $effectsGetter
	 */
	private function __construct(string $enumName, \Closure $effectsGetter){
		$this->Enum___construct($enumName);
		$this->effectsGetter = $effectsGetter;
	}

	/**
	 * @return EffectInstance[]
	 * @phpstan-return list<EffectInstance>
	 */
	public function getEffects() : array{
		return ($this->effectsGetter)();
	}
}
