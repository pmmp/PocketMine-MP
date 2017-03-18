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
 */
namespace pocketmine\tile;

use pocketmine\event\Timings;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

abstract class Tile extends Position{

	const BREWING_STAND = "BrewingStand";
	const CHEST = "Chest";
	const ENCHANT_TABLE = "EnchantTable";
	const FLOWER_POT = "FlowerPot";
	const FURNACE = "Furnace";
	const ITEM_FRAME = "ItemFrame";
	const MOB_SPAWNER = "MobSpawner";
	const SIGN = "Sign";
	const SKULL = "Skull";

	public static $tileCount = 1;

	private static $knownTiles = [];
	private static $shortNames = [];

	/** @var Chunk */
	public $chunk;
	public $name;
	public $id;
	public $attach;
	public $metadata;
	public $closed = false;
	public $namedtag;
	protected $lastUpdate;
	protected $server;
	protected $timings;

	/** @var \pocketmine\event\TimingsHandler */
	public $tickTimer;

	public static function init(){
		self::registerTile(Chest::class);
		self::registerTile(EnchantTable::class);
		self::registerTile(FlowerPot::class);
		self::registerTile(Furnace::class);
		self::registerTile(ItemFrame::class);
		self::registerTile(Sign::class);
		self::registerTile(Skull::class);
	}

	/**
	 * @param string      $type
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param             $args
	 *
	 * @return Tile
	 */
	public static function createTile($type, Level $level, CompoundTag $nbt, ...$args){
		if(isset(self::$knownTiles[$type])){
			$class = self::$knownTiles[$type];
			return new $class($level, $nbt, ...$args);
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

	public function __construct(Level $level, CompoundTag $nbt){
		$this->timings = Timings::getTileEntityTimings($this);

		$this->namedtag = $nbt;
		$this->server = $level->getServer();
		$this->setLevel($level);
		$this->chunk = $level->getChunk($this->namedtag["x"] >> 4, $this->namedtag["z"] >> 4, false);
		assert($this->chunk !== null);

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
		$this->namedtag->id = new StringTag("id", $this->getSaveId());
		$this->namedtag->x = new IntTag("x", $this->x);
		$this->namedtag->y = new IntTag("y", $this->y);
		$this->namedtag->z = new IntTag("z", $this->z);
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
			if($this->chunk instanceof Chunk){
				$this->chunk->removeTile($this);
				$this->chunk = null;
			}
			if(($level = $this->getLevel()) instanceof Level){
				$level->removeTile($this);
				$this->setLevel(null);
			}

			$this->namedtag = null;
		}
	}

	public function getName(){
		return $this->name;
	}

}
