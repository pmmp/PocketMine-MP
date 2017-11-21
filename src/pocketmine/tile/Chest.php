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
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class Chest extends Spawnable implements InventoryHolder, Container, Nameable{
	use NameableTrait, ContainerTrait;

	public const TAG_PAIRX = "pairx";
	public const TAG_PAIRZ = "pairz";
	public const TAG_PAIR_LEAD = "pairlead";

	/** @var ChestInventory */
	protected $inventory;
	/** @var DoubleChestInventory */
	protected $doubleInventory = null;

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);
		$this->inventory = new ChestInventory($this);
		$this->loadItems();
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
		parent::saveNBT();
		$this->saveItems();
	}

	/**
	 * @return int
	 */
	public function getSize() : int{
		return 27;
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
			$tile = $this->getLevel()->getTileAt($this->namedtag->getInt(self::TAG_PAIRX), $this->y, $this->namedtag->getInt(self::TAG_PAIRZ));
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

		$this->onChanged();
		$tile->onChanged();
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

		$this->onChanged();

		if($tile instanceof Chest){
			$tile->namedtag->removeTag(self::TAG_PAIRX, self::TAG_PAIRZ);
			$tile->checkPairing();
			$tile->onChanged();
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
