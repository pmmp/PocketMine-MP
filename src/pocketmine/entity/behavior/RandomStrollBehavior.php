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
use pocketmine\entity\utils\RandomPositionGenerator;
use pocketmine\math\Vector3;

class RandomStrollBehavior extends Behavior{

	/** @var float */
	protected $speedMultiplier = 1.0;
	/** @var int */
	protected $chance = 120;
	/** @var Vector3|null */
	protected $targetPos;

	public function __construct(Mob $mob, float $speedMultiplier = 1.0, int $chance = 120){
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
		$this->chance = $chance;
		$this->mutexBits = 1;
	}

	public function canStart() : bool{
		if($this->random->nextBoundedInt($this->chance) === 0){
			$pos = RandomPositionGenerator::findRandomTargetBlock($this->mob, 10, 7);

			if($pos === null) return false;

			$this->targetPos = $pos;

			return true;
		}

		return false;
	}

	public function canContinue() : bool{
		return $this->mob->getNavigator()->isBusy();
	}

	public function onStart() : void{
		$this->mob->getNavigator()->tryMoveTo($this->targetPos, $this->speedMultiplier);
	}

	public function onEnd() : void{
		$this->targetPos = null;
		$this->mob->getNavigator()->clearPath();
	}
}