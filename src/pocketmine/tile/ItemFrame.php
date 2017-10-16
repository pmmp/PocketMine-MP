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
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\Player;

class ItemFrame extends Spawnable{

	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->ItemRotation)){
			$nbt->ItemRotation = new ByteTag("ItemRotation", 0);
		}

		if(!isset($nbt->ItemDropChance)){
			$nbt->ItemDropChance = new FloatTag("ItemDropChance", 1.0);
		}

		parent::__construct($level, $nbt);
	}

	public function hasItem() : bool{
		return !$this->getItem()->isNull();
	}

	public function getItem() : Item{
		if(isset($this->namedtag->Item)){
			return Item::nbtDeserialize($this->namedtag->Item);
		}else{
			return ItemFactory::get(Item::AIR, 0, 0);
		}
	}

	public function setItem(Item $item = null){
		if($item !== null and !$item->isNull()){
			$this->namedtag->Item = $item->nbtSerialize(-1, "Item");
		}else{
			unset($this->namedtag->Item);
		}
		$this->onChanged();
	}

	public function getItemRotation() : int{
		return $this->namedtag->ItemRotation->getValue();
	}

	public function setItemRotation(int $rotation){
		$this->namedtag->ItemRotation->setValue($rotation);
		$this->onChanged();
	}

	public function getItemDropChance() : float{
		return $this->namedtag->ItemDropChance->getValue();
	}

	public function setItemDropChance(float $chance){
		$this->namedtag->ItemDropChance->setValue($chance);
		$this->onChanged();
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->ItemDropChance = $this->namedtag->ItemDropChance;
		$nbt->ItemRotation = $this->namedtag->ItemRotation;

		if($this->hasItem()){
			$nbt->Item = $this->namedtag->Item;
		}
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->ItemDropChance = new FloatTag("ItemDropChance", 1.0);
		$nbt->ItemRotation = new ByteTag("ItemRotation", 0);
	}

}