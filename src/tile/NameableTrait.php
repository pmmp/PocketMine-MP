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
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

/**
 * This trait implements most methods in the {@link Nameable} interface. It should only be used by Tiles.
 */
trait NameableTrait{
	/** @var string|null */
	private $customName;

	/**
	 * @return string
	 */
	abstract public function getDefaultName() : string;

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->customName ?? $this->getDefaultName();
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name) : void{
		if($name === ""){
			$this->customName = null;
		}else{
			$this->customName = $name;
		}
	}

	/**
	 * @return bool
	 */
	public function hasName() : bool{
		return $this->customName !== null;
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		if($item !== null and $item->hasCustomName()){
			$nbt->setString(Nameable::TAG_CUSTOM_NAME, $item->getCustomName());
		}
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->customName !== null){
			$nbt->setString(Nameable::TAG_CUSTOM_NAME, $this->customName);
		}
	}

	protected function loadName(CompoundTag $tag) : void{
		if($tag->hasTag(Nameable::TAG_CUSTOM_NAME, StringTag::class)){
			$this->customName = $tag->getString(Nameable::TAG_CUSTOM_NAME);
		}
	}

	protected function saveName(CompoundTag $tag) : void{
		if($this->customName !== null){
			$tag->setString(Nameable::TAG_CUSTOM_NAME, $this->customName);
		}
	}
}
