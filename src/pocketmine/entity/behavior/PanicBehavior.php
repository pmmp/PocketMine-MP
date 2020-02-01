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

namespace pocketmine\entity\behavior;

use pocketmine\entity\Mob;
use pocketmine\entity\utils\RandomPositionGenerator;

class PanicBehavior extends RandomStrollBehavior{

	public function __construct(Mob $mob, float $speedMultiplier = 1.0){
		parent::__construct($mob, $speedMultiplier, 0);
	}

	public function canStart() : bool{
		if($this->mob->getRevengeTarget() !== null or $this->mob->isOnFire()){
			$this->targetPos = RandomPositionGenerator::findRandomTargetBlock($this->mob, 5, 4);

			if($this->targetPos !== null){
				return true;
			}
		}
		return false;
	}
}