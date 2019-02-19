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

use pocketmine\entity\passive\AbstractHorse;
use pocketmine\network\mcpe\protocol\EntityEventPacket;

class HorseRiddenBehavior extends Behavior{

	protected $rideTime = 0;
	/** @var AbstractHorse */
	protected $mob;

	public function __construct(AbstractHorse $mob){
		parent::__construct($mob);

		$this->mutexBits = 2;
	}

	public function canStart() : bool{
		return $this->mob->getRiddenByEntity() !== null;
	}

	public function onTick() : void{
		if($this->canStart()){ // a minor check
			if(!$this->mob->isTamed()){
				if($this->rideTime > 100 and !$this->mob->isRearing()){
					if($this->mob->random->nextBoundedInt(1) === 0){
						$this->mob->setInLove(true);
						$this->mob->setTamed(true);
						$this->rideTime = 0;
					}else{
						$this->mob->setRearing(true);
					}
				}elseif($this->rideTime > 120){
					$this->mob->broadcastEntityEvent(EntityEventPacket::TAME_FAIL);
					$this->mob->throwRider();
					$this->mob->setRearing(false);
				}

				$this->rideTime++;
				$this->mutexBits = 2;
			}else{
				if($this->mob->isSaddled()){
					$this->mutexBits = 7; // This a nasty hack
				}
			}
		}
	}

	public function onEnd() : void{
		$this->rideTime = 0;
		$this->mob->setRearing(false);
		$this->mob->throwRider();
	}
}