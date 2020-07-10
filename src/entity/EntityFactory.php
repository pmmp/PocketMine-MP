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
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use pocketmine\world\World;
use function count;
use function reset;

/**
 * This class manages the creation of entities loaded from disk.
 * You need to register your entity into this factory if you want to load/save your entity on disk (saving with chunks).
 */
final class EntityFactory{
	use SingletonTrait;

	/**
	 * @var \Closure[] save ID => creator function
	 * @phpstan-var array<int|string, \Closure(World, CompoundTag) : Entity>
	 */
	private $creationFuncs = [];
	/**
	 * @var string[][]
	 * @phpstan-var array<class-string<Entity>, list<string>>
	 */
	private $saveNames = [];

	public function __construct(){
		//define legacy save IDs first - use them for saving for maximum compatibility with Minecraft PC
		//TODO: index them by version to allow proper multi-save compatibility

		$this->register(Arrow::class, function(World $world, CompoundTag $nbt) : Arrow{
			return new Arrow(EntityDataHelper::parseLocation($nbt, $world), null, false, $nbt); //TODO: missing critical flag
		}, ['Arrow', 'minecraft:arrow'], EntityLegacyIds::ARROW);

		$this->register(Egg::class, function(World $world, CompoundTag $nbt) : Egg{
			return new Egg(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['Egg', 'minecraft:egg'], EntityLegacyIds::EGG);

		$this->register(EnderPearl::class, function(World $world, CompoundTag $nbt) : EnderPearl{
			return new EnderPearl(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownEnderpearl', 'minecraft:ender_pearl'], EntityLegacyIds::ENDER_PEARL);

		$this->register(ExperienceBottle::class, function(World $world, CompoundTag $nbt) : ExperienceBottle{
			return new ExperienceBottle(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownExpBottle', 'minecraft:xp_bottle'], EntityLegacyIds::XP_BOTTLE);

		$this->register(ExperienceOrb::class, function(World $world, CompoundTag $nbt) : ExperienceOrb{
			return new ExperienceOrb(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['XPOrb', 'minecraft:xp_orb'], EntityLegacyIds::XP_ORB);

		$this->register(FallingBlock::class, function(World $world, CompoundTag $nbt) : FallingBlock{
			return new FallingBlock(EntityDataHelper::parseLocation($nbt, $world), FallingBlock::parseBlockNBT(BlockFactory::getInstance(), $nbt), $nbt);
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
			return new ItemEntity(EntityDataHelper::parseLocation($nbt, $world), $item, $nbt);
		}, ['Item', 'minecraft:item'], EntityLegacyIds::ITEM);

		$this->register(Painting::class, function(World $world, CompoundTag $nbt) : Painting{
			$motive = PaintingMotive::getMotiveByName($nbt->getString("Motive"));
			if($motive === null){
				throw new \UnexpectedValueException("Unknown painting motive");
			}
			$blockIn = new Vector3($nbt->getInt("TileX"), $nbt->getInt("TileY"), $nbt->getInt("TileZ"));
			if(($directionTag = $nbt->getTag("Direction")) instanceof ByteTag){
				$facing = Painting::DATA_TO_FACING[$directionTag->getValue()] ?? Facing::NORTH;
			}elseif(($facingTag = $nbt->getTag("Facing")) instanceof ByteTag){
				$facing = Painting::DATA_TO_FACING[$facingTag->getValue()] ?? Facing::NORTH;
			}else{
				throw new \UnexpectedValueException("Missing facing info");
			}

			return new Painting(EntityDataHelper::parseLocation($nbt, $world), $blockIn, $facing, $motive, $nbt);
		}, ['Painting', 'minecraft:painting'], EntityLegacyIds::PAINTING);

		$this->register(PrimedTNT::class, function(World $world, CompoundTag $nbt) : PrimedTNT{
			return new PrimedTNT(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['PrimedTnt', 'PrimedTNT', 'minecraft:tnt'], EntityLegacyIds::TNT);

		$this->register(Snowball::class, function(World $world, CompoundTag $nbt) : Snowball{
			return new Snowball(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['Snowball', 'minecraft:snowball'], EntityLegacyIds::SNOWBALL);

		$this->register(SplashPotion::class, function(World $world, CompoundTag $nbt) : SplashPotion{
			return new SplashPotion(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['ThrownPotion', 'minecraft:potion', 'thrownpotion'], EntityLegacyIds::SPLASH_POTION);

		$this->register(Squid::class, function(World $world, CompoundTag $nbt) : Squid{
			return new Squid(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['Squid', 'minecraft:squid'], EntityLegacyIds::SQUID);

		$this->register(Villager::class, function(World $world, CompoundTag $nbt) : Villager{
			return new Villager(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['Villager', 'minecraft:villager'], EntityLegacyIds::VILLAGER);

		$this->register(Zombie::class, function(World $world, CompoundTag $nbt) : Zombie{
			return new Zombie(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, ['Zombie', 'minecraft:zombie'], EntityLegacyIds::ZOMBIE);

		$this->register(Human::class, function(World $world, CompoundTag $nbt) : Human{
			return new Human(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
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
	 * @param string[] $saveNames An array of save names which this entity might be saved under.
	 * @phpstan-param class-string<Entity> $className
	 * @phpstan-param list<string> $saveNames
	 * @phpstan-param \Closure(World $world, CompoundTag $nbt) : Entity $creationFunc
	 *
	 * NOTE: The first save name in the $saveNames array will be used when saving the entity to disk.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function register(string $className, \Closure $creationFunc, array $saveNames, ?int $legacyMcpeSaveId = null) : void{
		if(count($saveNames) === 0){
			throw new \InvalidArgumentException("At least one save name must be provided");
		}
		Utils::testValidInstance($className, Entity::class);
		self::validateCreationFunc($className, $creationFunc);

		foreach($saveNames as $name){
			$this->creationFuncs[$name] = $creationFunc;
		}
		if($legacyMcpeSaveId !== null){
			$this->creationFuncs[$legacyMcpeSaveId] = $creationFunc;
		}

		$this->saveNames[$className] = $saveNames;
	}

	/**
	 * Creates an entity from data stored on a chunk.
	 *
	 * @throws \RuntimeException
	 * @internal
	 */
	public function createFromData(World $world, CompoundTag $nbt) : ?Entity{
		$saveId = $nbt->getTag("id") ?? $nbt->getTag("identifier");
		$func = null;
		if($saveId instanceof StringTag){
			$func = $this->creationFuncs[$saveId->getValue()] ?? null;
		}elseif($saveId instanceof IntTag){ //legacy MCPE format
			$func = $this->creationFuncs[$saveId->getValue() & 0xff] ?? null;
		}
		if($func === null){
			return null;
		}
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
}
