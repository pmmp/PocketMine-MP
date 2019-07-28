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

use pocketmine\entity\Entity;
use pocketmine\entity\Mob;

class LookAtTargetBehavior extends LookAtEntityBehavior{

	public function __construct(Mob $mob, float $lookDistance = 8.0){
		parent::__construct($mob, Entity::class, $lookDistance);
	}

	public function canStart() : bool{
		$target = $this->mob->getTargetEntity();

		if($target !== null){
			$this->nearestEntity = $target;

			return true;
		}

		return false;
	}
}