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

use pocketmine\entity\hostile\Creeper;

class CreeperSwellBehavior extends Behavior{

	/** @var Creeper */
	protected $mob;

	public function __construct(Creeper $mob){
		parent::__construct($mob);
		$this->mutexBits = 1;
	}

	public function canStart() : bool{
		$target = $this->mob->getTargetEntity();
		return $target === null ? false : ($this->mob->isIgnited() || $this->mob->distance($target) < 3);
	}

	public function onTick() : void{
		$target = $this->mob->getTargetEntity();
		if($this->mob->distance($target) > 7 or !$this->mob->canSeeEntity($target)){
			$this->mob->setIgnited(false);
		}else{
			$this->mob->setIgnited(true);
		}
	}

	public function onEnd() : void{
		$this->mob->setTargetEntity(null);
	}
}