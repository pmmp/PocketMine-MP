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

use pocketmine\block\inventory\ChestInventory;
use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\world\format\Chunk;
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

	private ?int $pairX = null;
	private ?int $pairZ = null;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new ChestInventory($this->position);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if(($pairXTag = $nbt->getTag(self::TAG_PAIRX)) instanceof IntTag && ($pairZTag = $nbt->getTag(self::TAG_PAIRZ)) instanceof IntTag){
			$pairX = $pairXTag->getValue();
			$pairZ = $pairZTag->getValue();
			if(
				($this->position->x === $pairX && abs($this->position->z - $pairZ) === 1) ||
				($this->position->z === $pairZ && abs($this->position->x - $pairX) === 1)
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
				if($this->isPaired() && $this->position->getWorld()->isChunkLoaded($this->pairX >> Chunk::COORD_BIT_SIZE, $this->pairZ >> Chunk::COORD_BIT_SIZE)){
					$this->doubleInventory->removeAllViewers();
					if(($pair = $this->getPair()) !== null){
						$pair->doubleInventory = null;
					}
				}
				$this->doubleInventory = null;
			}

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
		if($this->isPaired() && $this->doubleInventory === null){
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

	protected function checkPairing() : void{
		if($this->isPaired() && !$this->position->getWorld()->isInLoadedTerrain(new Vector3($this->pairX, $this->position->y, $this->pairZ))){
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
					if(($pair->getPosition()->x + ($pair->getPosition()->z << 15)) > ($this->position->x + ($this->position->z << 15))){ //Order them correctly
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

	public function getDefaultName() : string{
		return "Chest";
	}

	public function isPaired() : bool{
		return $this->pairX !== null && $this->pairZ !== null;
	}

	public function getPair() : ?Chest{
		if($this->isPaired()){
			$tile = $this->position->getWorld()->getTileAt($this->pairX, $this->position->y, $this->pairZ);
			if($tile instanceof Chest){
				return $tile;
			}
		}

		return null;
	}

	public function pairWith(Chest $tile) : bool{
		if($this->isPaired() || $tile->isPaired()){
			return false;
		}

		$this->createPair($tile);

		$this->clearSpawnCompoundCache();
		$tile->clearSpawnCompoundCache();
		$this->checkPairing();

		return true;
	}

	private function createPair(Chest $tile) : void{
		$this->pairX = $tile->getPosition()->x;
		$this->pairZ = $tile->getPosition()->z;

		$tile->pairX = $this->getPosition()->x;
		$tile->pairZ = $this->getPosition()->z;
	}

	public function unpair() : bool{
		if(!$this->isPaired()){
			return false;
		}

		$tile = $this->getPair();
		$this->pairX = $this->pairZ = null;

		$this->clearSpawnCompoundCache();

		if($tile instanceof Chest){
			$tile->pairX = $tile->pairZ = null;
			$tile->checkPairing();
			$tile->clearSpawnCompoundCache();
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
