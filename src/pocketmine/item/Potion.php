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

class Potion extends Item implements Consumable{
	protected static $effects = [];

	public static function init(){
		$data = json_decode(file_get_contents(\pocketmine\PATH . "src/pocketmine/resources/potions.json"), true);
		if(!is_array($data)){
			throw new \RuntimeException("Potions data could not be read");
		}

		foreach($data as $name => $type){
			if(isset($type["effects"][0])){
				$effectData = $type["effects"][0];
				$effect = Effect::getEffectByName($effectData["name"]);

				if(isset($effectData["amplifier"])){
					$effect->setAmplifier($effectData["amplifier"]);
				}

				if(isset($effectData["duration"])){
					$effect->setDuration($effectData["duration"] * 20);
				}

				self::$effects[(int) $type["id"]] = $effect;
				self::$effects[$name] = $effect;
			}
		}
	}

	public static function getEffectByPotionId(int $id){
		if(isset(self::$effects[$id])){
			return clone self::$effects[$id];
		}

		return null;
	}

	public static function getEffectByPotionName(string $name){
		if(isset(self::$effects[$name])){
			return clone self::$effects[$meta];
		}

		return null;
	}

	public function canBeConsumed() : bool{
		return true;
	}

	public function getResidue(){
		return Item::get(Item::GLASS_BOTTLE, 0, 1);
	}

	public function getAdditionalEffects() : array{
		$effects = [];
		if(($effect = Potion::getEffectByPotionId($this->meta)) !== null){
			$effects[] = $effect;
		}
		return $effects;
	}

	public function canBeConsumedBy(Entity $entity) : bool{
		return $entity instanceof Human;
	}

	public function onConsume(Entity $entity){
		foreach($this->getAdditionalEffects() as $effect){
			$entity->addEffect($effect);
		}
		// TODO: finish this off
	}
}