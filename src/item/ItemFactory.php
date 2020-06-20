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
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\inventory\ArmorInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityLegacyIds;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

/**
 * Manages Item instance creation and registration
 */
class ItemFactory{
	use SingletonTrait;

	/** @var Item[] */
	private $list = [];

	/** @var Item|null */
	private static $air = null;

	public function __construct(){
		$this->registerArmorItems();
		$this->registerSpawnEggs();
		$this->registerTierToolItems();

		$this->register(new Apple(ItemIds::APPLE, 0, "Apple"));
		$this->register(new Arrow(ItemIds::ARROW, 0, "Arrow"));

		$this->register(new BakedPotato(ItemIds::BAKED_POTATO, 0, "Baked Potato"));
		$this->register(new Beetroot(ItemIds::BEETROOT, 0, "Beetroot"));
		$this->register(new BeetrootSeeds(ItemIds::BEETROOT_SEEDS, 0, "Beetroot Seeds"));
		$this->register(new BeetrootSoup(ItemIds::BEETROOT_SOUP, 0, "Beetroot Soup"));
		$this->register(new BlazeRod(ItemIds::BLAZE_ROD, 0, "Blaze Rod"));
		$this->register(new Book(ItemIds::BOOK, 0, "Book"));
		$this->register(new Bow(ItemIds::BOW, 0, "Bow"));
		$this->register(new Bowl(ItemIds::BOWL, 0, "Bowl"));
		$this->register(new Bread(ItemIds::BREAD, 0, "Bread"));
		$this->register(new Bucket(ItemIds::BUCKET, 0, "Bucket"));
		$this->register(new Carrot(ItemIds::CARROT, 0, "Carrot"));
		$this->register(new ChorusFruit(ItemIds::CHORUS_FRUIT, 0, "Chorus Fruit"));
		$this->register(new Clock(ItemIds::CLOCK, 0, "Clock"));
		$this->register(new Clownfish(ItemIds::CLOWNFISH, 0, "Clownfish"));
		$this->register(new Coal(ItemIds::COAL, 0, "Coal"));
		$this->register(new Coal(ItemIds::COAL, 1, "Charcoal"));
		$this->register(new CocoaBeans(ItemIds::DYE, 3, "Cocoa Beans"));
		$this->register(new Compass(ItemIds::COMPASS, 0, "Compass"));
		$this->register(new CookedChicken(ItemIds::COOKED_CHICKEN, 0, "Cooked Chicken"));
		$this->register(new CookedFish(ItemIds::COOKED_FISH, 0, "Cooked Fish"));
		$this->register(new CookedMutton(ItemIds::COOKED_MUTTON, 0, "Cooked Mutton"));
		$this->register(new CookedPorkchop(ItemIds::COOKED_PORKCHOP, 0, "Cooked Porkchop"));
		$this->register(new CookedRabbit(ItemIds::COOKED_RABBIT, 0, "Cooked Rabbit"));
		$this->register(new CookedSalmon(ItemIds::COOKED_SALMON, 0, "Cooked Salmon"));
		$this->register(new Cookie(ItemIds::COOKIE, 0, "Cookie"));
		$this->register(new DriedKelp(ItemIds::DRIED_KELP, 0, "Dried Kelp"));
		$this->register(new Egg(ItemIds::EGG, 0, "Egg"));
		$this->register(new EnderPearl(ItemIds::ENDER_PEARL, 0, "Ender Pearl"));
		$this->register(new ExperienceBottle(ItemIds::EXPERIENCE_BOTTLE, 0, "Bottle o' Enchanting"));
		$this->register(new Fertilizer(ItemIds::DYE, 15, "Bone Meal"));
		$this->register(new FishingRod(ItemIds::FISHING_ROD, 0, "Fishing Rod"));
		$this->register(new FlintSteel(ItemIds::FLINT_STEEL, 0, "Flint and Steel"));
		$this->register(new GlassBottle(ItemIds::GLASS_BOTTLE, 0, "Glass Bottle"));
		$this->register(new GoldenApple(ItemIds::GOLDEN_APPLE, 0, "Golden Apple"));
		$this->register(new GoldenAppleEnchanted(ItemIds::ENCHANTED_GOLDEN_APPLE, 0, "Enchanted Golden Apple"));
		$this->register(new GoldenCarrot(ItemIds::GOLDEN_CARROT, 0, "Golden Carrot"));
		$this->register(new Item(ItemIds::BLAZE_POWDER, 0, "Blaze Powder"));
		$this->register(new Item(ItemIds::BLEACH, 0, "Bleach")); //EDU
		$this->register(new Item(ItemIds::BONE, 0, "Bone"));
		$this->register(new Item(ItemIds::BRICK, 0, "Brick"));
		$this->register(new Item(ItemIds::CHORUS_FRUIT_POPPED, 0, "Popped Chorus Fruit"));
		$this->register(new Item(ItemIds::CLAY_BALL, 0, "Clay"));
		$this->register(new Item(ItemIds::COMPOUND, 0, "Salt"));
		$this->register(new Item(ItemIds::COMPOUND, 1, "Sodium Oxide"));
		$this->register(new Item(ItemIds::COMPOUND, 2, "Sodium Hydroxide"));
		$this->register(new Item(ItemIds::COMPOUND, 3, "Magnesium Nitrate"));
		$this->register(new Item(ItemIds::COMPOUND, 4, "Iron Sulphide"));
		$this->register(new Item(ItemIds::COMPOUND, 5, "Lithium Hydride"));
		$this->register(new Item(ItemIds::COMPOUND, 6, "Sodium Hydride"));
		$this->register(new Item(ItemIds::COMPOUND, 7, "Calcium Bromide"));
		$this->register(new Item(ItemIds::COMPOUND, 8, "Magnesium Oxide"));
		$this->register(new Item(ItemIds::COMPOUND, 9, "Sodium Acetate"));
		$this->register(new Item(ItemIds::COMPOUND, 10, "Luminol"));
		$this->register(new Item(ItemIds::COMPOUND, 11, "Charcoal")); //??? maybe bug
		$this->register(new Item(ItemIds::COMPOUND, 12, "Sugar")); //??? maybe bug
		$this->register(new Item(ItemIds::COMPOUND, 13, "Aluminium Oxide"));
		$this->register(new Item(ItemIds::COMPOUND, 14, "Boron Trioxide"));
		$this->register(new Item(ItemIds::COMPOUND, 15, "Soap"));
		$this->register(new Item(ItemIds::COMPOUND, 16, "Polyethylene"));
		$this->register(new Item(ItemIds::COMPOUND, 17, "Rubbish"));
		$this->register(new Item(ItemIds::COMPOUND, 18, "Magnesium Salts"));
		$this->register(new Item(ItemIds::COMPOUND, 19, "Sulphate"));
		$this->register(new Item(ItemIds::COMPOUND, 20, "Barium Sulphate"));
		$this->register(new Item(ItemIds::COMPOUND, 21, "Potassium Chloride"));
		$this->register(new Item(ItemIds::COMPOUND, 22, "Mercuric Chloride"));
		$this->register(new Item(ItemIds::COMPOUND, 23, "Cerium Chloride"));
		$this->register(new Item(ItemIds::COMPOUND, 24, "Tungsten Chloride"));
		$this->register(new Item(ItemIds::COMPOUND, 25, "Calcium Chloride"));
		$this->register(new Item(ItemIds::COMPOUND, 26, "Water")); //???
		$this->register(new Item(ItemIds::COMPOUND, 27, "Glue"));
		$this->register(new Item(ItemIds::COMPOUND, 28, "Hypochlorite"));
		$this->register(new Item(ItemIds::COMPOUND, 29, "Crude Oil"));
		$this->register(new Item(ItemIds::COMPOUND, 30, "Latex"));
		$this->register(new Item(ItemIds::COMPOUND, 31, "Potassium Iodide"));
		$this->register(new Item(ItemIds::COMPOUND, 32, "Sodium Fluoride"));
		$this->register(new Item(ItemIds::COMPOUND, 33, "Benzene"));
		$this->register(new Item(ItemIds::COMPOUND, 34, "Ink"));
		$this->register(new Item(ItemIds::COMPOUND, 35, "Hydrogen Peroxide"));
		$this->register(new Item(ItemIds::COMPOUND, 36, "Ammonia"));
		$this->register(new Item(ItemIds::COMPOUND, 37, "Sodium Hypochlorite"));
		$this->register(new Item(ItemIds::DIAMOND, 0, "Diamond"));
		$this->register(new Item(ItemIds::DRAGON_BREATH, 0, "Dragon's Breath"));
		$this->register(new Item(ItemIds::DYE, 0, "Ink Sac"));
		$this->register(new Item(ItemIds::DYE, 4, "Lapis Lazuli"));
		$this->register(new Item(ItemIds::EMERALD, 0, "Emerald"));
		$this->register(new Item(ItemIds::FEATHER, 0, "Feather"));
		$this->register(new Item(ItemIds::FERMENTED_SPIDER_EYE, 0, "Fermented Spider Eye"));
		$this->register(new Item(ItemIds::FLINT, 0, "Flint"));
		$this->register(new Item(ItemIds::GHAST_TEAR, 0, "Ghast Tear"));
		$this->register(new Item(ItemIds::GLISTERING_MELON, 0, "Glistering Melon"));
		$this->register(new Item(ItemIds::GLOWSTONE_DUST, 0, "Glowstone Dust"));
		$this->register(new Item(ItemIds::GOLD_INGOT, 0, "Gold Ingot"));
		$this->register(new Item(ItemIds::GOLD_NUGGET, 0, "Gold Nugget"));
		$this->register(new Item(ItemIds::GUNPOWDER, 0, "Gunpowder"));
		$this->register(new Item(ItemIds::HEART_OF_THE_SEA, 0, "Heart of the Sea"));
		$this->register(new Item(ItemIds::IRON_INGOT, 0, "Iron Ingot"));
		$this->register(new Item(ItemIds::IRON_NUGGET, 0, "Iron Nugget"));
		$this->register(new Item(ItemIds::LEATHER, 0, "Leather"));
		$this->register(new Item(ItemIds::MAGMA_CREAM, 0, "Magma Cream"));
		$this->register(new Item(ItemIds::NAUTILUS_SHELL, 0, "Nautilus Shell"));
		$this->register(new Item(ItemIds::NETHER_BRICK, 0, "Nether Brick"));
		$this->register(new Item(ItemIds::NETHER_QUARTZ, 0, "Nether Quartz"));
		$this->register(new Item(ItemIds::NETHER_STAR, 0, "Nether Star"));
		$this->register(new Item(ItemIds::PAPER, 0, "Paper"));
		$this->register(new Item(ItemIds::PRISMARINE_CRYSTALS, 0, "Prismarine Crystals"));
		$this->register(new Item(ItemIds::PRISMARINE_SHARD, 0, "Prismarine Shard"));
		$this->register(new Item(ItemIds::RABBIT_FOOT, 0, "Rabbit's Foot"));
		$this->register(new Item(ItemIds::RABBIT_HIDE, 0, "Rabbit Hide"));
		$this->register(new Item(ItemIds::SHULKER_SHELL, 0, "Shulker Shell"));
		$this->register(new Item(ItemIds::SLIME_BALL, 0, "Slimeball"));
		$this->register(new Item(ItemIds::SUGAR, 0, "Sugar"));
		$this->register(new Item(ItemIds::TURTLE_SHELL_PIECE, 0, "Scute"));
		$this->register(new Item(ItemIds::WHEAT, 0, "Wheat"));
		$this->register(new ItemBlock(BlockLegacyIds::ACACIA_DOOR_BLOCK, 0, ItemIds::ACACIA_DOOR));
		$this->register(new ItemBlock(BlockLegacyIds::BIRCH_DOOR_BLOCK, 0, ItemIds::BIRCH_DOOR));
		$this->register(new ItemBlock(BlockLegacyIds::BREWING_STAND_BLOCK, 0, ItemIds::BREWING_STAND));
		$this->register(new ItemBlock(BlockLegacyIds::CAKE_BLOCK, 0, ItemIds::CAKE));
		$this->register(new ItemBlock(BlockLegacyIds::CAULDRON_BLOCK, 0, ItemIds::CAULDRON));
		$this->register(new ItemBlock(BlockLegacyIds::COMPARATOR_BLOCK, 0, ItemIds::COMPARATOR));
		$this->register(new ItemBlock(BlockLegacyIds::DARK_OAK_DOOR_BLOCK, 0, ItemIds::DARK_OAK_DOOR));
		$this->register(new ItemBlock(BlockLegacyIds::FLOWER_POT_BLOCK, 0, ItemIds::FLOWER_POT));
		$this->register(new ItemBlock(BlockLegacyIds::HOPPER_BLOCK, 0, ItemIds::HOPPER));
		$this->register(new ItemBlock(BlockLegacyIds::IRON_DOOR_BLOCK, 0, ItemIds::IRON_DOOR));
		$this->register(new ItemBlock(BlockLegacyIds::ITEM_FRAME_BLOCK, 0, ItemIds::ITEM_FRAME));
		$this->register(new ItemBlock(BlockLegacyIds::JUNGLE_DOOR_BLOCK, 0, ItemIds::JUNGLE_DOOR));
		$this->register(new ItemBlock(BlockLegacyIds::NETHER_WART_PLANT, 0, ItemIds::NETHER_WART));
		$this->register(new ItemBlock(BlockLegacyIds::OAK_DOOR_BLOCK, 0, ItemIds::OAK_DOOR));
		$this->register(new ItemBlock(BlockLegacyIds::REPEATER_BLOCK, 0, ItemIds::REPEATER));
		$this->register(new ItemBlock(BlockLegacyIds::SPRUCE_DOOR_BLOCK, 0, ItemIds::SPRUCE_DOOR));
		$this->register(new ItemBlock(BlockLegacyIds::SUGARCANE_BLOCK, 0, ItemIds::SUGARCANE));
		//TODO: fix metadata for buckets with still liquid in them
		//the meta values are intentionally hardcoded because block IDs will change in the future
		$this->register(new LiquidBucket(ItemIds::BUCKET, 8, "Water Bucket", VanillaBlocks::WATER()));
		$this->register(new LiquidBucket(ItemIds::BUCKET, 10, "Lava Bucket", VanillaBlocks::LAVA()));
		$this->register(new Melon(ItemIds::MELON, 0, "Melon"));
		$this->register(new MelonSeeds(ItemIds::MELON_SEEDS, 0, "Melon Seeds"));
		$this->register(new MilkBucket(ItemIds::BUCKET, 1, "Milk Bucket"));
		$this->register(new Minecart(ItemIds::MINECART, 0, "Minecart"));
		$this->register(new MushroomStew(ItemIds::MUSHROOM_STEW, 0, "Mushroom Stew"));
		$this->register(new PaintingItem(ItemIds::PAINTING, 0, "Painting"));
		$this->register(new PoisonousPotato(ItemIds::POISONOUS_POTATO, 0, "Poisonous Potato"));
		$this->register(new Potato(ItemIds::POTATO, 0, "Potato"));
		$this->register(new Pufferfish(ItemIds::PUFFERFISH, 0, "Pufferfish"));
		$this->register(new PumpkinPie(ItemIds::PUMPKIN_PIE, 0, "Pumpkin Pie"));
		$this->register(new PumpkinSeeds(ItemIds::PUMPKIN_SEEDS, 0, "Pumpkin Seeds"));
		$this->register(new RabbitStew(ItemIds::RABBIT_STEW, 0, "Rabbit Stew"));
		$this->register(new RawBeef(ItemIds::RAW_BEEF, 0, "Raw Beef"));
		$this->register(new RawChicken(ItemIds::RAW_CHICKEN, 0, "Raw Chicken"));
		$this->register(new RawFish(ItemIds::RAW_FISH, 0, "Raw Fish"));
		$this->register(new RawMutton(ItemIds::RAW_MUTTON, 0, "Raw Mutton"));
		$this->register(new RawPorkchop(ItemIds::RAW_PORKCHOP, 0, "Raw Porkchop"));
		$this->register(new RawRabbit(ItemIds::RAW_RABBIT, 0, "Raw Rabbit"));
		$this->register(new RawSalmon(ItemIds::RAW_SALMON, 0, "Raw Salmon"));
		$this->register(new Redstone(ItemIds::REDSTONE, 0, "Redstone"));
		$this->register(new RottenFlesh(ItemIds::ROTTEN_FLESH, 0, "Rotten Flesh"));
		$this->register(new Shears(ItemIds::SHEARS, 0, "Shears"));
		$this->register(new Sign(BlockLegacyIds::STANDING_SIGN, 0, ItemIds::SIGN));
		$this->register(new Sign(BlockLegacyIds::SPRUCE_STANDING_SIGN, 0, ItemIds::SPRUCE_SIGN));
		$this->register(new Sign(BlockLegacyIds::BIRCH_STANDING_SIGN, 0, ItemIds::BIRCH_SIGN));
		$this->register(new Sign(BlockLegacyIds::JUNGLE_STANDING_SIGN, 0, ItemIds::JUNGLE_SIGN));
		$this->register(new Sign(BlockLegacyIds::ACACIA_STANDING_SIGN, 0, ItemIds::ACACIA_SIGN));
		$this->register(new Sign(BlockLegacyIds::DARKOAK_STANDING_SIGN, 0, ItemIds::DARKOAK_SIGN));
		$this->register(new Snowball(ItemIds::SNOWBALL, 0, "Snowball"));
		$this->register(new SpiderEye(ItemIds::SPIDER_EYE, 0, "Spider Eye"));
		$this->register(new Steak(ItemIds::STEAK, 0, "Steak"));
		$this->register(new Stick(ItemIds::STICK, 0, "Stick"));
		$this->register(new StringItem(ItemIds::STRING, 0, "String"));
		$this->register(new Totem(ItemIds::TOTEM, 0, "Totem of Undying"));
		$this->register(new WheatSeeds(ItemIds::WHEAT_SEEDS, 0, "Wheat Seeds"));
		$this->register(new WritableBook(ItemIds::WRITABLE_BOOK, 0, "Book & Quill"));
		$this->register(new WrittenBook(ItemIds::WRITTEN_BOOK, 0, "Written Book"));

		foreach(SkullType::getAll() as $skullType){
			$this->register(new Skull(ItemIds::SKULL, $skullType->getMagicNumber(), $skullType->getDisplayName(), $skullType));
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
			$this->register(new Dye(ItemIds::DYE, $dyeMap[$color->id()] ?? $color->getInvertedMagicNumber(), $color->getDisplayName() . " Dye", $color));
			$this->register(new Bed(ItemIds::BED, $color->getMagicNumber(), $color->getDisplayName() . " Bed", $color));
			$this->register(new Banner(ItemIds::BANNER, $color->getInvertedMagicNumber(), $color->getDisplayName() . " Banner", $color));
		}

		foreach(Potion::ALL as $type){
			$this->register(new Potion(ItemIds::POTION, $type, "Potion", $type));
			$this->register(new SplashPotion(ItemIds::SPLASH_POTION, $type, "Splash Potion", $type));
		}

		foreach(TreeType::getAll() as $type){
			$this->register(new Boat(ItemIds::BOAT, $type->getMagicNumber(), $type->getDisplayName() . " Boat", $type));
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

	private function registerSpawnEggs() : void{
		//TODO: the meta values should probably be hardcoded; they won't change, but the EntityLegacyIds might
		$this->register(new class(ItemIds::SPAWN_EGG, EntityLegacyIds::ZOMBIE, "Zombie Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Zombie(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		$this->register(new class(ItemIds::SPAWN_EGG, EntityLegacyIds::SQUID, "Squid Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Squid(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		$this->register(new class(ItemIds::SPAWN_EGG, EntityLegacyIds::VILLAGER, "Villager Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Villager(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
	}

	private function registerTierToolItems() : void{
		$this->register(new Axe(ItemIds::DIAMOND_AXE, "Diamond Axe", ToolTier::DIAMOND()));
		$this->register(new Axe(ItemIds::GOLDEN_AXE, "Golden Axe", ToolTier::GOLD()));
		$this->register(new Axe(ItemIds::IRON_AXE, "Iron Axe", ToolTier::IRON()));
		$this->register(new Axe(ItemIds::STONE_AXE, "Stone Axe", ToolTier::STONE()));
		$this->register(new Axe(ItemIds::WOODEN_AXE, "Wooden Axe", ToolTier::WOOD()));
		$this->register(new Hoe(ItemIds::DIAMOND_HOE, "Diamond Hoe", ToolTier::DIAMOND()));
		$this->register(new Hoe(ItemIds::GOLDEN_HOE, "Golden Hoe", ToolTier::GOLD()));
		$this->register(new Hoe(ItemIds::IRON_HOE, "Iron Hoe", ToolTier::IRON()));
		$this->register(new Hoe(ItemIds::STONE_HOE, "Stone Hoe", ToolTier::STONE()));
		$this->register(new Hoe(ItemIds::WOODEN_HOE, "Wooden Hoe", ToolTier::WOOD()));
		$this->register(new Pickaxe(ItemIds::DIAMOND_PICKAXE, "Diamond Pickaxe", ToolTier::DIAMOND()));
		$this->register(new Pickaxe(ItemIds::GOLDEN_PICKAXE, "Golden Pickaxe", ToolTier::GOLD()));
		$this->register(new Pickaxe(ItemIds::IRON_PICKAXE, "Iron Pickaxe", ToolTier::IRON()));
		$this->register(new Pickaxe(ItemIds::STONE_PICKAXE, "Stone Pickaxe", ToolTier::STONE()));
		$this->register(new Pickaxe(ItemIds::WOODEN_PICKAXE, "Wooden Pickaxe", ToolTier::WOOD()));
		$this->register(new Shovel(ItemIds::DIAMOND_SHOVEL, "Diamond Shovel", ToolTier::DIAMOND()));
		$this->register(new Shovel(ItemIds::GOLDEN_SHOVEL, "Golden Shovel", ToolTier::GOLD()));
		$this->register(new Shovel(ItemIds::IRON_SHOVEL, "Iron Shovel", ToolTier::IRON()));
		$this->register(new Shovel(ItemIds::STONE_SHOVEL, "Stone Shovel", ToolTier::STONE()));
		$this->register(new Shovel(ItemIds::WOODEN_SHOVEL, "Wooden Shovel", ToolTier::WOOD()));
		$this->register(new Sword(ItemIds::DIAMOND_SWORD, "Diamond Sword", ToolTier::DIAMOND()));
		$this->register(new Sword(ItemIds::GOLDEN_SWORD, "Golden Sword", ToolTier::GOLD()));
		$this->register(new Sword(ItemIds::IRON_SWORD, "Iron Sword", ToolTier::IRON()));
		$this->register(new Sword(ItemIds::STONE_SWORD, "Stone Sword", ToolTier::STONE()));
		$this->register(new Sword(ItemIds::WOODEN_SWORD, "Wooden Sword", ToolTier::WOOD()));
	}

	private function registerArmorItems() : void{
		$this->register(new Armor(ItemIds::CHAIN_BOOTS, 0, "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(ItemIds::DIAMOND_BOOTS, 0, "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(ItemIds::GOLDEN_BOOTS, 0, "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(ItemIds::IRON_BOOTS, 0, "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(ItemIds::LEATHER_BOOTS, 0, "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(ItemIds::CHAIN_CHESTPLATE, 0, "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(ItemIds::DIAMOND_CHESTPLATE, 0, "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(ItemIds::GOLDEN_CHESTPLATE, 0, "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(ItemIds::IRON_CHESTPLATE, 0, "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(ItemIds::LEATHER_CHESTPLATE, 0, "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(ItemIds::CHAIN_HELMET, 0, "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(ItemIds::DIAMOND_HELMET, 0, "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(ItemIds::GOLDEN_HELMET, 0, "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(ItemIds::IRON_HELMET, 0, "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(ItemIds::LEATHER_HELMET, 0, "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(ItemIds::CHAIN_LEGGINGS, 0, "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(ItemIds::DIAMOND_LEGGINGS, 0, "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(ItemIds::GOLDEN_LEGGINGS, 0, "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(ItemIds::IRON_LEGGINGS, 0, "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(ItemIds::LEATHER_LEGGINGS, 0, "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS)));
	}

	/**
	 * Registers an item type into the index. Plugins may use this method to register new item types or override existing
	 * ones.
	 *
	 * NOTE: If you are registering a new item type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered item without specifying the
	 * $override parameter.
	 */
	public function register(Item $item, bool $override = false) : void{
		$id = $item->getId();
		$variant = $item->getMeta();

		if(!$override and $this->isRegistered($id, $variant)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		$this->list[self::getListOffset($id, $variant)] = clone $item;
	}

	/**
	 * Returns an instance of the Item with the specified id, meta, count and NBT.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function get(int $id, int $meta = 0, int $count = 1, ?CompoundTag $tags = null) : Item{
		/** @var Item|null $item */
		$item = null;
		if($meta !== -1){
			if(isset($this->list[$offset = self::getListOffset($id, $meta)])){
				$item = clone $this->list[$offset];
			}elseif(isset($this->list[$zero = self::getListOffset($id, 0)]) and $this->list[$zero] instanceof Durable){
				/** @var Durable $item */
				$item = clone $this->list[$zero];
				try{
					$item->setDamage($meta);
				}catch(\InvalidArgumentException $e){
					$item = new Item($id, $meta);
				}
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

	public static function air() : Item{
		return self::$air ?? (self::$air = self::getInstance()->get(ItemIds::AIR, 0, 0));
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 */
	public function isRegistered(int $id, int $variant = 0) : bool{
		if($id < 256){
			return BlockFactory::getInstance()->isRegistered($id);
		}

		return isset($this->list[self::getListOffset($id, $variant)]);
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
	public function getAllRegistered() : array{
		return $this->list;
	}
}
