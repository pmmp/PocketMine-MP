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
use pocketmine\math\Vector3;
use function cos;
use function pi;
use function sin;

class RandomLookAroundBehavior extends Behavior{

	/** @var int */
	protected $lookX = 0;
	/** @var int */
	protected $lookZ = 0;
	/** @var int */
	protected $idleTime = 0;

	public function __construct(Mob $mob){
		parent::__construct($mob);
		$this->mutexBits = 3;
	}

	public function canStart() : bool{
		return $this->random->nextFloat() < 0.02;
	}

	public function onStart() : void{
		$d0 = (pi() * 2) * $this->random->nextFloat();
		$this->lookX = cos($d0);
		$this->lookZ = sin($d0);
		$this->idleTime = 20 + $this->random->nextBoundedInt(20);
	}

	public function canContinue() : bool{
		return $this->idleTime > 0;
	}

	public function onTick() : void{
		$this->idleTime--;
		$this->mob->getLookHelper()->setLookPosition($this->mob->x + $this->lookX, $this->mob->y + $this->mob->getEyeHeight(), $this->mob->z + $this->lookZ, 10, $this->mob->getVerticalFaceSpeed());
	}
}