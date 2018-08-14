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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;

class Boat extends Vehicle{
	public const NETWORK_ID = self::BOAT;

	public const TAG_VARIANT = "Variant";

	public $height = 0.5;
	public $width = 1.0;

	protected $gravity = 0.9;
	protected $drag = 0.1;

	protected function initEntity(CompoundTag $nbt) : void{
		$this->setHealth(4);
		$this->setGenericFlag(self::DATA_FLAG_STACKABLE);
		$this->setImmobile(false);

		$this->setBoatType($nbt->getInt(self::TAG_VARIANT, 0));

		parent::initEntity($nbt);
	}

	public function getRiderSeatPosition(int $seatNumber = 0) : Vector3{
		return new Vector3($seatNumber * 0.8, -0.2, 0);
	}

	public function getBoatType() : int{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function setBoatType(int $boatType) : void{
		$this->propertyManager->setInt(self::DATA_VARIANT, $boatType);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setInt(self::TAG_VARIANT, $this->getBoatType());
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::BOAT, $this->getBoatType())
		];
	}

	public function entityBaseTick(int $diff = 1) : bool{
		if($this->getHealth() < $this->getMaxHealth() and Server::getInstance()->getTick() % 10 === 0){
			$this->heal(new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_REGEN));
		}

		return parent::entityBaseTick($diff);
	}

	protected function applyGravity() : void{
	    if(!$this->isUnderwater()){
            parent::applyGravity();
        }
    }

    public function getSeatCount() : int{
        return 2;
    }
}