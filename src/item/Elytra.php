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

use pocketmine\entity\Human;
use pocketmine\entity\Living;

class Elytra extends Armor{

	private const MINIMUM_PITCH = -59;
	private const MAXIMUM_PITCH = 38;

	private const APPLY_DAMAGE_INTERVAL = 21;
	private int $applyDamageTime = self::APPLY_DAMAGE_INTERVAL;

	public function onTickWorn(Living $entity) : bool {
		if ($entity instanceof Human && $entity->isGliding()) {

			$this->applyDamageTime--;
			if($this->applyDamageTime <= 0){
				$this->applyDamage(1);
				$this->applyDamageTime = self::APPLY_DAMAGE_INTERVAL;
			}

			$pitch = $entity->getLocation()->pitch;
			// Check if the player's pitch is within the allowed range between -59 and 38.
			if ($pitch >= self::MINIMUM_PITCH && $pitch <= self::MAXIMUM_PITCH) {
				$entity->resetFallDistance();
				$entity->setFallDistance(0.0);
				return true;
			}

			// If the player is on the ground, reset the fall distance and disable gliding.
			if ($entity->isOnGround()) {
				$entity->setGliding(false);
				$entity->resetFallDistance();
				$entity->setFallDistance(0.0);
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
