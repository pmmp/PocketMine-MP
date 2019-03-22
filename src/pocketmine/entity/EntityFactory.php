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

namespace pocketmine\entity;

use pocketmine\entity\effect\Effect;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\Painting;
use pocketmine\entity\object\PaintingMotive;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Egg;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\entity\projectile\ExperienceBottle;
use pocketmine\entity\projectile\Snowball;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\Utils;
use function array_keys;
use function assert;
use function in_array;
use function is_a;
use function reset;

final class EntityFactory{

	private static $entityCount = 1;
	/** @var string[] base class => currently used class for construction */
	private static $classMapping = [];
	/** @var Entity[] */
	private static $knownEntities = [];
	/** @var string[][] */
	private static $saveNames = [];

	private function __construct(){
		//NOOP
	}

	/**
	 * Called on server startup to register default entity types.
	 */
	public static function init() : void{
		//define legacy save IDs first - use them for saving for maximum compatibility with Minecraft PC
		//TODO: index them by version to allow proper multi-save compatibility

		self::register(Arrow::class, false, ['Arrow', 'minecraft:arrow']);
		self::register(Egg::class, false, ['Egg', 'minecraft:egg']);
		self::register(EnderPearl::class, false, ['ThrownEnderpearl', 'minecraft:ender_pearl']);
		self::register(ExperienceBottle::class, false, ['ThrownExpBottle', 'minecraft:xp_bottle']);
		self::register(ExperienceOrb::class, false, ['XPOrb', 'minecraft:xp_orb']);
		self::register(FallingBlock::class, false, ['FallingSand', 'minecraft:falling_block']);
		self::register(ItemEntity::class, false, ['Item', 'minecraft:item']);
		self::register(Painting::class, false, ['Painting', 'minecraft:painting']);
		self::register(PrimedTNT::class, false, ['PrimedTnt', 'PrimedTNT', 'minecraft:tnt']);
		self::register(Snowball::class, false, ['Snowball', 'minecraft:snowball']);
		self::register(SplashPotion::class, false, ['ThrownPotion', 'minecraft:potion', 'thrownpotion']);
		self::register(Squid::class, false, ['Squid', 'minecraft:squid']);
		self::register(Villager::class, false, ['Villager', 'minecraft:villager']);
		self::register(Zombie::class, false, ['Zombie', 'minecraft:zombie']);

		self::register(Human::class, true);

		Attribute::init();
		Effect::init();
		PaintingMotive::init();
	}

	/**
	 * Registers an entity type into the index.
	 *
	 * @param string   $className Class that extends Entity
	 * @param bool     $force Force registration even if the entity does not have a valid network ID
	 * @param string[] $saveNames An array of save names which this entity might be saved under. Defaults to the short name of the class itself if empty.
	 *
	 * NOTE: The first save name in the $saveNames array will be used when saving the entity to disk. The reflection
	 * name of the class will be appended to the end and only used if no other save names are specified.
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function register(string $className, bool $force = false, array $saveNames = []) : void{
		Utils::testValidInstance($className, Entity::class);

		/** @var Entity $className */
		if($className::NETWORK_ID !== -1){
			self::$knownEntities[$className::NETWORK_ID] = $className;
		}elseif(!$force){
			throw new \InvalidArgumentException("Class $className does not declare a valid NETWORK_ID and not force-registering");
		}
		self::$classMapping[$className] = $className;

		$shortName = (new \ReflectionClass($className))->getShortName();
		if(!in_array($shortName, $saveNames, true)){
			$saveNames[] = $shortName;
		}

		foreach($saveNames as $name){
			self::$knownEntities[$name] = $className;
		}

		self::$saveNames[$className] = $saveNames;
	}

	/**
	 * Registers a class override for the given class. When a new entity is constructed using the factory, the new class
	 * will be used instead of the base class.
	 *
	 * @param string $baseClass Already-registered entity class to override
	 * @param string $newClass Class which extends the base class
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function override(string $baseClass, string $newClass) : void{
		if(!isset(self::$classMapping[$baseClass])){
			throw new \InvalidArgumentException("Class $baseClass is not a registered entity");
		}

		Utils::testValidInstance($newClass, $baseClass);
		self::$classMapping[$baseClass] = $newClass;
	}

	/**
	 * Returns an array of all registered entity classpaths.
	 *
	 * @return string[]
	 */
	public static function getKnownTypes() : array{
		return array_keys(self::$classMapping);
	}

	/**
	 * Returns a new runtime entity ID for a new entity.
	 *
	 * @return int
	 */
	public static function nextRuntimeId() : int{
		return self::$entityCount++;
	}

	/**
	 * Creates an entity with the specified type, level and NBT, with optional additional arguments to pass to the
	 * entity's constructor.
	 *
	 * TODO: make this NBT-independent
	 *
	 * @param string      $baseClass
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param mixed       ...$args
	 *
	 * @return Entity instanceof $baseClass
	 * @throws \InvalidArgumentException if the class doesn't exist or is not registered
	 */
	public static function create(string $baseClass, Level $level, CompoundTag $nbt, ...$args) : Entity{
		if(isset(self::$classMapping[$baseClass])){
			$class = self::$classMapping[$baseClass];
			assert(is_a($class, $baseClass, true));
			/**
			 * @var Entity $entity
			 * @see Entity::__construct()
			 */
			$entity = new $class($level, $nbt, ...$args);

			return $entity;
		}

		throw new \InvalidArgumentException("Class $baseClass is not a registered entity");
	}

	/**
	 * Creates an entity from data stored on a chunk.
	 * @internal
	 *
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 *
	 * @return Entity|null
	 * @throws \RuntimeException
	 */
	public static function createFromData(Level $level, CompoundTag $nbt) : ?Entity{
		$saveId = $nbt->getTag("id") ?? $nbt->getTag("identifier");
		$baseClass = null;
		if($saveId instanceof StringTag){
			$baseClass = self::$knownEntities[$saveId->getValue()] ?? null;
		}elseif($saveId instanceof IntTag){ //legacy MCPE format
			$baseClass = self::$knownEntities[$saveId->getValue() & 0xff] ?? null;
		}
		if($baseClass === null){
			return null;
		}
		$class = self::$classMapping[$baseClass];
		assert(is_a($class, $baseClass, true));
		/**
		 * @var Entity $entity
		 * @see Entity::__construct()
		 */
		$entity = new $class($level, $nbt);

		return $entity;
	}

	public static function getSaveId(string $class) : string{
		if(isset(self::$saveNames[$class])){
			return reset(self::$saveNames[$class]);
		}
		throw new \InvalidArgumentException("Entity $class is not registered");
	}


	/**
	 * Helper function which creates minimal NBT needed to spawn an entity.
	 *
	 * @param Vector3      $pos
	 * @param Vector3|null $motion
	 * @param float        $yaw
	 * @param float        $pitch
	 *
	 * @return CompoundTag
	 */
	public static function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag{
		return CompoundTag::create()
			->setTag("Pos", new ListTag([
				new DoubleTag($pos->x),
				new DoubleTag($pos->y),
				new DoubleTag($pos->z)
			]))
			->setTag("Motion", new ListTag([
				new DoubleTag($motion ? $motion->x : 0.0),
				new DoubleTag($motion ? $motion->y : 0.0),
				new DoubleTag($motion ? $motion->z : 0.0)
			]))
			->setTag("Rotation", new ListTag([
				new FloatTag($yaw),
				new FloatTag($pitch)
			]));
	}
}
