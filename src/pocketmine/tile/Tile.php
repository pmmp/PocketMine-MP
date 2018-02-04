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

	public const TAG_ID = "id";
	public const TAG_X = "x";
	public const TAG_Y = "y";
	public const TAG_Z = "z";

	public const BANNER = "Banner";
	public const BED = "Bed";
	public const BREWING_STAND = "BrewingStand";
	public const CHEST = "Chest";
	public const ENCHANT_TABLE = "EnchantTable";
	public const ENDER_CHEST = "EnderChest";
	public const FLOWER_POT = "FlowerPot";
	public const FURNACE = "Furnace";
	public const ITEM_FRAME = "ItemFrame";
	public const MOB_SPAWNER = "MobSpawner";
	public const SIGN = "Sign";
	public const SKULL = "Skull";

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
	/** @var Server */
	protected $server;
	/** @var TimingsHandler */
	protected $timings;

	public static function init(){
		self::registerTile(Banner::class);
		self::registerTile(Bed::class);
		self::registerTile(Chest::class);
		self::registerTile(EnchantTable::class);
		self::registerTile(EnderChest::class);
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
	public static function createTile($type, Level $level, CompoundTag $nbt, ...$args) : ?Tile{
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
		$this->chunk = $level->getChunk($this->namedtag->getInt(self::TAG_X) >> 4, $this->namedtag->getInt(self::TAG_Z) >> 4, false);
		if($this->chunk === null){
			throw new \InvalidStateException("Cannot create tiles in unloaded chunks");
		}

		$this->name = "";
		$this->id = Tile::$tileCount++;
		$this->x = $this->namedtag->getInt(self::TAG_X);
		$this->y = $this->namedtag->getInt(self::TAG_Y);
		$this->z = $this->namedtag->getInt(self::TAG_Z);

		$this->chunk->addTile($this);
		$this->getLevel()->addTile($this);
	}

	public function getId() : int{
		return $this->id;
	}

	public function saveNBT() : void{
		$this->namedtag->setString(self::TAG_ID, static::getSaveId());
		$this->namedtag->setInt(self::TAG_X, $this->x);
		$this->namedtag->setInt(self::TAG_Y, $this->y);
		$this->namedtag->setInt(self::TAG_Z, $this->z);
	}

	public function getNBT() : CompoundTag{
		return $this->namedtag;
	}

	public function getCleanedNBT() : ?CompoundTag{
		$this->saveNBT();
		$tag = clone $this->namedtag;
		$tag->removeTag(self::TAG_X, self::TAG_Y, self::TAG_Z, self::TAG_ID);
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
			new StringTag(self::TAG_ID, static::getSaveId()),
			new IntTag(self::TAG_X, (int) $pos->x),
			new IntTag(self::TAG_Y, (int) $pos->y),
			new IntTag(self::TAG_Z, (int) $pos->z)
		]);

		static::createAdditionalNBT($nbt, $pos, $face, $item, $player);

		if($item !== null){
			if($item->hasCustomBlockData()){
				foreach($item->getCustomBlockData() as $customBlockDataTag){
					if(!($customBlockDataTag instanceof NamedTag)){
						continue;
					}
					$nbt->setTag($customBlockDataTag);
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
		return $this->level->getBlockAt($this->x, $this->y, $this->z);
	}

	/**
	 * @return bool
	 */
	public function onUpdate() : bool{
		return false;
	}

	final public function scheduleUpdate() : void{
		$this->level->updateTiles[$this->id] = $this;
	}

	public function isClosed() : bool{
		return $this->closed;
	}

	public function __destruct(){
		$this->close();
	}

	public function close() : void{
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

	public function getName() : string{
		return $this->name;
	}

}
