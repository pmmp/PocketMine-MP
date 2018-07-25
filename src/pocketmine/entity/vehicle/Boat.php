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

namespace pocketmine\entity\vehicle;

use pocketmine\entity\Vehicle;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;

class Boat extends Vehicle{
	public const NETWORK_ID = self::BOAT;

	public const TAG_VARIANT = "Variant";

	public $height = 0.455;
	public $width = 1;

	protected $gravity = 0.9;
	protected $drag = 0.1;

	protected function initEntity() : void{
		$this->setHealth(4);
		$this->setGenericFlag(self::DATA_FLAG_STACKABLE);
		$this->setImmobile(false);

		$this->setBoatType($this->namedtag->getInt(self::TAG_VARIANT, 0));

		parent::initEntity();
	}

	public function getRiderSeatPosition() : Vector3{
		return new Vector3(0, -0.2, 0);
	}

	public function getBoatType() : int{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function setBoatType(int $boatType) : void{
		$this->propertyManager->setInt(self::DATA_VARIANT, $boatType);
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setInt(self::TAG_VARIANT, $this->getBoatType());
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::BOAT, $this->getBoatType())
		];
	}

	public function onUpdate(int $currentTick) : bool{
		if($this->closed){
			return false;
		}

		$this->onGround = $this->isOnGround() and !$this->isUnderwater();

		if($this->getHealth() < $this->getMaxHealth() and $currentTick % 10 == 0 /* because of invincible normal 0/10 per tick*/){
			$this->heal(new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_REGEN));
		}

		return parent::onUpdate($currentTick);
	}
}