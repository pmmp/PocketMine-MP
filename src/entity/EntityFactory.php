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
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\world\World;
use function array_keys;
use function assert;
use function in_array;
use function is_a;
use function reset;

/**
 * This class manages the creation of entities loaded from disk (and optionally entities created at runtime).
 *
 * You need to register your entity class into this factory if:
 * a) you want to load/save your entity on disk (saving with chunks)
 * b) you want to allow custom things to provide a custom class for your entity. Note that you must use
 *    create(MyEntity::class) instead of `new MyEntity()` if you want to allow this.
 */
final class EntityFactory{
	use SingletonTrait;

	/** @var int */
	private static $entityCount = 1;

	/**
	 * @var string[] base class => currently used class for construction
	 * @phpstan-var array<class-string<Entity>, class-string<Entity>>
	 */
	private $classMapping = [];
	/**
	 * @var string[]
	 * @phpstan-var array<int|string, class-string<Entity>>
	 */
	private $knownEntities = [];
	/**
	 * @var string[][]
	 * @phpstan-var array<class-string<Entity>, list<string>>
	 */
	private $saveNames = [];

	public function __construct(){
		//define legacy save IDs first - use them for saving for maximum compatibility with Minecraft PC
		//TODO: index them by version to allow proper multi-save compatibility

		$this->register(Arrow::class, ['Arrow', 'minecraft:arrow'], EntityLegacyIds::ARROW);
		$this->register(Egg::class, ['Egg', 'minecraft:egg'], EntityLegacyIds::EGG);
		$this->register(EnderPearl::class, ['ThrownEnderpearl', 'minecraft:ender_pearl'], EntityLegacyIds::ENDER_PEARL);
		$this->register(ExperienceBottle::class, ['ThrownExpBottle', 'minecraft:xp_bottle'], EntityLegacyIds::XP_BOTTLE);
		$this->register(ExperienceOrb::class, ['XPOrb', 'minecraft:xp_orb'], EntityLegacyIds::XP_ORB);
		$this->register(FallingBlock::class, ['FallingSand', 'minecraft:falling_block'], EntityLegacyIds::FALLING_BLOCK);
		$this->register(ItemEntity::class, ['Item', 'minecraft:item'], EntityLegacyIds::ITEM);
		$this->register(Painting::class, ['Painting', 'minecraft:painting'], EntityLegacyIds::PAINTING);
		$this->register(PrimedTNT::class, ['PrimedTnt', 'PrimedTNT', 'minecraft:tnt'], EntityLegacyIds::TNT);
		$this->register(Snowball::class, ['Snowball', 'minecraft:snowball'], EntityLegacyIds::SNOWBALL);
		$this->register(SplashPotion::class, ['ThrownPotion', 'minecraft:potion', 'thrownpotion'], EntityLegacyIds::SPLASH_POTION);
		$this->register(Squid::class, ['Squid', 'minecraft:squid'], EntityLegacyIds::SQUID);
		$this->register(Villager::class, ['Villager', 'minecraft:villager'], EntityLegacyIds::VILLAGER);
		$this->register(Zombie::class, ['Zombie', 'minecraft:zombie'], EntityLegacyIds::ZOMBIE);

		$this->register(Human::class, ['Human']);

		PaintingMotive::init();
	}

	/**
	 * Registers an entity type into the index.
	 *
	 * @param string   $className Class that extends Entity
	 * @param string[] $saveNames An array of save names which this entity might be saved under. Defaults to the short name of the class itself if empty.
	 * @param int|null $legacyMcpeSaveId
	 * @phpstan-param class-string<Entity> $className
	 *
	 * NOTE: The first save name in the $saveNames array will be used when saving the entity to disk. The reflection
	 * name of the class will be appended to the end and only used if no other save names are specified.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function register(string $className, array $saveNames, ?int $legacyMcpeSaveId = null) : void{
		Utils::testValidInstance($className, Entity::class);

		$this->classMapping[$className] = $className;

		$shortName = (new \ReflectionClass($className))->getShortName();
		if(!in_array($shortName, $saveNames, true)){
			$saveNames[] = $shortName;
		}

		foreach($saveNames as $name){
			$this->knownEntities[$name] = $className;
		}
		if($legacyMcpeSaveId !== null){
			$this->knownEntities[$legacyMcpeSaveId] = $className;
		}

		$this->saveNames[$className] = $saveNames;
	}

	/**
	 * Registers a class override for the given class. When a new entity is constructed using the factory, the new class
	 * will be used instead of the base class.
	 *
	 * @param string $baseClass Already-registered entity class to override
	 * @param string $newClass Class which extends the base class
	 *
	 * TODO: use an explicit template for param1
	 * @phpstan-param class-string<Entity> $baseClass
	 * @phpstan-param class-string<Entity> $newClass
	 *
	 * @throws \InvalidArgumentException
	 */
	public function override(string $baseClass, string $newClass) : void{
		if(!isset($this->classMapping[$baseClass])){
			throw new \InvalidArgumentException("Class $baseClass is not a registered entity");
		}

		Utils::testValidInstance($newClass, $baseClass);
		$this->classMapping[$baseClass] = $newClass;
	}

