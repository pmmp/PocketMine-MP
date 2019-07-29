<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\hostile;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\level\particle\Particle;
use pocketmine\Player;

class MagmaCube extends Slime{

	public const NETWORK_ID = self::MAGMA_CUBE;

	public function getName() : string{
		return "Magma Cube";
	}

	public function getAttackStrength() : float{
		return parent::getAttackStrength() + 2;
	}

	public function canDamagePlayer() : bool{
		return true;
	}

	public function makesSoundOnLand() : bool{
		return true;
	}

	public function fall(float $fallDistance) : void{
		// NOOP
	}

	public function handleLavaJump() : void{
		$this->motion->y = 0.22 + $this->getSlimeSize() * 0.05;
	}

	public function jump() : void{
		$this->motion->y = 0.42 + $this->getSlimeSize() * 0.1;
	}

	protected function alterSquishAmount() : void{
		$this->squishAmount *= 0.9;
	}

	public function getJumpDelay() : int{
		return parent::getJumpDelay() * 4;
	}

	public function setOnFire(int $seconds) : void{
		// NOOP
	}

	public function getDrops() : array{
		$drops = [];

		if($this->getSlimeSize() > 1){
			$i = $this->random->nextBoundedInt(4) - 2;
			$loot = 0;

			$cause = $this->getLastDamageCause();
			if($cause instanceof EntityDamageByEntityEvent){
				$player = $cause->getDamager();
				if($player instanceof Player){
					$item = $player->getInventory()->getItemInHand();
					if($item->hasEnchantment(Enchantment::LOOTING)){
						$loot = $item->getEnchantmentLevel(Enchantment::LOOTING);
					}
				}
			}

			if($loot > 0){
				$i += $this->random->nextBoundedInt($loot + 1);
			}

			for($j = 0; $j < $i; $j++){
				$drops[] = ItemFactory::get(Item::MAGMA_CREAM);
			}
		}

		return $drops;
	}

	protected function getParticleType() : int{
		return Particle::TYPE_FLAME;
	}

	public function getArmorPoints() : int{
		return $this->getSlimeSize() * 3;
	}

	public function canSpawnHere() : bool{
		return $this->level->getDifficulty() !== Level::DIFFICULTY_PEACEFUL;
	}

	protected function addAttributes() : void{
		parent::addAttributes();

		$this->setMovementSpeed(0.2);
	}
}