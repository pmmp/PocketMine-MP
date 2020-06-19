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

use DaveRandom\CallbackValidator\CallbackType;
use DaveRandom\CallbackValidator\ParameterType;
use DaveRandom\CallbackValidator\ReturnType;
use pocketmine\block\BlockFactory;
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
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
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
use function count;
use function in_array;
use function reset;

/**
 * This class manages the creation of entities loaded from disk.
 * You need to register your entity into this factory if you want to load/save your entity on disk (saving with chunks).
 */
final class EntityFactory{
	use SingletonTrait;

	/** @var int */
	private static $entityCount = 1;

	/**
	 * @var \Closure[] base class => creator function
	 * @phpstan-var array<class-string<Entity>, \Closure(World, CompoundTag) : Entity>
	 */
	private $creationFuncs = [];
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

		$this->register(Arrow::class, function(World $world, CompoundTag $nbt) : Arrow{
			return new Arrow(self::parseLocation($nbt, $world), null, false, $nbt); //TODO: missing critical flag
		}, ['Arrow', 'minecraft:arrow'], EntityLegacyIds::ARROW);

		$this->register(Egg::class, function(World $world, CompoundTag $nbt) : Egg{
			return new Egg(self::parseLocation($nbt, $world), null, $nbt);
		}, ['Egg', 'minecraft:egg'], EntityLegacyIds::EGG);

