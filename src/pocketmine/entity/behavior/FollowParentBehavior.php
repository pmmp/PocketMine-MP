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

use pocketmine\entity\Animal;

class FollowParentBehavior extends Behavior{

	/** @var float */
	protected $speedMultiplier;
	/** @var int */
	protected $delay = 0;
	/** @var Animal */
	protected $parentAnimal;

	public function __construct(Animal $mob, float $speedMultiplier){
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
	}

	public function canStart() : bool{
		if($this->mob->isBaby()){
			$dist = 9;
			$animal = null;
			foreach($this->mob->level->getEntities() as $entity){
				if($entity !== $this->mob){
					if(!$entity->isBaby()){
						if(($d2 = $entity->distanceSquared($this->mob)) < $dist){
							$dist = $d2;
							$animal = $entity;
						}
					}
				}
			}

			if($animal instanceof Animal){
				if($dist >= 9){
					$this->parentAnimal = $animal;
					return true;
				}
			}
		}

		return false;
	}

	public function canContinue() : bool{
		$d = $this->mob->distanceSquared($this->parentAnimal);
		return $this->mob->isBaby() and $this->parentAnimal->isAlive() and $d >= 9 and $d <= 256;
	}

	public function onStart() : void{
		$this->delay = 0;
	}

	public function onTick() : void{
		if($this->delay-- <= 0){
			$this->delay = 10;
			$this->mob->getNavigator()->tryMoveTo($this->parentAnimal, $this->speedMultiplier);
		}
	}

	public function onEnd() : void{
		$this->parentAnimal = null;
	}
}