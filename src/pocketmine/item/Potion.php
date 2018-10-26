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

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Living;

class Potion extends Item implements Consumable{

	public const WATER = 0;
	public const MUNDANE = 1;
	public const LONG_MUNDANE = 2;
	public const THICK = 3;
	public const AWKWARD = 4;
	public const NIGHT_VISION = 5;
	public const LONG_NIGHT_VISION = 6;
	public const INVISIBILITY = 7;
	public const LONG_INVISIBILITY = 8;
	public const LEAPING = 9;
	public const LONG_LEAPING = 10;
	public const STRONG_LEAPING = 11;
	public const FIRE_RESISTANCE = 12;
	public const LONG_FIRE_RESISTANCE = 13;
	public const SWIFTNESS = 14;
	public const LONG_SWIFTNESS = 15;
	public const STRONG_SWIFTNESS = 16;
	public const SLOWNESS = 17;
	public const LONG_SLOWNESS = 18;
	public const WATER_BREATHING = 19;
	public const LONG_WATER_BREATHING = 20;
	public const HEALING = 21;
	public const STRONG_HEALING = 22;
	public const HARMING = 23;
	public const STRONG_HARMING = 24;
	public const POISON = 25;
	public const LONG_POISON = 26;
	public const STRONG_POISON = 27;
	public const REGENERATION = 28;
	public const LONG_REGENERATION = 29;
	public const STRONG_REGENERATION = 30;
	public const STRENGTH = 31;
	public const LONG_STRENGTH = 32;
	public const STRONG_STRENGTH = 33;
	public const WEAKNESS = 34;
	public const LONG_WEAKNESS = 35;
	public const WITHER = 36;

	public const ALL = [
		self::WATER,
		self::MUNDANE,
		self::LONG_MUNDANE,
		self::THICK,
		self::AWKWARD,
		self::NIGHT_VISION,
		self::LONG_NIGHT_VISION,
		self::INVISIBILITY,
		self::LONG_INVISIBILITY,
		self::LEAPING,
		self::LONG_LEAPING,
		self::STRONG_LEAPING,
		self::FIRE_RESISTANCE,
		self::LONG_FIRE_RESISTANCE,
		self::SWIFTNESS,
		self::LONG_SWIFTNESS,
		self::STRONG_SWIFTNESS,
		self::SLOWNESS,
		self::LONG_SLOWNESS,
		self::WATER_BREATHING,
		self::LONG_WATER_BREATHING,
		self::HEALING,
		self::STRONG_HEALING,
		self::HARMING,
		self::STRONG_HARMING,
		self::POISON,
		self::LONG_POISON,
		self::STRONG_POISON,
		self::REGENERATION,
		self::LONG_REGENERATION,
		self::STRONG_REGENERATION,
		self::STRENGTH,
		self::LONG_STRENGTH,
		self::STRONG_STRENGTH,
		self::WEAKNESS,
		self::LONG_WEAKNESS,
		self::WITHER
	];

	/**
	 * Returns a list of effects applied by potions with the specified ID.
	 *
	 * @param int $id
	 *
	 * @return EffectInstance[]
	 */
	public static function getPotionEffectsById(int $id) : array{
		switch($id){
			case self::WATER:
			case self::MUNDANE:
			case self::LONG_MUNDANE:
			case self::THICK:
			case self::AWKWARD:
				return [];
			case self::NIGHT_VISION:
				return [
					new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 3600)
				];
			case self::LONG_NIGHT_VISION:
				return [
					new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 9600)
				];
			case self::INVISIBILITY:
				return [
					new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 3600)
				];
			case self::LONG_INVISIBILITY:
				return [
					new EffectInstance(Effect::getEffect(Effect::INVISIBILITY), 9600)
				];
			case self::LEAPING:
				return [
					new EffectInstance(Effect::getEffect(Effect::JUMP_BOOST), 3600)
				];
			case self::LONG_LEAPING:
				return [
					new EffectInstance(Effect::getEffect(Effect::JUMP_BOOST), 9600)
				];
			case self::STRONG_LEAPING:
				return [
					new EffectInstance(Effect::getEffect(Effect::JUMP_BOOST), 1800, 1)
				];
			case self::FIRE_RESISTANCE:
				return [
					new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 3600)
				];
			case self::LONG_FIRE_RESISTANCE:
				return [
					new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 9600)
				];
			case self::SWIFTNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SPEED), 3600)
				];
			case self::LONG_SWIFTNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SPEED), 9600)
				];
			case self::STRONG_SWIFTNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SPEED), 1800, 1)
				];
			case self::SLOWNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 1800)
				];
			case self::LONG_SLOWNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::SLOWNESS), 4800)
				];
			case self::WATER_BREATHING:
				return [
					new EffectInstance(Effect::getEffect(Effect::WATER_BREATHING), 3600)
				];
			case self::LONG_WATER_BREATHING:
				return [
					new EffectInstance(Effect::getEffect(Effect::WATER_BREATHING), 9600)
				];
			case self::HEALING:
				return [
					new EffectInstance(Effect::getEffect(Effect::INSTANT_HEALTH))
				];
			case self::STRONG_HEALING:
				return [
					new EffectInstance(Effect::getEffect(Effect::INSTANT_HEALTH), null, 1)
				];
			case self::HARMING:
				return [
					new EffectInstance(Effect::getEffect(Effect::INSTANT_DAMAGE))
				];
			case self::STRONG_HARMING:
				return [
					new EffectInstance(Effect::getEffect(Effect::INSTANT_DAMAGE), null, 1)
				];
			case self::POISON:
				return [
					new EffectInstance(Effect::getEffect(Effect::POISON), 900)
				];
			case self::LONG_POISON:
				return [
					new EffectInstance(Effect::getEffect(Effect::POISON), 2400)
				];
			case self::STRONG_POISON:
				return [
					new EffectInstance(Effect::getEffect(Effect::POISON), 440, 1)
				];
			case self::REGENERATION:
				return [
					new EffectInstance(Effect::getEffect(Effect::REGENERATION), 900)
				];
			case self::LONG_REGENERATION:
				return [
					new EffectInstance(Effect::getEffect(Effect::REGENERATION), 2400)
				];
			case self::STRONG_REGENERATION:
				return [
					new EffectInstance(Effect::getEffect(Effect::REGENERATION), 440, 1)
				];
			case self::STRENGTH:
				return [
					new EffectInstance(Effect::getEffect(Effect::STRENGTH), 3600)
				];
			case self::LONG_STRENGTH:
				return [
					new EffectInstance(Effect::getEffect(Effect::STRENGTH), 9600)
				];
			case self::STRONG_STRENGTH:
				return [
					new EffectInstance(Effect::getEffect(Effect::STRENGTH), 1800, 1)
				];
			case self::WEAKNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::WEAKNESS), 1800)
				];
			case self::LONG_WEAKNESS:
				return [
					new EffectInstance(Effect::getEffect(Effect::WEAKNESS), 4800)
				];
			case self::WITHER:
				return [
					new EffectInstance(Effect::getEffect(Effect::WITHER), 800, 1)
				];
		}

		return [];
	}

	public function __construct(int $variant){
		parent::__construct(self::POTION, $variant, "Potion");
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function onConsume(Living $consumer){

	}

	public function getAdditionalEffects() : array{
		//TODO: check CustomPotionEffects NBT
		return self::getPotionEffectsById($this->meta);
	}

	public function getResidue(){
		return ItemFactory::get(Item::GLASS_BOTTLE);
	}
}
