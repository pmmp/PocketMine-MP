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
use pocketmine\math\Vector3;

class Elytra extends Armor{

	private const DAMAGE_INTERVAL = 20;
	private const MAX_DAMAGE = 20;

	private int $damageTimer = self::DAMAGE_INTERVAL;

	private Vector3|null $previousPosition = null;

	public function onTickWorn(Living $entity) : bool {

		if($entity->isGliding()){

			//Apply damage to elytra.
			$this->damageTimer--;
			if($this->damageTimer <= 0){
				$this->applyDamage(1);
				$this->damageTimer = self::DAMAGE_INTERVAL;
			}

			if($entity->isOnGround()){
				$entity->setGliding(false);
			}

			$currentPosition = $entity->getPosition()->asVector3();

			if($this->previousPosition === null){
				// First tick, set previous position
				$this->previousPosition = $currentPosition;
			}

			// Calculate velocity vector between previous and current position
			$velocity = $currentPosition->subtractVector($this->previousPosition);

			$yVelocity = $velocity->y;
			$xzVelocity = $this->getHorizontalVelocity($velocity);

			if($yVelocity < 0.5){
				// Reset fall distance if vertical velocity is low
				$entity->resetFallDistance();
			}

			$speed = $xzVelocity->length(); // Horizontal speed

			if($this->shouldCheckCollision($entity, $speed)){

				// Calculate kinetic energy and damage
				$kineticEnergy = $this->calculateKineticEnergy($speed);
				$damage = $this->calculateDamage($kineticEnergy);

				if($damage > 0){
					// Deal damage if kinetic energy is above threshold
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_CUSTOM, $damage);
					$entity->attack($ev);
				}

			}

			// Update previous position for next tick
			$this->previousPosition = $currentPosition;

			return true;
		}
		return false;
	}

	private function getHorizontalVelocity(Vector3 $velocity) : Vector2 {
		return new Vector2($velocity->x, $velocity->z);
	}

	private function shouldCheckCollision(Living $entity, float $speed) : bool {

		$eyePosition = $entity->getEyePos();
		$direction = $entity->getDirectionVector()->normalize();

		// Trace entity's eye position + direction * speed
		$traceEnd = $eyePosition->addVector($direction->multiply($speed));

		$block = $entity->getWorld()->getBlock($traceEnd);

		// Check if block is solid
		if(!$block->isSolid()){
			return false;
		}

		// Check if distance is within 1-2 blocks
		$traceDistance = $traceEnd->distance($eyePosition);
		if($traceDistance > 2){
			return false;
		}

		return true; // Collision ahead
	}

	private function calculateKineticEnergy(float $speed) : float {
		//10 = mass
		return 0.5 * 10 * $speed ** 2;
	}

	private function calculateDamage(float $kineticEnergy) : int {

		$damage = 0;

		if($kineticEnergy > 100) {
			return 50; // Instant kill
		}

		if($kineticEnergy < 5) {
			return $damage; // No damage below threshold
		}

		$damage = (int)($kineticEnergy - 5); // Subtract threshold

		return min($damage, self::MAX_DAMAGE); // Cap damage

	}

	protected function onBroken() : void {
		//NOOP
	}
}