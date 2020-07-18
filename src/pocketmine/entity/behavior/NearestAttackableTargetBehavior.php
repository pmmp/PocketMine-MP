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
use pocketmine\entity\Living;
use pocketmine\entity\Mob;
use pocketmine\Player;

class NearestAttackableTargetBehavior extends TargetBehavior{

	protected $targetClass;
	private $targetChance;
	protected $targetEntity;
	protected $targetEntitySelector;

	public function __construct(Mob $mob, string $classTarget, bool $checkSight = true, int $chance = 10, bool $onlyNearby = false, ?\Closure $targetSelector = null){
		parent::__construct($mob, $checkSight, $onlyNearby);

		$this->targetClass = $classTarget;
		$this->targetChance = $chance;

		$this->setMutexBits(1);

		$this->targetEntitySelector = function(Entity $entity) use ($targetSelector) : bool{
			if($targetSelector !== null and !$targetSelector($entity)){
				return false;
			}else{
				if($entity instanceof Player){
					$d = $this->getTargetDistance();

					if($entity->isSneaking()){
						$d *= 0.8;
					}

					if($entity->isInvisible()){
						$av = 0;

						foreach($entity->getArmorInventory()->getContents() as $item){
							if(!$item->isNull()){
								$av += 0.25;
							}
						}

						$d *= $av * 0.7;
					}

					if($entity->distance($this->mob) > $d){
						return false;
					}
				}

				return $entity instanceof Living and $this->isSuitableTargetLocal($entity, false);
			}
		};
	}

	public function canStart() : bool{
		if($this->targetChance > 0 and $this->random->nextBoundedInt($this->targetChance) !== 0){
			return false;
		}else{
			$d0 = $this->getTargetDistance();
			$list = $this->mob->level->getCollidingEntities($this->mob->getBoundingBox()->expandedCopy($d0, 4.0, $d0), $this->mob);
			$filters = [
				$this->targetEntitySelector,
				function(Entity $entity) : bool{
					return get_class($entity) === $this->targetClass;
				}
			];

			// Filter
			foreach($list as $i => $entity){
				foreach($filters as $filter){
					if(!$filter($entity)){
						unset($list[$i]);
					}
				}
			}

			// Sort
			$nearest = null;
			$lastDistance = PHP_INT_MAX;
			foreach($list as $entity){
				if($d = $entity->distance($this->mob) < $lastDistance){
					$lastDistance = $d;
					$nearest = $entity;
				}
			}

			if($nearest !== null){
				$this->targetEntity = $nearest;

				return true;
			}

			return false;
		}
	}

	public function onStart() : void{
		$this->mob->setTargetEntity($this->targetEntity);

		parent::onStart();
	}
}