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

use pocketmine\entity\Living;

class Elytra extends Armor{

	private const APPLY_DAMAGE_INTERVAL = 20;
	private int $applyDamageTime = self::APPLY_DAMAGE_INTERVAL;

	public function onTickWorn(Living $entity) : bool {
		if ($entity->isGliding()) {

			$direction = $entity->getDirectionVector();
			$yVel = $direction->y;

			if($yVel < 0.5){
				$entity->resetFallDistance();
			}

			/**
			 * $eyePos = $entity->getEyePos();
			 * $viewVector = $directionVector->normalize();
			 * $length = $xzVel; // Trace the full speed distance
			 * $blockPos = $eyePos->addVector($viewVector->multiply($length));
			 *
			 * $block = $entity->getWorld()->getBlock($blockPos->floor());
			 *
			 * if($block->isSolid()) {
			 * $health = $entity->getHealth();
			 * $mass = $health * 10; // 10 kg per health point
			 *
			 * $energy = 0.5 * $mass * $xzVel ** 2;
			 * $entity->setHealth($health - $energy);
			 * }
			 */

			//Apply damage to elytra.
			$this->applyDamageTime--;
			if($this->applyDamageTime <= 0){
				$this->applyDamage(1);
				$this->applyDamageTime = self::APPLY_DAMAGE_INTERVAL;
			}

			// If the player is on the ground, reset the fall distance and disable gliding.
			if ($entity->isOnGround()) {
				$entity->resetFallDistance();
				$entity->setGliding(false);
				return true;
			}

			return true;
		}

		return false;
	}

	protected function onBroken() : void{
		//NOOP
	}
}
