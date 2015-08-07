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

use pocketmine\event\Timings;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\utils\ChunkException;

abstract class Tile extends Position{
	const SIGN = "Sign";
	const CHEST = "Chest";
	const FURNACE = "Furnace";
	const FLOWER_POT = "FlowerPot";
	const MOB_SPAWNER = "MobSpawner";
	const SKULL = "Skull";
	const BREWING_STAND = "Cauldron";
	const ENCHANT_TABLE = "EnchantTable";

	public static $tileCount = 1;

	private static $knownTiles = [];
	private static $shortNames = [];

	/** @var Chunk */
	public $chunk;
	public $name;
	public $id;
	public $x;
	public $y;
	public $z;
	public $attach;
	public $metadata;
	public $closed = false;
	public $namedtag;
	protected $lastUpdate;
	protected $server;
	protected $timings;

	/** @var \pocketmine\event\TimingsHandler */
	public $tickTimer;

	/**
	 * @param string    $type
	 * @param FullChunk $chunk
	 * @param Compound  $nbt
	 * @param           $args
	 *
	 * @return Tile
	 */
	public static function createTile($type, FullChunk $chunk, Compound $nbt, ...$args){
		if(isset(self::$knownTiles[$type])){
			$class = self::$knownTiles[$type];
			return new $class($chunk, $nbt, ...$args);
		}

		return null;
	}

	/**
	 * @param $className
	 *
	 * @return bool
	 */
	public static function registerTile($className){
		$class = new \ReflectionClass($className);
		if(is_a($className, Tile::class, true) and !$class->isAbstract()){
			self::$knownTiles[$class->getShortName()] = $className;
			self::$shortNames[$className] = $class->getShortName();
			return true;
		}

		return false;
	}

	/**
	 * Returns the short save name
	 *
	 * @return string
	 */
	public function getSaveId(){
		return self::$shortNames[static::class];
	}

	public function __construct(FullChunk $chunk, Compound $nbt){
		if($chunk === null or $chunk->getProvider() === null){
			throw new ChunkException("Invalid garbage Chunk given to Tile");
		}

		$this->timings = Timings::getTileEntityTimings($this);

		$this->server = $chunk->getProvider()->getLevel()->getServer();
		$this->chunk = $chunk;
		$this->setLevel($chunk->getProvider()->getLevel());
		$this->namedtag = $nbt;
		$this->name = "";
		$this->lastUpdate = microtime(true);
		$this->id = Tile::$tileCount++;
		$this->x = (int) $this->namedtag["x"];
		$this->y = (int) $this->namedtag["y"];
		$this->z = (int) $this->namedtag["z"];

		$this->chunk->addTile($this);
		$this->getLevel()->addTile($this);
		$this->tickTimer = Timings::getTileEntityTimings($this);
	}

	public function getId(){
		return $this->id;
	}

	public function saveNBT(){
		$this->namedtag->id = new String("id", $this->getSaveId());
		$this->namedtag->x = new Int("x", $this->x);
		$this->namedtag->y = new Int("y", $this->y);
		$this->namedtag->z = new Int("z", $this->z);
	}

	/**
	 * @return \pocketmine\block\Block
	 */
	public function getBlock(){
		return $this->level->getBlock($this);
	}

	public function onUpdate(){
		return false;
	}

	public final function scheduleUpdate(){
		$this->level->updateTiles[$this->id] = $this;
	}

	public function __destruct(){
		$this->close();
	}

	public function close(){
		if(!$this->closed){
			$this->closed = true;
			unset($this->level->updateTiles[$this->id]);
			if($this->chunk instanceof FullChunk){
				$this->chunk->removeTile($this);
			}
			if(($level = $this->getLevel()) instanceof Level){
				$level->removeTile($this);
			}
			$this->level = null;
		}
	}

	public function getName(){
		return $this->name;
	}

}
