<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\tile;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Binary;
use pocketmine\utils\Color;

class Cauldron extends Spawnable{

	protected $potionId = -1;
	protected $splashPotion = false;
	/** @var Color|null */
	protected $customColor;

	public function getName() : string{
		return "Cauldron";
	}

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->potionId = $nbt->getShort("PotionId", -1);
		$this->splashPotion = boolval($nbt->getByte("SplashPotion", 0));

		if($nbt->hasTag("CustomColor", IntTag::class)){
			$this->customColor = Color::fromARGB(Binary::unsignInt($nbt->getInt("CustomColor")));
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setShort("PotionId", $this->potionId);
		$nbt->setByte("SplashPotion", intval($this->splashPotion));

		if($this->customColor !== null){
			$nbt->setInt("CustomColor", Binary::signInt($this->customColor->toARGB()));
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$this->writeSaveData($nbt);
	}

	/**
	 * @return Color|null
	 */
	public function getCustomColor() : ?Color{
		return $this->customColor;
	}

	/**
	 * @param Color|null $customColor
	 */
	public function setCustomColor(?Color $customColor) : void{
		$this->customColor = $customColor;
		$this->onChanged();
	}

	/**
	 * @return int
	 */
	public function getPotionId() : int{
		return $this->potionId;
	}

	/**
	 * @param int $potionId
	 */
	public function setPotionId(int $potionId) : void{
		$this->potionId = $potionId;
		$this->onChanged();
	}

	public function hasPotion() : bool{
		return $this->potionId !== -1;
	}

	/**
	 * @return bool
	 */
	public function isSplashPotion() : bool{
		return $this->splashPotion;
	}

	/**
	 * @param bool $splashPotion
	 */
	public function setSplashPotion(bool $splashPotion) : void{
		$this->splashPotion = $splashPotion;
		$this->onChanged();
	}
}
