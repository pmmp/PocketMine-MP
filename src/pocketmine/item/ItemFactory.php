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

namespace pocketmine\item;

use pocketmine\block\BlockFactory;
use pocketmine\nbt\tag\CompoundTag;

/**
 * Manages Item instance creation and registration
 */
class ItemFactory{

	/** @var \SplFixedArray */
	public static $list = null;

	public static function init(){
		if(self::$list === null){
			self::$list = new \SplFixedArray(65536);

			self::registerItem(new IronShovel());
			self::registerItem(new IronPickaxe());
			self::registerItem(new IronAxe());
			self::registerItem(new FlintSteel());
			self::registerItem(new Apple());
			self::registerItem(new Bow());
			self::registerItem(new Arrow());
			self::registerItem(new Coal());
			self::registerItem(new Diamond());
			self::registerItem(new IronIngot());
			self::registerItem(new GoldIngot());
			self::registerItem(new IronSword());
			self::registerItem(new WoodenSword());
			self::registerItem(new WoodenShovel());
			self::registerItem(new WoodenPickaxe());
			self::registerItem(new WoodenAxe());
			self::registerItem(new StoneSword());
			self::registerItem(new StoneShovel());
			self::registerItem(new StonePickaxe());
			self::registerItem(new StoneAxe());
			self::registerItem(new DiamondSword());
			self::registerItem(new DiamondShovel());
			self::registerItem(new DiamondPickaxe());
			self::registerItem(new DiamondAxe());
			self::registerItem(new Stick());
			self::registerItem(new Bowl());
			self::registerItem(new MushroomStew());
			self::registerItem(new GoldSword());
			self::registerItem(new GoldShovel());
			self::registerItem(new GoldPickaxe());
			self::registerItem(new GoldAxe());
			self::registerItem(new StringItem());
			self::registerItem(new Feather());
			self::registerItem(new Gunpowder());
			self::registerItem(new WoodenHoe());
			self::registerItem(new StoneHoe());
			self::registerItem(new IronHoe());
			self::registerItem(new DiamondHoe());
			self::registerItem(new GoldHoe());
			self::registerItem(new WheatSeeds());
			self::registerItem(new Wheat());
			self::registerItem(new Bread());
			self::registerItem(new LeatherCap());
			self::registerItem(new LeatherTunic());
			self::registerItem(new LeatherPants());
			self::registerItem(new LeatherBoots());
			self::registerItem(new ChainHelmet());
			self::registerItem(new ChainChestplate());
			self::registerItem(new ChainLeggings());
			self::registerItem(new ChainBoots());
			self::registerItem(new IronHelmet());
			self::registerItem(new IronChestplate());
			self::registerItem(new IronLeggings());
			self::registerItem(new IronBoots());
			self::registerItem(new DiamondHelmet());
			self::registerItem(new DiamondChestplate());
			self::registerItem(new DiamondLeggings());
			self::registerItem(new DiamondBoots());
			self::registerItem(new GoldHelmet());
			self::registerItem(new GoldChestplate());
			self::registerItem(new GoldLeggings());
			self::registerItem(new GoldBoots());
			self::registerItem(new Flint());
			self::registerItem(new RawPorkchop());
			self::registerItem(new CookedPorkchop());
			self::registerItem(new Painting());
			self::registerItem(new GoldenApple());
			self::registerItem(new Sign());
			self::registerItem(new WoodenDoor());
			self::registerItem(new Bucket());

			self::registerItem(new Minecart());

			self::registerItem(new IronDoor());
			self::registerItem(new Redstone());
			self::registerItem(new Snowball());
			self::registerItem(new Boat());
			self::registerItem(new Leather());

			self::registerItem(new Brick());
			self::registerItem(new Clay());
			self::registerItem(new Sugarcane());
			self::registerItem(new Paper());
			self::registerItem(new Book());
			self::registerItem(new Slimeball());

			self::registerItem(new Egg());
			self::registerItem(new Compass());
			self::registerItem(new FishingRod());
			self::registerItem(new Clock());
			self::registerItem(new GlowstoneDust());
			self::registerItem(new Fish());
			self::registerItem(new CookedFish());
			self::registerItem(new Dye());
			self::registerItem(new Bone());
			self::registerItem(new Sugar());
			self::registerItem(new Cake());
			self::registerItem(new Bed());

			self::registerItem(new Cookie());

			self::registerItem(new Shears());
			self::registerItem(new Melon());
			self::registerItem(new PumpkinSeeds());
			self::registerItem(new MelonSeeds());
			self::registerItem(new RawBeef());
			self::registerItem(new Steak());
			self::registerItem(new RawChicken());
			self::registerItem(new CookedChicken());

			self::registerItem(new GoldNugget());
			self::registerItem(new NetherWart());
			self::registerItem(new Potion());
			self::registerItem(new GlassBottle());
			self::registerItem(new SpiderEye());
			self::registerItem(new FermentedSpiderEye());
			self::registerItem(new BlazePowder());
			self::registerItem(new MagmaCream());
			self::registerItem(new BrewingStand());

			self::registerItem(new GlisteringMelon());
			self::registerItem(new SpawnEgg());

			self::registerItem(new Emerald());
			self::registerItem(new ItemFrame());
			self::registerItem(new FlowerPot());
			self::registerItem(new Carrot());
			self::registerItem(new Potato());
			self::registerItem(new BakedPotato());

			self::registerItem(new GoldenCarrot());
			self::registerItem(new Skull());

			self::registerItem(new NetherStar());
			self::registerItem(new PumpkinPie());

			self::registerItem(new NetherBrick());
			self::registerItem(new NetherQuartz());

			self::registerItem(new PrismarineShard());

			self::registerItem(new CookedRabbit());

			self::registerItem(new PrismarineCrystals());

			self::registerItem(new Beetroot());
			self::registerItem(new BeetrootSeeds());
			self::registerItem(new BeetrootSoup());

			self::registerItem(new GoldenAppleEnchanted());
		}

		Item::initCreativeItems();
	}