	/**
	 * Returns an array of all registered entity classpaths.
	 *
	 * @return string[]
	 * @return class-string<Entity>[]
	 */
	public function getKnownTypes() : array{
		return array_keys($this->classMapping);
	}

	/**
	 * Returns a new runtime entity ID for a new entity.
	 */
	public static function nextRuntimeId() : int{
		return self::$entityCount++;
	}

	/**
	 * Creates an entity with the specified type, world and NBT, with optional additional arguments to pass to the
	 * entity's constructor.
	 *
	 * TODO: make this NBT-independent
	 *
	 * @phpstan-template TEntity of Entity
	 *
	 * @param mixed       ...$args
	 * @phpstan-param class-string<TEntity> $baseClass
	 *
	 * @return Entity instanceof $baseClass
	 * @phpstan-return TEntity
	 *
	 * @throws \InvalidArgumentException if the class doesn't exist or is not registered
	 */
	public function create(string $baseClass, World $world, CompoundTag $nbt, ...$args) : Entity{
		if(isset($this->classMapping[$baseClass])){
			$class = $this->classMapping[$baseClass];
			assert(is_a($class, $baseClass, true));
			/**
			 * @var Entity $entity
			 * @phpstan-var TEntity $entity
			 * @see Entity::__construct()
			 */
			$entity = new $class($world, $nbt, ...$args);

			return $entity;
		}

		throw new \InvalidArgumentException("Class $baseClass is not a registered entity");
	}

	/**
	 * Creates an entity from data stored on a chunk.
	 *
	 * @throws \RuntimeException
	 * @internal
	 */
	public function createFromData(World $world, CompoundTag $nbt) : ?Entity{
		$saveId = $nbt->getTag("id") ?? $nbt->getTag("identifier");
		$baseClass = null;
		if($saveId instanceof StringTag){
			$baseClass = $this->knownEntities[$saveId->getValue()] ?? null;
		}elseif($saveId instanceof IntTag){ //legacy MCPE format
			$baseClass = $this->knownEntities[$saveId->getValue() & 0xff] ?? null;
		}
		if($baseClass === null){
			return null;
		}
		$class = $this->classMapping[$baseClass];
		assert(is_a($class, $baseClass, true));
		/**
		 * @var Entity $entity
		 * @see Entity::__construct()
		 */
		$entity = new $class($world, $nbt);

		return $entity;
	}

	/**
	 * @phpstan-param class-string<Entity> $class
	 */
	public function getSaveId(string $class) : string{
		if(isset($this->saveNames[$class])){
			return reset($this->saveNames[$class]);
		}
		throw new \InvalidArgumentException("Entity $class is not registered");
	}

	/**
	 * Helper function which creates minimal NBT needed to spawn an entity.
	 */
	public static function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag{
		return CompoundTag::create()
			->setTag("Pos", new ListTag([
				new DoubleTag($pos->x),
				new DoubleTag($pos->y),
				new DoubleTag($pos->z)
			]))
			->setTag("Motion", new ListTag([
				new DoubleTag($motion !== null ? $motion->x : 0.0),
				new DoubleTag($motion !== null ? $motion->y : 0.0),
				new DoubleTag($motion !== null ? $motion->z : 0.0)
			]))
			->setTag("Rotation", new ListTag([
				new FloatTag($yaw),
				new FloatTag($pitch)
			]));
	}
}
