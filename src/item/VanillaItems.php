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

use pocketmine\block\utils\RecordType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ItemIdentifier as IID;
use pocketmine\item\ItemTypeIds as Ids;
use pocketmine\math\Vector3;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\CloningRegistryTrait;
use pocketmine\world\World;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static Boat ACACIA_BOAT()
 * @method static ItemBlockWallOrFloor ACACIA_SIGN()
 * @method static ItemBlock AIR()
 * @method static Item AMETHYST_SHARD()
 * @method static Apple APPLE()
 * @method static Arrow ARROW()
 * @method static BakedPotato BAKED_POTATO()
 * @method static Bamboo BAMBOO()
 * @method static Banner BANNER()
 * @method static Beetroot BEETROOT()
 * @method static BeetrootSeeds BEETROOT_SEEDS()
 * @method static BeetrootSoup BEETROOT_SOUP()
 * @method static Boat BIRCH_BOAT()
 * @method static ItemBlockWallOrFloor BIRCH_SIGN()
 * @method static Item BLAZE_POWDER()
 * @method static BlazeRod BLAZE_ROD()
 * @method static Item BLEACH()
 * @method static Item BONE()
 * @method static Fertilizer BONE_MEAL()
 * @method static Book BOOK()
 * @method static Bow BOW()
 * @method static Bowl BOWL()
 * @method static Bread BREAD()
 * @method static Item BRICK()
 * @method static Bucket BUCKET()
 * @method static Carrot CARROT()
 * @method static Armor CHAINMAIL_BOOTS()
 * @method static Armor CHAINMAIL_CHESTPLATE()
 * @method static Armor CHAINMAIL_HELMET()
 * @method static Armor CHAINMAIL_LEGGINGS()
 * @method static Coal CHARCOAL()
 * @method static Item CHEMICAL_ALUMINIUM_OXIDE()
 * @method static Item CHEMICAL_AMMONIA()
 * @method static Item CHEMICAL_BARIUM_SULPHATE()
 * @method static Item CHEMICAL_BENZENE()
 * @method static Item CHEMICAL_BORON_TRIOXIDE()
 * @method static Item CHEMICAL_CALCIUM_BROMIDE()
 * @method static Item CHEMICAL_CALCIUM_CHLORIDE()
 * @method static Item CHEMICAL_CERIUM_CHLORIDE()
 * @method static Item CHEMICAL_CHARCOAL()
 * @method static Item CHEMICAL_CRUDE_OIL()
 * @method static Item CHEMICAL_GLUE()
 * @method static Item CHEMICAL_HYDROGEN_PEROXIDE()
 * @method static Item CHEMICAL_HYPOCHLORITE()
 * @method static Item CHEMICAL_INK()
 * @method static Item CHEMICAL_IRON_SULPHIDE()
 * @method static Item CHEMICAL_LATEX()
 * @method static Item CHEMICAL_LITHIUM_HYDRIDE()
 * @method static Item CHEMICAL_LUMINOL()
 * @method static Item CHEMICAL_MAGNESIUM_NITRATE()
 * @method static Item CHEMICAL_MAGNESIUM_OXIDE()
 * @method static Item CHEMICAL_MAGNESIUM_SALTS()
 * @method static Item CHEMICAL_MERCURIC_CHLORIDE()
 * @method static Item CHEMICAL_POLYETHYLENE()
 * @method static Item CHEMICAL_POTASSIUM_CHLORIDE()
 * @method static Item CHEMICAL_POTASSIUM_IODIDE()
 * @method static Item CHEMICAL_RUBBISH()
 * @method static Item CHEMICAL_SALT()
 * @method static Item CHEMICAL_SOAP()
 * @method static Item CHEMICAL_SODIUM_ACETATE()
 * @method static Item CHEMICAL_SODIUM_FLUORIDE()
 * @method static Item CHEMICAL_SODIUM_HYDRIDE()
 * @method static Item CHEMICAL_SODIUM_HYDROXIDE()
 * @method static Item CHEMICAL_SODIUM_HYPOCHLORITE()
 * @method static Item CHEMICAL_SODIUM_OXIDE()
 * @method static Item CHEMICAL_SUGAR()
 * @method static Item CHEMICAL_SULPHATE()
 * @method static Item CHEMICAL_TUNGSTEN_CHLORIDE()
 * @method static Item CHEMICAL_WATER()
 * @method static ChorusFruit CHORUS_FRUIT()
 * @method static Item CLAY()
 * @method static Clock CLOCK()
 * @method static Clownfish CLOWNFISH()
 * @method static Coal COAL()
 * @method static CocoaBeans COCOA_BEANS()
 * @method static Compass COMPASS()
 * @method static CookedChicken COOKED_CHICKEN()
 * @method static CookedFish COOKED_FISH()
 * @method static CookedMutton COOKED_MUTTON()
 * @method static CookedPorkchop COOKED_PORKCHOP()
 * @method static CookedRabbit COOKED_RABBIT()
 * @method static CookedSalmon COOKED_SALMON()
 * @method static Cookie COOKIE()
 * @method static Item COPPER_INGOT()
 * @method static CoralFan CORAL_FAN()
 * @method static ItemBlockWallOrFloor CRIMSON_SIGN()
 * @method static Boat DARK_OAK_BOAT()
 * @method static ItemBlockWallOrFloor DARK_OAK_SIGN()
 * @method static Item DIAMOND()
 * @method static Axe DIAMOND_AXE()
 * @method static Armor DIAMOND_BOOTS()
 * @method static Armor DIAMOND_CHESTPLATE()
 * @method static Armor DIAMOND_HELMET()
 * @method static Hoe DIAMOND_HOE()
 * @method static Armor DIAMOND_LEGGINGS()
 * @method static Pickaxe DIAMOND_PICKAXE()
 * @method static Shovel DIAMOND_SHOVEL()
 * @method static Sword DIAMOND_SWORD()
 * @method static Item DISC_FRAGMENT_5()
 * @method static Item DRAGON_BREATH()
 * @method static DriedKelp DRIED_KELP()
 * @method static Dye DYE()
 * @method static Item ECHO_SHARD()
 * @method static Egg EGG()
 * @method static Item EMERALD()
 * @method static GoldenAppleEnchanted ENCHANTED_GOLDEN_APPLE()
 * @method static EnderPearl ENDER_PEARL()
 * @method static ExperienceBottle EXPERIENCE_BOTTLE()
 * @method static Item FEATHER()
 * @method static Item FERMENTED_SPIDER_EYE()
 * @method static FireCharge FIRE_CHARGE()
 * @method static FishingRod FISHING_ROD()
 * @method static Item FLINT()
 * @method static FlintSteel FLINT_AND_STEEL()
 * @method static Item GHAST_TEAR()
 * @method static GlassBottle GLASS_BOTTLE()
 * @method static Item GLISTERING_MELON()
 * @method static Item GLOWSTONE_DUST()
 * @method static GlowBerries GLOW_BERRIES()
 * @method static Item GLOW_INK_SAC()
 * @method static GoldenApple GOLDEN_APPLE()
 * @method static Axe GOLDEN_AXE()
 * @method static Armor GOLDEN_BOOTS()
 * @method static GoldenCarrot GOLDEN_CARROT()
 * @method static Armor GOLDEN_CHESTPLATE()
 * @method static Armor GOLDEN_HELMET()
 * @method static Hoe GOLDEN_HOE()
 * @method static Armor GOLDEN_LEGGINGS()
 * @method static Pickaxe GOLDEN_PICKAXE()
 * @method static Shovel GOLDEN_SHOVEL()
 * @method static Sword GOLDEN_SWORD()
 * @method static Item GOLD_INGOT()
 * @method static Item GOLD_NUGGET()
 * @method static Item GUNPOWDER()
 * @method static Item HEART_OF_THE_SEA()
 * @method static Item HONEYCOMB()
 * @method static HoneyBottle HONEY_BOTTLE()
 * @method static Item INK_SAC()
 * @method static Axe IRON_AXE()
 * @method static Armor IRON_BOOTS()
 * @method static Armor IRON_CHESTPLATE()
 * @method static Armor IRON_HELMET()
 * @method static Hoe IRON_HOE()
 * @method static Item IRON_INGOT()
 * @method static Armor IRON_LEGGINGS()
 * @method static Item IRON_NUGGET()
 * @method static Pickaxe IRON_PICKAXE()
 * @method static Shovel IRON_SHOVEL()
 * @method static Sword IRON_SWORD()
 * @method static Boat JUNGLE_BOAT()
 * @method static ItemBlockWallOrFloor JUNGLE_SIGN()
 * @method static Item LAPIS_LAZULI()
 * @method static LiquidBucket LAVA_BUCKET()
 * @method static Item LEATHER()
 * @method static Armor LEATHER_BOOTS()
 * @method static Armor LEATHER_CAP()
 * @method static Armor LEATHER_PANTS()
 * @method static Armor LEATHER_TUNIC()
 * @method static Item MAGMA_CREAM()
 * @method static Boat MANGROVE_BOAT()
 * @method static ItemBlockWallOrFloor MANGROVE_SIGN()
 * @method static Medicine MEDICINE()
 * @method static Melon MELON()
 * @method static MelonSeeds MELON_SEEDS()
 * @method static MilkBucket MILK_BUCKET()
 * @method static Minecart MINECART()
 * @method static MushroomStew MUSHROOM_STEW()
 * @method static Item NAUTILUS_SHELL()
 * @method static Axe NETHERITE_AXE()
 * @method static Armor NETHERITE_BOOTS()
 * @method static Armor NETHERITE_CHESTPLATE()
 * @method static Armor NETHERITE_HELMET()
 * @method static Hoe NETHERITE_HOE()
 * @method static Item NETHERITE_INGOT()
 * @method static Armor NETHERITE_LEGGINGS()
 * @method static Pickaxe NETHERITE_PICKAXE()
 * @method static Item NETHERITE_SCRAP()
 * @method static Shovel NETHERITE_SHOVEL()
 * @method static Sword NETHERITE_SWORD()
 * @method static Item NETHER_BRICK()
 * @method static Item NETHER_QUARTZ()
 * @method static Item NETHER_STAR()
 * @method static Boat OAK_BOAT()
 * @method static ItemBlockWallOrFloor OAK_SIGN()
 * @method static PaintingItem PAINTING()
 * @method static Item PAPER()
 * @method static Item PHANTOM_MEMBRANE()
 * @method static PoisonousPotato POISONOUS_POTATO()
 * @method static Item POPPED_CHORUS_FRUIT()
 * @method static Potato POTATO()
 * @method static Potion POTION()
 * @method static Item PRISMARINE_CRYSTALS()
 * @method static Item PRISMARINE_SHARD()
 * @method static Pufferfish PUFFERFISH()
 * @method static PumpkinPie PUMPKIN_PIE()
 * @method static PumpkinSeeds PUMPKIN_SEEDS()
 * @method static Item RABBIT_FOOT()
 * @method static Item RABBIT_HIDE()
 * @method static RabbitStew RABBIT_STEW()
 * @method static RawBeef RAW_BEEF()
 * @method static RawChicken RAW_CHICKEN()
 * @method static Item RAW_COPPER()
 * @method static RawFish RAW_FISH()
 * @method static Item RAW_GOLD()
 * @method static Item RAW_IRON()
 * @method static RawMutton RAW_MUTTON()
 * @method static RawPorkchop RAW_PORKCHOP()
 * @method static RawRabbit RAW_RABBIT()
 * @method static RawSalmon RAW_SALMON()
 * @method static Record RECORD_11()
 * @method static Record RECORD_13()
 * @method static Record RECORD_5()
 * @method static Record RECORD_BLOCKS()
 * @method static Record RECORD_CAT()
 * @method static Record RECORD_CHIRP()
 * @method static Record RECORD_FAR()
 * @method static Record RECORD_MALL()
 * @method static Record RECORD_MELLOHI()
 * @method static Record RECORD_OTHERSIDE()
 * @method static Record RECORD_PIGSTEP()
 * @method static Record RECORD_STAL()
 * @method static Record RECORD_STRAD()
 * @method static Record RECORD_WAIT()
 * @method static Record RECORD_WARD()
 * @method static Redstone REDSTONE_DUST()
 * @method static RottenFlesh ROTTEN_FLESH()
 * @method static Item SCUTE()
 * @method static Shears SHEARS()
 * @method static Item SHULKER_SHELL()
 * @method static Item SLIMEBALL()
 * @method static Snowball SNOWBALL()
 * @method static SpiderEye SPIDER_EYE()
 * @method static SplashPotion SPLASH_POTION()
 * @method static Boat SPRUCE_BOAT()
 * @method static ItemBlockWallOrFloor SPRUCE_SIGN()
 * @method static Spyglass SPYGLASS()
 * @method static SpawnEgg SQUID_SPAWN_EGG()
 * @method static Steak STEAK()
 * @method static Stick STICK()
 * @method static Axe STONE_AXE()
 * @method static Hoe STONE_HOE()
 * @method static Pickaxe STONE_PICKAXE()
 * @method static Shovel STONE_SHOVEL()
 * @method static Sword STONE_SWORD()
 * @method static StringItem STRING()
 * @method static Item SUGAR()
 * @method static SuspiciousStew SUSPICIOUS_STEW()
 * @method static SweetBerries SWEET_BERRIES()
 * @method static Totem TOTEM()
 * @method static TurtleHelmet TURTLE_HELMET()
 * @method static SpawnEgg VILLAGER_SPAWN_EGG()
 * @method static ItemBlockWallOrFloor WARPED_SIGN()
 * @method static LiquidBucket WATER_BUCKET()
 * @method static Item WHEAT()
 * @method static WheatSeeds WHEAT_SEEDS()
 * @method static Axe WOODEN_AXE()
 * @method static Hoe WOODEN_HOE()
 * @method static Pickaxe WOODEN_PICKAXE()
 * @method static Shovel WOODEN_SHOVEL()
 * @method static Sword WOODEN_SWORD()
 * @method static WritableBook WRITABLE_BOOK()
 * @method static WrittenBook WRITTEN_BOOK()
 * @method static SpawnEgg ZOMBIE_SPAWN_EGG()
 */
