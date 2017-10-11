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

/**
 * All the Tile classes and related classes
 */

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;
use pocketmine\item\Item;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;

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
	const BED = "Bed";

	/** @var int */
	public static $tileCount = 1;

	/** @var string[] classes that extend Tile */
	private static $knownTiles = [];
	/** @var string[] */
	private static $shortNames = [];

	/** @var Chunk */
	public $chunk;
	/** @var string */
	public $name;
	/** @var int */
	public $id;
	/** @var bool */
	public $closed = false;
	/** @var CompoundTag */
	public $namedtag;
	/** @var float */
	protected $lastUpdate;
	/** @var Server */
	protected $server;
	/** @var TimingsHandler */
	protected $timings;

	public static function init(){
		self::registerTile(Bed::class);
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
	 * @return Tile|null
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
	public static function registerTile($className) : bool{
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
	 * @return string
	 */
	public static function getSaveId() : string{
		return self::$shortNames[static::class];
	}

	public function __construct(Level $level, CompoundTag $nbt){
		$this->timings = Timings::getTileEntityTimings($this);

		$this->namedtag = $nbt;
		$this->server = $level->getServer();
		$this->setLevel($level);
		$this->chunk = $level->getChunk($this->namedtag->x->getValue() >> 4, $this->namedtag->z->getValue() >> 4, false);
		assert($this->chunk !== null);

		$this->name = "";
		$this->lastUpdate = microtime(true);
		$this->id = Tile::$tileCount++;
		$this->x = $this->namedtag->x->getValue();
		$this->y = $this->namedtag->y->getValue();
		$this->z = $this->namedtag->z->getValue();

		$this->chunk->addTile($this);
		$this->getLevel()->addTile($this);
	}

	public function getId(){
		return $this->id;
	}

	public function saveNBT(){
		$this->namedtag->id->setValue(static::getSaveId());
		$this->namedtag->x->setValue($this->x);
		$this->namedtag->y->setValue($this->y);
		$this->namedtag->z->setValue($this->z);
	}

	public function getCleanedNBT(){
		$this->saveNBT();
		$tag = clone $this->namedtag;
		unset($tag->x, $tag->y, $tag->z, $tag->id);
		if($tag->getCount() > 0){
			return $tag;
		}else{
			return null;
		}
	}

	/**
	 * Creates and returns a CompoundTag containing the necessary information to spawn a tile of this type.
	 *
	 * @param Vector3     $pos
	 * @param int|null    $face
	 * @param Item|null   $item
	 * @param Player|null $player
	 *
	 * @return CompoundTag
	 */
	public static function createNBT(Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : CompoundTag{
		$nbt = new CompoundTag("", [
			new StringTag("id", static::getSaveId()),
			new IntTag("x", (int) $pos->x),
			new IntTag("y", (int) $pos->y),
			new IntTag("z", (int) $pos->z)
		]);

		static::createAdditionalNBT($nbt, $pos, $face, $item, $player);

		if($item !== null){
			if($item->hasCustomBlockData()){
				foreach($item->getCustomBlockData() as $customBlockDataTag){
					if(!($customBlockDataTag instanceof NamedTag)){
						continue;
					}
					$nbt->{$customBlockDataTag->getName()} = $customBlockDataTag;
				}
			}
		}

		return $nbt;
	}

	/**
	 * Called by createNBT() to allow descendent classes to add their own base NBT using the parameters provided.
	 *
	 * @param CompoundTag $nbt
	 * @param Vector3     $pos
	 * @param int|null    $face
	 * @param Item|null   $item
	 * @param Player|null $player
	 */
	protected static function createAdditionalNBT(CompoundTag $nbt, Vector3 $pos, ?int $face = null, ?Item $item = null, ?Player $player = null) : void{

	}

	/**
	 * @return Block
	 */
	public function getBlock() : Block{
		return $this->level->getBlock($this);
	}

	/**
	 * @return bool
	 */
	public function onUpdate() : bool{
		return false;
	}

	final public function scheduleUpdate(){
		$this->level->updateTiles[$this->id] = $this;
	}

	public function isClosed() : bool{
		return $this->closed;
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