		$this->register(EnderPearl::class, function(World $world, CompoundTag $nbt) : EnderPearl{
			return new EnderPearl(self::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownEnderpearl', 'minecraft:ender_pearl'], EntityLegacyIds::ENDER_PEARL);

		$this->register(ExperienceBottle::class, function(World $world, CompoundTag $nbt) : ExperienceBottle{
			return new ExperienceBottle(self::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownExpBottle', 'minecraft:xp_bottle'], EntityLegacyIds::XP_BOTTLE);

		$this->register(ExperienceOrb::class, function(World $world, CompoundTag $nbt) : ExperienceOrb{
			return new ExperienceOrb(self::parseLocation($nbt, $world), $nbt);
		}, ['XPOrb', 'minecraft:xp_orb'], EntityLegacyIds::XP_ORB);

		$this->register(FallingBlock::class, function(World $world, CompoundTag $nbt) : FallingBlock{
			return new FallingBlock(self::parseLocation($nbt, $world), FallingBlock::parseBlockNBT(BlockFactory::getInstance(), $nbt), $nbt);
		}, ['FallingSand', 'minecraft:falling_block'], EntityLegacyIds::FALLING_BLOCK);

		$this->register(ItemEntity::class, function(World $world, CompoundTag $nbt) : ItemEntity{
			$itemTag = $nbt->getCompoundTag("Item");
			if($itemTag === null){
				throw new \UnexpectedValueException("Expected \"Item\" NBT tag not found");
			}

			$item = Item::nbtDeserialize($itemTag);
			if($item->isNull()){
				throw new \UnexpectedValueException("Item is invalid");
			}
			return new ItemEntity(self::parseLocation($nbt, $world), $item, $nbt);
		}, ['Item', 'minecraft:item'], EntityLegacyIds::ITEM);

		$this->register(Painting::class, function(World $world, CompoundTag $nbt) : Painting{
			$motive = PaintingMotive::getMotiveByName($nbt->getString("Motive"));
			if($motive === null){
				throw new \UnexpectedValueException("Unknown painting motive");
			}
			$blockIn = new Vector3($nbt->getInt("TileX"), $nbt->getInt("TileY"), $nbt->getInt("TileZ"));
			if($nbt->hasTag("Direction", ByteTag::class)){
				$facing = Painting::DATA_TO_FACING[$nbt->getByte("Direction")] ?? Facing::NORTH;
			}elseif($nbt->hasTag("Facing", ByteTag::class)){
				$facing = Painting::DATA_TO_FACING[$nbt->getByte("Facing")] ?? Facing::NORTH;
			}else{
				throw new \UnexpectedValueException("Missing facing info");
			}

			return new Painting(self::parseLocation($nbt, $world), $blockIn, $facing, $motive, $nbt);
		}, ['Painting', 'minecraft:painting'], EntityLegacyIds::PAINTING);

		$this->register(PrimedTNT::class, function(World $world, CompoundTag $nbt) : PrimedTNT{
			return new PrimedTNT(self::parseLocation($nbt, $world), $nbt);
		}, ['PrimedTnt', 'PrimedTNT', 'minecraft:tnt'], EntityLegacyIds::TNT);

		$this->register(Snowball::class, function(World $world, CompoundTag $nbt) : Snowball{
			return new Snowball(self::parseLocation($nbt, $world), null, $nbt);
		}, ['Snowball', 'minecraft:snowball'], EntityLegacyIds::SNOWBALL);

		$this->register(SplashPotion::class, function(World $world, CompoundTag $nbt) : SplashPotion{
			return new SplashPotion(self::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownPotion', 'minecraft:potion', 'thrownpotion'], EntityLegacyIds::SPLASH_POTION);

		$this->register(Squid::class, function(World $world, CompoundTag $nbt) : Squid{
			return new Squid(self::parseLocation($nbt, $world), $nbt);
		}, ['Squid', 'minecraft:squid'], EntityLegacyIds::SQUID);

		$this->register(Villager::class, function(World $world, CompoundTag $nbt) : Villager{
			return new Villager(self::parseLocation($nbt, $world), $nbt);
		}, ['Villager', 'minecraft:villager'], EntityLegacyIds::VILLAGER);

		$this->register(Zombie::class, function(World $world, CompoundTag $nbt) : Zombie{
			return new Zombie(self::parseLocation($nbt, $world), $nbt);
		}, ['Zombie', 'minecraft:zombie'], EntityLegacyIds::ZOMBIE);

		$this->register(Human::class, function(World $world, CompoundTag $nbt) : Human{
			return new Human(self::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
		}, ['Human']);

		PaintingMotive::init();
	}

	/**
	 * @phpstan-param class-string<Entity> $baseClass
	 * @phpstan-param \Closure(World, CompoundTag) : Entity $creationFunc
	 */
	private static function validateCreationFunc(string $baseClass, \Closure $creationFunc) : void{
		$sig = new CallbackType(
			new ReturnType($baseClass),
			new ParameterType("world", World::class),
			new ParameterType("nbt", CompoundTag::class)
		);
		if(!$sig->isSatisfiedBy($creationFunc)){
			throw new \TypeError("Declaration of callable `" . CallbackType::createFromCallable($creationFunc) . "` must be compatible with `" . $sig . "`");
		}
	}

	/**
	 * Registers an entity type into the index.
	 *
	 * @param string   $className Class that extends Entity
	 * @param \Closure $creationFunc
	 * @param string[] $saveNames An array of save names which this entity might be saved under. Defaults to the short name of the class itself if empty.
	 * @phpstan-param class-string<Entity> $className
	 * @phpstan-param \Closure(World $world, CompoundTag $nbt) : Entity $creationFunc
	 *
	 * NOTE: The first save name in the $saveNames array will be used when saving the entity to disk. The reflection
	 * name of the class will be appended to the end and only used if no other save names are specified.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function register(string $className, \Closure $creationFunc, array $saveNames, ?int $legacyMcpeSaveId = null) : void{
		Utils::testValidInstance($className, Entity::class);

		self::validateCreationFunc($className, $creationFunc);
		$this->creationFuncs[$className] = $creationFunc;

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
	 * Returns an array of all registered entity classpaths.
	 *
	 * @return string[]
	 * @return class-string<Entity>[]
	 */
	public function getKnownTypes() : array{
		return array_keys($this->creationFuncs);
	}

	/**
	 * Returns a new runtime entity ID for a new entity.
	 */
	public static function nextRuntimeId() : int{
		return self::$entityCount++;
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
		$func = $this->creationFuncs[$baseClass];
		/** @var Entity $entity */
		$entity = $func($world, $nbt);

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

	public static function parseLocation(CompoundTag $nbt, World $world) : Location{
		$pos = self::parseVec3($nbt, "Pos", false);

		$yawPitch = $nbt->getTag("Rotation");
		if(!($yawPitch instanceof ListTag) or $yawPitch->getTagType() !== NBT::TAG_Float){
			throw new \UnexpectedValueException("'Rotation' should be a List<Float>");
		}
		$values = $yawPitch->getValue();
		if(count($values) !== 2){
			throw new \UnexpectedValueException("Expected exactly 2 entries for 'Rotation'");
		}

		return Location::fromObject($pos, $world, $values[0]->getValue(), $values[1]->getValue());
	}

	public static function parseVec3(CompoundTag $nbt, string $tagName, bool $optional) : Vector3{
		$pos = $nbt->getTag($tagName);
		if($pos === null and $optional){
			return new Vector3(0, 0, 0);
		}
		if(!($pos instanceof ListTag) or $pos->getTagType() !== NBT::TAG_Double){
			throw new \UnexpectedValueException("'$tagName' should be a List<Double>");
		}
		/** @var DoubleTag[] $values */
		$values = $pos->getValue();
		if(count($values) !== 3){
			throw new \UnexpectedValueException("Expected exactly 3 entries in '$tagName' tag");
		}
		return new Vector3($values[0]->getValue(), $values[1]->getValue(), $values[2]->getValue());
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
