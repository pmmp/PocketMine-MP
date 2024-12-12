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
use pocketmine\item\VanillaArmorMaterials as ArmorMaterials;
use pocketmine\math\Vector3;
use pocketmine\utils\CloningRegistryTrait;
use pocketmine\world\World;
use function is_int;
use function mb_strtoupper;
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
 * @method static EndCrystal END_CRYSTAL()
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
 * @method static GoatHorn GOAT_HORN()
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
 * @method static IceBomb ICE_BOMB()
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
 * @method static Item RECOVERY_COMPASS()
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

	/**
	 * @phpstan-template TItem of Item
	 * @phpstan-param \Closure(IID) : TItem $createItem
	 * @phpstan-return TItem
	 */
	protected static function register(string $name, \Closure $createItem) : Item{
		//this sketchy hack allows us to avoid manually writing the constants inline
		//since type IDs are generated from this class anyway, I'm OK with this hack
		//nonetheless, we should try to get rid of it in a future major version (e.g by using string type IDs)
		$reflect = new \ReflectionClass(ItemTypeIds::class);
		$typeId = $reflect->getConstant(mb_strtoupper($name));
		if(!is_int($typeId)){
			//this allows registering new stuff without adding new type ID constants
			//this reduces the number of mandatory steps to test new features in local development
			\GlobalLogger::get()->error(self::class . ": No constant type ID found for $name, generating a new one");
			$typeId = ItemTypeIds::newId();
		}

		$item = $createItem(new IID($typeId));

		self::_registryRegister($name, $item);

		return $item;
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

		//this doesn't use the regular register() because it doesn't have an item typeID
		//in the future we'll probably want to dissociate this from the air block and make a proper null item
		self::_registryRegister("air", Blocks::AIR()->asItem()->setCount(0));

		self::register("acacia_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::ACACIA_SIGN(), Blocks::ACACIA_WALL_SIGN()));
		self::register("amethyst_shard", fn(IID $id) => new Item($id, "Amethyst Shard"));
		self::register("apple", fn(IID $id) => new Apple($id, "Apple"));
		self::register("arrow", fn(IID $id) => new Arrow($id, "Arrow"));
		self::register("baked_potato", fn(IID $id) => new BakedPotato($id, "Baked Potato"));
		self::register("bamboo", fn(IID $id) => new Bamboo($id, "Bamboo"));
		self::register("banner", fn(IID $id) => new Banner($id, Blocks::BANNER(), Blocks::WALL_BANNER()));
		self::register("beetroot", fn(IID $id) => new Beetroot($id, "Beetroot"));
		self::register("beetroot_seeds", fn(IID $id) => new BeetrootSeeds($id, "Beetroot Seeds"));
		self::register("beetroot_soup", fn(IID $id) => new BeetrootSoup($id, "Beetroot Soup"));
		self::register("birch_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::BIRCH_SIGN(), Blocks::BIRCH_WALL_SIGN()));
		self::register("blaze_powder", fn(IID $id) => new Item($id, "Blaze Powder"));
		self::register("blaze_rod", fn(IID $id) => new BlazeRod($id, "Blaze Rod"));
		self::register("bleach", fn(IID $id) => new Item($id, "Bleach"));
		self::register("bone", fn(IID $id) => new Item($id, "Bone"));
		self::register("bone_meal", fn(IID $id) => new Fertilizer($id, "Bone Meal"));
		self::register("book", fn(IID $id) => new Book($id, "Book", [EnchantmentTags::ALL]));
		self::register("bow", fn(IID $id) => new Bow($id, "Bow", [EnchantmentTags::BOW]));
		self::register("bowl", fn(IID $id) => new Bowl($id, "Bowl"));
		self::register("bread", fn(IID $id) => new Bread($id, "Bread"));
		self::register("brick", fn(IID $id) => new Item($id, "Brick"));
		self::register("bucket", fn(IID $id) => new Bucket($id, "Bucket"));
		self::register("carrot", fn(IID $id) => new Carrot($id, "Carrot"));
		self::register("charcoal", fn(IID $id) => new Coal($id, "Charcoal"));
		self::register("cherry_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::CHERRY_SIGN(), Blocks::CHERRY_WALL_SIGN()));
		self::register("chemical_aluminium_oxide", fn(IID $id) => new Item($id, "Aluminium Oxide"));
		self::register("chemical_ammonia", fn(IID $id) => new Item($id, "Ammonia"));
		self::register("chemical_barium_sulphate", fn(IID $id) => new Item($id, "Barium Sulphate"));
		self::register("chemical_benzene", fn(IID $id) => new Item($id, "Benzene"));
		self::register("chemical_boron_trioxide", fn(IID $id) => new Item($id, "Boron Trioxide"));
		self::register("chemical_calcium_bromide", fn(IID $id) => new Item($id, "Calcium Bromide"));
		self::register("chemical_calcium_chloride", fn(IID $id) => new Item($id, "Calcium Chloride"));
		self::register("chemical_cerium_chloride", fn(IID $id) => new Item($id, "Cerium Chloride"));
		self::register("chemical_charcoal", fn(IID $id) => new Item($id, "Charcoal"));
		self::register("chemical_crude_oil", fn(IID $id) => new Item($id, "Crude Oil"));
		self::register("chemical_glue", fn(IID $id) => new Item($id, "Glue"));
		self::register("chemical_hydrogen_peroxide", fn(IID $id) => new Item($id, "Hydrogen Peroxide"));
		self::register("chemical_hypochlorite", fn(IID $id) => new Item($id, "Hypochlorite"));
		self::register("chemical_ink", fn(IID $id) => new Item($id, "Ink"));
		self::register("chemical_iron_sulphide", fn(IID $id) => new Item($id, "Iron Sulphide"));
		self::register("chemical_latex", fn(IID $id) => new Item($id, "Latex"));
		self::register("chemical_lithium_hydride", fn(IID $id) => new Item($id, "Lithium Hydride"));
		self::register("chemical_luminol", fn(IID $id) => new Item($id, "Luminol"));
		self::register("chemical_magnesium_nitrate", fn(IID $id) => new Item($id, "Magnesium Nitrate"));
		self::register("chemical_magnesium_oxide", fn(IID $id) => new Item($id, "Magnesium Oxide"));
		self::register("chemical_magnesium_salts", fn(IID $id) => new Item($id, "Magnesium Salts"));
		self::register("chemical_mercuric_chloride", fn(IID $id) => new Item($id, "Mercuric Chloride"));
		self::register("chemical_polyethylene", fn(IID $id) => new Item($id, "Polyethylene"));
		self::register("chemical_potassium_chloride", fn(IID $id) => new Item($id, "Potassium Chloride"));
		self::register("chemical_potassium_iodide", fn(IID $id) => new Item($id, "Potassium Iodide"));
		self::register("chemical_rubbish", fn(IID $id) => new Item($id, "Rubbish"));
		self::register("chemical_salt", fn(IID $id) => new Item($id, "Salt"));
		self::register("chemical_soap", fn(IID $id) => new Item($id, "Soap"));
		self::register("chemical_sodium_acetate", fn(IID $id) => new Item($id, "Sodium Acetate"));
		self::register("chemical_sodium_fluoride", fn(IID $id) => new Item($id, "Sodium Fluoride"));
		self::register("chemical_sodium_hydride", fn(IID $id) => new Item($id, "Sodium Hydride"));
		self::register("chemical_sodium_hydroxide", fn(IID $id) => new Item($id, "Sodium Hydroxide"));
		self::register("chemical_sodium_hypochlorite", fn(IID $id) => new Item($id, "Sodium Hypochlorite"));
		self::register("chemical_sodium_oxide", fn(IID $id) => new Item($id, "Sodium Oxide"));
		self::register("chemical_sugar", fn(IID $id) => new Item($id, "Sugar"));
		self::register("chemical_sulphate", fn(IID $id) => new Item($id, "Sulphate"));
		self::register("chemical_tungsten_chloride", fn(IID $id) => new Item($id, "Tungsten Chloride"));
		self::register("chemical_water", fn(IID $id) => new Item($id, "Water"));
		self::register("chorus_fruit", fn(IID $id) => new ChorusFruit($id, "Chorus Fruit"));
		self::register("clay", fn(IID $id) => new Item($id, "Clay"));
		self::register("clock", fn(IID $id) => new Clock($id, "Clock"));
		self::register("clownfish", fn(IID $id) => new Clownfish($id, "Clownfish"));
		self::register("coal", fn(IID $id) => new Coal($id, "Coal"));
		self::register("cocoa_beans", fn(IID $id) => new CocoaBeans($id, "Cocoa Beans"));
		self::register("compass", fn(IID $id) => new Compass($id, "Compass", [EnchantmentTags::COMPASS]));
		self::register("cooked_chicken", fn(IID $id) => new CookedChicken($id, "Cooked Chicken"));
		self::register("cooked_fish", fn(IID $id) => new CookedFish($id, "Cooked Fish"));
		self::register("cooked_mutton", fn(IID $id) => new CookedMutton($id, "Cooked Mutton"));
		self::register("cooked_porkchop", fn(IID $id) => new CookedPorkchop($id, "Cooked Porkchop"));
		self::register("cooked_rabbit", fn(IID $id) => new CookedRabbit($id, "Cooked Rabbit"));
		self::register("cooked_salmon", fn(IID $id) => new CookedSalmon($id, "Cooked Salmon"));
		self::register("cookie", fn(IID $id) => new Cookie($id, "Cookie"));
		self::register("copper_ingot", fn(IID $id) => new Item($id, "Copper Ingot"));
		self::register("coral_fan", fn(IID $id) => new CoralFan($id));
		self::register("crimson_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::CRIMSON_SIGN(), Blocks::CRIMSON_WALL_SIGN()));
		self::register("dark_oak_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::DARK_OAK_SIGN(), Blocks::DARK_OAK_WALL_SIGN()));
		self::register("diamond", fn(IID $id) => new Item($id, "Diamond"));
		self::register("disc_fragment_5", fn(IID $id) => new Item($id, "Disc Fragment (5)"));
		self::register("dragon_breath", fn(IID $id) => new Item($id, "Dragon's Breath"));
		self::register("dried_kelp", fn(IID $id) => new DriedKelp($id, "Dried Kelp"));
		//TODO: add interface to dye-colour objects
		self::register("dye", fn(IID $id) => new Dye($id, "Dye"));
		self::register("echo_shard", fn(IID $id) => new Item($id, "Echo Shard"));
		self::register("egg", fn(IID $id) => new Egg($id, "Egg"));
		self::register("emerald", fn(IID $id) => new Item($id, "Emerald"));
		self::register("enchanted_book", fn(IID $id) => new EnchantedBook($id, "Enchanted Book", [EnchantmentTags::ALL]));
		self::register("enchanted_golden_apple", fn(IID $id) => new GoldenAppleEnchanted($id, "Enchanted Golden Apple"));
		self::register("end_crystal", fn(IID $id) => new EndCrystal($id, "End Crystal"));
		self::register("ender_pearl", fn(IID $id) => new EnderPearl($id, "Ender Pearl"));
		self::register("experience_bottle", fn(IID $id) => new ExperienceBottle($id, "Bottle o' Enchanting"));
		self::register("feather", fn(IID $id) => new Item($id, "Feather"));
		self::register("fermented_spider_eye", fn(IID $id) => new Item($id, "Fermented Spider Eye"));
		self::register("fire_charge", fn(IID $id) => new FireCharge($id, "Fire Charge"));
		self::register("fishing_rod", fn(IID $id) => new FishingRod($id, "Fishing Rod", [EnchantmentTags::FISHING_ROD]));
		self::register("flint", fn(IID $id) => new Item($id, "Flint"));
		self::register("flint_and_steel", fn(IID $id) => new FlintSteel($id, "Flint and Steel", [EnchantmentTags::FLINT_AND_STEEL]));
		self::register("ghast_tear", fn(IID $id) => new Item($id, "Ghast Tear"));
		self::register("glass_bottle", fn(IID $id) => new GlassBottle($id, "Glass Bottle"));
		self::register("glistering_melon", fn(IID $id) => new Item($id, "Glistering Melon"));
		self::register("glow_berries", fn(IID $id) => new GlowBerries($id, "Glow Berries"));
		self::register("glow_ink_sac", fn(IID $id) => new Item($id, "Glow Ink Sac"));
		self::register("glowstone_dust", fn(IID $id) => new Item($id, "Glowstone Dust"));
		self::register("goat_horn", fn(IID $id) => new GoatHorn($id, "Goat Horn"));
		self::register("gold_ingot", fn(IID $id) => new Item($id, "Gold Ingot"));
		self::register("gold_nugget", fn(IID $id) => new Item($id, "Gold Nugget"));
		self::register("golden_apple", fn(IID $id) => new GoldenApple($id, "Golden Apple"));
		self::register("golden_carrot", fn(IID $id) => new GoldenCarrot($id, "Golden Carrot"));
		self::register("gunpowder", fn(IID $id) => new Item($id, "Gunpowder"));
		self::register("heart_of_the_sea", fn(IID $id) => new Item($id, "Heart of the Sea"));
		self::register("honey_bottle", fn(IID $id) => new HoneyBottle($id, "Honey Bottle"));
		self::register("honeycomb", fn(IID $id) => new Item($id, "Honeycomb"));
		self::register("ice_bomb", fn(IID $id) => new IceBomb($id, "Ice Bomb"));
		self::register("ink_sac", fn(IID $id) => new Item($id, "Ink Sac"));
		self::register("iron_ingot", fn(IID $id) => new Item($id, "Iron Ingot"));
		self::register("iron_nugget", fn(IID $id) => new Item($id, "Iron Nugget"));
		self::register("jungle_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::JUNGLE_SIGN(), Blocks::JUNGLE_WALL_SIGN()));
		self::register("lapis_lazuli", fn(IID $id) => new Item($id, "Lapis Lazuli"));
		self::register("lava_bucket", fn(IID $id) => new LiquidBucket($id, "Lava Bucket", Blocks::LAVA()));
		self::register("leather", fn(IID $id) => new Item($id, "Leather"));
		self::register("magma_cream", fn(IID $id) => new Item($id, "Magma Cream"));
		self::register("mangrove_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::MANGROVE_SIGN(), Blocks::MANGROVE_WALL_SIGN()));
		self::register("medicine", fn(IID $id) => new Medicine($id, "Medicine"));
		self::register("melon", fn(IID $id) => new Melon($id, "Melon"));
		self::register("melon_seeds", fn(IID $id) => new MelonSeeds($id, "Melon Seeds"));
		self::register("milk_bucket", fn(IID $id) => new MilkBucket($id, "Milk Bucket"));
		self::register("minecart", fn(IID $id) => new Minecart($id, "Minecart"));
		self::register("mushroom_stew", fn(IID $id) => new MushroomStew($id, "Mushroom Stew"));
		self::register("name_tag", fn(IID $id) => new NameTag($id, "Name Tag"));
		self::register("nautilus_shell", fn(IID $id) => new Item($id, "Nautilus Shell"));
		self::register("nether_brick", fn(IID $id) => new Item($id, "Nether Brick"));
		self::register("nether_quartz", fn(IID $id) => new Item($id, "Nether Quartz"));
		self::register("nether_star", fn(IID $id) => new Item($id, "Nether Star"));
		self::register("netherite_ingot", fn(IID $id) => new class($id, "Netherite Ingot") extends Item{
			public function isFireProof() : bool{ return true; }
		});
		self::register("netherite_scrap", fn(IID $id) => new class($id, "Netherite Scrap") extends Item{
			public function isFireProof() : bool{ return true; }
		});
		self::register("oak_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::OAK_SIGN(), Blocks::OAK_WALL_SIGN()));
		self::register("painting", fn(IID $id) => new PaintingItem($id, "Painting"));
		self::register("paper", fn(IID $id) => new Item($id, "Paper"));
		self::register("phantom_membrane", fn(IID $id) => new Item($id, "Phantom Membrane"));
		self::register("pitcher_pod", fn(IID $id) => new PitcherPod($id, "Pitcher Pod"));
		self::register("poisonous_potato", fn(IID $id) => new PoisonousPotato($id, "Poisonous Potato"));
		self::register("popped_chorus_fruit", fn(IID $id) => new Item($id, "Popped Chorus Fruit"));
		self::register("potato", fn(IID $id) => new Potato($id, "Potato"));
		self::register("potion", fn(IID $id) => new Potion($id, "Potion"));
		self::register("prismarine_crystals", fn(IID $id) => new Item($id, "Prismarine Crystals"));
		self::register("prismarine_shard", fn(IID $id) => new Item($id, "Prismarine Shard"));
		self::register("pufferfish", fn(IID $id) => new Pufferfish($id, "Pufferfish"));
		self::register("pumpkin_pie", fn(IID $id) => new PumpkinPie($id, "Pumpkin Pie"));
		self::register("pumpkin_seeds", fn(IID $id) => new PumpkinSeeds($id, "Pumpkin Seeds"));
		self::register("rabbit_foot", fn(IID $id) => new Item($id, "Rabbit's Foot"));
		self::register("rabbit_hide", fn(IID $id) => new Item($id, "Rabbit Hide"));
		self::register("rabbit_stew", fn(IID $id) => new RabbitStew($id, "Rabbit Stew"));
		self::register("raw_beef", fn(IID $id) => new RawBeef($id, "Raw Beef"));
		self::register("raw_chicken", fn(IID $id) => new RawChicken($id, "Raw Chicken"));
		self::register("raw_copper", fn(IID $id) => new Item($id, "Raw Copper"));
		self::register("raw_fish", fn(IID $id) => new RawFish($id, "Raw Fish"));
		self::register("raw_gold", fn(IID $id) => new Item($id, "Raw Gold"));
		self::register("raw_iron", fn(IID $id) => new Item($id, "Raw Iron"));
		self::register("raw_mutton", fn(IID $id) => new RawMutton($id, "Raw Mutton"));
		self::register("raw_porkchop", fn(IID $id) => new RawPorkchop($id, "Raw Porkchop"));
		self::register("raw_rabbit", fn(IID $id) => new RawRabbit($id, "Raw Rabbit"));
		self::register("raw_salmon", fn(IID $id) => new RawSalmon($id, "Raw Salmon"));
		self::register("record_11", fn(IID $id) => new Record($id, RecordType::DISK_11, "Record 11"));
		self::register("record_13", fn(IID $id) => new Record($id, RecordType::DISK_13, "Record 13"));
		self::register("record_5", fn(IID $id) => new Record($id, RecordType::DISK_5, "Record 5"));
		self::register("record_blocks", fn(IID $id) => new Record($id, RecordType::DISK_BLOCKS, "Record Blocks"));
		self::register("record_cat", fn(IID $id) => new Record($id, RecordType::DISK_CAT, "Record Cat"));
		self::register("record_chirp", fn(IID $id) => new Record($id, RecordType::DISK_CHIRP, "Record Chirp"));
		self::register("record_far", fn(IID $id) => new Record($id, RecordType::DISK_FAR, "Record Far"));
		self::register("record_mall", fn(IID $id) => new Record($id, RecordType::DISK_MALL, "Record Mall"));
		self::register("record_mellohi", fn(IID $id) => new Record($id, RecordType::DISK_MELLOHI, "Record Mellohi"));
		self::register("record_otherside", fn(IID $id) => new Record($id, RecordType::DISK_OTHERSIDE, "Record Otherside"));
		self::register("record_pigstep", fn(IID $id) => new Record($id, RecordType::DISK_PIGSTEP, "Record Pigstep"));
		self::register("record_stal", fn(IID $id) => new Record($id, RecordType::DISK_STAL, "Record Stal"));
		self::register("record_strad", fn(IID $id) => new Record($id, RecordType::DISK_STRAD, "Record Strad"));
		self::register("record_wait", fn(IID $id) => new Record($id, RecordType::DISK_WAIT, "Record Wait"));
		self::register("record_ward", fn(IID $id) => new Record($id, RecordType::DISK_WARD, "Record Ward"));
		self::register("recovery_compass", fn(IID $id) => new Item($id, "Recovery Compass"));
		self::register("redstone_dust", fn(IID $id) => new Redstone($id, "Redstone"));
		self::register("rotten_flesh", fn(IID $id) => new RottenFlesh($id, "Rotten Flesh"));
		self::register("scute", fn(IID $id) => new Item($id, "Scute"));
		self::register("shears", fn(IID $id) => new Shears($id, "Shears", [EnchantmentTags::SHEARS]));
		self::register("shulker_shell", fn(IID $id) => new Item($id, "Shulker Shell"));
		self::register("slimeball", fn(IID $id) => new Item($id, "Slimeball"));
		self::register("snowball", fn(IID $id) => new Snowball($id, "Snowball"));
		self::register("spider_eye", fn(IID $id) => new SpiderEye($id, "Spider Eye"));
		self::register("splash_potion", fn(IID $id) => new SplashPotion($id, "Splash Potion"));
		self::register("spruce_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::SPRUCE_SIGN(), Blocks::SPRUCE_WALL_SIGN()));
		self::register("spyglass", fn(IID $id) => new Spyglass($id, "Spyglass"));
		self::register("steak", fn(IID $id) => new Steak($id, "Steak"));
		self::register("stick", fn(IID $id) => new Stick($id, "Stick"));
		self::register("string", fn(IID $id) => new StringItem($id, "String"));
		self::register("sugar", fn(IID $id) => new Item($id, "Sugar"));
		self::register("suspicious_stew", fn(IID $id) => new SuspiciousStew($id, "Suspicious Stew"));
		self::register("sweet_berries", fn(IID $id) => new SweetBerries($id, "Sweet Berries"));
		self::register("torchflower_seeds", fn(IID $id) => new TorchflowerSeeds($id, "Torchflower Seeds"));
		self::register("totem", fn(IID $id) => new Totem($id, "Totem of Undying"));
		self::register("warped_sign", fn(IID $id) => new ItemBlockWallOrFloor($id, Blocks::WARPED_SIGN(), Blocks::WARPED_WALL_SIGN()));
		self::register("water_bucket", fn(IID $id) => new LiquidBucket($id, "Water Bucket", Blocks::WATER()));
		self::register("wheat", fn(IID $id) => new Item($id, "Wheat"));
		self::register("wheat_seeds", fn(IID $id) => new WheatSeeds($id, "Wheat Seeds"));
		self::register("writable_book", fn(IID $id) => new WritableBook($id, "Book & Quill"));
		self::register("written_book", fn(IID $id) => new WrittenBook($id, "Written Book"));

		foreach(BoatType::cases() as $type){
			//boat type is static, because different types of wood may have different properties
			self::register(strtolower($type->name) . "_boat", fn(IID $id) => new Boat($id, $type->getDisplayName() . " Boat", $type));
		}
	}

	private static function registerSpawnEggs() : void{
		self::register("zombie_spawn_egg", fn(IID $id) => new class($id, "Zombie Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Zombie(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		self::register("squid_spawn_egg", fn(IID $id) => new class($id, "Squid Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Squid(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		self::register("villager_spawn_egg", fn(IID $id) => new class($id, "Villager Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Villager(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
	}

	private static function registerTierToolItems() : void{
		self::register("diamond_axe", fn(IID $id) => new Axe($id, "Diamond Axe", ToolTier::DIAMOND, [EnchantmentTags::AXE]));
		self::register("golden_axe", fn(IID $id) => new Axe($id, "Golden Axe", ToolTier::GOLD, [EnchantmentTags::AXE]));
		self::register("iron_axe", fn(IID $id) => new Axe($id, "Iron Axe", ToolTier::IRON, [EnchantmentTags::AXE]));
		self::register("netherite_axe", fn(IID $id) => new Axe($id, "Netherite Axe", ToolTier::NETHERITE, [EnchantmentTags::AXE]));
		self::register("stone_axe", fn(IID $id) => new Axe($id, "Stone Axe", ToolTier::STONE, [EnchantmentTags::AXE]));
		self::register("wooden_axe", fn(IID $id) => new Axe($id, "Wooden Axe", ToolTier::WOOD, [EnchantmentTags::AXE]));
		self::register("diamond_hoe", fn(IID $id) => new Hoe($id, "Diamond Hoe", ToolTier::DIAMOND, [EnchantmentTags::HOE]));
		self::register("golden_hoe", fn(IID $id) => new Hoe($id, "Golden Hoe", ToolTier::GOLD, [EnchantmentTags::HOE]));
		self::register("iron_hoe", fn(IID $id) => new Hoe($id, "Iron Hoe", ToolTier::IRON, [EnchantmentTags::HOE]));
		self::register("netherite_hoe", fn(IID $id) => new Hoe($id, "Netherite Hoe", ToolTier::NETHERITE, [EnchantmentTags::HOE]));
		self::register("stone_hoe", fn(IID $id) => new Hoe($id, "Stone Hoe", ToolTier::STONE, [EnchantmentTags::HOE]));
		self::register("wooden_hoe", fn(IID $id) => new Hoe($id, "Wooden Hoe", ToolTier::WOOD, [EnchantmentTags::HOE]));
		self::register("diamond_pickaxe", fn(IID $id) => new Pickaxe($id, "Diamond Pickaxe", ToolTier::DIAMOND, [EnchantmentTags::PICKAXE]));
		self::register("golden_pickaxe", fn(IID $id) => new Pickaxe($id, "Golden Pickaxe", ToolTier::GOLD, [EnchantmentTags::PICKAXE]));
		self::register("iron_pickaxe", fn(IID $id) => new Pickaxe($id, "Iron Pickaxe", ToolTier::IRON, [EnchantmentTags::PICKAXE]));
		self::register("netherite_pickaxe", fn(IID $id) => new Pickaxe($id, "Netherite Pickaxe", ToolTier::NETHERITE, [EnchantmentTags::PICKAXE]));
		self::register("stone_pickaxe", fn(IID $id) => new Pickaxe($id, "Stone Pickaxe", ToolTier::STONE, [EnchantmentTags::PICKAXE]));
		self::register("wooden_pickaxe", fn(IID $id) => new Pickaxe($id, "Wooden Pickaxe", ToolTier::WOOD, [EnchantmentTags::PICKAXE]));
		self::register("diamond_shovel", fn(IID $id) => new Shovel($id, "Diamond Shovel", ToolTier::DIAMOND, [EnchantmentTags::SHOVEL]));
		self::register("golden_shovel", fn(IID $id) => new Shovel($id, "Golden Shovel", ToolTier::GOLD, [EnchantmentTags::SHOVEL]));
		self::register("iron_shovel", fn(IID $id) => new Shovel($id, "Iron Shovel", ToolTier::IRON, [EnchantmentTags::SHOVEL]));
		self::register("netherite_shovel", fn(IID $id) => new Shovel($id, "Netherite Shovel", ToolTier::NETHERITE, [EnchantmentTags::SHOVEL]));
		self::register("stone_shovel", fn(IID $id) => new Shovel($id, "Stone Shovel", ToolTier::STONE, [EnchantmentTags::SHOVEL]));
		self::register("wooden_shovel", fn(IID $id) => new Shovel($id, "Wooden Shovel", ToolTier::WOOD, [EnchantmentTags::SHOVEL]));
		self::register("diamond_sword", fn(IID $id) => new Sword($id, "Diamond Sword", ToolTier::DIAMOND, [EnchantmentTags::SWORD]));
		self::register("golden_sword", fn(IID $id) => new Sword($id, "Golden Sword", ToolTier::GOLD, [EnchantmentTags::SWORD]));
		self::register("iron_sword", fn(IID $id) => new Sword($id, "Iron Sword", ToolTier::IRON, [EnchantmentTags::SWORD]));
		self::register("netherite_sword", fn(IID $id) => new Sword($id, "Netherite Sword", ToolTier::NETHERITE, [EnchantmentTags::SWORD]));
		self::register("stone_sword", fn(IID $id) => new Sword($id, "Stone Sword", ToolTier::STONE, [EnchantmentTags::SWORD]));
		self::register("wooden_sword", fn(IID $id) => new Sword($id, "Wooden Sword", ToolTier::WOOD, [EnchantmentTags::SWORD]));
	}

	private static function registerArmorItems() : void{
		self::register("chainmail_boots", fn(IID $id) => new Armor($id, "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::BOOTS]));
		self::register("diamond_boots", fn(IID $id) => new Armor($id, "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::BOOTS]));
		self::register("golden_boots", fn(IID $id) => new Armor($id, "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET, material: ArmorMaterials::GOLD()), [EnchantmentTags::BOOTS]));
		self::register("iron_boots", fn(IID $id) => new Armor($id, "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET, material: ArmorMaterials::IRON()), [EnchantmentTags::BOOTS]));
		self::register("leather_boots", fn(IID $id) => new Armor($id, "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET, material: ArmorMaterials::LEATHER()), [EnchantmentTags::BOOTS]));
		self::register("netherite_boots", fn(IID $id) => new Armor($id, "Netherite Boots", new ArmorTypeInfo(3, 482, ArmorInventory::SLOT_FEET, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::BOOTS]));

		self::register("chainmail_chestplate", fn(IID $id) => new Armor($id, "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::CHESTPLATE]));
		self::register("diamond_chestplate", fn(IID $id) => new Armor($id, "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::CHESTPLATE]));
		self::register("golden_chestplate", fn(IID $id) => new Armor($id, "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::GOLD()), [EnchantmentTags::CHESTPLATE]));
		self::register("iron_chestplate", fn(IID $id) => new Armor($id, "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::IRON()), [EnchantmentTags::CHESTPLATE]));
		self::register("leather_tunic", fn(IID $id) => new Armor($id, "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::LEATHER()), [EnchantmentTags::CHESTPLATE]));
		self::register("netherite_chestplate", fn(IID $id) => new Armor($id, "Netherite Chestplate", new ArmorTypeInfo(8, 593, ArmorInventory::SLOT_CHEST, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::CHESTPLATE]));

		self::register("chainmail_helmet", fn(IID $id) => new Armor($id, "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::HELMET]));
		self::register("diamond_helmet", fn(IID $id) => new Armor($id, "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::HELMET]));
		self::register("golden_helmet", fn(IID $id) => new Armor($id, "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::GOLD()), [EnchantmentTags::HELMET]));
		self::register("iron_helmet", fn(IID $id) => new Armor($id, "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::IRON()), [EnchantmentTags::HELMET]));
		self::register("leather_cap", fn(IID $id) => new Armor($id, "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::LEATHER()), [EnchantmentTags::HELMET]));
		self::register("netherite_helmet", fn(IID $id) => new Armor($id, "Netherite Helmet", new ArmorTypeInfo(3, 408, ArmorInventory::SLOT_HEAD, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::HELMET]));
		self::register("turtle_helmet", fn(IID $id) => new TurtleHelmet($id, "Turtle Shell", new ArmorTypeInfo(2, 276, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::TURTLE()), [EnchantmentTags::HELMET]));

		self::register("chainmail_leggings", fn(IID $id) => new Armor($id, "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::LEGGINGS]));
		self::register("diamond_leggings", fn(IID $id) => new Armor($id, "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::LEGGINGS]));
		self::register("golden_leggings", fn(IID $id) => new Armor($id, "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::GOLD()), [EnchantmentTags::LEGGINGS]));
		self::register("iron_leggings", fn(IID $id) => new Armor($id, "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::IRON()), [EnchantmentTags::LEGGINGS]));
		self::register("leather_pants", fn(IID $id) => new Armor($id, "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::LEATHER()), [EnchantmentTags::LEGGINGS]));
		self::register("netherite_leggings", fn(IID $id) => new Armor($id, "Netherite Leggings", new ArmorTypeInfo(6, 556, ArmorInventory::SLOT_LEGS, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::LEGGINGS]));
	}

	private static function registerSmithingTemplates() : void{
		self::register("netherite_upgrade_smithing_template", fn(IID $id) => new Item($id, "Netherite Upgrade Smithing Template"));
		self::register("coast_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Coast Armor Trim Smithing Template"));
		self::register("dune_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Dune Armor Trim Smithing Template"));
		self::register("eye_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Eye Armor Trim Smithing Template"));
		self::register("host_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Host Armor Trim Smithing Template"));
		self::register("raiser_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Raiser Armor Trim Smithing Template"));
		self::register("rib_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Rib Armor Trim Smithing Template"));
		self::register("sentry_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Sentry Armor Trim Smithing Template"));
		self::register("shaper_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Shaper Armor Trim Smithing Template"));
		self::register("silence_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Silence Armor Trim Smithing Template"));
		self::register("snout_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Snout Armor Trim Smithing Template"));
		self::register("spire_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Spire Armor Trim Smithing Template"));
		self::register("tide_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Tide Armor Trim Smithing Template"));
		self::register("vex_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Vex Armor Trim Smithing Template"));
		self::register("ward_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Ward Armor Trim Smithing Template"));
		self::register("wayfinder_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Wayfinder Armor Trim Smithing Template"));
		self::register("wild_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Wild Armor Trim Smithing Template"));
	}

}
