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

namespace pocketmine\level\format\generic;

use pocketmine\entity\DroppedItem;
use pocketmine\entity\Entity;
use pocketmine\level\format\FullChunk;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\String;
use pocketmine\tile\Chest;
use pocketmine\tile\Furnace;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\Binary;

abstract class BaseFullChunk implements FullChunk{

	/** @var Entity[] */
	protected $entities = [];

	/** @var Tile[] */
	protected $tiles = [];

	/** @var string */
	protected $biomeIds;

	/** @var int[256] */
	protected $biomeColors;

	protected $blocks;

	protected $data;

	protected $skyLight;

	protected $blockLight;

	/** @var LevelProvider */
	protected $provider;

	protected $x;
	protected $z;

	/**
	 * @param LevelProvider $provider
	 * @param int           $x
	 * @param int           $z
	 * @param string        $blocks
	 * @param string        $data
	 * @param string        $skyLight
	 * @param string        $blockLight
	 * @param string        $biomeIds
	 * @param int[]         $biomeColors
	 * @param Compound[]    $entities
	 * @param Compound[]    $tiles
	 *
	 * @throws \Exception
	 */
	protected function __construct($provider, $x, $z, $blocks, $data, $skyLight, $blockLight, $biomeIds = null, array $biomeColors = [], array $entities = [], array $tiles = []){
		$this->provider = $provider;
		$this->x = (int) $x;
		$this->z = (int) $z;

		$this->blocks = $blocks;
		$this->data = $data;
		$this->skyLight = $skyLight;
		$this->blockLight = $blockLight;

		if(strlen($biomeIds) === 256){
			$this->biomeIds = $biomeIds;
		}else{
			$this->biomeIds = str_repeat("\x01", 256);
		}

		if(count($biomeColors) === 256){
			$this->biomeColors = $biomeColors;
		}else{
			$this->biomeColors = array_fill(0, 256, Binary::readInt("\x00\x85\xb2\x4a"));
		}

		if($this->getProvider() instanceof LevelProvider){
			$this->getProvider()->getLevel()->timings->syncChunkLoadEntitiesTimer->startTiming();
			foreach($entities as $nbt){
				if($nbt instanceof Compound){
					if(!isset($nbt->id)){
						continue;
					}

					if($nbt->id instanceof String){ //New format
						switch($nbt["id"]){
							case "Item":
								(new DroppedItem($this, $nbt))->spawnToAll();
								break;
						}
					}else{ //Old format

					}
				}
			}
			$this->getProvider()->getLevel()->timings->syncChunkLoadEntitiesTimer->stopTiming();

			$this->getProvider()->getLevel()->timings->syncChunkLoadTileEntitiesTimer->startTiming();
			foreach($tiles as $nbt){
				if($nbt instanceof Compound){
					if(!isset($nbt->id)){
						continue;
					}
					switch($nbt["id"]){
						case Tile::CHEST:
							new Chest($this, $nbt);
							break;
						case Tile::FURNACE:
							new Furnace($this, $nbt);
							break;
						case Tile::SIGN:
							new Sign($this, $nbt);
							break;
					}
				}
			}
			$this->getProvider()->getLevel()->timings->syncChunkLoadTileEntitiesTimer->stopTiming();
		}
	}

	public function getX(){
		return $this->x;
	}

	public function getZ(){
		return $this->z;
	}

	public function setX($x){
		$this->x = $x;
	}

	public function setZ($z){
		$this->z = $z;
	}

	/**
	 * @return LevelProvider
	 *
	 * @deprecated
	 */
	public function getLevel(){
		return $this->getProvider();
	}

	/**
	 * @return LevelProvider
	 */
	public function getProvider(){
		return $this->provider;
	}

	public function setProvider(LevelProvider $provider){
		$this->provider = $provider;
	}

	public function getBiomeId($x, $z){
		return ord($this->biomeIds{($z << 4) + $x});
	}

	public function setBiomeId($x, $z, $biomeId){
		$this->biomeIds{($z << 4) + $x} = chr($biomeId);
	}

	public function getBiomeColor($x, $z){
		$color = $this->biomeColors[($z << 4) + $x] & 0xFFFFFF;

		return [$color >> 16, ($color >> 8) & 0xFF, $color & 0xFF];
	}

	public function setBiomeColor($x, $z, $R, $G, $B){
		$this->biomeColors[($z << 4) + $x] = 0 | (($R & 0xFF) << 16) | (($G & 0xFF) << 8) | ($B & 0xFF);
	}

	public function getHighestBlockAt($x, $z){
		$column = $this->getBlockIdColumn($x, $z);
		for($y = 127; $y >= 0; --$y){
			if($column{$y} !== "\x00"){
				return $y;
			}
		}

		return 0;
	}

	public function addEntity(Entity $entity){
		$this->entities[$entity->getID()] = $entity;
	}

	public function removeEntity(Entity $entity){
		unset($this->entities[$entity->getID()]);
	}

	public function addTile(Tile $tile){
		$this->tiles[$tile->getID()] = $tile;
	}

	public function removeTile(Tile $tile){
		unset($this->tiles[$tile->getID()]);
	}

	public function getEntities(){
		return $this->entities;
	}

	public function getTiles(){
		return $this->tiles;
	}

	public function isLoaded(){
		return $this->getProvider() === null ? false : $this->getProvider()->isChunkLoaded($this->getX(), $this->getZ());
	}

	public function load($generate = true){
		return $this->getProvider() === null ? false : $this->getProvider()->getChunk($this->getX(), $this->getZ(), true) instanceof FullChunk;
	}

	public function unload($save = true, $safe = true){
		$level = $this->getProvider();
		if($level === null){
			return true;
		}
		if($save === true){
			$level->saveChunk($this->getX(), $this->getZ());
		}
		if($this->getProvider()->unloadChunk($this->getX(), $this->getZ(), $safe)){
			foreach($this->getEntities() as $entity){
				$entity->close();
			}
			foreach($this->getTiles() as $tile){
				$tile->close();
			}
		}
	}

	public function getBlockIdArray(){
		return $this->blocks;
	}

	public function getBlockDataArray(){
		return $this->data;
	}

	public function getBlockSkyLightArray(){
		return $this->skyLight;
	}

	public function getBlockLightArray(){
		return $this->blockLight;
	}

	public function getBiomeIdArray(){
		return $this->biomeIds;
	}

	public function getBiomeColorArray(){
		return $this->biomeColors;
	}

}