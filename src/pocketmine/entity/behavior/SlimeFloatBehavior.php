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

class SlimeFloatBehavior extends Behavior{
	/** @var Slime */
	protected $mob;

	public function __construct(Slime $slime){
		parent::__construct($slime);

		$this->setMutexBits(5);
		$this->mob->setSwimmer(true);
	}

	public function canStart() : bool{
		return $this->mob->isInsideOfWater() or $this->mob->isInsideOfLava();
	}

	public function onTick() : void{
		if ($this->random->nextFloat() < 0.8){
			$this->mob->getJumpHelper()->setJumping(true);
		}

		$this->mob->getMoveHelper()->setSpeed(1.2);
	}
}