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

use pocketmine\math\Vector3;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\world\World;
use function assert;
use function in_array;
use function is_a;
use function reset;

final class TileFactory{
	use SingletonTrait;

	/**
	 * @var string[] classes that extend Tile
	 * @phpstan-var array<string, class-string<Tile>>
	 */
	private $knownTiles = [];
	/**
	 * @var string[]
	 * @phpstan-var array<class-string<Tile>, string>
	 */
	private $saveNames = [];

	public function __construct(){
		$this->register(Barrel::class, ["Barrel", "minecraft:barrel"]);
		$this->register(Banner::class, ["Banner", "minecraft:banner"]);
		$this->register(Beacon::class, ["Beacon", "minecraft:beacon"]);
		$this->register(Bed::class, ["Bed", "minecraft:bed"]);
		$this->register(Bell::class, ["Bell", "minecraft:bell"]);
		$this->register(BrewingStand::class, ["BrewingStand", "minecraft:brewing_stand"]);
		$this->register(Chest::class, ["Chest", "minecraft:chest"]);
		$this->register(Comparator::class, ["Comparator", "minecraft:comparator"]);
		$this->register(DaylightSensor::class, ["DaylightDetector", "minecraft:daylight_detector"]);
		$this->register(EnchantTable::class, ["EnchantTable", "minecraft:enchanting_table"]);
		$this->register(EnderChest::class, ["EnderChest", "minecraft:ender_chest"]);
		$this->register(FlowerPot::class, ["FlowerPot", "minecraft:flower_pot"]);
		$this->register(Furnace::class, ["Furnace", "minecraft:furnace"]);
		$this->register(Hopper::class, ["Hopper", "minecraft:hopper"]);
		$this->register(ItemFrame::class, ["ItemFrame"]); //this is an entity in PC
		$this->register(Jukebox::class, ["Jukebox", "RecordPlayer", "minecraft:jukebox"]);
		$this->register(MonsterSpawner::class, ["MobSpawner", "minecraft:mob_spawner"]);
		$this->register(Note::class, ["Music", "minecraft:noteblock"]);
		$this->register(ShulkerBox::class, ["ShulkerBox", "minecraft:shulker_box"]);
		$this->register(Sign::class, ["Sign", "minecraft:sign"]);
		$this->register(Skull::class, ["Skull", "minecraft:skull"]);

		//TODO: BlastFurnace
		//TODO: Campfire
		//TODO: Cauldron
		//TODO: ChalkboardBlock
		//TODO: ChemistryTable
		//TODO: CommandBlock
		//TODO: Conduit
		//TODO: Dispenser
		//TODO: Dropper
		//TODO: EndGateway
		//TODO: EndPortal
		//TODO: JigsawBlock
		//TODO: Lectern
		//TODO: MovingBlock
		//TODO: NetherReactor
		//TODO: PistonArm
		//TODO: Smoker
		//TODO: StructureBlock
	}

	/**
	 * @param string[] $saveNames
	 * @phpstan-param class-string<Tile> $className
	 */
	public function register(string $className, array $saveNames = []) : void{
		Utils::testValidInstance($className, Tile::class);

		$shortName = (new \ReflectionClass($className))->getShortName();
		if(!in_array($shortName, $saveNames, true)){
			$saveNames[] = $shortName;
		}

		foreach($saveNames as $name){
			$this->knownTiles[$name] = $className;
		}

		$this->saveNames[$className] = reset($saveNames);
	}

	/**
	 * @internal
	 * @throws NbtDataException
	 */
	public function createFromData(World $world, CompoundTag $nbt) : ?Tile{
		$type = $nbt->getString(Tile::TAG_ID, "");
		if(!isset($this->knownTiles[$type])){
			return null;
		}
		$class = $this->knownTiles[$type];
		assert(is_a($class, Tile::class, true));
		/**
		 * @var Tile $tile
		 * @see Tile::__construct()
		 */
		$tile = new $class($world, new Vector3($nbt->getInt(Tile::TAG_X), $nbt->getInt(Tile::TAG_Y), $nbt->getInt(Tile::TAG_Z)));
		$tile->readSaveData($nbt);

		return $tile;
	}

	/**
	 * @phpstan-param class-string<Tile> $class
	 */
	public function getSaveId(string $class) : string{
		if(isset($this->saveNames[$class])){
			return $this->saveNames[$class];
		}
		throw new \InvalidArgumentException("Tile $class is not registered");
	}
}
