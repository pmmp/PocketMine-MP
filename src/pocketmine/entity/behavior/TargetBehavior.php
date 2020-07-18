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

use pocketmine\entity\Attribute;
use pocketmine\entity\Living;
use pocketmine\entity\Mob;
use pocketmine\Player;

abstract class TargetBehavior extends Behavior{

	protected $shouldCheckSight;

	private $nearbyOnly;
	private $targetSearchStatus;
	private $targetSearchDelay;
	private $targetUnseenTicks;

	public function __construct(Mob $mob, bool $checkSight, bool $onlyNearby = false){
		parent::__construct($mob);

		$this->shouldCheckSight = $checkSight;
		$this->nearbyOnly = $onlyNearby;
	}

	public function canContinue() : bool{
		$target = $this->mob->getTargetEntity();

		if($target == null){
			return false;
		}elseif(!$target->isAlive()){
			return false;
		}else{
			if($this->mob->distance($target) > $this->getTargetDistance()){
				return false;
			}

			return true;
		}
	}

	protected function getTargetDistance() : float{
		return $this->mob->getAttributeMap()->getAttribute(Attribute::FOLLOW_RANGE)->getValue();
	}

	public function onStart() : void{
		$this->targetSearchStatus = 0;
		$this->targetSearchDelay = 0;
		$this->targetUnseenTicks = 0;
	}

	public function onEnd() : void{
		$this->mob->setTargetEntity(null);
	}

	public function isSuitableTarget(Mob $attacker, Living $target, bool $includeInvisibles, bool $checkSight) : bool{
		if($target == null){
			return false;
		}elseif($target === $attacker){
			return false;
		}elseif(!$target->isAlive()){
			return false;
		}elseif($target instanceof Player and !$includeInvisibles and $target->isCreative()){
			return false;
		}

		return !$checkSight or $attacker->canSeeEntity($target);
	}

	public function isSuitableTargetLocal(Living $target, bool $includeInvisibles) : bool{
		if(!$this->isSuitableTarget($this->mob, $target, $includeInvisibles, $this->shouldCheckSight)){
			return false;
		}else{
			if($this->nearbyOnly){
				if(--$this->targetSearchDelay <= 0){
					$this->targetSearchStatus = 0;
				}

				if($this->targetSearchStatus == 0){
					$this->targetSearchStatus = $this->canEasilyReach($target) ? 1 : 2;
				}

				if($this->targetSearchStatus == 2){
					return false;
				}
			}

			return true;
		}
	}

	private function canEasilyReach(Living $entity) : bool{
		$this->targetSearchDelay = 10 + $this->mob->random->nextBoundedInt(5);
		$path = $this->mob->getNavigator()->findPath($entity);

		if($path == null){
			return false;
		}else{
			$point = $path->getFinalPathPoint();

			if($point == null){
				return false;
			}else{
				$i = $point->x - floor($entity->x);
				$j = $point->y - floor($entity->z);

				return ($i * $i + $j * $j) <= 2.25;
			}
		}
	}
}