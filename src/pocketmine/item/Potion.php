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


namespace pocketmine\item;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDrinkPotionEvent;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\Player;

class Potion extends Item{

	//No effects
	const WATER_BOTTLE = 0;
	const MUNDANE = 1;
	const MUNDANE_EXTENDED = 2;
	const THICK = 3;
	const AWKWARD = 4;

	//Actual potions
	const NIGHT_VISION = 5;
	const NIGHT_VISION_T = 6;
	const INVISIBILITY = 7;
	const INVISIBILITY_T = 8;
	const LEAPING = 9;
	const LEAPING_T = 10;
	const LEAPING_TWO = 11;
	const FIRE_RESISTANCE = 12;
	const FIRE_RESISTANCE_T = 13;
	const SWIFTNESS = 14;
	const SWIFTNESS_T = 15;
	const SWIFTNESS_TWO = 16;
	const SLOWNESS = 17;
	const SLOWNESS_T = 18;
	const WATER_BREATHING = 19;
	const WATER_BREATHING_T = 20;
	const HEALING = 21;
	const HEALING_TWO = 22;
	const HARMING = 23;
	const HARMING_TWO = 24;
	const POISON = 25;
	const POISON_T = 26;
	const POISON_TWO = 27;
	const REGENERATION = 28;
	const REGENERATION_T = 29;
	const REGENERATION_TWO = 30;
	const STRENGTH = 31;
	const STRENGTH_T = 32;
	const STRENGTH_TWO = 33;
	const WEAKNESS = 34;
	const WEAKNESS_T = 35;
	const DECAY = 36; //TODO

	//Structure: Potion ID => [matching effect, duration in ticks, amplifier]
	//Use false if no effects.
	const POTIONS = [
		self::WATER_BOTTLE => false,
		self::MUNDANE => false,
		self::MUNDANE_EXTENDED => false,
		self::THICK => false,
		self::AWKWARD => false,

		self::NIGHT_VISION => [Effect::NIGHT_VISION, (180 * 20), 0],
		self::NIGHT_VISION_T =>	[Effect::NIGHT_VISION, (480 * 20), 0],

		self::INVISIBILITY => [Effect::INVISIBILITY, (180 * 20), 0],
		self::INVISIBILITY_T => [Effect::INVISIBILITY, (480 * 20), 0],

		self::LEAPING => [Effect::JUMP, (180 * 20), 0],
		self::LEAPING_T => [Effect::JUMP, (480 * 20), 0],
		self::LEAPING_TWO => [Effect::JUMP, (90 * 20), 1],

		self::FIRE_RESISTANCE => [Effect::FIRE_RESISTANCE, (180 * 20), 0],
		self::FIRE_RESISTANCE_T => [Effect::FIRE_RESISTANCE, (480 * 20), 0],

		self::SWIFTNESS => [Effect::SPEED, (180 * 20), 0],
		self::SWIFTNESS_T => [Effect::SPEED, (480 * 20), 0],
		self::SWIFTNESS_TWO => [Effect::SPEED, (90 * 20), 1],

		self::SLOWNESS => [Effect::SLOWNESS, (90 * 20), 0],
		self::SLOWNESS_T => [Effect::SLOWNESS, (240 * 20), 0],

		self::WATER_BREATHING => [Effect::WATER_BREATHING, (180 * 20), 0],
		self::WATER_BREATHING_T => [Effect::WATER_BREATHING, (480 * 20), 0],

		self::HEALING => [Effect::HEALING, (1), 0],
		self::HEALING_TWO => [Effect::HEALING, (1), 1],

		self::HARMING => [Effect::HARMING, (1), 0],
		self::HARMING_TWO => [Effect::HARMING, (1), 1],

		self::POISON => [Effect::POISON, (45 * 20), 0],
		self::POISON_T => [Effect::POISON, (120 * 20), 0],
		self::POISON_TWO => [Effect::POISON, (22 * 20), 1],

		self::REGENERATION => [Effect::REGENERATION, (45 * 20), 0],
		self::REGENERATION_T => [Effect::REGENERATION, (120 * 20), 0],
		self::REGENERATION_TWO => [Effect::REGENERATION, (22 * 20), 1],

		self::STRENGTH => [Effect::STRENGTH, (180 * 20), 0],
		self::STRENGTH_T => [Effect::STRENGTH, (480 * 20), 0],
		self::STRENGTH_TWO => [Effect::STRENGTH, (90 * 20), 1],

		self::WEAKNESS => [Effect::WEAKNESS, (90 * 20), 0],
		self::WEAKNESS_T => [Effect::WEAKNESS, (240 * 20), 0]
	];

	public function __construct($meta = 0, $count = 1){
		parent::__construct(self::POTION, $meta, $count, self::getNameByMeta($meta));
	}

