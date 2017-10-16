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

use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\DoubleChestInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;

class Chest extends Spawnable implements InventoryHolder, Container, Nameable{

	/** @var ChestInventory */
	protected $inventory;
	/** @var DoubleChestInventory */
	protected $doubleInventory = null;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->inventory = new ChestInventory($this);

		if(!isset($this->namedtag->Items) or !($this->namedtag->Items instanceof ListTag)){
			$this->namedtag->Items = new ListTag("Items", [], NBT::TAG_Compound);
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i), false);
		}
	}

	public function close(){
		if($this->closed === false){
			$this->inventory->removeAllViewers(true);

			if($this->doubleInventory !== null){
				$this->doubleInventory->removeAllViewers(true);
				$this->doubleInventory->invalidate();
				$this->doubleInventory = null;
			}

			$this->inventory = null;

			parent::close();
		}
	}

	public function saveNBT(){
		$this->namedtag->Items->setValue([]);
		for($index = 0; $index < $this->getSize(); ++$index){
			$this->setItem($index, $this->inventory->getItem($index));
		}
	}

	/**
	 * @return int
	 */
	public function getSize() : int{
		return 27;
	}

	/**
	 * @param $index
	 *
	 * @return int
	 */
	protected function getSlotIndex(int $index){
		foreach($this->namedtag->Items as $i => $slot){
			if($slot->Slot->getValue() === $index){
				return (int) $i;
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
	public function getItem(int $index) : Item{
		$i = $this->getSlotIndex($index);
		if($i < 0){
			return ItemFactory::get(Item::AIR, 0, 0);
		}else{
			return Item::nbtDeserialize($this->namedtag->Items[$i]);
		}
	}

	/**
	 * This method should not be used by plugins, use the Inventory
	 *
	 * @param int  $index
	 * @param Item $item
	 */
	public function setItem(int $index, Item $item){
		$i = $this->getSlotIndex($index);

		$d = $item->nbtSerialize($index);

		if($item->isNull()){
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
	}

	/**
	 * @return ChestInventory|DoubleChestInventory
	 */
	public function getInventory(){
		if($this->isPaired() and $this->doubleInventory === null){
			$this->checkPairing();
		}
		return $this->doubleInventory instanceof DoubleChestInventory ? $this->doubleInventory : $this->inventory;
	}

	/**
	 * @return ChestInventory
	 */
	public function getRealInventory(){
		return $this->inventory;
	}

	protected function checkPairing(){
		if($this->isPaired() and !$this->getLevel()->isChunkLoaded($this->namedtag->pairx->getValue() >> 4, $this->namedtag->pairz->getValue() >> 4)){
			//paired to a tile in an unloaded chunk
			$this->doubleInventory = null;

		}elseif(($pair = $this->getPair()) instanceof Chest){
			if(!$pair->isPaired()){
				$pair->createPair($this);
				$pair->checkPairing();
			}
			if($this->doubleInventory === null){
				if(($pair->x + ($pair->z << 15)) > ($this->x + ($this->z << 15))){ //Order them correctly
					$this->doubleInventory = new DoubleChestInventory($pair, $this);
				}else{
					$this->doubleInventory = new DoubleChestInventory($this, $pair);
				}
			}
		}else{
			$this->doubleInventory = null;
			unset($this->namedtag->pairx, $this->namedtag->pairz);
		}
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return isset($this->namedtag->CustomName) ? $this->namedtag->CustomName->getValue() : "Chest";
	}

	/**
	 * @return bool
	 */
	public function hasName() : bool{
		return isset($this->namedtag->CustomName);
	}

	/**
	 * @param string $str
	 */
	public function setName(string $str){
		if($str === ""){
			unset($this->namedtag->CustomName);
			return;
		}

		$this->namedtag->CustomName = new StringTag("CustomName", $str);
	}

	public function isPaired(){
		return isset($this->namedtag->pairx) and isset($this->namedtag->pairz);
	}

	/**
	 * @return Chest|null
	 */
	public function getPair(){
		if($this->isPaired()){
			$tile = $this->getLevel()->getTile(new Vector3($this->namedtag->pairx->getValue(), $this->y, $this->namedtag->pairz->getValue()));
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

		$this->createPair($tile);

		$this->spawnToAll();
		$tile->spawnToAll();
		$this->checkPairing();

		return true;
	}

	private function createPair(Chest $tile){
		$this->namedtag->pairx = new IntTag("pairx", $tile->x);
		$this->namedtag->pairz = new IntTag("pairz", $tile->z);

		$tile->namedtag->pairx = new IntTag("pairx", $this->x);
		$tile->namedtag->pairz = new IntTag("pairz", $this->z);
	}

	public function unpair(){
		if(!$this->isPaired()){
			return false;
		}

		$tile = $this->getPair();
		unset($this->namedtag->pairx, $this->namedtag->pairz);

		$this->spawnToAll();

		if($tile instanceof Chest){
			unset($tile->namedtag->pairx, $tile->namedtag->pairz);
			$tile->checkPairing();
			$tile->spawnToAll();
		}
		$this->checkPairing();

		return true;
	}

	public function addAdditionalSpawnData(CompoundTag $nbt){
		if($this->isPaired()){
			$nbt->pairx = $this->namedtag->pairx;
			$nbt->pairz = $this->namedtag->pairz;
		}

		if($this->hasName()){
			$nbt->CustomName = $this->namedtag->CustomName;
		}
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->Items = new ListTag("Items", [], NBT::TAG_Compound);

		if($item !== null and $item->hasCustomName()){
			$nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
		}
	}
}
