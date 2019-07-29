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

class SlimeRandomDirectionBehavior extends Behavior{
	/** @var Slime */
	protected $mob;
	protected $randomYaw;
	protected $directionTimer;

	public function __construct(Slime $slime){
		parent::__construct($slime);

		$this->setMutexBits(2);
	}

	public function canStart() : bool{
		return $this->mob->getTargetEntity() === null and ($this->mob->onGround or $this->mob->isInsideOfWater() or $this->mob->isInsideOfLava());
	}

	public function onTick() : void{
		if(--$this->directionTimer <= 0){
			$this->directionTimer = 40 + $this->random->nextBoundedInt(60);
			$this->randomYaw = $this->random->nextBoundedInt(360);
		}

		$this->mob->getMoveHelper()->jumpWithYaw($this->randomYaw, false);
	}
}