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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\nbt\tag\CompoundTag;

/**
 * Manages Item instance creation and registration
 */
class ItemFactory{

	/** @var \SplFixedArray */
	private static $list = null;

	public static function init(){
		if(self::$list === null){
			self::$list = new \SplFixedArray(65536);

			self::registerItem(new Shovel(Item::IRON_SHOVEL, 0, "Iron Shovel", TieredTool::TIER_IRON));
			self::registerItem(new Pickaxe(Item::IRON_PICKAXE, 0, "Iron Pickaxe", TieredTool::TIER_IRON));
			self::registerItem(new Axe(Item::IRON_AXE, 0, "Iron Axe", TieredTool::TIER_IRON));
			self::registerItem(new FlintSteel());
			self::registerItem(new Apple());
			self::registerItem(new Bow());
			self::registerItem(new Arrow());
			self::registerItem(new Coal());
			self::registerItem(new Item(Item::DIAMOND, 0, "Diamond"));
			self::registerItem(new Item(Item::IRON_INGOT, 0, "Iron Ingot"));
			self::registerItem(new Item(Item::GOLD_INGOT, 0, "Gold Ingot"));
			self::registerItem(new Sword(Item::IRON_SWORD, 0, "Iron Sword", TieredTool::TIER_IRON));
			self::registerItem(new Sword(Item::WOODEN_SWORD, 0, "Wooden Sword", TieredTool::TIER_WOODEN));
			self::registerItem(new Shovel(Item::WOODEN_SHOVEL, 0, "Wooden Shovel", TieredTool::TIER_WOODEN));
			self::registerItem(new Pickaxe(Item::WOODEN_PICKAXE, 0, "Wooden Pickaxe", TieredTool::TIER_WOODEN));
			self::registerItem(new Axe(Item::WOODEN_AXE, 0, "Wooden Axe", TieredTool::TIER_WOODEN));
			self::registerItem(new Sword(Item::STONE_SWORD, 0, "Stone Sword", TieredTool::TIER_STONE));
			self::registerItem(new Shovel(Item::STONE_SHOVEL, 0, "Stone Shovel", TieredTool::TIER_STONE));
			self::registerItem(new Pickaxe(Item::STONE_PICKAXE, 0, "Stone Pickaxe", TieredTool::TIER_STONE));
			self::registerItem(new Axe(Item::STONE_AXE, 0, "Stone Axe", TieredTool::TIER_STONE));
			self::registerItem(new Sword(Item::DIAMOND_SWORD, 0, "Diamond Sword", TieredTool::TIER_DIAMOND));
			self::registerItem(new Shovel(Item::DIAMOND_SHOVEL, 0, "Diamond Shovel", TieredTool::TIER_DIAMOND));
			self::registerItem(new Pickaxe(Item::DIAMOND_PICKAXE, 0, "Diamond Pickaxe", TieredTool::TIER_DIAMOND));
			self::registerItem(new Axe(Item::DIAMOND_AXE, 0, "Diamond Axe", TieredTool::TIER_DIAMOND));
			self::registerItem(new Stick());
			self::registerItem(new Bowl());
			self::registerItem(new MushroomStew());
			self::registerItem(new Sword(Item::GOLDEN_SWORD, 0, "Gold Sword", TieredTool::TIER_GOLD));
			self::registerItem(new Shovel(Item::GOLDEN_SHOVEL, 0, "Gold Shovel", TieredTool::TIER_GOLD));
			self::registerItem(new Pickaxe(Item::GOLDEN_PICKAXE, 0, "Gold Pickaxe", TieredTool::TIER_GOLD));
			self::registerItem(new Axe(Item::GOLDEN_AXE, 0, "Gold Axe", TieredTool::TIER_GOLD));
			self::registerItem(new StringItem());
			self::registerItem(new Item(Item::FEATHER, 0, "Feather"));
			self::registerItem(new Item(Item::GUNPOWDER, 0, "Gunpowder"));
			self::registerItem(new Hoe(Item::WOODEN_HOE, 0, "Wooden Hoe", TieredTool::TIER_WOODEN));
			self::registerItem(new Hoe(Item::STONE_HOE, 0, "Stone Hoe", TieredTool::TIER_STONE));
			self::registerItem(new Hoe(Item::IRON_HOE, 0, "Iron Hoe", TieredTool::TIER_IRON));
			self::registerItem(new Hoe(Item::DIAMOND_HOE, 0, "Diamond Hoe", TieredTool::TIER_DIAMOND));
			self::registerItem(new Hoe(Item::GOLDEN_HOE, 0, "Golden Hoe", TieredTool::TIER_GOLD));
			self::registerItem(new WheatSeeds());
			self::registerItem(new Item(Item::WHEAT, 0, "Wheat"));
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
			self::registerItem(new Item(Item::FLINT, 0, "Flint"));
			self::registerItem(new RawPorkchop());
			self::registerItem(new CookedPorkchop());
			self::registerItem(new Painting());
			self::registerItem(new GoldenApple());
			self::registerItem(new Sign());
			self::registerItem(new ItemBlock(Block::OAK_DOOR_BLOCK, 0, Item::OAK_DOOR));
			self::registerItem(new Bucket());

			self::registerItem(new Minecart());
			//TODO: SADDLE
			self::registerItem(new ItemBlock(Block::IRON_DOOR_BLOCK, 0, Item::IRON_DOOR));
			self::registerItem(new Redstone());
			self::registerItem(new Snowball());
			self::registerItem(new Boat());
			self::registerItem(new Item(Item::LEATHER, 0, "Leather"));

			self::registerItem(new Item(Item::BRICK, 0, "Brick"));
			self::registerItem(new Item(Item::CLAY_BALL, 0, "Clay"));
			self::registerItem(new ItemBlock(Block::SUGARCANE_BLOCK, 0, Item::SUGARCANE));
			self::registerItem(new Item(Item::PAPER, 0, "Paper"));
			self::registerItem(new Book());
			self::registerItem(new Item(Item::SLIME_BALL, 0, "Slimeball"));
			//TODO: CHEST_MINECART

			self::registerItem(new Egg());
			self::registerItem(new Compass());
			self::registerItem(new FishingRod());
			self::registerItem(new Clock());
			self::registerItem(new Item(Item::GLOWSTONE_DUST, 0, "Glowstone Dust"));
			self::registerItem(new Fish());
			self::registerItem(new CookedFish());
			self::registerItem(new Dye());
			self::registerItem(new Item(Item::BONE, 0, "Bone"));
			self::registerItem(new Item(Item::SUGAR, 0, "Sugar"));
			self::registerItem(new ItemBlock(Block::CAKE_BLOCK, 0, Item::CAKE));
			self::registerItem(new Bed());
			self::registerItem(new ItemBlock(Block::REPEATER_BLOCK, 0, Item::REPEATER));
			self::registerItem(new Cookie());
			//TODO: FILLED_MAP
			self::registerItem(new Shears());
			self::registerItem(new Melon());
			self::registerItem(new PumpkinSeeds());
			self::registerItem(new MelonSeeds());
			self::registerItem(new RawBeef());
			self::registerItem(new Steak());
			self::registerItem(new RawChicken());
			self::registerItem(new CookedChicken());
			self::registerItem(new RottenFlesh());
			//TODO: ENDER_PEARL
			self::registerItem(new BlazeRod());
			self::registerItem(new Item(Item::GHAST_TEAR, 0, "Ghast Tear"));
			self::registerItem(new Item(Item::GOLD_NUGGET, 0, "Gold Nugget"));
			self::registerItem(new ItemBlock(Block::NETHER_WART_PLANT, 0, Item::NETHER_WART));
			self::registerItem(new Potion());
			self::registerItem(new GlassBottle());
			self::registerItem(new SpiderEye());
			self::registerItem(new Item(Item::FERMENTED_SPIDER_EYE, 0, "Fermented Spider Eye"));
			self::registerItem(new Item(Item::BLAZE_POWDER, 0, "Blaze Powder"));
			self::registerItem(new Item(Item::MAGMA_CREAM, 0, "Magma Cream"));
			self::registerItem(new ItemBlock(Block::BREWING_STAND_BLOCK, 0, Item::BREWING_STAND));
			self::registerItem(new ItemBlock(Block::CAULDRON_BLOCK, 0, Item::CAULDRON));
			//TODO: ENDER_EYE
			self::registerItem(new Item(Item::GLISTERING_MELON, 0, "Glistering Melon"));
			self::registerItem(new SpawnEgg());
			//TODO: BOTTLE_O_ENCHANTING
			//TODO: FIREBALL
			self::registerItem(new WritableBook());
			self::registerItem(new WrittenBook());
			self::registerItem(new Item(Item::EMERALD, 0, "Emerald"));
			self::registerItem(new ItemBlock(Block::ITEM_FRAME_BLOCK, 0, Item::ITEM_FRAME));
			self::registerItem(new ItemBlock(Block::FLOWER_POT_BLOCK, 0, Item::FLOWER_POT));
			self::registerItem(new Carrot());
			self::registerItem(new Potato());
			self::registerItem(new BakedPotato());
			//TODO: POISONOUS_POTATO
			//TODO: EMPTYMAP
			self::registerItem(new GoldenCarrot());
			self::registerItem(new ItemBlock(Block::SKULL_BLOCK, 0, Item::SKULL));
			//TODO: CARROTONASTICK
			self::registerItem(new Item(Item::NETHER_STAR, 0, "Nether Star"));
			self::registerItem(new PumpkinPie());
			//TODO: FIREWORKS
			//TODO: FIREWORKSCHARGE
			//TODO: ENCHANTED_BOOK
			self::registerItem(new ItemBlock(Block::COMPARATOR_BLOCK, 0, Item::COMPARATOR));
			self::registerItem(new Item(Item::NETHER_BRICK, 0, "Nether Brick"));
			self::registerItem(new Item(Item::NETHER_QUARTZ, 0, "Nether Quartz"));
			//TODO: MINECART_WITH_TNT
			//TODO: HOPPER_MINECART
			self::registerItem(new Item(Item::PRISMARINE_SHARD, 0, "Prismarine Shard"));
			self::registerItem(new ItemBlock(Block::HOPPER_BLOCK, 0, Item::HOPPER));
			//TODO: RABBIT
			self::registerItem(new CookedRabbit());
			//TODO: RABBIT_STEW
			self::registerItem(new Item(Item::RABBIT_FOOT, 0, "Rabbit's Foot"));
			self::registerItem(new Item(Item::RABBIT_HIDE, 0, "Rabbit Hide"));
			//TODO: HORSEARMORLEATHER
			//TODO: HORSEARMORIRON
			//TODO: GOLD_HORSE_ARMOR
			//TODO: DIAMOND_HORSE_ARMOR
			//TODO: LEAD
			//TODO: NAMETAG
			self::registerItem(new Item(Item::PRISMARINE_CRYSTALS, 0, "Prismarine Crystals"));
			//TODO: MUTTONRAW
			//TODO: COOKED_MUTTON
			//TODO: ARMOR_STAND
			//TODO: END_CRYSTAL
			self::registerItem(new ItemBlock(Block::SPRUCE_DOOR_BLOCK, 0, Item::SPRUCE_DOOR));
			self::registerItem(new ItemBlock(Block::BIRCH_DOOR_BLOCK, 0, Item::BIRCH_DOOR));
			self::registerItem(new ItemBlock(Block::JUNGLE_DOOR_BLOCK, 0, Item::JUNGLE_DOOR));
			self::registerItem(new ItemBlock(Block::ACACIA_DOOR_BLOCK, 0, Item::ACACIA_DOOR));
			self::registerItem(new ItemBlock(Block::DARK_OAK_DOOR_BLOCK, 0, Item::DARK_OAK_DOOR));
			self::registerItem(new ChorusFruit());
			self::registerItem(new Item(Item::CHORUS_FRUIT_POPPED, 0, "Popped Chorus Fruit"));

			//TODO: DRAGON_BREATH
			//TODO: SPLASH_POTION

			//TODO: LINGERING_POTION

			//TODO: COMMAND_BLOCK_MINECART
			//TODO: ELYTRA
			self::registerItem(new Item(Item::SHULKER_SHELL, 0, "Shulker Shell"));
			self::registerItem(new Banner());

			//TODO: TOTEM

			self::registerItem(new Item(Item::IRON_NUGGET, 0, "Iron Nugget"));

			self::registerItem(new Beetroot());
			self::registerItem(new BeetrootSeeds());
			self::registerItem(new BeetrootSoup());
			//TODO: RAW_SALMON
			//TODO: CLOWNFISH
			//TODO: PUFFERFISH
			//TODO: COOKED_SALMON

			self::registerItem(new GoldenAppleEnchanted());

			//TODO: RECORD_13
			//TODO: RECORD_CAT
			//TODO: RECORD_BLOCKS
			//TODO: RECORD_CHIRP
			//TODO: RECORD_FAR
			//TODO: RECORD_MALL
			//TODO: RECORD_MELLOHI
			//TODO: RECORD_STAL
			//TODO: RECORD_STRAD
			//TODO: RECORD_WARD
			//TODO: RECORD_11
			//TODO: RECORD_WAIT
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
		if(!$override and self::isRegistered($id)){
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
	 * @throws \TypeError
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, $tags = "") : Item{
		if(!is_string($tags) and !($tags instanceof CompoundTag)){
			throw new \TypeError("`tags` argument must be a string or CompoundTag instance, " . (is_object($tags) ? "instance of " . get_class($tags) : gettype($tags)) . " given");
		}

		$item = null;
		try{
			if($id < 256){
				/* Blocks must have a damage value 0-15, but items can have damage value -1 to indicate that they are
				 * crafting ingredients with any-damage. */
				$item = new ItemBlock($id, $meta);
			}else{
				/** @var Item|null $listed */
				$listed = self::$list[$id];
				if($listed !== null){
					$item = clone $listed;
				}
			}
		}catch(\RuntimeException $e){
			throw new \InvalidArgumentException("Item ID $id is invalid or out of bounds");
		}

		$item = ($item ?? new Item($id, $meta));

		$item->setDamage($meta);
		$item->setCount($count);
		$item->setCompoundTag($tags);
		return $item;
	}

	/**
	 * Tries to parse the specified string into Item ID/meta identifiers, and returns Item instances it created.
	 *
	 * Example accepted formats:
	 * - `diamond_pickaxe:5`
	 * - `minecraft:string`
	 * - `351:4 (lapis lazuli ID:meta)`
	 *
	 * If multiple item instances are to be created, their identifiers must be comma-separated, for example:
	 * `diamond_pickaxe,wooden_shovel:18,iron_ingot`
	 *
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

			if(defined(ItemIds::class . "::" . strtoupper($b[0]))){
				$item = self::get(constant(ItemIds::class . "::" . strtoupper($b[0])), $meta);
			}elseif(is_numeric($b[0])){
				$item = self::get(((int) $b[0]) & 0xFFFF, $meta);
			}else{
				$item = self::get(Item::AIR, 0, 0);
			}

			return $item;
		}
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function isRegistered(int $id) : bool{
		if($id < 256){
			return BlockFactory::isRegistered($id);
		}
		return self::$list[$id] !== null;
	}
}
