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

use pocketmine\entity\Tamable;

class StayWhileSittingBehavior extends Behavior{

	/** @var Tamable */
	protected $mob;
	protected $isSitting = false;

	public function __construct(Tamable $mob){
		parent::__construct($mob);
		$this->mutexBits = 1;
	}

	public function canStart() : bool{
		if($this->mob->isTamed() and !$this->mob->isInsideOfWater() and $this->mob->onGround){
			$owner = $this->mob->getOwningEntity();

			return $owner === null ? true : ($this->mob->distanceSquared($owner) < 144 and $this->mob->getTargetEntity() !== null ? false : $this->isSitting);
		}

		return false;
	}

	public function onStart() : void{
		$this->mob->getNavigator()->clearPath(true);
		$this->mob->setSitting(true);
	}

	public function onEnd() : void{
		$this->mob->setSitting(false);
	}

	public function setSitting(bool $value) : void{
		$this->isSitting = $value;
	}
}