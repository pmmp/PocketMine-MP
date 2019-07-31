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

use pocketmine\entity\hostile\Slime;
use pocketmine\Player;

class SlimeAttackBehavior extends Behavior{
	/** @var Slime */
	protected $mob;

	private $attackTime;

	public function __construct(Slime $slime){
		parent::__construct($slime);

		$this->setMutexBits(2);
	}

	public function canStart() : bool{
		$target = $this->mob->getTargetEntity();

		return $target === null ? false : (!$target->isAlive() ? false : !($target instanceof Player) or !$target->isCreative());
	}

	public function onStart() : void{
		$this->attackTime = 300;
	}

	public function canContinue() : bool{
		return $this->canStart() and --$this->attackTime > 0;
	}

	public function onTick() : void{
		$this->mob->faceEntity($this->mob->getTargetEntity(), 10, 10);
		$this->mob->getMoveHelper()->jumpWithYaw($this->mob->yaw, $this->mob->canDamagePlayer());
	}
}