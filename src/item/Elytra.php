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

	public const MINIMUM_PITCH = -59;
	public const MAXIMUM_PITCH = 38;

	public function onTickWorn(Living $entity) : bool{
		if($entity instanceof Human){
			if(!$entity->isGliding()){
				$location = $entity->getLocation();
				if($location->pitch >= self::MINIMUM_PITCH and $location->pitch <= self::MAXIMUM_PITCH){
					$entity->resetFallDistance();
					return true;
				}
			}else{
				$this->applyDamage(1);
				return true;
			}
		}

		return false;
	}
}
