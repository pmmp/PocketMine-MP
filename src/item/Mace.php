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

use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;

class Mace extends Tool{

	public function getBlockToolType() : int{
		return BlockToolType::SWORD;
	}

	public function getMaxDurability() : int{
		return 250;
	}

	public function getAttackPoints() : int{
		return 5;
	}

	public function getBlockToolHarvestLevel() : int{
		return 1;
	}

	public function getMiningEfficiency(bool $isCorrectTool) : float{
		return parent::getMiningEfficiency($isCorrectTool) * 1.5;
	}

	public function getBaseMiningEfficiency() : float{
		return 10;
	}

	public function onDestroyBlock(Block $block, array &$returnedItems) : bool{
		if(!$block->getBreakInfo()->breaksInstantly()){
			return $this->applyDamage(2);
		}
		return false;
	}

	public function onAttackEntity(EntityDamageByEntityEvent $damager, Entity $victim, array &$returnedItems) : bool{
		$damageEvent = $victim->getLastDamageCause();

		if($damageEvent instanceof EntityDamageByEntityEvent){

			$user = $damageEvent->getDamager();

			if($user !== null){
				$height = $user->getFallDistance();

				if($height >= 2) {
					$damage = ($height - 1) * 5;
					$victim->setHealth($victim->getHealth() - $damage);

					$motion = $user->getMotion();
					$user->setMotion(new Vector3($motion->x, 0, $motion->z));

					$user->fallDistance = 0;
				}
			}
		}

		$this->applyDamage(2);

		return true;
	}
}
