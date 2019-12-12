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
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\SkullType;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Living;
use pocketmine\inventory\ArmorInventory;
use pocketmine\nbt\tag\CompoundTag;
use function constant;
use function defined;
use function explode;
use function is_a;
use function is_numeric;
use function str_replace;
use function strtoupper;
use function trim;

/**
 * Manages Item instance creation and registration
 */
class ItemFactory{

	/** @var Item[] */
	private static $list = [];

	/** @var Item|null */
	private static $air = null;

	public static function init() : void{
		self::$list = []; //in case of re-initializing

		self::registerArmorItems();
		self::registerTierToolItems();

		self::register(new Apple(ItemIds::APPLE, 0, "Apple"));
		self::register(new Arrow(ItemIds::ARROW, 0, "Arrow"));

		self::register(new BakedPotato(ItemIds::BAKED_POTATO, 0, "Baked Potato"));
		self::register(new Beetroot(ItemIds::BEETROOT, 0, "Beetroot"));
		self::register(new BeetrootSeeds(ItemIds::BEETROOT_SEEDS, 0, "Beetroot Seeds"));
		self::register(new BeetrootSoup(ItemIds::BEETROOT_SOUP, 0, "Beetroot Soup"));
		self::register(new BlazeRod(ItemIds::BLAZE_ROD, 0, "Blaze Rod"));
		self::register(new Book(ItemIds::BOOK, 0, "Book"));
		self::register(new Bow(ItemIds::BOW, 0, "Bow"));
		self::register(new Bowl(ItemIds::BOWL, 0, "Bowl"));
		self::register(new Bread(ItemIds::BREAD, 0, "Bread"));
		self::register(new Bucket(ItemIds::BUCKET, 0, "Bucket"));
		self::register(new Carrot(ItemIds::CARROT, 0, "Carrot"));
		self::register(new ChorusFruit(ItemIds::CHORUS_FRUIT, 0, "Chorus Fruit"));
		self::register(new Clock(ItemIds::CLOCK, 0, "Clock"));
		self::register(new Clownfish(ItemIds::CLOWNFISH, 0, "Clownfish"));
		self::register(new Coal(ItemIds::COAL, 0, "Coal"));
		self::register(new Coal(ItemIds::COAL, 1, "Charcoal"));
		self::register(new CocoaBeans(ItemIds::DYE, 3, "Cocoa Beans"));
		self::register(new Compass(ItemIds::COMPASS, 0, "Compass"));
		self::register(new CookedChicken(ItemIds::COOKED_CHICKEN, 0, "Cooked Chicken"));
		self::register(new CookedFish(ItemIds::COOKED_FISH, 0, "Cooked Fish"));
		self::register(new CookedMutton(ItemIds::COOKED_MUTTON, 0, "Cooked Mutton"));
		self::register(new CookedPorkchop(ItemIds::COOKED_PORKCHOP, 0, "Cooked Porkchop"));
		self::register(new CookedRabbit(ItemIds::COOKED_RABBIT, 0, "Cooked Rabbit"));
		self::register(new CookedSalmon(ItemIds::COOKED_SALMON, 0, "Cooked Salmon"));
		self::register(new Cookie(ItemIds::COOKIE, 0, "Cookie"));
		self::register(new DriedKelp(ItemIds::DRIED_KELP, 0, "Dried Kelp"));
		self::register(new Egg(ItemIds::EGG, 0, "Egg"));
		self::register(new EnderPearl(ItemIds::ENDER_PEARL, 0, "Ender Pearl"));
		self::register(new ExperienceBottle(ItemIds::EXPERIENCE_BOTTLE, 0, "Bottle o' Enchanting"));
		self::register(new Fertilizer(ItemIds::DYE, 15, "Bone Meal"));
		self::register(new FishingRod(ItemIds::FISHING_ROD, 0, "Fishing Rod"));
		self::register(new FlintSteel(ItemIds::FLINT_STEEL, 0, "Flint and Steel"));
		self::register(new GlassBottle(ItemIds::GLASS_BOTTLE, 0, "Glass Bottle"));
		self::register(new GoldenApple(ItemIds::GOLDEN_APPLE, 0, "Golden Apple"));
		self::register(new GoldenAppleEnchanted(ItemIds::ENCHANTED_GOLDEN_APPLE, 0, "Enchanted Golden Apple"));
		self::register(new GoldenCarrot(ItemIds::GOLDEN_CARROT, 0, "Golden Carrot"));
		self::register(new Item(ItemIds::BLAZE_POWDER, 0, "Blaze Powder"));
		self::register(new Item(ItemIds::BLEACH, 0, "Bleach")); //EDU
		self::register(new Item(ItemIds::BONE, 0, "Bone"));
		self::register(new Item(ItemIds::BRICK, 0, "Brick"));
		self::register(new Item(ItemIds::CHORUS_FRUIT_POPPED, 0, "Popped Chorus Fruit"));
		self::register(new Item(ItemIds::CLAY_BALL, 0, "Clay"));
		self::register(new Item(ItemIds::COMPOUND, 0, "Salt"));
		self::register(new Item(ItemIds::COMPOUND, 1, "Sodium Oxide"));
		self::register(new Item(ItemIds::COMPOUND, 2, "Sodium Hydroxide"));
		self::register(new Item(ItemIds::COMPOUND, 3, "Magnesium Nitrate"));
		self::register(new Item(ItemIds::COMPOUND, 4, "Iron Sulphide"));
		self::register(new Item(ItemIds::COMPOUND, 5, "Lithium Hydride"));
		self::register(new Item(ItemIds::COMPOUND, 6, "Sodium Hydride"));
		self::register(new Item(ItemIds::COMPOUND, 7, "Calcium Bromide"));
		self::register(new Item(ItemIds::COMPOUND, 8, "Magnesium Oxide"));
		self::register(new Item(ItemIds::COMPOUND, 9, "Sodium Acetate"));
		self::register(new Item(ItemIds::COMPOUND, 10, "Luminol"));
		self::register(new Item(ItemIds::COMPOUND, 11, "Charcoal")); //??? maybe bug
		self::register(new Item(ItemIds::COMPOUND, 12, "Sugar")); //??? maybe bug
		self::register(new Item(ItemIds::COMPOUND, 13, "Aluminium Oxide"));
		self::register(new Item(ItemIds::COMPOUND, 14, "Boron Trioxide"));
		self::register(new Item(ItemIds::COMPOUND, 15, "Soap"));
		self::register(new Item(ItemIds::COMPOUND, 16, "Polyethylene"));
		self::register(new Item(ItemIds::COMPOUND, 17, "Rubbish"));
		self::register(new Item(ItemIds::COMPOUND, 18, "Magnesium Salts"));
		self::register(new Item(ItemIds::COMPOUND, 19, "Sulphate"));
		self::register(new Item(ItemIds::COMPOUND, 20, "Barium Sulphate"));
		self::register(new Item(ItemIds::COMPOUND, 21, "Potassium Chloride"));
		self::register(new Item(ItemIds::COMPOUND, 22, "Mercuric Chloride"));
		self::register(new Item(ItemIds::COMPOUND, 23, "Cerium Chloride"));
		self::register(new Item(ItemIds::COMPOUND, 24, "Tungsten Chloride"));
		self::register(new Item(ItemIds::COMPOUND, 25, "Calcium Chloride"));
		self::register(new Item(ItemIds::COMPOUND, 26, "Water")); //???
		self::register(new Item(ItemIds::COMPOUND, 27, "Glue"));
		self::register(new Item(ItemIds::COMPOUND, 28, "Hypochlorite"));
		self::register(new Item(ItemIds::COMPOUND, 29, "Crude Oil"));
		self::register(new Item(ItemIds::COMPOUND, 30, "Latex"));
		self::register(new Item(ItemIds::COMPOUND, 31, "Potassium Iodide"));
		self::register(new Item(ItemIds::COMPOUND, 32, "Sodium Fluoride"));
		self::register(new Item(ItemIds::COMPOUND, 33, "Benzene"));
		self::register(new Item(ItemIds::COMPOUND, 34, "Ink"));
		self::register(new Item(ItemIds::COMPOUND, 35, "Hydrogen Peroxide"));
		self::register(new Item(ItemIds::COMPOUND, 36, "Ammonia"));
		self::register(new Item(ItemIds::COMPOUND, 37, "Sodium Hypochlorite"));
		self::register(new Item(ItemIds::DIAMOND, 0, "Diamond"));
		self::register(new Item(ItemIds::DRAGON_BREATH, 0, "Dragon's Breath"));
		self::register(new Item(ItemIds::DYE, 0, "Ink Sac"));
		self::register(new Item(ItemIds::DYE, 4, "Lapis Lazuli"));
		self::register(new Item(ItemIds::EMERALD, 0, "Emerald"));
		self::register(new Item(ItemIds::FEATHER, 0, "Feather"));
		self::register(new Item(ItemIds::FERMENTED_SPIDER_EYE, 0, "Fermented Spider Eye"));
		self::register(new Item(ItemIds::FLINT, 0, "Flint"));
		self::register(new Item(ItemIds::GHAST_TEAR, 0, "Ghast Tear"));
		self::register(new Item(ItemIds::GLISTERING_MELON, 0, "Glistering Melon"));
		self::register(new Item(ItemIds::GLOWSTONE_DUST, 0, "Glowstone Dust"));
		self::register(new Item(ItemIds::GOLD_INGOT, 0, "Gold Ingot"));
		self::register(new Item(ItemIds::GOLD_NUGGET, 0, "Gold Nugget"));
		self::register(new Item(ItemIds::GUNPOWDER, 0, "Gunpowder"));
		self::register(new Item(ItemIds::HEART_OF_THE_SEA, 0, "Heart of the Sea"));
		self::register(new Item(ItemIds::IRON_INGOT, 0, "Iron Ingot"));
		self::register(new Item(ItemIds::IRON_NUGGET, 0, "Iron Nugget"));
		self::register(new Item(ItemIds::LEATHER, 0, "Leather"));
		self::register(new Item(ItemIds::MAGMA_CREAM, 0, "Magma Cream"));
		self::register(new Item(ItemIds::NAUTILUS_SHELL, 0, "Nautilus Shell"));
		self::register(new Item(ItemIds::NETHER_BRICK, 0, "Nether Brick"));
		self::register(new Item(ItemIds::NETHER_QUARTZ, 0, "Nether Quartz"));
		self::register(new Item(ItemIds::NETHER_STAR, 0, "Nether Star"));
		self::register(new Item(ItemIds::PAPER, 0, "Paper"));
		self::register(new Item(ItemIds::PRISMARINE_CRYSTALS, 0, "Prismarine Crystals"));
		self::register(new Item(ItemIds::PRISMARINE_SHARD, 0, "Prismarine Shard"));
		self::register(new Item(ItemIds::RABBIT_FOOT, 0, "Rabbit's Foot"));
		self::register(new Item(ItemIds::RABBIT_HIDE, 0, "Rabbit Hide"));
		self::register(new Item(ItemIds::SHULKER_SHELL, 0, "Shulker Shell"));
		self::register(new Item(ItemIds::SLIME_BALL, 0, "Slimeball"));
		self::register(new Item(ItemIds::SUGAR, 0, "Sugar"));
		self::register(new Item(ItemIds::TURTLE_SHELL_PIECE, 0, "Scute"));
		self::register(new Item(ItemIds::WHEAT, 0, "Wheat"));
		self::register(new ItemBlock(BlockLegacyIds::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::BREWING_STAND_BLOCK, 0, ItemIds::BREWING_STAND));
		self::register(new ItemBlock(BlockLegacyIds::CAKE_BLOCK, 0, ItemIds::CAKE));
		self::register(new ItemBlock(BlockLegacyIds::CAULDRON_BLOCK, 0, ItemIds::CAULDRON));
		self::register(new ItemBlock(BlockLegacyIds::COMPARATOR_BLOCK, 0, ItemIds::COMPARATOR));
		self::register(new ItemBlock(BlockLegacyIds::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::FLOWER_POT_BLOCK, 0, ItemIds::FLOWER_POT));
		self::register(new ItemBlock(BlockLegacyIds::HOPPER_BLOCK, 0, ItemIds::HOPPER));
		self::register(new ItemBlock(BlockLegacyIds::IRON_DOOR_BLOCK, 0, ItemIds::IRON_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::ITEM_FRAME_BLOCK, 0, ItemIds::ITEM_FRAME));
		self::register(new ItemBlock(BlockLegacyIds::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::NETHER_WART_PLANT, 0, ItemIds::NETHER_WART));
		self::register(new ItemBlock(BlockLegacyIds::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::REPEATER_BLOCK, 0, ItemIds::REPEATER));
		self::register(new ItemBlock(BlockLegacyIds::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR));
		self::register(new ItemBlock(BlockLegacyIds::SUGARCANE_BLOCK, 0, ItemIds::SUGARCANE));
		//TODO: fix metadata for buckets with still liquid in them
		//the meta values are intentionally hardcoded because block IDs will change in the future
		self::register(new LiquidBucket(ItemIds::BUCKET, 8, "Water Bucket", VanillaBlocks::WATER()));
		self::register(new LiquidBucket(ItemIds::BUCKET, 10, "Lava Bucket", VanillaBlocks::LAVA()));
		self::register(new Melon(ItemIds::MELON, 0, "Melon"));
		self::register(new MelonSeeds(ItemIds::MELON_SEEDS, 0, "Melon Seeds"));
		self::register(new MilkBucket(ItemIds::BUCKET, 1, "Milk Bucket"));
		self::register(new Minecart(ItemIds::MINECART, 0, "Minecart"));
		self::register(new MushroomStew(ItemIds::MUSHROOM_STEW, 0, "Mushroom Stew"));
		self::register(new PaintingItem(ItemIds::PAINTING, 0, "Painting"));
		self::register(new PoisonousPotato(ItemIds::POISONOUS_POTATO, 0, "Poisonous Potato"));
		self::register(new Potato(ItemIds::POTATO, 0, "Potato"));
		self::register(new Pufferfish(ItemIds::PUFFERFISH, 0, "Pufferfish"));
		self::register(new PumpkinPie(ItemIds::PUMPKIN_PIE, 0, "Pumpkin Pie"));
		self::register(new PumpkinSeeds(ItemIds::PUMPKIN_SEEDS, 0, "Pumpkin Seeds"));
		self::register(new RabbitStew(ItemIds::RABBIT_STEW, 0, "Rabbit Stew"));
		self::register(new RawBeef(ItemIds::RAW_BEEF, 0, "Raw Beef"));
		self::register(new RawChicken(ItemIds::RAW_CHICKEN, 0, "Raw Chicken"));
		self::register(new RawFish(ItemIds::RAW_FISH, 0, "Raw Fish"));
		self::register(new RawMutton(ItemIds::RAW_MUTTON, 0, "Raw Mutton"));
		self::register(new RawPorkchop(ItemIds::RAW_PORKCHOP, 0, "Raw Porkchop"));
		self::register(new RawRabbit(ItemIds::RAW_RABBIT, 0, "Raw Rabbit"));
		self::register(new RawSalmon(ItemIds::RAW_SALMON, 0, "Raw Salmon"));
		self::register(new Redstone(ItemIds::REDSTONE, 0, "Redstone"));
		self::register(new RottenFlesh(ItemIds::ROTTEN_FLESH, 0, "Rotten Flesh"));
		self::register(new Shears(ItemIds::SHEARS, 0, "Shears"));
		self::register(new Sign(BlockLegacyIds::STANDING_SIGN, 0, ItemIds::SIGN));
		self::register(new Sign(BlockLegacyIds::SPRUCE_STANDING_SIGN, 0, ItemIds::SPRUCE_SIGN));
		self::register(new Sign(BlockLegacyIds::BIRCH_STANDING_SIGN, 0, ItemIds::BIRCH_SIGN));
		self::register(new Sign(BlockLegacyIds::JUNGLE_STANDING_SIGN, 0, ItemIds::JUNGLE_SIGN));
		self::register(new Sign(BlockLegacyIds::ACACIA_STANDING_SIGN, 0, ItemIds::ACACIA_SIGN));
		self::register(new Sign(BlockLegacyIds::DARKOAK_STANDING_SIGN, 0, ItemIds::DARKOAK_SIGN));
		self::register(new Snowball(ItemIds::SNOWBALL, 0, "Snowball"));
		self::register(new SpiderEye(ItemIds::SPIDER_EYE, 0, "Spider Eye"));
		self::register(new Steak(ItemIds::STEAK, 0, "Steak"));
		self::register(new Stick(ItemIds::STICK, 0, "Stick"));
		self::register(new StringItem(ItemIds::STRING, 0, "String"));
		self::register(new Totem(ItemIds::TOTEM, 0, "Totem of Undying"));
		self::register(new WheatSeeds(ItemIds::WHEAT_SEEDS, 0, "Wheat Seeds"));
		self::register(new WritableBook(ItemIds::WRITABLE_BOOK, 0, "Book & Quill"));
		self::register(new WrittenBook(ItemIds::WRITTEN_BOOK, 0, "Written Book"));

		foreach(SkullType::getAll() as $skullType){
			self::register(new Skull(ItemIds::SKULL, $skullType->getMagicNumber(), $skullType->getDisplayName(), $skullType));
		}

		$dyeMap = [
			DyeColor::BLACK()->id() => 16,
			DyeColor::BROWN()->id() => 17,
			DyeColor::BLUE()->id() => 18,
			DyeColor::WHITE()->id() => 19
		];
		foreach(DyeColor::getAll() as $color){
			//TODO: use colour object directly
			//TODO: add interface to dye-colour objects
			self::register(new Dye(ItemIds::DYE, $dyeMap[$color->id()] ?? $color->getInvertedMagicNumber(), $color->getDisplayName() . " Dye", $color));
			self::register(new Bed(ItemIds::BED, $color->getMagicNumber(), $color->getDisplayName() . " Bed", $color));
			self::register(new Banner(ItemIds::BANNER, $color->getInvertedMagicNumber(), $color->getDisplayName() . " Banner", $color));
		}

		foreach(Potion::ALL as $type){
			self::register(new Potion(ItemIds::POTION, $type, "Potion"));
			self::register(new SplashPotion(ItemIds::SPLASH_POTION, $type, "Splash Potion"));
		}

		foreach(EntityFactory::getKnownTypes() as $className){
			/** @var Living|string $className */
			if(is_a($className, Living::class, true) and $className::NETWORK_ID !== -1){
				self::register(new SpawnEgg(ItemIds::SPAWN_EGG, $className::NETWORK_ID, "Spawn Egg", $className));
			}
		}

		foreach(TreeType::getAll() as $type){
			self::register(new Boat(ItemIds::BOAT, $type->getMagicNumber(), $type->getDisplayName() . " Boat", $type));
		}

		//region --- auto-generated TODOs ---
		//TODO: minecraft:armor_stand
		//TODO: minecraft:balloon
		//TODO: minecraft:banner_pattern
		//TODO: minecraft:campfire
		//TODO: minecraft:carrotOnAStick
		//TODO: minecraft:chest_minecart
		//TODO: minecraft:command_block_minecart
		//TODO: minecraft:crossbow
		//TODO: minecraft:elytra
		//TODO: minecraft:emptyMap
		//TODO: minecraft:enchanted_book
		//TODO: minecraft:end_crystal
		//TODO: minecraft:ender_eye
		//TODO: minecraft:fireball
		//TODO: minecraft:fireworks
		//TODO: minecraft:fireworksCharge
		//TODO: minecraft:glow_stick
		//TODO: minecraft:hopper_minecart
		//TODO: minecraft:horsearmordiamond
		//TODO: minecraft:horsearmorgold
		//TODO: minecraft:horsearmoriron
		//TODO: minecraft:horsearmorleather
		//TODO: minecraft:ice_bomb
		//TODO: minecraft:kelp
		//TODO: minecraft:lead
		//TODO: minecraft:lingering_potion
		//TODO: minecraft:map
		//TODO: minecraft:medicine
		//TODO: minecraft:name_tag
		//TODO: minecraft:phantom_membrane
		//TODO: minecraft:rapid_fertilizer
		//TODO: minecraft:record_11
		//TODO: minecraft:record_13
		//TODO: minecraft:record_blocks
		//TODO: minecraft:record_cat
		//TODO: minecraft:record_chirp
		//TODO: minecraft:record_far
		//TODO: minecraft:record_mall
		//TODO: minecraft:record_mellohi
		//TODO: minecraft:record_stal
		//TODO: minecraft:record_strad
		//TODO: minecraft:record_wait
		//TODO: minecraft:record_ward
		//TODO: minecraft:saddle
		//TODO: minecraft:shield
		//TODO: minecraft:sparkler
		//TODO: minecraft:spawn_egg
		//TODO: minecraft:sweet_berries
		//TODO: minecraft:tnt_minecart
		//TODO: minecraft:trident
		//TODO: minecraft:turtle_helmet
		//endregion
	}

