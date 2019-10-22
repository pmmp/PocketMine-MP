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

namespace pocketmine\block\tile;

use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\DoubleChestInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\world\World;
use function abs;

class Chest extends Spawnable implements Container, Nameable{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait {
		onBlockDestroyedHook as containerTraitBlockDestroyedHook;
	}

	public const TAG_PAIRX = "pairx";
	public const TAG_PAIRZ = "pairz";
	public const TAG_PAIR_LEAD = "pairlead";

	/** @var ChestInventory */
	protected $inventory;
	/** @var DoubleChestInventory|null */
	protected $doubleInventory = null;

	/** @var int|null */
	private $pairX;
	/** @var int|null */
	private $pairZ;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new ChestInventory($this->pos);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if($nbt->hasTag(self::TAG_PAIRX, IntTag::class) and $nbt->hasTag(self::TAG_PAIRZ, IntTag::class)){
			$pairX = $nbt->getInt(self::TAG_PAIRX);
			$pairZ = $nbt->getInt(self::TAG_PAIRZ);
			if(
				($this->pos->x === $pairX and abs($this->pos->z - $pairZ) === 1) or
				($this->pos->z === $pairZ and abs($this->pos->x - $pairX) === 1)
			){
				$this->pairX = $pairX;
				$this->pairZ = $pairZ;
			}else{
				$this->pairX = $this->pairZ = null;
			}
		}
		$this->loadName($nbt);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		if($this->isPaired()){
			$nbt->setInt(self::TAG_PAIRX, $this->pairX);
			$nbt->setInt(self::TAG_PAIRZ, $this->pairZ);
		}
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function getCleanedNBT() : ?CompoundTag{
		$tag = parent::getCleanedNBT();
		if($tag !== null){
			//TODO: replace this with a purpose flag on writeSaveData()
			$tag->removeTag(self::TAG_PAIRX, self::TAG_PAIRZ);
		}
		return $tag;
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();

			if($this->doubleInventory !== null){
				if($this->isPaired() and $this->pos->getWorld()->isChunkLoaded($this->pairX >> 4, $this->pairZ >> 4)){
					$this->doubleInventory->removeAllViewers();
					if(($pair = $this->getPair()) !== null){
						$pair->doubleInventory = null;
					}
				}
				$this->doubleInventory = null;
			}

			$this->inventory = null;

			parent::close();
		}
	}

	protected function onBlockDestroyedHook() : void{
		$this->unpair();
		$this->containerTraitBlockDestroyedHook();
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
		if($this->isPaired() and !$this->pos->getWorld()->isInLoadedTerrain(new Vector3($this->pairX, $this->pos->y, $this->pairZ))){
			//paired to a tile in an unloaded chunk
			$this->doubleInventory = null;

		}elseif(($pair = $this->getPair()) instanceof Chest){
			if(!$pair->isPaired()){
				$pair->createPair($this);
				$pair->checkPairing();
			}
			if($this->doubleInventory === null){
				if($pair->doubleInventory !== null){
					$this->doubleInventory = $pair->doubleInventory;
				}else{
					if(($pair->getPos()->x + ($pair->getPos()->z << 15)) > ($this->pos->x + ($this->pos->z << 15))){ //Order them correctly
						$this->doubleInventory = $pair->doubleInventory = new DoubleChestInventory($pair->inventory, $this->inventory);
					}else{
						$this->doubleInventory = $pair->doubleInventory = new DoubleChestInventory($this->inventory, $pair->inventory);
					}
				}
			}
		}else{
			$this->doubleInventory = null;
			$this->pairX = $this->pairZ = null;
		}
	}

	/**
	 * @return string
	 */
	public function getDefaultName() : string{
		return "Chest";
	}

	public function isPaired(){
		return $this->pairX !== null and $this->pairZ !== null;
	}

	/**
	 * @return Chest|null
	 */
	public function getPair() : ?Chest{
		if($this->isPaired()){
			$tile = $this->pos->getWorld()->getTileAt($this->pairX, $this->pos->y, $this->pairZ);
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

		$this->setDirty();
		$tile->setDirty();
		$this->checkPairing();

		return true;
	}

	private function createPair(Chest $tile){
		$this->pairX = $tile->getPos()->x;
		$this->pairZ = $tile->getPos()->z;

		$tile->pairX = $this->getPos()->x;
		$tile->pairZ = $this->getPos()->z;
	}

	public function unpair(){
		if(!$this->isPaired()){
			return false;
		}

		$tile = $this->getPair();
		$this->pairX = $this->pairZ = null;

		$this->setDirty();

		if($tile instanceof Chest){
			$tile->pairX = $tile->pairZ = null;
			$tile->checkPairing();
			$tile->setDirty();
		}
		$this->checkPairing();

		return true;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->isPaired()){
			$nbt->setInt(self::TAG_PAIRX, $this->pairX);
			$nbt->setInt(self::TAG_PAIRZ, $this->pairZ);
		}

		$this->addNameSpawnData($nbt);
	}
}
