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

use pocketmine\entity\Living;
use pocketmine\entity\Tamable;

class OwnerHurtByTargetBehavior extends TargetBehavior{
	/** @var Tamable */
	protected $mob;
	protected $revengeTimerOld = 0;
	/** @var Living */
	protected $ownerAttacker;

	public function __construct(Tamable $mob){
		parent::__construct($mob, false);

		$this->mutexBits = 1;
	}

	public function canStart() : bool{
		if($this->mob->isTamed()){
			$owner = $this->mob->getOwningEntity();

			if($owner instanceof Living){
				$this->ownerAttacker = $owner->getRevengeTarget();
				$i = $owner->getRevengeTimer();

				return $i !== $this->revengeTimerOld and $this->ownerAttacker instanceof Living and $this->isSuitableTargetLocal($this->ownerAttacker, false);
			}
		}

		return false;
	}

	public function onStart() : void{
		$this->mob->setTargetEntity($this->ownerAttacker);
		$owner = $this->mob->getOwningEntity();

		if($owner instanceof Living){
			$this->revengeTimerOld = $owner->getRevengeTimer();
		}

		parent::onStart();
	}
}