	private static function registerTierToolItems() : void{
		self::register(new Axe(ItemIds::DIAMOND_AXE, "Diamond Axe", ToolTier::DIAMOND()));
		self::register(new Axe(ItemIds::GOLDEN_AXE, "Golden Axe", ToolTier::GOLD()));
		self::register(new Axe(ItemIds::IRON_AXE, "Iron Axe", ToolTier::IRON()));
		self::register(new Axe(ItemIds::STONE_AXE, "Stone Axe", ToolTier::STONE()));
		self::register(new Axe(ItemIds::WOODEN_AXE, "Wooden Axe", ToolTier::WOOD()));
		self::register(new Hoe(ItemIds::DIAMOND_HOE, "Diamond Hoe", ToolTier::DIAMOND()));
		self::register(new Hoe(ItemIds::GOLDEN_HOE, "Golden Hoe", ToolTier::GOLD()));
		self::register(new Hoe(ItemIds::IRON_HOE, "Iron Hoe", ToolTier::IRON()));
		self::register(new Hoe(ItemIds::STONE_HOE, "Stone Hoe", ToolTier::STONE()));
		self::register(new Hoe(ItemIds::WOODEN_HOE, "Wooden Hoe", ToolTier::WOOD()));
		self::register(new Pickaxe(ItemIds::DIAMOND_PICKAXE, "Diamond Pickaxe", ToolTier::DIAMOND()));
		self::register(new Pickaxe(ItemIds::GOLDEN_PICKAXE, "Golden Pickaxe", ToolTier::GOLD()));
		self::register(new Pickaxe(ItemIds::IRON_PICKAXE, "Iron Pickaxe", ToolTier::IRON()));
		self::register(new Pickaxe(ItemIds::STONE_PICKAXE, "Stone Pickaxe", ToolTier::STONE()));
		self::register(new Pickaxe(ItemIds::WOODEN_PICKAXE, "Wooden Pickaxe", ToolTier::WOOD()));
		self::register(new Shovel(ItemIds::DIAMOND_SHOVEL, "Diamond Shovel", ToolTier::DIAMOND()));
		self::register(new Shovel(ItemIds::GOLDEN_SHOVEL, "Golden Shovel", ToolTier::GOLD()));
		self::register(new Shovel(ItemIds::IRON_SHOVEL, "Iron Shovel", ToolTier::IRON()));
		self::register(new Shovel(ItemIds::STONE_SHOVEL, "Stone Shovel", ToolTier::STONE()));
		self::register(new Shovel(ItemIds::WOODEN_SHOVEL, "Wooden Shovel", ToolTier::WOOD()));
		self::register(new Sword(ItemIds::DIAMOND_SWORD, "Diamond Sword", ToolTier::DIAMOND()));
		self::register(new Sword(ItemIds::GOLDEN_SWORD, "Golden Sword", ToolTier::GOLD()));
		self::register(new Sword(ItemIds::IRON_SWORD, "Iron Sword", ToolTier::IRON()));
		self::register(new Sword(ItemIds::STONE_SWORD, "Stone Sword", ToolTier::STONE()));
		self::register(new Sword(ItemIds::WOODEN_SWORD, "Wooden Sword", ToolTier::WOOD()));
	}

