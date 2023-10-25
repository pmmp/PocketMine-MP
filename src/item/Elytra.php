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
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector2;

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

			//Calculate kinetic energy.
			$xzVector = new Vector2($direction->x, $direction->z);
			$horizontalSpeed = $xzVector->length();

			$eyePos = $entity->getEyePos();
			$viewVector = $direction->normalize();

			$block = $entity->getWorld()->getBlock($eyePos->addVector($viewVector->multiply($horizontalSpeed)));

			if($block->isSolid()){
				$mass = $entity->getHealth() * 10;
				$kineticEnergy = 0.5 * $mass * $horizontalSpeed ** 2;

				$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_CONTACT, $kineticEnergy);
				$entity->attack($ev);
			}

			return true;
		}

		return false;
	}

	protected function onBroken() : void{
		//NOOP
	}
}
