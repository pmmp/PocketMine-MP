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
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\Utils;

abstract class Tile extends Position{

	public const TAG_ID = "id";
	public const TAG_X = "x";
	public const TAG_Y = "y";
	public const TAG_Z = "z";

	/** @var string[] classes that extend Tile */
	private static $knownTiles = [];
	/** @var string[][] */
	private static $saveNames = [];

	/** @var string[] base class => overridden class */
	private static $classMapping = [];

	/** @var string */
	public $name = "";
	/** @var bool */
	public $closed = false;
	/** @var TimingsHandler */
	protected $timings;

	public static function init(){
		self::register(Banner::class, ["Banner", "minecraft:banner"]);
		self::register(Bed::class, ["Bed", "minecraft:bed"]);
		self::register(Chest::class, ["Chest", "minecraft:chest"]);
		self::register(EnchantTable::class, ["EnchantTable", "minecraft:enchanting_table"]);
		self::register(EnderChest::class, ["EnderChest", "minecraft:ender_chest"]);
		self::register(FlowerPot::class, ["FlowerPot", "minecraft:flower_pot"]);
		self::register(Furnace::class, ["Furnace", "minecraft:furnace"]);
		self::register(ItemFrame::class, ["ItemFrame"]); //this is an entity in PC
		self::register(Sign::class, ["Sign", "minecraft:sign"]);
		self::register(Skull::class, ["Skull", "minecraft:skull"]);
	}

	/**
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 *
	 * @return Tile|null
	 */
	public static function createFromData(Level $level, CompoundTag $nbt) : ?Tile{
		$type = $nbt->getString(self::TAG_ID, "", true);
		if(!isset(self::$knownTiles[$type])){
			return null;
		}
		$class = self::$knownTiles[$type];
		assert(is_a($class, Tile::class, true));
		/**
		 * @var Tile $tile
		 * @see Tile::__construct()
		 */
		$tile = new $class($level, new Vector3($nbt->getInt(self::TAG_X), $nbt->getInt(self::TAG_Y), $nbt->getInt(self::TAG_Z)));
		$tile->readSaveData($nbt);
		return $tile;
	}

	/**
	 * @param string  $baseClass
	 * @param Level   $level
	 * @param Vector3 $pos
	 * @param Item    $item
	 *
	 * @return Tile (instanceof $baseClass)
	 * @throws \InvalidArgumentException if the base class is not a registered tile
	 */
	public static function createFromItem(string $baseClass, Level $level, Vector3 $pos, Item $item) : Tile{
		$tile = self::create($baseClass, $level, $pos);
		$tile->copyDataFromItem($item);

		return $tile;
	}

	/**
	 * @param string   $className
	 * @param string[] $saveNames
	 */
	public static function register(string $className, array $saveNames = []) : void{
		Utils::testValidInstance($className, Tile::class);

		self::$classMapping[$className] = $className;

		$shortName = (new \ReflectionClass($className))->getShortName();
		if(!in_array($shortName, $saveNames, true)){
			$saveNames[] = $shortName;
		}

		foreach($saveNames as $name){
			self::$knownTiles[$name] = $className;
		}

		self::$saveNames[$className] = $saveNames;
	}

	/**
	 * @param string $baseClass Already-registered tile class to override
	 * @param string $newClass Class which extends the base class
	 *
	 * @throws \InvalidArgumentException if the base class is not a registered tile
	 */
	public static function override(string $baseClass, string $newClass) : void{
		if(!isset(self::$classMapping[$baseClass])){
			throw new \InvalidArgumentException("Class $baseClass is not a registered tile");
		}

		Utils::testValidInstance($newClass, $baseClass);
		self::$classMapping[$baseClass] = $newClass;
	}

	/**
	 * @param string  $baseClass
	 * @param Level   $level
	 * @param Vector3 $pos
	 *
	 * @return Tile (will be an instanceof $baseClass)
	 * @throws \InvalidArgumentException if the specified class is not a registered tile
	 */
	public static function create(string $baseClass, Level $level, Vector3 $pos) : Tile{
		if(isset(self::$classMapping[$baseClass])){
			$class = self::$classMapping[$baseClass];
			assert(is_a($class, $baseClass, true));
			/**
			 * @var Tile $tile
			 * @see Tile::__construct()
			 */
			$tile = new $class($level, $pos);
			return $tile;
		}

		throw new \InvalidArgumentException("Class $baseClass is not a registered tile");
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

	public function __construct(Level $level, Vector3 $pos){
		$this->timings = Timings::getTileEntityTimings($this);
		parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), $level);
	}

	/**
	 * Reads additional data from the CompoundTag on tile creation.
	 *
	 * @param CompoundTag $nbt
	 */
	abstract protected function readSaveData(CompoundTag $nbt) : void;

	/**
	 * Writes additional save data to a CompoundTag, not including generic things like ID and coordinates.
	 *
	 * @param CompoundTag $nbt
	 */
	abstract protected function writeSaveData(CompoundTag $nbt) : void;

	public function saveNBT() : CompoundTag{
		$nbt = new CompoundTag();
		$nbt->setString(self::TAG_ID, static::getSaveId());
		$nbt->setInt(self::TAG_X, $this->x);
		$nbt->setInt(self::TAG_Y, $this->y);
		$nbt->setInt(self::TAG_Z, $this->z);
		$this->writeSaveData($nbt);

		return $nbt;
	}

	public function getCleanedNBT() : ?CompoundTag{
		$this->writeSaveData($tag = new CompoundTag());
		return $tag->getCount() > 0 ? $tag : null;
	}

	protected function copyDataFromItem(Item $item) : void{
		if($item->hasCustomBlockData()){ //TODO: check item root tag (MCPE doesn't use BlockEntityTag)
			$this->readSaveData($item->getCustomBlockData());
		}
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
		if($this->closed){
			throw new \InvalidStateException("Cannot schedule update on garbage tile " . get_class($this));
		}
		$this->level->updateTiles[Level::blockHash($this->x, $this->y, $this->z)] = $this;
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
		}
	}

	public function getName() : string{
		return $this->name;
	}
}
