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

use pocketmine\block\Block;
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
use pocketmine\item\PotionType;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\RegistryTrait;
use pocketmine\utils\Utils;
use function mb_strtoupper;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static Arrow ARROW(Location $location, ?Entity $shootingEntity, bool $critical, ?CompoundTag $nbt = null)
 * @method static Egg EGG(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
 * @method static EnderPearl ENDER_PEARL(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
 * @method static ExperienceBottle EXPERIENCE_BOTTLE(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
 * @method static ExperienceOrb EXPERIENCE_ORB(Location $location, int $xpValue, ?CompoundTag $nbt = null)
 * @method static FallingBlock FALLING_BLOCK(Location $location, Block $block, ?CompoundTag $nbt = null)
 * @method static Human HUMAN(Location $location, Skin $skin, ?CompoundTag $nbt = null)
 * @method static ItemEntity ITEM_ENTITY(Location $location, Item $item, ?CompoundTag $nbt = null)
 * @method static Painting PAINTING(Location $location, Vector3 $blockIn, int $facing, PaintingMotive $motive, ?CompoundTag $nbt = null)
 * @method static PrimedTNT PRIMED_TNT(Location $location, ?CompoundTag $nbt = null)
 * @method static Snowball SNOWBALL(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null)
 * @method static SplashPotion SPLASH_POTION(Location $location, ?Entity $shootingEntity, PotionType $potionType, ?CompoundTag $nbt = null)
 * @method static Squid SQUID(Location $location, ?CompoundTag $nbt = null)
 * @method static Villager VILLAGER(Location $location, ?CompoundTag $nbt = null)
 * @method static Zombie ZOMBIE(Location $location, ?CompoundTag $nbt = null)
 */
final class VanillaEntities{
	use RegistryTrait;

	protected static function setup() : void{
		self::_registryRegister("arrow", function(Location $location, ?Entity $shootingEntity, bool $critical, ?CompoundTag $nbt = null) : Arrow{
			return new Arrow($location, $shootingEntity, $critical, $nbt);
		});
		self::_registryRegister("egg", function(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null) : Egg{
			return new Egg($location, $shootingEntity, $nbt);
		});
		self::_registryRegister("ender_pearl", function(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null) : EnderPearl{
			return new EnderPearl($location, $shootingEntity, $nbt);
		});
		self::_registryRegister("experience_bottle", function(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null) : ExperienceBottle{
			return new ExperienceBottle($location, $shootingEntity, $nbt);
		});
		self::_registryRegister("experience_orb", function(Location $location, int $xpValue, ?CompoundTag $nbt = null) : ExperienceOrb{
			return new ExperienceOrb($location, $xpValue, $nbt);
		});
		self::_registryRegister("falling_block", function(Location $location, Block $block, ?CompoundTag $nbt = null) : FallingBlock{
			return new FallingBlock($location, $block, $nbt);
		});
		self::_registryRegister("item_entity", function(Location $location, Item $item, ?CompoundTag $nbt = null) : ItemEntity{
			return new ItemEntity($location, $item, $nbt);
		});
		self::_registryRegister("painting", function(Location $location, Vector3 $blockIn, int $facing, PaintingMotive $motive, ?CompoundTag $nbt = null) : Painting{
			return new Painting($location, $blockIn, $facing, $motive, $nbt);
		});
		self::_registryRegister("primed_tnt", function(Location $location, ?CompoundTag $nbt = null) : PrimedTNT{
			return new PrimedTNT($location, $nbt);
		});
		self::_registryRegister("snowball", function(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null) : Snowball{
			return new Snowball($location, $shootingEntity, $nbt);
		});
		self::_registryRegister("splash_potion", function(Location $location, ?Entity $shootingEntity, PotionType $potionType, ?CompoundTag $nbt = null) : SplashPotion{
			return new SplashPotion($location, $shootingEntity, $potionType, $nbt);
		});
		self::_registryRegister("squid", function(Location $location, ?CompoundTag $nbt = null) : Squid{
			return new Squid($location, $nbt);
		});
		self::_registryRegister("villager", function(Location $location, ?CompoundTag $nbt = null) : Villager{
			return new Villager($location, $nbt);
		});
		self::_registryRegister("zombie", function(Location $location, ?CompoundTag $nbt = null) : Zombie{
			return new Zombie($location, $nbt);
		});
		self::_registryRegister("human", function(Location $location, Skin $skin, ?CompoundTag $nbt = null) : Human{
			return new Human($location, $skin, $nbt);
		});
	}

	public static function register(string $name, \Closure $callback) : void{
		self::checkInit();
		$name = mb_strtoupper($name);
		if(isset(self::$members[$name])){
			Utils::validateCallableSignature(self::$members[$name], $callback);
		}
		self::$members[mb_strtoupper($name)] = $callback;
	}

	/**
	 * @param string  $name
	 * @param mixed[] $arguments
	 *
	 * @phpstan-param list<mixed> $arguments
	 *
	 * @return object
	 */
	public static function __callStatic($name, $arguments){
		try{
			return self::_registryFromString($name)(...$arguments);
		}catch(\InvalidArgumentException $e){
			throw new \Error($e->getMessage(), 0, $e);
		}
	}

	/**
	 * @return callable[]
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var callable[] $result */
		$result = self::_registryGetAll();
		return $result;
	}
}