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
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\enchantment\ItemEnchantmentTags as EnchantmentTags;
use pocketmine\item\ItemIdentifier as IID;
use pocketmine\item\ItemTypeIds as Ids;
use pocketmine\item\VanillaArmorMaterials as ArmorMaterials;
use pocketmine\math\Vector3;
use pocketmine\utils\CloningRegistryTrait;
use pocketmine\world\World;
use function strtolower;

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
 * @method static ItemBlockWallOrFloor CHERRY_SIGN()
 * @method static ChorusFruit CHORUS_FRUIT()
 * @method static Item CLAY()
 * @method static Clock CLOCK()
 * @method static Clownfish CLOWNFISH()
 * @method static Coal COAL()
 * @method static Item COAST_ARMOR_TRIM_SMITHING_TEMPLATE()
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
 * @method static Item DUNE_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static Dye DYE()
 * @method static Item ECHO_SHARD()
 * @method static Egg EGG()
 * @method static Item EMERALD()
 * @method static EnchantedBook ENCHANTED_BOOK()
 * @method static GoldenAppleEnchanted ENCHANTED_GOLDEN_APPLE()
 * @method static EnderPearl ENDER_PEARL()
 * @method static ExperienceBottle EXPERIENCE_BOTTLE()
 * @method static Item EYE_ARMOR_TRIM_SMITHING_TEMPLATE()
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
 * @method static Item HOST_ARMOR_TRIM_SMITHING_TEMPLATE()
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
 * @method static NameTag NAME_TAG()
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
 * @method static Item NETHERITE_UPGRADE_SMITHING_TEMPLATE()
 * @method static Item NETHER_BRICK()
 * @method static Item NETHER_QUARTZ()
 * @method static Item NETHER_STAR()
 * @method static Boat OAK_BOAT()
 * @method static ItemBlockWallOrFloor OAK_SIGN()
 * @method static PaintingItem PAINTING()
 * @method static Item PAPER()
 * @method static Item PHANTOM_MEMBRANE()
 * @method static PitcherPod PITCHER_POD()
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
 * @method static Item RAISER_ARMOR_TRIM_SMITHING_TEMPLATE()
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
 * @method static Item RIB_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static RottenFlesh ROTTEN_FLESH()
 * @method static Item SCUTE()
 * @method static Item SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static Item SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static Shears SHEARS()
 * @method static Item SHULKER_SHELL()
 * @method static Item SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static Item SLIMEBALL()
 * @method static Item SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static Snowball SNOWBALL()
 * @method static SpiderEye SPIDER_EYE()
 * @method static Item SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE()
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
 * @method static Item TIDE_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static TorchflowerSeeds TORCHFLOWER_SEEDS()
 * @method static Totem TOTEM()
 * @method static TurtleHelmet TURTLE_HELMET()
 * @method static Item VEX_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static SpawnEgg VILLAGER_SPAWN_EGG()
 * @method static Item WARD_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static ItemBlockWallOrFloor WARPED_SIGN()
 * @method static LiquidBucket WATER_BUCKET()
 * @method static Item WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE()
 * @method static Item WHEAT()
 * @method static WheatSeeds WHEAT_SEEDS()
 * @method static Item WILD_ARMOR_TRIM_SMITHING_TEMPLATE()
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

	private static int $nextTypeId = 20_000;

	private static function newIID() : IID{
		//TODO: IID barely has a reason to exist anymore - perhaps we should just use integers directly in PM6
		return new IID(self::$nextTypeId++);
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
		self::registerSmithingTemplates();

		self::register("air", Blocks::AIR()->asItem()->setCount(0));

		self::register("acacia_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::ACACIA_SIGN(), Blocks::ACACIA_WALL_SIGN()));
		self::register("amethyst_shard", new Item(self::newIID(), "Amethyst Shard"));
		self::register("apple", new Apple(self::newIID(), "Apple"));
		self::register("arrow", new Arrow(self::newIID(), "Arrow"));
		self::register("baked_potato", new BakedPotato(self::newIID(), "Baked Potato"));
		self::register("bamboo", new Bamboo(self::newIID(), "Bamboo"));
		self::register("banner", new Banner(self::newIID(), Blocks::BANNER(), Blocks::WALL_BANNER()));
		self::register("beetroot", new Beetroot(self::newIID(), "Beetroot"));
		self::register("beetroot_seeds", new BeetrootSeeds(self::newIID(), "Beetroot Seeds"));
		self::register("beetroot_soup", new BeetrootSoup(self::newIID(), "Beetroot Soup"));
		self::register("birch_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::BIRCH_SIGN(), Blocks::BIRCH_WALL_SIGN()));
		self::register("blaze_powder", new Item(self::newIID(), "Blaze Powder"));
		self::register("blaze_rod", new BlazeRod(self::newIID(), "Blaze Rod"));
		self::register("bleach", new Item(self::newIID(), "Bleach"));
		self::register("bone", new Item(self::newIID(), "Bone"));
		self::register("bone_meal", new Fertilizer(self::newIID(), "Bone Meal"));
		self::register("book", new Book(self::newIID(), "Book", [EnchantmentTags::ALL]));
		self::register("bow", new Bow(self::newIID(), "Bow", [EnchantmentTags::BOW]));
		self::register("bowl", new Bowl(self::newIID(), "Bowl"));
		self::register("bread", new Bread(self::newIID(), "Bread"));
		self::register("brick", new Item(self::newIID(), "Brick"));
		self::register("bucket", new Bucket(self::newIID(), "Bucket"));
		self::register("carrot", new Carrot(self::newIID(), "Carrot"));
		self::register("charcoal", new Coal(self::newIID(), "Charcoal"));
		self::register("cherry_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::CHERRY_SIGN(), Blocks::CHERRY_WALL_SIGN()));
		self::register("chemical_aluminium_oxide", new Item(self::newIID(), "Aluminium Oxide"));
		self::register("chemical_ammonia", new Item(self::newIID(), "Ammonia"));
		self::register("chemical_barium_sulphate", new Item(self::newIID(), "Barium Sulphate"));
		self::register("chemical_benzene", new Item(self::newIID(), "Benzene"));
		self::register("chemical_boron_trioxide", new Item(self::newIID(), "Boron Trioxide"));
		self::register("chemical_calcium_bromide", new Item(self::newIID(), "Calcium Bromide"));
		self::register("chemical_calcium_chloride", new Item(self::newIID(), "Calcium Chloride"));
		self::register("chemical_cerium_chloride", new Item(self::newIID(), "Cerium Chloride"));
		self::register("chemical_charcoal", new Item(self::newIID(), "Charcoal"));
		self::register("chemical_crude_oil", new Item(self::newIID(), "Crude Oil"));
		self::register("chemical_glue", new Item(self::newIID(), "Glue"));
		self::register("chemical_hydrogen_peroxide", new Item(self::newIID(), "Hydrogen Peroxide"));
		self::register("chemical_hypochlorite", new Item(self::newIID(), "Hypochlorite"));
		self::register("chemical_ink", new Item(self::newIID(), "Ink"));
		self::register("chemical_iron_sulphide", new Item(self::newIID(), "Iron Sulphide"));
		self::register("chemical_latex", new Item(self::newIID(), "Latex"));
		self::register("chemical_lithium_hydride", new Item(self::newIID(), "Lithium Hydride"));
		self::register("chemical_luminol", new Item(self::newIID(), "Luminol"));
		self::register("chemical_magnesium_nitrate", new Item(self::newIID(), "Magnesium Nitrate"));
		self::register("chemical_magnesium_oxide", new Item(self::newIID(), "Magnesium Oxide"));
		self::register("chemical_magnesium_salts", new Item(self::newIID(), "Magnesium Salts"));
		self::register("chemical_mercuric_chloride", new Item(self::newIID(), "Mercuric Chloride"));
		self::register("chemical_polyethylene", new Item(self::newIID(), "Polyethylene"));
		self::register("chemical_potassium_chloride", new Item(self::newIID(), "Potassium Chloride"));
		self::register("chemical_potassium_iodide", new Item(self::newIID(), "Potassium Iodide"));
		self::register("chemical_rubbish", new Item(self::newIID(), "Rubbish"));
		self::register("chemical_salt", new Item(self::newIID(), "Salt"));
		self::register("chemical_soap", new Item(self::newIID(), "Soap"));
		self::register("chemical_sodium_acetate", new Item(self::newIID(), "Sodium Acetate"));
		self::register("chemical_sodium_fluoride", new Item(self::newIID(), "Sodium Fluoride"));
		self::register("chemical_sodium_hydride", new Item(self::newIID(), "Sodium Hydride"));
		self::register("chemical_sodium_hydroxide", new Item(self::newIID(), "Sodium Hydroxide"));
		self::register("chemical_sodium_hypochlorite", new Item(self::newIID(), "Sodium Hypochlorite"));
		self::register("chemical_sodium_oxide", new Item(self::newIID(), "Sodium Oxide"));
		self::register("chemical_sugar", new Item(self::newIID(), "Sugar"));
		self::register("chemical_sulphate", new Item(self::newIID(), "Sulphate"));
		self::register("chemical_tungsten_chloride", new Item(self::newIID(), "Tungsten Chloride"));
		self::register("chemical_water", new Item(self::newIID(), "Water"));
		self::register("chorus_fruit", new ChorusFruit(self::newIID(), "Chorus Fruit"));
		self::register("clay", new Item(self::newIID(), "Clay"));
		self::register("clock", new Clock(self::newIID(), "Clock"));
		self::register("clownfish", new Clownfish(self::newIID(), "Clownfish"));
		self::register("coal", new Coal(self::newIID(), "Coal"));
		self::register("cocoa_beans", new CocoaBeans(self::newIID(), "Cocoa Beans"));
		self::register("compass", new Compass(self::newIID(), "Compass", [EnchantmentTags::COMPASS]));
		self::register("cooked_chicken", new CookedChicken(self::newIID(), "Cooked Chicken"));
		self::register("cooked_fish", new CookedFish(self::newIID(), "Cooked Fish"));
		self::register("cooked_mutton", new CookedMutton(self::newIID(), "Cooked Mutton"));
		self::register("cooked_porkchop", new CookedPorkchop(self::newIID(), "Cooked Porkchop"));
		self::register("cooked_rabbit", new CookedRabbit(self::newIID(), "Cooked Rabbit"));
		self::register("cooked_salmon", new CookedSalmon(self::newIID(), "Cooked Salmon"));
		self::register("cookie", new Cookie(self::newIID(), "Cookie"));
		self::register("copper_ingot", new Item(self::newIID(), "Copper Ingot"));
		self::register("coral_fan", new CoralFan(self::newIID()));
		self::register("crimson_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::CRIMSON_SIGN(), Blocks::CRIMSON_WALL_SIGN()));
		self::register("dark_oak_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::DARK_OAK_SIGN(), Blocks::DARK_OAK_WALL_SIGN()));
		self::register("diamond", new Item(self::newIID(), "Diamond"));
		self::register("disc_fragment_5", new Item(self::newIID(), "Disc Fragment (5)"));
		self::register("dragon_breath", new Item(self::newIID(), "Dragon's Breath"));
		self::register("dried_kelp", new DriedKelp(self::newIID(), "Dried Kelp"));
		//TODO: add interface to dye-colour objects
		self::register("dye", new Dye(self::newIID(), "Dye"));
		self::register("echo_shard", new Item(self::newIID(), "Echo Shard"));
		self::register("egg", new Egg(self::newIID(), "Egg"));
		self::register("emerald", new Item(self::newIID(), "Emerald"));
		self::register("enchanted_book", new EnchantedBook(self::newIID(), "Enchanted Book", [EnchantmentTags::ALL]));
		self::register("enchanted_golden_apple", new GoldenAppleEnchanted(self::newIID(), "Enchanted Golden Apple"));
		self::register("ender_pearl", new EnderPearl(self::newIID(), "Ender Pearl"));
		self::register("experience_bottle", new ExperienceBottle(self::newIID(), "Bottle o' Enchanting"));
		self::register("feather", new Item(self::newIID(), "Feather"));
		self::register("fermented_spider_eye", new Item(self::newIID(), "Fermented Spider Eye"));
		self::register("fire_charge", new FireCharge(self::newIID(), "Fire Charge"));
		self::register("fishing_rod", new FishingRod(self::newIID(), "Fishing Rod", [EnchantmentTags::FISHING_ROD]));
		self::register("flint", new Item(self::newIID(), "Flint"));
		self::register("flint_and_steel", new FlintSteel(self::newIID(), "Flint and Steel", [EnchantmentTags::FLINT_AND_STEEL]));
		self::register("ghast_tear", new Item(self::newIID(), "Ghast Tear"));
		self::register("glass_bottle", new GlassBottle(self::newIID(), "Glass Bottle"));
		self::register("glistering_melon", new Item(self::newIID(), "Glistering Melon"));
		self::register("glow_berries", new GlowBerries(self::newIID(), "Glow Berries"));
		self::register("glow_ink_sac", new Item(self::newIID(), "Glow Ink Sac"));
		self::register("glowstone_dust", new Item(self::newIID(), "Glowstone Dust"));
		self::register("gold_ingot", new Item(self::newIID(), "Gold Ingot"));
		self::register("gold_nugget", new Item(self::newIID(), "Gold Nugget"));
		self::register("golden_apple", new GoldenApple(self::newIID(), "Golden Apple"));
		self::register("golden_carrot", new GoldenCarrot(self::newIID(), "Golden Carrot"));
		self::register("gunpowder", new Item(self::newIID(), "Gunpowder"));
		self::register("heart_of_the_sea", new Item(self::newIID(), "Heart of the Sea"));
		self::register("honey_bottle", new HoneyBottle(self::newIID(), "Honey Bottle"));
		self::register("honeycomb", new Item(self::newIID(), "Honeycomb"));
		self::register("ink_sac", new Item(self::newIID(), "Ink Sac"));
		self::register("iron_ingot", new Item(self::newIID(), "Iron Ingot"));
		self::register("iron_nugget", new Item(self::newIID(), "Iron Nugget"));
		self::register("jungle_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::JUNGLE_SIGN(), Blocks::JUNGLE_WALL_SIGN()));
		self::register("lapis_lazuli", new Item(self::newIID(), "Lapis Lazuli"));
		self::register("lava_bucket", new LiquidBucket(self::newIID(), "Lava Bucket", Blocks::LAVA()));
		self::register("leather", new Item(self::newIID(), "Leather"));
		self::register("magma_cream", new Item(self::newIID(), "Magma Cream"));
		self::register("mangrove_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::MANGROVE_SIGN(), Blocks::MANGROVE_WALL_SIGN()));
		self::register("medicine", new Medicine(self::newIID(), "Medicine"));
		self::register("melon", new Melon(self::newIID(), "Melon"));
		self::register("melon_seeds", new MelonSeeds(self::newIID(), "Melon Seeds"));
		self::register("milk_bucket", new MilkBucket(self::newIID(), "Milk Bucket"));
		self::register("minecart", new Minecart(self::newIID(), "Minecart"));
		self::register("mushroom_stew", new MushroomStew(self::newIID(), "Mushroom Stew"));
		self::register("name_tag", new NameTag(self::newIID(), "Name Tag"));
		self::register("nautilus_shell", new Item(self::newIID(), "Nautilus Shell"));
		self::register("nether_brick", new Item(self::newIID(), "Nether Brick"));
		self::register("nether_quartz", new Item(self::newIID(), "Nether Quartz"));
		self::register("nether_star", new Item(self::newIID(), "Nether Star"));
		self::register("netherite_ingot", new class(self::newIID(), "Netherite Ingot") extends Item{
			public function isFireProof() : bool{ return true; }
		});
		self::register("netherite_scrap", new class(self::newIID(), "Netherite Scrap") extends Item{
			public function isFireProof() : bool{ return true; }
		});
		self::register("oak_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::OAK_SIGN(), Blocks::OAK_WALL_SIGN()));
		self::register("painting", new PaintingItem(self::newIID(), "Painting"));
		self::register("paper", new Item(self::newIID(), "Paper"));
		self::register("phantom_membrane", new Item(self::newIID(), "Phantom Membrane"));
		self::register("pitcher_pod", new PitcherPod(self::newIID(), "Pitcher Pod"));
		self::register("poisonous_potato", new PoisonousPotato(self::newIID(), "Poisonous Potato"));
		self::register("popped_chorus_fruit", new Item(self::newIID(), "Popped Chorus Fruit"));
		self::register("potato", new Potato(self::newIID(), "Potato"));
		self::register("potion", new Potion(self::newIID(), "Potion"));
		self::register("prismarine_crystals", new Item(self::newIID(), "Prismarine Crystals"));
		self::register("prismarine_shard", new Item(self::newIID(), "Prismarine Shard"));
		self::register("pufferfish", new Pufferfish(self::newIID(), "Pufferfish"));
		self::register("pumpkin_pie", new PumpkinPie(self::newIID(), "Pumpkin Pie"));
		self::register("pumpkin_seeds", new PumpkinSeeds(self::newIID(), "Pumpkin Seeds"));
		self::register("rabbit_foot", new Item(self::newIID(), "Rabbit's Foot"));
		self::register("rabbit_hide", new Item(self::newIID(), "Rabbit Hide"));
		self::register("rabbit_stew", new RabbitStew(self::newIID(), "Rabbit Stew"));
		self::register("raw_beef", new RawBeef(self::newIID(), "Raw Beef"));
		self::register("raw_chicken", new RawChicken(self::newIID(), "Raw Chicken"));
		self::register("raw_copper", new Item(self::newIID(), "Raw Copper"));
		self::register("raw_fish", new RawFish(self::newIID(), "Raw Fish"));
		self::register("raw_gold", new Item(self::newIID(), "Raw Gold"));
		self::register("raw_iron", new Item(self::newIID(), "Raw Iron"));
		self::register("raw_mutton", new RawMutton(self::newIID(), "Raw Mutton"));
		self::register("raw_porkchop", new RawPorkchop(self::newIID(), "Raw Porkchop"));
		self::register("raw_rabbit", new RawRabbit(self::newIID(), "Raw Rabbit"));
		self::register("raw_salmon", new RawSalmon(self::newIID(), "Raw Salmon"));
		self::register("record_11", new Record(self::newIID(), RecordType::DISK_11, "Record 11"));
		self::register("record_13", new Record(self::newIID(), RecordType::DISK_13, "Record 13"));
		self::register("record_5", new Record(self::newIID(), RecordType::DISK_5, "Record 5"));
		self::register("record_blocks", new Record(self::newIID(), RecordType::DISK_BLOCKS, "Record Blocks"));
		self::register("record_cat", new Record(self::newIID(), RecordType::DISK_CAT, "Record Cat"));
		self::register("record_chirp", new Record(self::newIID(), RecordType::DISK_CHIRP, "Record Chirp"));
		self::register("record_far", new Record(self::newIID(), RecordType::DISK_FAR, "Record Far"));
		self::register("record_mall", new Record(self::newIID(), RecordType::DISK_MALL, "Record Mall"));
		self::register("record_mellohi", new Record(self::newIID(), RecordType::DISK_MELLOHI, "Record Mellohi"));
		self::register("record_otherside", new Record(self::newIID(), RecordType::DISK_OTHERSIDE, "Record Otherside"));
		self::register("record_pigstep", new Record(self::newIID(), RecordType::DISK_PIGSTEP, "Record Pigstep"));
		self::register("record_stal", new Record(self::newIID(), RecordType::DISK_STAL, "Record Stal"));
		self::register("record_strad", new Record(self::newIID(), RecordType::DISK_STRAD, "Record Strad"));
		self::register("record_wait", new Record(self::newIID(), RecordType::DISK_WAIT, "Record Wait"));
		self::register("record_ward", new Record(self::newIID(), RecordType::DISK_WARD, "Record Ward"));
		self::register("redstone_dust", new Redstone(self::newIID(), "Redstone"));
		self::register("rotten_flesh", new RottenFlesh(self::newIID(), "Rotten Flesh"));
		self::register("scute", new Item(self::newIID(), "Scute"));
		self::register("shears", new Shears(self::newIID(), "Shears", [EnchantmentTags::SHEARS]));
		self::register("shulker_shell", new Item(self::newIID(), "Shulker Shell"));
		self::register("slimeball", new Item(self::newIID(), "Slimeball"));
		self::register("snowball", new Snowball(self::newIID(), "Snowball"));
		self::register("spider_eye", new SpiderEye(self::newIID(), "Spider Eye"));
		self::register("splash_potion", new SplashPotion(self::newIID(), "Splash Potion"));
		self::register("spruce_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::SPRUCE_SIGN(), Blocks::SPRUCE_WALL_SIGN()));
		self::register("spyglass", new Spyglass(self::newIID(), "Spyglass"));
		self::register("steak", new Steak(self::newIID(), "Steak"));
		self::register("stick", new Stick(self::newIID(), "Stick"));
		self::register("string", new StringItem(self::newIID(), "String"));
		self::register("sugar", new Item(self::newIID(), "Sugar"));
		self::register("suspicious_stew", new SuspiciousStew(self::newIID(), "Suspicious Stew"));
		self::register("sweet_berries", new SweetBerries(self::newIID(), "Sweet Berries"));
		self::register("torchflower_seeds", new TorchflowerSeeds(self::newIID(), "Torchflower Seeds"));
		self::register("totem", new Totem(self::newIID(), "Totem of Undying"));
		self::register("warped_sign", new ItemBlockWallOrFloor(self::newIID(), Blocks::WARPED_SIGN(), Blocks::WARPED_WALL_SIGN()));
		self::register("water_bucket", new LiquidBucket(self::newIID(), "Water Bucket", Blocks::WATER()));
		self::register("wheat", new Item(self::newIID(), "Wheat"));
		self::register("wheat_seeds", new WheatSeeds(self::newIID(), "Wheat Seeds"));
		self::register("writable_book", new WritableBook(self::newIID(), "Book & Quill"));
		self::register("written_book", new WrittenBook(self::newIID(), "Written Book"));

		foreach(BoatType::cases() as $type){
			//boat type is static, because different types of wood may have different properties
			self::register(strtolower($type->name) . "_boat", new Boat(self::newIID(), $type->getDisplayName() . " Boat", $type));
		}
	}

	private static function registerSpawnEggs() : void{
		self::register("zombie_spawn_egg", new class(self::newIID(), "Zombie Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Zombie(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		self::register("squid_spawn_egg", new class(self::newIID(), "Squid Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Squid(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		self::register("villager_spawn_egg", new class(self::newIID(), "Villager Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Villager(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
	}

	private static function registerTierToolItems() : void{
		self::register("diamond_axe", new Axe(self::newIID(), "Diamond Axe", ToolTier::DIAMOND, [EnchantmentTags::AXE]));
		self::register("golden_axe", new Axe(self::newIID(), "Golden Axe", ToolTier::GOLD, [EnchantmentTags::AXE]));
		self::register("iron_axe", new Axe(self::newIID(), "Iron Axe", ToolTier::IRON, [EnchantmentTags::AXE]));
		self::register("netherite_axe", new Axe(self::newIID(), "Netherite Axe", ToolTier::NETHERITE, [EnchantmentTags::AXE]));
		self::register("stone_axe", new Axe(self::newIID(), "Stone Axe", ToolTier::STONE, [EnchantmentTags::AXE]));
		self::register("wooden_axe", new Axe(self::newIID(), "Wooden Axe", ToolTier::WOOD, [EnchantmentTags::AXE]));
		self::register("diamond_hoe", new Hoe(self::newIID(), "Diamond Hoe", ToolTier::DIAMOND, [EnchantmentTags::HOE]));
		self::register("golden_hoe", new Hoe(self::newIID(), "Golden Hoe", ToolTier::GOLD, [EnchantmentTags::HOE]));
		self::register("iron_hoe", new Hoe(self::newIID(), "Iron Hoe", ToolTier::IRON, [EnchantmentTags::HOE]));
		self::register("netherite_hoe", new Hoe(self::newIID(), "Netherite Hoe", ToolTier::NETHERITE, [EnchantmentTags::HOE]));
		self::register("stone_hoe", new Hoe(self::newIID(), "Stone Hoe", ToolTier::STONE, [EnchantmentTags::HOE]));
		self::register("wooden_hoe", new Hoe(self::newIID(), "Wooden Hoe", ToolTier::WOOD, [EnchantmentTags::HOE]));
		self::register("diamond_pickaxe", new Pickaxe(self::newIID(), "Diamond Pickaxe", ToolTier::DIAMOND, [EnchantmentTags::PICKAXE]));
		self::register("golden_pickaxe", new Pickaxe(self::newIID(), "Golden Pickaxe", ToolTier::GOLD, [EnchantmentTags::PICKAXE]));
		self::register("iron_pickaxe", new Pickaxe(self::newIID(), "Iron Pickaxe", ToolTier::IRON, [EnchantmentTags::PICKAXE]));
		self::register("netherite_pickaxe", new Pickaxe(self::newIID(), "Netherite Pickaxe", ToolTier::NETHERITE, [EnchantmentTags::PICKAXE]));
		self::register("stone_pickaxe", new Pickaxe(self::newIID(), "Stone Pickaxe", ToolTier::STONE, [EnchantmentTags::PICKAXE]));
		self::register("wooden_pickaxe", new Pickaxe(self::newIID(), "Wooden Pickaxe", ToolTier::WOOD, [EnchantmentTags::PICKAXE]));
		self::register("diamond_shovel", new Shovel(self::newIID(), "Diamond Shovel", ToolTier::DIAMOND, [EnchantmentTags::SHOVEL]));
		self::register("golden_shovel", new Shovel(self::newIID(), "Golden Shovel", ToolTier::GOLD, [EnchantmentTags::SHOVEL]));
		self::register("iron_shovel", new Shovel(self::newIID(), "Iron Shovel", ToolTier::IRON, [EnchantmentTags::SHOVEL]));
		self::register("netherite_shovel", new Shovel(self::newIID(), "Netherite Shovel", ToolTier::NETHERITE, [EnchantmentTags::SHOVEL]));
		self::register("stone_shovel", new Shovel(self::newIID(), "Stone Shovel", ToolTier::STONE, [EnchantmentTags::SHOVEL]));
		self::register("wooden_shovel", new Shovel(self::newIID(), "Wooden Shovel", ToolTier::WOOD, [EnchantmentTags::SHOVEL]));
		self::register("diamond_sword", new Sword(self::newIID(), "Diamond Sword", ToolTier::DIAMOND, [EnchantmentTags::SWORD]));
		self::register("golden_sword", new Sword(self::newIID(), "Golden Sword", ToolTier::GOLD, [EnchantmentTags::SWORD]));
		self::register("iron_sword", new Sword(self::newIID(), "Iron Sword", ToolTier::IRON, [EnchantmentTags::SWORD]));
		self::register("netherite_sword", new Sword(self::newIID(), "Netherite Sword", ToolTier::NETHERITE, [EnchantmentTags::SWORD]));
		self::register("stone_sword", new Sword(self::newIID(), "Stone Sword", ToolTier::STONE, [EnchantmentTags::SWORD]));
		self::register("wooden_sword", new Sword(self::newIID(), "Wooden Sword", ToolTier::WOOD, [EnchantmentTags::SWORD]));
	}

	private static function registerArmorItems() : void{
		self::register("chainmail_boots", new Armor(self::newIID(), "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::BOOTS]));
		self::register("diamond_boots", new Armor(self::newIID(), "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::BOOTS]));
		self::register("golden_boots", new Armor(self::newIID(), "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET, material: ArmorMaterials::GOLD()), [EnchantmentTags::BOOTS]));
		self::register("iron_boots", new Armor(self::newIID(), "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET, material: ArmorMaterials::IRON()), [EnchantmentTags::BOOTS]));
		self::register("leather_boots", new Armor(self::newIID(), "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET, material: ArmorMaterials::LEATHER()), [EnchantmentTags::BOOTS]));
		self::register("netherite_boots", new Armor(self::newIID(), "Netherite Boots", new ArmorTypeInfo(3, 482, ArmorInventory::SLOT_FEET, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::BOOTS]));

		self::register("chainmail_chestplate", new Armor(self::newIID(), "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::CHESTPLATE]));
		self::register("diamond_chestplate", new Armor(self::newIID(), "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::CHESTPLATE]));
		self::register("golden_chestplate", new Armor(self::newIID(), "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::GOLD()), [EnchantmentTags::CHESTPLATE]));
		self::register("iron_chestplate", new Armor(self::newIID(), "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::IRON()), [EnchantmentTags::CHESTPLATE]));
		self::register("leather_tunic", new Armor(self::newIID(), "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::LEATHER()), [EnchantmentTags::CHESTPLATE]));
		self::register("netherite_chestplate", new Armor(self::newIID(), "Netherite Chestplate", new ArmorTypeInfo(8, 593, ArmorInventory::SLOT_CHEST, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::CHESTPLATE]));

		self::register("chainmail_helmet", new Armor(self::newIID(), "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::HELMET]));
		self::register("diamond_helmet", new Armor(self::newIID(), "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::HELMET]));
		self::register("golden_helmet", new Armor(self::newIID(), "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::GOLD()), [EnchantmentTags::HELMET]));
		self::register("iron_helmet", new Armor(self::newIID(), "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::IRON()), [EnchantmentTags::HELMET]));
		self::register("leather_cap", new Armor(self::newIID(), "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::LEATHER()), [EnchantmentTags::HELMET]));
		self::register("netherite_helmet", new Armor(self::newIID(), "Netherite Helmet", new ArmorTypeInfo(3, 408, ArmorInventory::SLOT_HEAD, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::HELMET]));
		self::register("turtle_helmet", new TurtleHelmet(self::newIID(), "Turtle Shell", new ArmorTypeInfo(2, 276, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::TURTLE()), [EnchantmentTags::HELMET]));

		self::register("chainmail_leggings", new Armor(self::newIID(), "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::LEGGINGS]));
		self::register("diamond_leggings", new Armor(self::newIID(), "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::LEGGINGS]));
		self::register("golden_leggings", new Armor(self::newIID(), "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::GOLD()), [EnchantmentTags::LEGGINGS]));
		self::register("iron_leggings", new Armor(self::newIID(), "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::IRON()), [EnchantmentTags::LEGGINGS]));
		self::register("leather_pants", new Armor(self::newIID(), "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::LEATHER()), [EnchantmentTags::LEGGINGS]));
		self::register("netherite_leggings", new Armor(self::newIID(), "Netherite Leggings", new ArmorTypeInfo(6, 556, ArmorInventory::SLOT_LEGS, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::LEGGINGS]));
	}

	private static function registerSmithingTemplates() : void{
		self::register("netherite_upgrade_smithing_template", new Item(self::newIID(), "Netherite Upgrade Smithing Template"));
		self::register("coast_armor_trim_smithing_template", new Item(self::newIID(), "Coast Armor Trim Smithing Template"));
		self::register("dune_armor_trim_smithing_template", new Item(self::newIID(), "Dune Armor Trim Smithing Template"));
		self::register("eye_armor_trim_smithing_template", new Item(self::newIID(), "Eye Armor Trim Smithing Template"));
		self::register("host_armor_trim_smithing_template", new Item(self::newIID(), "Host Armor Trim Smithing Template"));
		self::register("raiser_armor_trim_smithing_template", new Item(self::newIID(), "Raiser Armor Trim Smithing Template"));
		self::register("rib_armor_trim_smithing_template", new Item(self::newIID(), "Rib Armor Trim Smithing Template"));
		self::register("sentry_armor_trim_smithing_template", new Item(self::newIID(), "Sentry Armor Trim Smithing Template"));
		self::register("shaper_armor_trim_smithing_template", new Item(self::newIID(), "Shaper Armor Trim Smithing Template"));
		self::register("silence_armor_trim_smithing_template", new Item(self::newIID(), "Silence Armor Trim Smithing Template"));
		self::register("snout_armor_trim_smithing_template", new Item(self::newIID(), "Snout Armor Trim Smithing Template"));
		self::register("spire_armor_trim_smithing_template", new Item(self::newIID(), "Spire Armor Trim Smithing Template"));
		self::register("tide_armor_trim_smithing_template", new Item(self::newIID(), "Tide Armor Trim Smithing Template"));
		self::register("vex_armor_trim_smithing_template", new Item(self::newIID(), "Vex Armor Trim Smithing Template"));
		self::register("ward_armor_trim_smithing_template", new Item(self::newIID(), "Ward Armor Trim Smithing Template"));
		self::register("wayfinder_armor_trim_smithing_template", new Item(self::newIID(), "Wayfinder Armor Trim Smithing Template"));
		self::register("wild_armor_trim_smithing_template", new Item(self::newIID(), "Wild Armor Trim Smithing Template"));
	}

}
