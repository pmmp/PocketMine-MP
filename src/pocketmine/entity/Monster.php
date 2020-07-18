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

declare(strict_types = 1);

namespace pocketmine\entity;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use function max;

abstract class Monster extends Mob{

	protected function isValidLightLevel() : bool{
		$x = $this->getFloorX();
		$y = $this->getFloorY();
		$z = $this->getFloorZ();

		if($this->level->getBlockLightAt($x, $y, $z) < $this->random->nextBoundedInt(32)){
			$i = max(
				$this->level->getRealBlockSkyLightAt($x, $y + 1, $z),
				$this->level->getRealBlockSkyLightAt($x, $y - 1, $z),
				$this->level->getRealBlockSkyLightAt($x, $y, $z + 1),
				$this->level->getRealBlockSkyLightAt($x, $y, $z - 1),
				$this->level->getRealBlockSkyLightAt($x + 1, $y, $z),
				$this->level->getRealBlockSkyLightAt($x - 1, $y, $z)
			);

			return $i <= $this->random->nextBoundedInt(8);
		}
		return false;
	}

	public function entityBaseTick(int $diff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($diff);

		if($this->isAlive()){
			if($this->level->getDifficulty() === Level::DIFFICULTY_PEACEFUL){
				$this->flagForDespawn();
			}
		}

		return $hasUpdate;
	}

	public function canSpawnHere() : bool{
		return $this->level->getDifficulty() !== Level::DIFFICULTY_PEACEFUL and $this->isValidLightLevel();
	}

	public function getBlockPathWeight(Vector3 $pos) : float{
		return 0.5 - max(
				$this->level->getRealBlockSkyLightAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()),
				$this->level->getBlockLightAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())
			);
	}

}