	public static function getColor(int $meta){
		$effect = Effect::getEffect(self::getEffectId($meta));
		if($effect !== null){
			return $effect->getColor();
		}
		return [0, 0, 0];
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function canBeConsumed() : bool{
		return $this->meta > 0;
	}

	public function canBeConsumedBy(Entity $entity) : bool{
		return $entity instanceof Human;
	}

	public function getEffects(): array{
		return self::getEffectsById($this->meta);
	}

	/**
	 * @param int $id
	 * @return Effect[]
	 */
	public static function getEffectsById(int $id) : array{
		if(count(self::POTIONS[$id] ?? []) === 3){
			return [Effect::getEffect(self::POTIONS[$id][0])->setDuration(self::POTIONS[$id][1])->setAmplifier(self::POTIONS[$id][2])];
		}
		return [];
	}


	public function onConsume(Entity $human){
		$pk = new EntityEventPacket();
		$pk->eid = $human->getId();
		$pk->event = EntityEventPacket::USE_ITEM;
		if($human instanceof Player){
			$human->dataPacket($pk);
		}
		$server = $human->getLevel()->getServer();
		$server->broadcastPacket($human->getViewers(), $pk);

		$server->getPluginManager()->callEvent($ev = new EntityDrinkPotionEvent($human, $this));

		if(!$ev->isCancelled()){
			foreach($ev->getEffects() as $effect){
				$human->addEffect($effect);
			}
			//Don't set the held item to glass bottle if we're in creative
			if($human instanceof Player){
				if($human->getGamemode() === 1){
					return;
				}
			}
			$human->getInventory()->setItemInHand(Item::get(self::GLASS_BOTTLE));
		}


	}

	public static function getEffectId(int $meta) : int{
		switch($meta){
			case self::INVISIBILITY:
			case self::INVISIBILITY_T:
				return Effect::INVISIBILITY;
			case self::LEAPING:
			case self::LEAPING_T:
			case self::LEAPING_TWO:
				return Effect::JUMP;
			case self::FIRE_RESISTANCE:
			case self::FIRE_RESISTANCE_T:
				return Effect::FIRE_RESISTANCE;
			case self::SWIFTNESS:
			case self::SWIFTNESS_T:
			case self::SWIFTNESS_TWO:
				return Effect::SPEED;
			case self::SLOWNESS:
			case self::SLOWNESS_T:
				return Effect::SLOWNESS;
			case self::WATER_BREATHING:
			case self::WATER_BREATHING_T:
				return Effect::WATER_BREATHING;
			case self::HARMING:
			case self::HARMING_TWO:
				return Effect::HARMING;
			case self::POISON:
			case self::POISON_T:
			case self::POISON_TWO:
				return Effect::POISON;
			case self::HEALING:
			case self::HEALING_TWO:
				return Effect::HEALING;
			case self::NIGHT_VISION:
			case self::NIGHT_VISION_T:
				return Effect::NIGHT_VISION;
			case self::REGENERATION:
			case self::REGENERATION_T:
			case self::REGENERATION_TWO:
				return Effect::REGENERATION;
			default:
				return 0;
		}
	}

	public static function getNameByMeta(int $meta) : string{
		switch($meta){
			case self::WATER_BOTTLE:
				return "Water Bottle";
			case self::MUNDANE:
			case self::MUNDANE_EXTENDED:
				return "Mundane Potion";
			case self::THICK:
				return "Thick Potion";
			case self::AWKWARD:
				return "Awkward Potion";
			case self::INVISIBILITY:
			case self::INVISIBILITY_T:
				return "Potion of Invisibility";
			case self::LEAPING:
			case self::LEAPING_T:
				return "Potion of Leaping";
			case self::LEAPING_TWO:
				return "Potion of Leaping II";
			case self::FIRE_RESISTANCE:
			case self::FIRE_RESISTANCE_T:
				return "Potion of Fire Resistance";
			case self::SWIFTNESS:
			case self::SWIFTNESS_T:
				return "Potion of Swiftness";
			case self::SWIFTNESS_TWO:
				return "Potion of Swiftness II";
			case self::SLOWNESS:
			case self::SLOWNESS_T:
				return "Potion of Slowness";
			case self::WATER_BREATHING:
			case self::WATER_BREATHING_T:
				return "Potion of Water Breathing";
			case self::HARMING:
				return "Potion of Harming";
			case self::HARMING_TWO:
				return "Potion of Harming II";
			case self::POISON:
			case self::POISON_T:
				return "Potion of Poison";
			case self::POISON_TWO:
				return "Potion of Poison II";
			case self::HEALING:
				return "Potion of Healing";
			case self::HEALING_TWO:
				return "Potion of Healing II";
			case self::NIGHT_VISION:
			case self::NIGHT_VISION_T:
				return "Potion of Night Vision";
			case self::STRENGTH:
			case self::STRENGTH_T:
				return "Potion of Strength";
			case self::STRENGTH_TWO:
				return "Potion of Strength II";
			case self::REGENERATION:
			case self::REGENERATION_T:
				return "Potion of Regeneration";
			case self::REGENERATION_TWO:
				return "Potion of Regeneration II";
			case self::WEAKNESS:
			case self::WEAKNESS_T:
				return "Potion of Weakness";
			default:
				return "Potion";
		}
	}

}
