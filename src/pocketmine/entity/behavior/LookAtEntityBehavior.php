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

class LookAtEntityBehavior extends Behavior{

	/** @var float */
	protected $lookDistance = 6.0;
	/** @var Entity */
	protected $nearestEntity;
	/** @var int */
	protected $lookTime = 0;
	/** @var string */
	protected $targetClass;

	public function __construct(Mob $mob, string $targetClass, float $lookDistance = 6.0){
		parent::__construct($mob);

		$this->lookDistance = $lookDistance;
		$this->targetClass = $targetClass;
		$this->mutexBits = 2;
	}

	public function canStart() : bool{
		if($this->random->nextFloat() < 0.02){
			$target = $this->mob->level->getNearestEntity($this->mob->asVector3(), $this->lookDistance, $this->targetClass);

			if($target !== null){
				$this->nearestEntity = $target;
				
				return true;
			}
		}

		return false;
	}

	public function onStart() : void{
		$this->lookTime = 40 + $this->random->nextBoundedInt(40);
	}

	public function canContinue() : bool{
		return !($this->nearestEntity->isAlive() ? false : (($this->mob->distanceSquared($this->nearestEntity) > $this->lookDistance ** 2) ? false : ($this->lookTime > 0)));
	}

	public function onTick() : void{
		$this->mob->getLookHelper()->setLookPositionWithEntity($this->nearestEntity, 10, $this->mob->getVerticalFaceSpeed());
		$this->lookTime--;
	}

	public function onEnd() : void{
		$this->nearestEntity = null;
	}
}
