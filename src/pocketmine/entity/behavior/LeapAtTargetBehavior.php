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

use pocketmine\entity\Mob;

class LeapAtTargetBehavior extends Behavior{

	/** @var float */
	protected $leapHeight;
	protected $mustBeOnGround;
	protected $leapTarget;

	public function __construct(Mob $mob, float $leapHeight, bool $mustBeOnGround = true){
		parent::__construct($mob);

		$this->leapHeight = $leapHeight;
		$this->mustBeOnGround = $mustBeOnGround;

		$this->mutexBits = 5;
	}

	public function canStart() : bool{
		$this->leapTarget = $this->mob->getTargetEntity();

		if($this->leapTarget == null) return false;

		$distance = $this->mob->distance($this->leapTarget);

		return $distance >= 4 and $distance <= 16 and ($this->mustBeOnGround ? $this->mob->isOnGround() : true) and $this->random->nextBoundedInt(5) == 0;
	}

	public function canContinue() : bool{
		return !$this->mob->onGround;
	}

	public function onStart() : void{
		$d1 = $this->leapTarget->x - $this->mob->x;
		$d2 = $this->leapTarget->z - $this->mob->z;
		$f = sqrt($d1 ** 2 + $d2 ** 2);

		$motion = $this->mob->getMotion();

		$motion->x += $d1 / $f * 0.5 * 0.8 + $motion->x * 0.2;
		$motion->y = $this->leapHeight;
		$motion->z += $d2 / $f * 0.5 * 0.8 + $motion->z * 0.2;

		$this->mob->setMotion($motion);
	}
}