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
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;

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
	/** @var string[][] */
	private static $saveNames = [];

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
		self::registerTile(Banner::class, [self::BANNER, "minecraft:banner"]);
		self::registerTile(Bed::class, [self::BED, "minecraft:bed"]);
		self::registerTile(Chest::class, [self::CHEST, "minecraft:chest"]);
		self::registerTile(EnchantTable::class, [self::ENCHANT_TABLE, "minecraft:enchanting_table"]);
		self::registerTile(EnderChest::class, [self::ENDER_CHEST, "minecraft:ender_chest"]);
		self::registerTile(FlowerPot::class, [self::FLOWER_POT, "minecraft:flower_pot"]);
		self::registerTile(Furnace::class, [self::FURNACE, "minecraft:furnace"]);
		self::registerTile(ItemFrame::class, [self::ITEM_FRAME]); //this is an entity in PC
		self::registerTile(Sign::class, [self::SIGN, "minecraft:sign"]);
		self::registerTile(Skull::class, [self::SKULL, "minecraft:skull"]);
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
	 * @param       $className
	 * @param array $saveNames
	 *
	 * @return bool
	 * @throws \ReflectionException
	 */
	public static function registerTile($className, array $saveNames = []) : bool{
		$class = new \ReflectionClass($className);
		if(is_a($className, Tile::class, true) and !$class->isAbstract()){
			$shortName = $class->getShortName();
			if(!in_array($shortName, $saveNames, true)){
				$saveNames[] = $shortName;
			}

			foreach($saveNames as $name){
				self::$knownTiles[$name] = $className;
			}

			self::$saveNames[$className] = $saveNames;


			return true;
		}

		return false;
	}

	/**
	 * Returns the short save name
	 * @return string
	 */
	public static function getSaveId() : string{
		if(!isset(self::$saveNames[static::class])){
			throw new \InvalidStateException("Tile is not registered");
		}

		reset(self::$saveNames[static::class]);
		return current(self::$saveNames[static::class]);
	}

	public function __construct(Level $level, CompoundTag $nbt){
		$this->timings = Timings::getTileEntityTimings($this);

		$this->namedtag = $nbt;
		$this->server = $level->getServer();
		$this->setLevel($level);

		$this->name = "";
		$this->id = Tile::$tileCount++;
		$this->x = $this->namedtag->getInt(self::TAG_X);
		$this->y = $this->namedtag->getInt(self::TAG_Y);
		$this->z = $this->namedtag->getInt(self::TAG_Z);

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
			$customBlockData = $item->getCustomBlockData();
			if($customBlockData !== null){
				foreach($customBlockData as $customBlockDataTag){
					$nbt->setTag(clone $customBlockDataTag);
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

			if($this->isValid()){
				$this->level->removeTile($this);
				$this->setLevel(null);
			}

			$this->namedtag = null;
		}
	}

	public function getName() : string{
		return $this->name;
	}

}