	/**
	 * Registers an item type into the index. Plugins may use this method to register new item types or override existing
	 * ones.
	 *
	 * NOTE: If you are registering a new item type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param Item $item
	 * @param bool $override
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered item without specifying the
	 * $override parameter.
	 */
	public static function registerItem(Item $item, bool $override = false){
		$id = $item->getId();
		if(!$override and self::$list[$id] !== null){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		self::$list[$id] = clone $item;
	}

	/**
	 * Returns an instance of the Item with the specified id, meta, count and NBT.
	 *
	 * @param int                $id
	 * @param int                $meta
	 * @param int                $count
	 * @param CompoundTag|string $tags
	 *
	 * @return Item
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, $tags = "") : Item{
		try{
			if($id < 256){
				return (new ItemBlock(BlockFactory::get($id, $meta), $meta, $count))->setCompoundTag($tags);
			}else{
				/** @var Item|null $item */
				$item = self::$list[$id];
				if($item === null){
					return (new Item($id, $meta, $count))->setCompoundTag($tags);
				}else{
					$item = clone $item;
					$item->setDamage($meta);
					$item->setCount($count);
					$item->setCompoundTag($tags);

					return $item;
				}
			}
		}catch(\RuntimeException $e){
			return (new Item($id, $meta, $count))->setCompoundTag($tags);
		}
	}

	/**
	 * @param string $str
	 * @param bool   $multiple
	 *
	 * @return Item[]|Item
	 */
	public static function fromString(string $str, bool $multiple = false){
		if($multiple === true){
			$blocks = [];
			foreach(explode(",", $str) as $b){
				$blocks[] = self::fromString($b, false);
			}

			return $blocks;
		}else{
			$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			}else{
				$meta = $b[1] & 0xFFFF;
			}

			if(defined(Item::class . "::" . strtoupper($b[0]))){
				$item = self::get(constant(Item::class . "::" . strtoupper($b[0])), $meta);
				if($item->getId() === Item::AIR and strtoupper($b[0]) !== "AIR"){
					$item = self::get($b[0] & 0xFFFF, $meta);
				}
			}else{
				$item = self::get($b[0] & 0xFFFF, $meta);
			}

			return $item;
		}
	}
}