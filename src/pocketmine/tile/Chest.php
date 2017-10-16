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
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class Chest extends Spawnable implements InventoryHolder, Container, Nameable{
	use NameableTrait;

	const TAG_PAIRX = "pairx";
	const TAG_PAIRZ = "pairz";
	const TAG_PAIR_LEAD = "pairlead";

	/** @var ChestInventory */
	protected $inventory;
	/** @var DoubleChestInventory */
	protected $doubleInventory = null;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->inventory = new ChestInventory($this);

		if(!($this->namedtag->getTag("Items") instanceof ListTag)){
			$this->namedtag->setTag(new ListTag("Items", [], NBT::TAG_Compound));
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$this->inventory->setItem($i, $this->getItem($i), false);
		}
	}

	public function close() : void{
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

	public function saveNBT() : void{
		$this->namedtag->setTag(new ListTag("Items", [], NBT::TAG_Compound));
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
	protected function getSlotIndex(int $index) : int{
		$items = $this->namedtag->getListTag("Items");
		assert($items instanceof ListTag);
		foreach($items as $i => $slot){
			/** @var CompoundTag $slot */
			if($slot->getByte("Slot") === $index){
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
			return Item::nbtDeserialize($this->namedtag->getListTag("Items")[$i]);
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

		$items = $this->namedtag->getListTag("Items");
		assert($items instanceof ListTag);

		if($item->isNull()){
			if($i >= 0){
				unset($items[$i]);
			}
		}elseif($i < 0){
			for($i = 0; $i <= $this->getSize(); ++$i){
				if(!isset($items[$i])){
					break;
				}
			}
			$items[$i] = $d;
		}else{
			$items[$i] = $d;
		}

		$this->namedtag->setTag($items);
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
		if($this->isPaired() and !$this->getLevel()->isChunkLoaded($this->namedtag->getInt(self::TAG_PAIRX) >> 4, $this->namedtag->getInt(self::TAG_PAIRZ) >> 4)){
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
			$this->namedtag->removeTag(self::TAG_PAIRX, self::TAG_PAIRZ);
		}
	}

	/**
	 * @return string
	 */
	public function getDefaultName() : string{
		return "Chest";
	}

	public function isPaired(){
		return $this->namedtag->hasTag(self::TAG_PAIRX) and $this->namedtag->hasTag(self::TAG_PAIRZ);
	}

	/**
	 * @return Chest|null
	 */
	public function getPair() : ?Chest{
		if($this->isPaired()){
			$tile = $this->getLevel()->getTile(new Vector3($this->namedtag->getInt(self::TAG_PAIRX), $this->y, $this->namedtag->getInt(self::TAG_PAIRZ)));
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
		$this->namedtag->setInt(self::TAG_PAIRX, $tile->x);
		$this->namedtag->setInt(self::TAG_PAIRZ, $tile->z);

		$tile->namedtag->setInt(self::TAG_PAIRX, $this->x);
		$tile->namedtag->setInt(self::TAG_PAIRZ, $this->z);
	}

	public function unpair(){
		if(!$this->isPaired()){
			return false;
		}

		$tile = $this->getPair();
		$this->namedtag->removeTag(self::TAG_PAIRX, self::TAG_PAIRZ);

		$this->spawnToAll();

		if($tile instanceof Chest){
			$tile->namedtag->removeTag(self::TAG_PAIRX, self::TAG_PAIRZ);
			$tile->checkPairing();
			$tile->spawnToAll();
		}
		$this->checkPairing();

		return true;
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->isPaired()){
			$nbt->setTag($this->namedtag->getTag(self::TAG_PAIRX));
			$nbt->setTag($this->namedtag->getTag(self::TAG_PAIRZ));
		}

		if($this->hasName()){
			$nbt->setTag($this->namedtag->getTag("CustomName"));
		}
	}

	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{
		$nbt->setTag(new ListTag("Items", [], NBT::TAG_Compound));

		if($item !== null and $item->hasCustomName()){
			$nbt->setString("CustomName", $item->getCustomName());
		}
	}
}