	private static function registerArmorItems() : void{
		self::register(new Armor(ItemIds::CHAIN_BOOTS, 0, "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET)));
		self::register(new Armor(ItemIds::DIAMOND_BOOTS, 0, "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET)));
		self::register(new Armor(ItemIds::GOLDEN_BOOTS, 0, "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET)));
		self::register(new Armor(ItemIds::IRON_BOOTS, 0, "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET)));
		self::register(new Armor(ItemIds::LEATHER_BOOTS, 0, "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET)));
		self::register(new Armor(ItemIds::CHAIN_CHESTPLATE, 0, "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST)));
		self::register(new Armor(ItemIds::DIAMOND_CHESTPLATE, 0, "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST)));
		self::register(new Armor(ItemIds::GOLDEN_CHESTPLATE, 0, "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST)));
		self::register(new Armor(ItemIds::IRON_CHESTPLATE, 0, "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST)));
		self::register(new Armor(ItemIds::LEATHER_CHESTPLATE, 0, "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST)));
		self::register(new Armor(ItemIds::CHAIN_HELMET, 0, "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		self::register(new Armor(ItemIds::DIAMOND_HELMET, 0, "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD)));
		self::register(new Armor(ItemIds::GOLDEN_HELMET, 0, "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD)));
		self::register(new Armor(ItemIds::IRON_HELMET, 0, "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		self::register(new Armor(ItemIds::LEATHER_HELMET, 0, "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD)));
		self::register(new Armor(ItemIds::CHAIN_LEGGINGS, 0, "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS)));
		self::register(new Armor(ItemIds::DIAMOND_LEGGINGS, 0, "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS)));
		self::register(new Armor(ItemIds::GOLDEN_LEGGINGS, 0, "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS)));
		self::register(new Armor(ItemIds::IRON_LEGGINGS, 0, "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS)));
		self::register(new Armor(ItemIds::LEATHER_LEGGINGS, 0, "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS)));
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
	public static function register(Item $item, bool $override = false) : void{
		$id = $item->getId();
		$variant = $item->getMeta();

		if(!$override and self::isRegistered($id, $variant)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		self::$list[self::getListOffset($id, $variant)] = clone $item;
	}

	/**
	 * Returns an instance of the Item with the specified id, meta, count and NBT.
	 *
	 * @param int              $id
	 * @param int              $meta
	 * @param int              $count
	 * @param CompoundTag|null $tags
	 *
	 * @return Item
	 * @throws \InvalidArgumentException
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, ?CompoundTag $tags = null) : Item{
		/** @var Item|null $item */
		$item = null;
		if($meta !== -1){
			if(isset(self::$list[$offset = self::getListOffset($id, $meta)])){
				$item = clone self::$list[$offset];
			}elseif(isset(self::$list[$zero = self::getListOffset($id, 0)]) and self::$list[$zero] instanceof Durable){
				/** @var Durable $item */
				$item = clone self::$list[$zero];
				$item->setDamage($meta);
			}elseif($id < 256){ //intentionally includes negatives, for extended block IDs
				$item = new ItemBlock($id, $meta);
			}
		}

		if($item === null){
			//negative damage values will fallthru to here, to avoid crazy shit with crafting wildcard hacks
			$item = new Item($id, $meta);
		}

		$item->setCount($count);
		if($tags !== null){
			$item->setNamedTag($tags);
		}
		return $item;
	}

	/**
	 * Tries to parse the specified string into Item types.
	 *
	 * Example accepted formats:
	 * - `diamond_pickaxe:5`
	 * - `minecraft:string`
	 * - `351:4 (lapis lazuli ID:meta)`
	 *
	 * @param string $str
	 *
	 * @return Item
	 *
	 * @throws \InvalidArgumentException if the given string cannot be parsed as an item identifier
	 */
	public static function fromString(string $str) : Item{
		$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
		if(!isset($b[1])){
			$meta = 0;
		}elseif(is_numeric($b[1])){
			$meta = (int) $b[1];
		}else{
			throw new \InvalidArgumentException("Unable to parse \"" . $b[1] . "\" from \"" . $str . "\" as a valid meta value");
		}

		if(is_numeric($b[0])){
			$item = self::get((int) $b[0], $meta);
		}elseif(defined(ItemIds::class . "::" . strtoupper($b[0]))){
			$item = self::get(constant(ItemIds::class . "::" . strtoupper($b[0])), $meta);
		}else{
			throw new \InvalidArgumentException("Unable to resolve \"" . $str . "\" to a valid item");
		}

		return $item;
	}

	public static function air() : Item{
		return self::$air ?? (self::$air = self::get(ItemIds::AIR, 0, 0));
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 *
	 * @param int $id
	 * @param int $variant
	 *
	 * @return bool
	 */
	public static function isRegistered(int $id, int $variant = 0) : bool{
		if($id < 256){
			return BlockFactory::isRegistered($id);
		}

		return isset(self::$list[self::getListOffset($id, $variant)]);
	}

	private static function getListOffset(int $id, int $variant) : int{
		if($id < -0x8000 or $id > 0x7fff){
			throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		return (($id & 0xffff) << 16) | ($variant & 0xffff);
	}

	/**
	 * @return Item[]
	 */
	public static function getAllRegistered() : array{
		return self::$list;
	}
}
