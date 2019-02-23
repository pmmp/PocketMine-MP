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

namespace pocketmine\tile;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Utils;
use function assert;
use function in_array;
use function is_a;
use function reset;

final class TileFactory{

	/** @var string[] classes that extend Tile */
	private static $knownTiles = [];
	/** @var string[][] */
	private static $saveNames = [];
	/** @var string[] base class => overridden class */
	private static $classMapping = [];

	private function __construct(){
		//NOOP
	}

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
	 * @internal
	 *
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 *
	 * @return Tile|null
	 */
	public static function createFromData(Level $level, CompoundTag $nbt) : ?Tile{
		$type = $nbt->getString(Tile::TAG_ID, "", true);
		if(!isset(self::$knownTiles[$type])){
			return null;
		}
		$class = self::$knownTiles[$type];
		assert(is_a($class, Tile::class, true));
		/**
		 * @var Tile $tile
		 * @see Tile::__construct()
		 */
		$tile = new $class($level, new Vector3($nbt->getInt(Tile::TAG_X), $nbt->getInt(Tile::TAG_Y), $nbt->getInt(Tile::TAG_Z)));
		$tile->readSaveData($nbt);

		return $tile;
	}

	public static function getSaveId(string $class) : string{
		if(isset(self::$saveNames[$class])){
			return reset(self::$saveNames[$class]);
		}
		throw new \InvalidArgumentException("Tile $class is not registered");
	}
}
