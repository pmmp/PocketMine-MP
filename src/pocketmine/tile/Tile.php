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

/**
 * All the Tile classes and related classes
 * TODO: Add Nether Reactor tile
 */
namespace pocketmine\tile;

use pocketmine\level\format\pmf\LevelFormat;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\Compound;
use pocketmine\Server;

abstract class Tile extends Position{
	const SIGN = "Sign";
	const CHEST = "Chest";
	const FURNACE = "Furnace";

	//TODO: pre-close step NBT data saving method

	public static $tileCount = 1;

	/**
	 * @var Tile[]
	 */
	public static $needUpdate = [];

	public $chunkIndex;
	public $name;
	public $id;
	public $x;
	public $y;
	public $z;
	public $attach;
	public $metadata;
	public $closed;
	public $namedtag;
	protected $lastUpdate;
	protected $server;

	public function getID(){
		return $this->id;
	}


	public function __construct(Level $level, Compound $nbt){
		$this->server = Server::getInstance();
		$this->setLevel($level, true); //Strong reference
		$this->namedtag = $nbt;
		$this->closed = false;
		$this->name = "";
		$this->lastUpdate = microtime(true);
		$this->id = Tile::$tileCount++;
		$this->x = (int) $this->namedtag["x"];
		$this->y = (int) $this->namedtag["y"];
		$this->z = (int) $this->namedtag["z"];

		$index = LevelFormat::getIndex($this->x >> 4, $this->z >> 4);
		$this->chunkIndex = $index;
		$this->getLevel()->addTile($this);
		$this->getLevel()->chunkTiles[$this->chunkIndex][$this->id] = $this;
	}

	public function saveNBT(){
		$this->namedtag["x"] = $this->x;
		$this->namedtag["y"] = $this->y;
		$this->namedtag["z"] = $this->z;
	}

	public function onUpdate(){
		return false;
	}

	public final function scheduleUpdate(){
		Tile::$needUpdate[$this->id] = $this;
	}

	public function close(){
		if($this->closed === false){
			$this->closed = true;
			unset(Tile::$needUpdate[$this->id]);
			$this->getLevel()->removeTile($this);
			unset($this->getLevel()->chunkTiles[$this->chunkIndex][$this->id]);
			unset(Tile::$list[$this->id]);
		}
	}

	public function __destruct(){
		$this->close();
	}

	public function getName(){
		return $this->name;
	}

}