final class VanillaItems{
	use CloningRegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, Item $item) : void{
		self::_registryRegister($name, $item);
	}

	/**
	 * @return Item[]
	 * @phpstan-return array<string, Item>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var Item[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void{
		self::registerArmorItems();
		self::registerSpawnEggs();
		self::registerTierToolItems();

		self::register("air", VanillaBlocks::AIR()->asItem()->setCount(0));

		self::register("acacia_sign", new ItemBlockWallOrFloor(new IID(Ids::ACACIA_SIGN), Blocks::ACACIA_SIGN(), Blocks::ACACIA_WALL_SIGN()));
		self::register("amethyst_shard", new Item(new IID(Ids::AMETHYST_SHARD), "Amethyst Shard"));
		self::register("apple", new Apple(new IID(Ids::APPLE), "Apple"));
		self::register("arrow", new Arrow(new IID(Ids::ARROW), "Arrow"));
		self::register("baked_potato", new BakedPotato(new IID(Ids::BAKED_POTATO), "Baked Potato"));
		self::register("bamboo", new Bamboo(new IID(Ids::BAMBOO), "Bamboo"));
		self::register("banner", new Banner(new IID(Ids::BANNER), Blocks::BANNER(), Blocks::WALL_BANNER()));
		self::register("beetroot", new Beetroot(new IID(Ids::BEETROOT), "Beetroot"));
		self::register("beetroot_seeds", new BeetrootSeeds(new IID(Ids::BEETROOT_SEEDS), "Beetroot Seeds"));
		self::register("beetroot_soup", new BeetrootSoup(new IID(Ids::BEETROOT_SOUP), "Beetroot Soup"));
		self::register("birch_sign", new ItemBlockWallOrFloor(new IID(Ids::BIRCH_SIGN), Blocks::BIRCH_SIGN(), Blocks::BIRCH_WALL_SIGN()));
		self::register("blaze_powder", new Item(new IID(Ids::BLAZE_POWDER), "Blaze Powder"));
		self::register("blaze_rod", new BlazeRod(new IID(Ids::BLAZE_ROD), "Blaze Rod"));
		self::register("bleach", new Item(new IID(Ids::BLEACH), "Bleach"));
		self::register("bone", new Item(new IID(Ids::BONE), "Bone"));
		self::register("bone_meal", new Fertilizer(new IID(Ids::BONE_MEAL), "Bone Meal"));
		self::register("book", new Book(new IID(Ids::BOOK), "Book"));
		self::register("bow", new Bow(new IID(Ids::BOW), "Bow"));
		self::register("bowl", new Bowl(new IID(Ids::BOWL), "Bowl"));
		self::register("bread", new Bread(new IID(Ids::BREAD), "Bread"));
		self::register("brick", new Item(new IID(Ids::BRICK), "Brick"));
		self::register("bucket", new Bucket(new IID(Ids::BUCKET), "Bucket"));
		self::register("carrot", new Carrot(new IID(Ids::CARROT), "Carrot"));
		self::register("charcoal", new Coal(new IID(Ids::CHARCOAL), "Charcoal"));
		self::register("chemical_aluminium_oxide", new Item(new IID(Ids::CHEMICAL_ALUMINIUM_OXIDE), "Aluminium Oxide"));
		self::register("chemical_ammonia", new Item(new IID(Ids::CHEMICAL_AMMONIA), "Ammonia"));
		self::register("chemical_barium_sulphate", new Item(new IID(Ids::CHEMICAL_BARIUM_SULPHATE), "Barium Sulphate"));
		self::register("chemical_benzene", new Item(new IID(Ids::CHEMICAL_BENZENE), "Benzene"));
		self::register("chemical_boron_trioxide", new Item(new IID(Ids::CHEMICAL_BORON_TRIOXIDE), "Boron Trioxide"));
		self::register("chemical_calcium_bromide", new Item(new IID(Ids::CHEMICAL_CALCIUM_BROMIDE), "Calcium Bromide"));
		self::register("chemical_calcium_chloride", new Item(new IID(Ids::CHEMICAL_CALCIUM_CHLORIDE), "Calcium Chloride"));
		self::register("chemical_cerium_chloride", new Item(new IID(Ids::CHEMICAL_CERIUM_CHLORIDE), "Cerium Chloride"));
		self::register("chemical_charcoal", new Item(new IID(Ids::CHEMICAL_CHARCOAL), "Charcoal"));
		self::register("chemical_crude_oil", new Item(new IID(Ids::CHEMICAL_CRUDE_OIL), "Crude Oil"));
		self::register("chemical_glue", new Item(new IID(Ids::CHEMICAL_GLUE), "Glue"));
		self::register("chemical_hydrogen_peroxide", new Item(new IID(Ids::CHEMICAL_HYDROGEN_PEROXIDE), "Hydrogen Peroxide"));
		self::register("chemical_hypochlorite", new Item(new IID(Ids::CHEMICAL_HYPOCHLORITE), "Hypochlorite"));
		self::register("chemical_ink", new Item(new IID(Ids::CHEMICAL_INK), "Ink"));
		self::register("chemical_iron_sulphide", new Item(new IID(Ids::CHEMICAL_IRON_SULPHIDE), "Iron Sulphide"));
		self::register("chemical_latex", new Item(new IID(Ids::CHEMICAL_LATEX), "Latex"));
		self::register("chemical_lithium_hydride", new Item(new IID(Ids::CHEMICAL_LITHIUM_HYDRIDE), "Lithium Hydride"));
		self::register("chemical_luminol", new Item(new IID(Ids::CHEMICAL_LUMINOL), "Luminol"));
		self::register("chemical_magnesium_nitrate", new Item(new IID(Ids::CHEMICAL_MAGNESIUM_NITRATE), "Magnesium Nitrate"));
		self::register("chemical_magnesium_oxide", new Item(new IID(Ids::CHEMICAL_MAGNESIUM_OXIDE), "Magnesium Oxide"));
		self::register("chemical_magnesium_salts", new Item(new IID(Ids::CHEMICAL_MAGNESIUM_SALTS), "Magnesium Salts"));
		self::register("chemical_mercuric_chloride", new Item(new IID(Ids::CHEMICAL_MERCURIC_CHLORIDE), "Mercuric Chloride"));
		self::register("chemical_polyethylene", new Item(new IID(Ids::CHEMICAL_POLYETHYLENE), "Polyethylene"));
		self::register("chemical_potassium_chloride", new Item(new IID(Ids::CHEMICAL_POTASSIUM_CHLORIDE), "Potassium Chloride"));
		self::register("chemical_potassium_iodide", new Item(new IID(Ids::CHEMICAL_POTASSIUM_IODIDE), "Potassium Iodide"));
		self::register("chemical_rubbish", new Item(new IID(Ids::CHEMICAL_RUBBISH), "Rubbish"));
		self::register("chemical_salt", new Item(new IID(Ids::CHEMICAL_SALT), "Salt"));
		self::register("chemical_soap", new Item(new IID(Ids::CHEMICAL_SOAP), "Soap"));
		self::register("chemical_sodium_acetate", new Item(new IID(Ids::CHEMICAL_SODIUM_ACETATE), "Sodium Acetate"));
		self::register("chemical_sodium_fluoride", new Item(new IID(Ids::CHEMICAL_SODIUM_FLUORIDE), "Sodium Fluoride"));
		self::register("chemical_sodium_hydride", new Item(new IID(Ids::CHEMICAL_SODIUM_HYDRIDE), "Sodium Hydride"));
		self::register("chemical_sodium_hydroxide", new Item(new IID(Ids::CHEMICAL_SODIUM_HYDROXIDE), "Sodium Hydroxide"));
		self::register("chemical_sodium_hypochlorite", new Item(new IID(Ids::CHEMICAL_SODIUM_HYPOCHLORITE), "Sodium Hypochlorite"));
		self::register("chemical_sodium_oxide", new Item(new IID(Ids::CHEMICAL_SODIUM_OXIDE), "Sodium Oxide"));
		self::register("chemical_sugar", new Item(new IID(Ids::CHEMICAL_SUGAR), "Sugar"));
		self::register("chemical_sulphate", new Item(new IID(Ids::CHEMICAL_SULPHATE), "Sulphate"));
		self::register("chemical_tungsten_chloride", new Item(new IID(Ids::CHEMICAL_TUNGSTEN_CHLORIDE), "Tungsten Chloride"));
		self::register("chemical_water", new Item(new IID(Ids::CHEMICAL_WATER), "Water"));
		self::register("chorus_fruit", new ChorusFruit(new IID(Ids::CHORUS_FRUIT), "Chorus Fruit"));
		self::register("clay", new Item(new IID(Ids::CLAY), "Clay"));
		self::register("clock", new Clock(new IID(Ids::CLOCK), "Clock"));
		self::register("clownfish", new Clownfish(new IID(Ids::CLOWNFISH), "Clownfish"));
		self::register("coal", new Coal(new IID(Ids::COAL), "Coal"));
		self::register("cocoa_beans", new CocoaBeans(new IID(Ids::COCOA_BEANS), "Cocoa Beans"));
		self::register("compass", new Compass(new IID(Ids::COMPASS), "Compass"));
		self::register("cooked_chicken", new CookedChicken(new IID(Ids::COOKED_CHICKEN), "Cooked Chicken"));
		self::register("cooked_fish", new CookedFish(new IID(Ids::COOKED_FISH), "Cooked Fish"));
		self::register("cooked_mutton", new CookedMutton(new IID(Ids::COOKED_MUTTON), "Cooked Mutton"));
		self::register("cooked_porkchop", new CookedPorkchop(new IID(Ids::COOKED_PORKCHOP), "Cooked Porkchop"));
		self::register("cooked_rabbit", new CookedRabbit(new IID(Ids::COOKED_RABBIT), "Cooked Rabbit"));
		self::register("cooked_salmon", new CookedSalmon(new IID(Ids::COOKED_SALMON), "Cooked Salmon"));
		self::register("cookie", new Cookie(new IID(Ids::COOKIE), "Cookie"));
		self::register("copper_ingot", new Item(new IID(Ids::COPPER_INGOT), "Copper Ingot"));
		self::register("coral_fan", new CoralFan(new IID(Ids::CORAL_FAN)));
		self::register("crimson_sign", new ItemBlockWallOrFloor(new IID(Ids::CRIMSON_SIGN), Blocks::CRIMSON_SIGN(), Blocks::CRIMSON_WALL_SIGN()));
		self::register("dark_oak_sign", new ItemBlockWallOrFloor(new IID(Ids::DARK_OAK_SIGN), Blocks::DARK_OAK_SIGN(), Blocks::DARK_OAK_WALL_SIGN()));
		self::register("diamond", new Item(new IID(Ids::DIAMOND), "Diamond"));
		self::register("disc_fragment_5", new Item(new IID(Ids::DISC_FRAGMENT_5), "Disc Fragment (5)"));
		self::register("dragon_breath", new Item(new IID(Ids::DRAGON_BREATH), "Dragon's Breath"));
		self::register("dried_kelp", new DriedKelp(new IID(Ids::DRIED_KELP), "Dried Kelp"));
		//TODO: add interface to dye-colour objects
		self::register("dye", new Dye(new IID(Ids::DYE), "Dye"));
		self::register("echo_shard", new Item(new IID(Ids::ECHO_SHARD), "Echo Shard"));
		self::register("egg", new Egg(new IID(Ids::EGG), "Egg"));
		self::register("emerald", new Item(new IID(Ids::EMERALD), "Emerald"));
		self::register("enchanted_golden_apple", new GoldenAppleEnchanted(new IID(Ids::ENCHANTED_GOLDEN_APPLE), "Enchanted Golden Apple"));
		self::register("ender_pearl", new EnderPearl(new IID(Ids::ENDER_PEARL), "Ender Pearl"));
		self::register("experience_bottle", new ExperienceBottle(new IID(Ids::EXPERIENCE_BOTTLE), "Bottle o' Enchanting"));
		self::register("feather", new Item(new IID(Ids::FEATHER), "Feather"));
		self::register("fermented_spider_eye", new Item(new IID(Ids::FERMENTED_SPIDER_EYE), "Fermented Spider Eye"));
		self::register("fire_charge", new FireCharge(new IID(Ids::FIRE_CHARGE), "Fire Charge"));
		self::register("fishing_rod", new FishingRod(new IID(Ids::FISHING_ROD), "Fishing Rod"));
		self::register("flint", new Item(new IID(Ids::FLINT), "Flint"));
		self::register("flint_and_steel", new FlintSteel(new IID(Ids::FLINT_AND_STEEL), "Flint and Steel"));
		self::register("ghast_tear", new Item(new IID(Ids::GHAST_TEAR), "Ghast Tear"));
		self::register("glass_bottle", new GlassBottle(new IID(Ids::GLASS_BOTTLE), "Glass Bottle"));
		self::register("glistering_melon", new Item(new IID(Ids::GLISTERING_MELON), "Glistering Melon"));
		self::register("glow_berries", new GlowBerries(new IID(Ids::GLOW_BERRIES), "Glow Berries"));
		self::register("glow_ink_sac", new Item(new IID(Ids::GLOW_INK_SAC), "Glow Ink Sac"));
		self::register("glowstone_dust", new Item(new IID(Ids::GLOWSTONE_DUST), "Glowstone Dust"));
		self::register("gold_ingot", new Item(new IID(Ids::GOLD_INGOT), "Gold Ingot"));
		self::register("gold_nugget", new Item(new IID(Ids::GOLD_NUGGET), "Gold Nugget"));
		self::register("golden_apple", new GoldenApple(new IID(Ids::GOLDEN_APPLE), "Golden Apple"));
		self::register("golden_carrot", new GoldenCarrot(new IID(Ids::GOLDEN_CARROT), "Golden Carrot"));
		self::register("gunpowder", new Item(new IID(Ids::GUNPOWDER), "Gunpowder"));
		self::register("heart_of_the_sea", new Item(new IID(Ids::HEART_OF_THE_SEA), "Heart of the Sea"));
		self::register("honey_bottle", new HoneyBottle(new IID(Ids::HONEY_BOTTLE), "Honey Bottle"));
		self::register("honeycomb", new Item(new IID(Ids::HONEYCOMB), "Honeycomb"));
		self::register("ink_sac", new Item(new IID(Ids::INK_SAC), "Ink Sac"));
		self::register("iron_ingot", new Item(new IID(Ids::IRON_INGOT), "Iron Ingot"));
		self::register("iron_nugget", new Item(new IID(Ids::IRON_NUGGET), "Iron Nugget"));
		self::register("jungle_sign", new ItemBlockWallOrFloor(new IID(Ids::JUNGLE_SIGN), Blocks::JUNGLE_SIGN(), Blocks::JUNGLE_WALL_SIGN()));
		self::register("lapis_lazuli", new Item(new IID(Ids::LAPIS_LAZULI), "Lapis Lazuli"));
		self::register("lava_bucket", new LiquidBucket(new IID(Ids::LAVA_BUCKET), "Lava Bucket", Blocks::LAVA()));
		self::register("leather", new Item(new IID(Ids::LEATHER), "Leather"));
		self::register("magma_cream", new Item(new IID(Ids::MAGMA_CREAM), "Magma Cream"));
		self::register("mangrove_sign", new ItemBlockWallOrFloor(new IID(Ids::MANGROVE_SIGN), Blocks::MANGROVE_SIGN(), Blocks::MANGROVE_WALL_SIGN()));
		self::register("medicine", new Medicine(new IID(Ids::MEDICINE), "Medicine"));
		self::register("melon", new Melon(new IID(Ids::MELON), "Melon"));
		self::register("melon_seeds", new MelonSeeds(new IID(Ids::MELON_SEEDS), "Melon Seeds"));
		self::register("milk_bucket", new MilkBucket(new IID(Ids::MILK_BUCKET), "Milk Bucket"));
		self::register("minecart", new Minecart(new IID(Ids::MINECART), "Minecart"));
		self::register("mushroom_stew", new MushroomStew(new IID(Ids::MUSHROOM_STEW), "Mushroom Stew"));
		self::register("nautilus_shell", new Item(new IID(Ids::NAUTILUS_SHELL), "Nautilus Shell"));
		self::register("nether_brick", new Item(new IID(Ids::NETHER_BRICK), "Nether Brick"));
		self::register("nether_quartz", new Item(new IID(Ids::NETHER_QUARTZ), "Nether Quartz"));
		self::register("nether_star", new Item(new IID(Ids::NETHER_STAR), "Nether Star"));
		self::register("netherite_ingot", new class(new IID(Ids::NETHERITE_INGOT), "Netherite Ingot") extends Item{
			public function isFireProof() : bool{ return true; }
		});
		self::register("netherite_scrap", new class(new IID(Ids::NETHERITE_SCRAP), "Netherite Scrap") extends Item{
			public function isFireProof() : bool{ return true; }
		});
		self::register("oak_sign", new ItemBlockWallOrFloor(new IID(Ids::OAK_SIGN), Blocks::OAK_SIGN(), Blocks::OAK_WALL_SIGN()));
		self::register("painting", new PaintingItem(new IID(Ids::PAINTING), "Painting"));
		self::register("paper", new Item(new IID(Ids::PAPER), "Paper"));
		self::register("phantom_membrane", new Item(new IID(Ids::PHANTOM_MEMBRANE), "Phantom Membrane"));
		self::register("poisonous_potato", new PoisonousPotato(new IID(Ids::POISONOUS_POTATO), "Poisonous Potato"));
		self::register("popped_chorus_fruit", new Item(new IID(Ids::POPPED_CHORUS_FRUIT), "Popped Chorus Fruit"));
		self::register("potato", new Potato(new IID(Ids::POTATO), "Potato"));
		self::register("potion", new Potion(new IID(Ids::POTION), "Potion"));
		self::register("prismarine_crystals", new Item(new IID(Ids::PRISMARINE_CRYSTALS), "Prismarine Crystals"));
		self::register("prismarine_shard", new Item(new IID(Ids::PRISMARINE_SHARD), "Prismarine Shard"));
		self::register("pufferfish", new Pufferfish(new IID(Ids::PUFFERFISH), "Pufferfish"));
		self::register("pumpkin_pie", new PumpkinPie(new IID(Ids::PUMPKIN_PIE), "Pumpkin Pie"));
		self::register("pumpkin_seeds", new PumpkinSeeds(new IID(Ids::PUMPKIN_SEEDS), "Pumpkin Seeds"));
		self::register("rabbit_foot", new Item(new IID(Ids::RABBIT_FOOT), "Rabbit's Foot"));
		self::register("rabbit_hide", new Item(new IID(Ids::RABBIT_HIDE), "Rabbit Hide"));
		self::register("rabbit_stew", new RabbitStew(new IID(Ids::RABBIT_STEW), "Rabbit Stew"));
		self::register("raw_beef", new RawBeef(new IID(Ids::RAW_BEEF), "Raw Beef"));
		self::register("raw_chicken", new RawChicken(new IID(Ids::RAW_CHICKEN), "Raw Chicken"));
		self::register("raw_copper", new Item(new IID(Ids::RAW_COPPER), "Raw Copper"));
		self::register("raw_fish", new RawFish(new IID(Ids::RAW_FISH), "Raw Fish"));
		self::register("raw_gold", new Item(new IID(Ids::RAW_GOLD), "Raw Gold"));
		self::register("raw_iron", new Item(new IID(Ids::RAW_IRON), "Raw Iron"));
		self::register("raw_mutton", new RawMutton(new IID(Ids::RAW_MUTTON), "Raw Mutton"));
		self::register("raw_porkchop", new RawPorkchop(new IID(Ids::RAW_PORKCHOP), "Raw Porkchop"));
		self::register("raw_rabbit", new RawRabbit(new IID(Ids::RAW_RABBIT), "Raw Rabbit"));
		self::register("raw_salmon", new RawSalmon(new IID(Ids::RAW_SALMON), "Raw Salmon"));
		self::register("record_11", new Record(new IID(Ids::RECORD_11), RecordType::DISK_11(), "Record 11"));
		self::register("record_13", new Record(new IID(Ids::RECORD_13), RecordType::DISK_13(), "Record 13"));
		self::register("record_5", new Record(new IID(Ids::RECORD_5), RecordType::DISK_5(), "Record 5"));
		self::register("record_blocks", new Record(new IID(Ids::RECORD_BLOCKS), RecordType::DISK_BLOCKS(), "Record Blocks"));
		self::register("record_cat", new Record(new IID(Ids::RECORD_CAT), RecordType::DISK_CAT(), "Record Cat"));
		self::register("record_chirp", new Record(new IID(Ids::RECORD_CHIRP), RecordType::DISK_CHIRP(), "Record Chirp"));
		self::register("record_far", new Record(new IID(Ids::RECORD_FAR), RecordType::DISK_FAR(), "Record Far"));
		self::register("record_mall", new Record(new IID(Ids::RECORD_MALL), RecordType::DISK_MALL(), "Record Mall"));
		self::register("record_mellohi", new Record(new IID(Ids::RECORD_MELLOHI), RecordType::DISK_MELLOHI(), "Record Mellohi"));
		self::register("record_otherside", new Record(new IID(Ids::RECORD_OTHERSIDE), RecordType::DISK_OTHERSIDE(), "Record Otherside"));
		self::register("record_pigstep", new Record(new IID(Ids::RECORD_PIGSTEP), RecordType::DISK_PIGSTEP(), "Record Pigstep"));
		self::register("record_stal", new Record(new IID(Ids::RECORD_STAL), RecordType::DISK_STAL(), "Record Stal"));
		self::register("record_strad", new Record(new IID(Ids::RECORD_STRAD), RecordType::DISK_STRAD(), "Record Strad"));
		self::register("record_wait", new Record(new IID(Ids::RECORD_WAIT), RecordType::DISK_WAIT(), "Record Wait"));
		self::register("record_ward", new Record(new IID(Ids::RECORD_WARD), RecordType::DISK_WARD(), "Record Ward"));
		self::register("redstone_dust", new Redstone(new IID(Ids::REDSTONE_DUST), "Redstone"));
		self::register("rotten_flesh", new RottenFlesh(new IID(Ids::ROTTEN_FLESH), "Rotten Flesh"));
		self::register("scute", new Item(new IID(Ids::SCUTE), "Scute"));
		self::register("shears", new Shears(new IID(Ids::SHEARS), "Shears"));
		self::register("shulker_shell", new Item(new IID(Ids::SHULKER_SHELL), "Shulker Shell"));
		self::register("slimeball", new Item(new IID(Ids::SLIMEBALL), "Slimeball"));
		self::register("snowball", new Snowball(new IID(Ids::SNOWBALL), "Snowball"));
		self::register("spider_eye", new SpiderEye(new IID(Ids::SPIDER_EYE), "Spider Eye"));
		self::register("splash_potion", new SplashPotion(new IID(Ids::SPLASH_POTION), "Splash Potion"));
		self::register("spruce_sign", new ItemBlockWallOrFloor(new IID(Ids::SPRUCE_SIGN), Blocks::SPRUCE_SIGN(), Blocks::SPRUCE_WALL_SIGN()));
		self::register("spyglass", new Spyglass(new IID(Ids::SPYGLASS), "Spyglass"));
		self::register("steak", new Steak(new IID(Ids::STEAK), "Steak"));
		self::register("stick", new Stick(new IID(Ids::STICK), "Stick"));
		self::register("string", new StringItem(new IID(Ids::STRING), "String"));
		self::register("sugar", new Item(new IID(Ids::SUGAR), "Sugar"));
		self::register("suspicious_stew", new SuspiciousStew(new IID(Ids::SUSPICIOUS_STEW), "Suspicious Stew"));
		self::register("sweet_berries", new SweetBerries(new IID(Ids::SWEET_BERRIES), "Sweet Berries"));
		self::register("totem", new Totem(new IID(Ids::TOTEM), "Totem of Undying"));
		self::register("warped_sign", new ItemBlockWallOrFloor(new IID(Ids::WARPED_SIGN), Blocks::WARPED_SIGN(), Blocks::WARPED_WALL_SIGN()));
		self::register("water_bucket", new LiquidBucket(new IID(Ids::WATER_BUCKET), "Water Bucket", Blocks::WATER()));
		self::register("wheat", new Item(new IID(Ids::WHEAT), "Wheat"));
		self::register("wheat_seeds", new WheatSeeds(new IID(Ids::WHEAT_SEEDS), "Wheat Seeds"));
		self::register("writable_book", new WritableBook(new IID(Ids::WRITABLE_BOOK), "Book & Quill"));
		self::register("written_book", new WrittenBook(new IID(Ids::WRITTEN_BOOK), "Written Book"));

		foreach(BoatType::getAll() as $type){
			//boat type is static, because different types of wood may have different properties
			self::register($type->name() . "_boat", new Boat(new IID(match($type){
				BoatType::OAK() => Ids::OAK_BOAT,
				BoatType::SPRUCE() => Ids::SPRUCE_BOAT,
				BoatType::BIRCH() => Ids::BIRCH_BOAT,
				BoatType::JUNGLE() => Ids::JUNGLE_BOAT,
				BoatType::ACACIA() => Ids::ACACIA_BOAT,
				BoatType::DARK_OAK() => Ids::DARK_OAK_BOAT,
				BoatType::MANGROVE() => Ids::MANGROVE_BOAT,
				default => throw new AssumptionFailedError("Unhandled tree type " . $type->name())
			}), $type->getDisplayName() . " Boat", $type));
		}
	}

	private static function registerSpawnEggs() : void{
		self::register("zombie_spawn_egg", new class(new IID(Ids::ZOMBIE_SPAWN_EGG), "Zombie Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Zombie(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		self::register("squid_spawn_egg", new class(new IID(Ids::SQUID_SPAWN_EGG), "Squid Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Squid(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		self::register("villager_spawn_egg", new class(new IID(Ids::VILLAGER_SPAWN_EGG), "Villager Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Villager(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
	}

	private static function registerTierToolItems() : void{
		self::register("diamond_axe", new Axe(new IID(Ids::DIAMOND_AXE), "Diamond Axe", ToolTier::DIAMOND()));
		self::register("golden_axe", new Axe(new IID(Ids::GOLDEN_AXE), "Golden Axe", ToolTier::GOLD()));
		self::register("iron_axe", new Axe(new IID(Ids::IRON_AXE), "Iron Axe", ToolTier::IRON()));
		self::register("netherite_axe", new Axe(new IID(Ids::NETHERITE_AXE), "Netherite Axe", ToolTier::NETHERITE()));
		self::register("stone_axe", new Axe(new IID(Ids::STONE_AXE), "Stone Axe", ToolTier::STONE()));
		self::register("wooden_axe", new Axe(new IID(Ids::WOODEN_AXE), "Wooden Axe", ToolTier::WOOD()));
		self::register("diamond_hoe", new Hoe(new IID(Ids::DIAMOND_HOE), "Diamond Hoe", ToolTier::DIAMOND()));
		self::register("golden_hoe", new Hoe(new IID(Ids::GOLDEN_HOE), "Golden Hoe", ToolTier::GOLD()));
		self::register("iron_hoe", new Hoe(new IID(Ids::IRON_HOE), "Iron Hoe", ToolTier::IRON()));
		self::register("netherite_hoe", new Hoe(new IID(Ids::NETHERITE_HOE), "Netherite Hoe", ToolTier::NETHERITE()));
		self::register("stone_hoe", new Hoe(new IID(Ids::STONE_HOE), "Stone Hoe", ToolTier::STONE()));
		self::register("wooden_hoe", new Hoe(new IID(Ids::WOODEN_HOE), "Wooden Hoe", ToolTier::WOOD()));
		self::register("diamond_pickaxe", new Pickaxe(new IID(Ids::DIAMOND_PICKAXE), "Diamond Pickaxe", ToolTier::DIAMOND()));
		self::register("golden_pickaxe", new Pickaxe(new IID(Ids::GOLDEN_PICKAXE), "Golden Pickaxe", ToolTier::GOLD()));
		self::register("iron_pickaxe", new Pickaxe(new IID(Ids::IRON_PICKAXE), "Iron Pickaxe", ToolTier::IRON()));
		self::register("netherite_pickaxe", new Pickaxe(new IID(Ids::NETHERITE_PICKAXE), "Netherite Pickaxe", ToolTier::NETHERITE()));
		self::register("stone_pickaxe", new Pickaxe(new IID(Ids::STONE_PICKAXE), "Stone Pickaxe", ToolTier::STONE()));
		self::register("wooden_pickaxe", new Pickaxe(new IID(Ids::WOODEN_PICKAXE), "Wooden Pickaxe", ToolTier::WOOD()));
		self::register("diamond_shovel", new Shovel(new IID(Ids::DIAMOND_SHOVEL), "Diamond Shovel", ToolTier::DIAMOND()));
		self::register("golden_shovel", new Shovel(new IID(Ids::GOLDEN_SHOVEL), "Golden Shovel", ToolTier::GOLD()));
		self::register("iron_shovel", new Shovel(new IID(Ids::IRON_SHOVEL), "Iron Shovel", ToolTier::IRON()));
		self::register("netherite_shovel", new Shovel(new IID(Ids::NETHERITE_SHOVEL), "Netherite Shovel", ToolTier::NETHERITE()));
		self::register("stone_shovel", new Shovel(new IID(Ids::STONE_SHOVEL), "Stone Shovel", ToolTier::STONE()));
		self::register("wooden_shovel", new Shovel(new IID(Ids::WOODEN_SHOVEL), "Wooden Shovel", ToolTier::WOOD()));
		self::register("diamond_sword", new Sword(new IID(Ids::DIAMOND_SWORD), "Diamond Sword", ToolTier::DIAMOND()));
		self::register("golden_sword", new Sword(new IID(Ids::GOLDEN_SWORD), "Golden Sword", ToolTier::GOLD()));
		self::register("iron_sword", new Sword(new IID(Ids::IRON_SWORD), "Iron Sword", ToolTier::IRON()));
		self::register("netherite_sword", new Sword(new IID(Ids::NETHERITE_SWORD), "Netherite Sword", ToolTier::NETHERITE()));
		self::register("stone_sword", new Sword(new IID(Ids::STONE_SWORD), "Stone Sword", ToolTier::STONE()));
		self::register("wooden_sword", new Sword(new IID(Ids::WOODEN_SWORD), "Wooden Sword", ToolTier::WOOD()));
	}

	private static function registerArmorItems() : void{
		self::register("chainmail_boots", new Armor(new IID(Ids::CHAINMAIL_BOOTS), "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET)));
		self::register("diamond_boots", new Armor(new IID(Ids::DIAMOND_BOOTS), "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET, 2)));
		self::register("golden_boots", new Armor(new IID(Ids::GOLDEN_BOOTS), "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET)));
		self::register("iron_boots", new Armor(new IID(Ids::IRON_BOOTS), "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET)));
		self::register("leather_boots", new Armor(new IID(Ids::LEATHER_BOOTS), "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET)));
		self::register("netherite_boots", new Armor(new IID(Ids::NETHERITE_BOOTS), "Netherite Boots", new ArmorTypeInfo(3, 482, ArmorInventory::SLOT_FEET, 3, true)));

		self::register("chainmail_chestplate", new Armor(new IID(Ids::CHAINMAIL_CHESTPLATE), "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST)));
		self::register("diamond_chestplate", new Armor(new IID(Ids::DIAMOND_CHESTPLATE), "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST, 2)));
		self::register("golden_chestplate", new Armor(new IID(Ids::GOLDEN_CHESTPLATE), "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST)));
		self::register("iron_chestplate", new Armor(new IID(Ids::IRON_CHESTPLATE), "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST)));
		self::register("leather_tunic", new Armor(new IID(Ids::LEATHER_TUNIC), "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST)));
		self::register("netherite_chestplate", new Armor(new IID(Ids::NETHERITE_CHESTPLATE), "Netherite Chestplate", new ArmorTypeInfo(8, 593, ArmorInventory::SLOT_CHEST, 3, true)));

		self::register("chainmail_helmet", new Armor(new IID(Ids::CHAINMAIL_HELMET), "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		self::register("diamond_helmet", new Armor(new IID(Ids::DIAMOND_HELMET), "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD, 2)));
		self::register("golden_helmet", new Armor(new IID(Ids::GOLDEN_HELMET), "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD)));
		self::register("iron_helmet", new Armor(new IID(Ids::IRON_HELMET), "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		self::register("leather_cap", new Armor(new IID(Ids::LEATHER_CAP), "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD)));
		self::register("netherite_helmet", new Armor(new IID(Ids::NETHERITE_HELMET), "Netherite Helmet", new ArmorTypeInfo(3, 408, ArmorInventory::SLOT_HEAD, 3, true)));
		self::register("turtle_helmet", new TurtleHelmet(new IID(Ids::TURTLE_HELMET), "Turtle Shell", new ArmorTypeInfo(2, 276, ArmorInventory::SLOT_HEAD)));

		self::register("chainmail_leggings", new Armor(new IID(Ids::CHAINMAIL_LEGGINGS), "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS)));
		self::register("diamond_leggings", new Armor(new IID(Ids::DIAMOND_LEGGINGS), "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS, 2)));
		self::register("golden_leggings", new Armor(new IID(Ids::GOLDEN_LEGGINGS), "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS)));
		self::register("iron_leggings", new Armor(new IID(Ids::IRON_LEGGINGS), "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS)));
		self::register("leather_pants", new Armor(new IID(Ids::LEATHER_PANTS), "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS)));
		self::register("netherite_leggings", new Armor(new IID(Ids::NETHERITE_LEGGINGS), "Netherite Leggings", new ArmorTypeInfo(6, 556, ArmorInventory::SLOT_LEGS, 3, true)));
	}

}
