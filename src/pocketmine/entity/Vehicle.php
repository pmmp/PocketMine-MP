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

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Vehicle extends Entity implements Rideable{

	public function onFirstInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		return $player->mountEntity($this);
	}

	public function setHealth(float $amount) : void{
		parent::setHealth($amount);

		$this->propertyManager->setInt(self::DATA_HEALTH, (int) $amount);
	}

	public function getHurtTime() : int{
		return $this->propertyManager->getInt(self::DATA_HURT_TIME) ?? 0;
	}

	public function setHurtTime(int $value) : void{
		$this->propertyManager->setInt(self::DATA_HURT_TIME, $value);
	}

	public function getHurtDirection() : int{
		return $this->propertyManager->getInt(self::DATA_HURT_DIRECTION) ?? 0;
	}

	public function setHurtDirection(int $value) : void{
		$this->propertyManager->setInt(self::DATA_HURT_DIRECTION, $value);
	}
}