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
use pocketmine\entity\Tamable;
use pocketmine\event\entity\EntityDamageByEntityEvent;

/**
 * @property Tamable $mob
 */
class SittingBehavior extends Behavior{

	public function __construct(Tamable $mob){
		parent::__construct($mob);
		$this->mutexBits = 1;
	}

	public function canStart(): bool{
		if(!$this->mob->isTamed() or !$this->mob->isBreathing()) return false;

		$owner = $this->mob->getOwningEntity();

		$shouldStart = $owner == null || ((!($this->mob->distance($owner) < 144.0) || $this->getLastAttackSource() == null) && $this->mob->isSitting());
		if(!$shouldStart) return false;

		$this->mob->resetMotion();

		return true;
	}

	public function canContinue() : bool{
		return $this->mob->isSitting();
	}

	public function onEnd() : void{
		$this->mob->setSitting(false);
	}

	public function getLastAttackSource() : ?Entity{
		$cause = $this->mob->getLastDamageCause();
		return $cause instanceof EntityDamageByEntityEvent ? $cause->getDamager() : null;
	}
}