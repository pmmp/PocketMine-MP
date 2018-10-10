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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\UUID;

abstract class Tamable extends Animal{

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setByte("Tamed", intval($this->isTamed()));
		$nbt->setByte("Sitting", intval($this->isSitting()));

		if($this->getOwningEntity() !== null){
			$uuid = $this->getOwningEntity()->getUniqueId();

			$nbt->setString("OwnerUUID", $uuid->toString());
		}

		return $nbt;
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->setTamed(boolval($nbt->getByte("Tamed", 0)));
		$this->setSitting(boolval($nbt->getByte("Sitting", 0)));
		if($nbt->hasTag("OwnerUUID", StringTag::class)){
			$owner = $this->server->getPlayerByUUID(UUID::fromString($nbt->getString("OwnerUUID"))); // why only player?

			if($owner !== null){
				$this->setOwningEntity($owner);
			}
		}
	}

	public function isTamed() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_TAMED);
    }

    public function setTamed(bool $tamed = true) : void{
        $this->setGenericFlag(self::DATA_FLAG_TAMED, $tamed);
    }

    public function isSitting() : bool{
        return $this->getGenericFlag(self::DATA_FLAG_SITTING);
    }

    public function setSitting(bool $sitting = true) : void{
        $this->setGenericFlag(self::DATA_FLAG_SITTING, $sitting);
    }

}