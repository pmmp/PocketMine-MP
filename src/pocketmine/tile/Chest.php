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

namespace pocketmine\tile;

use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\DoubleChestInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;

class Chest extends Spawnable implements InventoryHolder, Container{

	/** @var ChestInventory */
	protected $inventory;
	/** @var DoubleChestInventory */
	protected $doubleInventory = null;

	public function __construct(FullChunk $chunk, Compound $nbt){
		$nbt["id"] = Tile::CHEST;
		parent::__construct($chunk, $nbt);
		$this->inventory = new ChestInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof Enum)){
			$this->namedtag->Items = new Enum("Inventory", []);
			$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i));
		}
		$this->checkPairing();
	}

	public function saveNBT(){
		$this->namedtag->Items = new Enum("Items", []);
		$this->namedtag->Items->setTagType(NBT::TAG_Compound);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 * @return int
	 */
	public function getSize(){
		return 27;
	}

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex($index){
		foreach($this->namedtag->Items as $i => $slot){
			if($slot["Slot"] === $index){
				return $i;
			}
		}

		return -1;
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int $index
	 *
	 * @return Item
	 */
	public function getItem($index){
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return Item::get(Item::AIR, 0, 0);
		}else{
			return Item::get($this->namedtag->Items[$i]["id"], $this->namedtag->Items[$i]["Damage"], $this->namedtag->Items[$i]["Count"]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItem($index, Item $item){
		$i = $this->getSlotIndex($index);

		$d = new Compound(false, array(
			new Byte("Count", $item->getCount()),
			new Byte("Slot", $index),
			new Short("id", $item->getID()),
			new Short("Damage", $item->getDamage()),
		));

		if($item->getID() === Item::AIR or $item->getCount() <= 0){
			if($i >= 0){
				unset($this->namedtag->Items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($this->namedtag->Items[$i])){
					break;
				}
			}
			$this->namedtag->Items[$i] = $d;
		}else{
			$this->namedtag->Items[$i] = $d;
		}

		return true;
	}

	/**
	 * @return ChestInventory|DoubleChestInventory
	 */
	public function getInventory(){
		return $this->doubleInventory instanceof DoubleChestInventory ? $this->doubleInventory : $this->inventory;
	}

	/**
	 * @return ChestInventory
	 */
	public function getRealInventory(){
		return $this->inventory;
	}

	protected function checkPairing(){
		if(($pair = $this->getPair()) instanceof Chest){
			if(($pair->x + ($pair->z << 15)) > ($this->x + ($this->z << 15))){ //Order them correctly
				$this->doubleInventory = new DoubleChestInventory($pair, $this);
			}else{
				$this->doubleInventory = new DoubleChestInventory($this, $pair);
			}
		}else{
			$this->doubleInventory = null;
		}
	}

	public function isPaired(){
		if(!isset($this->namedtag->pairx) or !isset($this->namedtag->pairz)){
			return false;
		}

		return true;
	}

	/**
	 * @return Chest
	 */
	public function getPair(){
		if($this->isPaired()){
			$tile = $this->getLevel()->getTile(new Vector3((int) $this->namedtag->pairx, $this->y, (int) $this->namedtag->pairz));
			if($tile instanceof Chest){
				return $tile;
			}
		}

		return null;
	}

	public function pairWith(Chest $tile){
		if($this->isPaired() or $tile->isPaired()){
			return false;
		}

		$this->namedtag->pairx = $tile->x;
		$this->namedtag->pairz = $tile->z;

		$tile->namedtag->pairx = $this->x;
		$tile->namedtag->pairz = $this->z;

		$this->spawnToAll();
		$tile->spawnToAll();
		$this->checkPairing();

		return true;
	}

	public function unpair(){
		if(!$this->isPaired()){
			return false;
		}

		$tile = $this->getPair();
		unset($this->namedtag->pairx, $this->namedtag->pairz, $tile->namedtag->pairx, $tile->namedtag->pairz);

		$this->spawnToAll();
		$this->checkPairing();

		if($tile instanceof Chest){
			$tile->spawnToAll();
		}

		return true;
	}

	public function getSpawnCompound(){
		if($this->isPaired()){
			return new Compound("", array(
				new String("id", Tile::CHEST),
				new Int("x", (int) $this->x),
				new Int("y", (int) $this->y),
				new Int("z", (int) $this->z),
				new Int("pairx", (int) $this->namedtag->pairx),
				new Int("pairz", (int) $this->namedtag->pairz)
			));
		}else{
			return new Compound("", array(
				new String("id", Tile::CHEST),
				new Int("x", (int) $this->x),
				new Int("y", (int) $this->y),
				new Int("z", (int) $this->z)
			));
		}
	}
}