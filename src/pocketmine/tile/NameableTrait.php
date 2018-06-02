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

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

/**
 * This trait implements most methods in the {@link Nameable} interface. It should only be used by Tiles.
 */
trait NameableTrait{

	/**
	 * @return string
	 */
	abstract public function getDefaultName() : string;

	/**
	 * @return CompoundTag
	 */
	abstract public function getNBT() : CompoundTag;

	/**
	 * @return string
	 */
	public function getName() : string{
		$nbt = $this->getNBT();
		return $nbt->getString(Nameable::TAG_CUSTOM_NAME) ?? $this->getDefaultName();
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name) : void{
		$nbt = $this->getNBT();
		if($name === ""){
			$nbt->removeTag(Nameable::TAG_CUSTOM_NAME);

			return;
		}

		$nbt->setString(Nameable::TAG_CUSTOM_NAME, $name);
	}

	/**
	 * @return bool
	 */
	public function hasName() : bool{
		return $this->getNBT()->hasTag(Nameable::TAG_CUSTOM_NAME);
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		if($item !== null and $item->hasCustomName()){
			$nbt->setString(Nameable::TAG_CUSTOM_NAME, $item->getCustomName());
		}
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->hasName()){
			$nbt->setString(Nameable::TAG_CUSTOM_NAME, $this->getName());
		}
	}
}
