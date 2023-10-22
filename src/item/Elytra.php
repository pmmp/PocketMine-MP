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

	//Check if the player's pitch is within the allowed range between -59 and 38.
	private const MINIMUM_PITCH = -59;
	private const MAXIMUM_PITCH = 38;

	private int $applyDamageTime = 61;

	public function onTickWorn(Living $entity) : bool {
		if ($entity instanceof Human && $entity->isGliding()) {

			$this->applyDamageTime--;
			if($this->applyDamageTime <= 0){
				$this->applyDamage(1);
				$this->applyDamageTime = 61;
			}

			$location = $entity->getLocation();
			$pitch = $location->pitch;

			// Check if the player's pitch is within the allowed range between -59 and 38.
			if ($pitch >= self::MINIMUM_PITCH && $pitch <= self::MAXIMUM_PITCH) {
				$entity->resetFallDistance();
				return true;
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
