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

namespace pocketmine\item\enchantment;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\Shears;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class ThornsEnchantment extends Enchantment{

	public function getMinEnchantAbility(int $level) : int{
		return 10 + ($level - 1) * 20;
	}

	public function getMaxEnchantAbility(int $level) : int{
		return $this->getMinEnchantAbility($level) + 50;
	}

	public function onHurtEntity(Entity $attacker, Entity $victim, Item $item, int $enchantmentLevel) : void{
		if($attacker instanceof Human){
			if($enchantmentLevel > 0 and $victim->random->nextFloat() < 0.15 * $enchantmentLevel){
				$victim->attack(new EntityDamageByEntityEvent($attacker, $victim, EntityDamageEvent::CAUSE_ENTITY_ATTACK, ($enchantmentLevel > 10 ? $enchantmentLevel - 10 : 1 + $victim->random->nextBoundedInt(4))));
				$victim->level->broadcastLevelSoundEvent($victim, LevelSoundEventPacket::SOUND_THORNS);

				if($item instanceof Durable){
					$item->applyDamage(3);
				}
			}elseif($item instanceof Durable){
				$item->applyDamage(1);
			}
		}
	}
}
