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

use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemTypeIds as Ids;
use pocketmine\utils\CloningRegistryTrait;

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
 * @method static FishingRod FISHING_ROD()
 * @method static Item FLINT()
 * @method static FlintSteel FLINT_AND_STEEL()
 * @method static Item GHAST_TEAR()
 * @method static GlassBottle GLASS_BOTTLE()
 * @method static Item GLISTERING_MELON()
 * @method static Item GLOWSTONE_DUST()
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
 * @method static ItemBlockWallOrFloor MANGROVE_SIGN()
 * @method static Melon MELON()
 * @method static MelonSeeds MELON_SEEDS()
 * @method static MilkBucket MILK_BUCKET()
 * @method static Minecart MINECART()
 * @method static MushroomStew MUSHROOM_STEW()
 * @method static Item NAUTILUS_SHELL()
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
 * @method static Record RECORD_BLOCKS()
 * @method static Record RECORD_CAT()
 * @method static Record RECORD_CHIRP()
 * @method static Record RECORD_FAR()
 * @method static Record RECORD_MALL()
 * @method static Record RECORD_MELLOHI()
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
 * @method static SweetBerries SWEET_BERRIES()
 * @method static Totem TOTEM()
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
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var Item[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void{
		$factory = ItemFactory::getInstance();
		self::register("air", VanillaBlocks::AIR()->asItem()->setCount(0));

		self::register("acacia_boat", $factory->fromTypeId(Ids::ACACIA_BOAT));
		self::register("acacia_sign", $factory->fromTypeId(Ids::ACACIA_SIGN));
		self::register("amethyst_shard", $factory->fromTypeId(Ids::AMETHYST_SHARD));
		self::register("apple", $factory->fromTypeId(Ids::APPLE));
		self::register("arrow", $factory->fromTypeId(Ids::ARROW));
		self::register("baked_potato", $factory->fromTypeId(Ids::BAKED_POTATO));
		self::register("bamboo", $factory->fromTypeId(Ids::BAMBOO));
		self::register("banner", $factory->fromTypeId(Ids::BANNER));
		self::register("beetroot", $factory->fromTypeId(Ids::BEETROOT));
		self::register("beetroot_seeds", $factory->fromTypeId(Ids::BEETROOT_SEEDS));
		self::register("beetroot_soup", $factory->fromTypeId(Ids::BEETROOT_SOUP));
		self::register("birch_boat", $factory->fromTypeId(Ids::BIRCH_BOAT));
		self::register("birch_sign", $factory->fromTypeId(Ids::BIRCH_SIGN));
		self::register("blaze_powder", $factory->fromTypeId(Ids::BLAZE_POWDER));
		self::register("blaze_rod", $factory->fromTypeId(Ids::BLAZE_ROD));
		self::register("bleach", $factory->fromTypeId(Ids::BLEACH));
		self::register("bone", $factory->fromTypeId(Ids::BONE));
		self::register("bone_meal", $factory->fromTypeId(Ids::BONE_MEAL));
		self::register("book", $factory->fromTypeId(Ids::BOOK));
		self::register("bow", $factory->fromTypeId(Ids::BOW));
		self::register("bowl", $factory->fromTypeId(Ids::BOWL));
		self::register("bread", $factory->fromTypeId(Ids::BREAD));
		self::register("brick", $factory->fromTypeId(Ids::BRICK));
		self::register("bucket", $factory->fromTypeId(Ids::BUCKET));
		self::register("carrot", $factory->fromTypeId(Ids::CARROT));
		self::register("chainmail_boots", $factory->fromTypeId(Ids::CHAINMAIL_BOOTS));
		self::register("chainmail_chestplate", $factory->fromTypeId(Ids::CHAINMAIL_CHESTPLATE));
		self::register("chainmail_helmet", $factory->fromTypeId(Ids::CHAINMAIL_HELMET));
		self::register("chainmail_leggings", $factory->fromTypeId(Ids::CHAINMAIL_LEGGINGS));
		self::register("charcoal", $factory->fromTypeId(Ids::CHARCOAL));
		self::register("chemical_aluminium_oxide", $factory->fromTypeId(Ids::CHEMICAL_ALUMINIUM_OXIDE));
		self::register("chemical_ammonia", $factory->fromTypeId(Ids::CHEMICAL_AMMONIA));
		self::register("chemical_barium_sulphate", $factory->fromTypeId(Ids::CHEMICAL_BARIUM_SULPHATE));
		self::register("chemical_benzene", $factory->fromTypeId(Ids::CHEMICAL_BENZENE));
		self::register("chemical_boron_trioxide", $factory->fromTypeId(Ids::CHEMICAL_BORON_TRIOXIDE));
		self::register("chemical_calcium_bromide", $factory->fromTypeId(Ids::CHEMICAL_CALCIUM_BROMIDE));
		self::register("chemical_calcium_chloride", $factory->fromTypeId(Ids::CHEMICAL_CALCIUM_CHLORIDE));
		self::register("chemical_cerium_chloride", $factory->fromTypeId(Ids::CHEMICAL_CERIUM_CHLORIDE));
		self::register("chemical_charcoal", $factory->fromTypeId(Ids::CHEMICAL_CHARCOAL));
		self::register("chemical_crude_oil", $factory->fromTypeId(Ids::CHEMICAL_CRUDE_OIL));
		self::register("chemical_glue", $factory->fromTypeId(Ids::CHEMICAL_GLUE));
		self::register("chemical_hydrogen_peroxide", $factory->fromTypeId(Ids::CHEMICAL_HYDROGEN_PEROXIDE));
		self::register("chemical_hypochlorite", $factory->fromTypeId(Ids::CHEMICAL_HYPOCHLORITE));
		self::register("chemical_ink", $factory->fromTypeId(Ids::CHEMICAL_INK));
		self::register("chemical_iron_sulphide", $factory->fromTypeId(Ids::CHEMICAL_IRON_SULPHIDE));
		self::register("chemical_latex", $factory->fromTypeId(Ids::CHEMICAL_LATEX));
		self::register("chemical_lithium_hydride", $factory->fromTypeId(Ids::CHEMICAL_LITHIUM_HYDRIDE));
		self::register("chemical_luminol", $factory->fromTypeId(Ids::CHEMICAL_LUMINOL));
		self::register("chemical_magnesium_nitrate", $factory->fromTypeId(Ids::CHEMICAL_MAGNESIUM_NITRATE));
		self::register("chemical_magnesium_oxide", $factory->fromTypeId(Ids::CHEMICAL_MAGNESIUM_OXIDE));
		self::register("chemical_magnesium_salts", $factory->fromTypeId(Ids::CHEMICAL_MAGNESIUM_SALTS));
		self::register("chemical_mercuric_chloride", $factory->fromTypeId(Ids::CHEMICAL_MERCURIC_CHLORIDE));
		self::register("chemical_polyethylene", $factory->fromTypeId(Ids::CHEMICAL_POLYETHYLENE));
		self::register("chemical_potassium_chloride", $factory->fromTypeId(Ids::CHEMICAL_POTASSIUM_CHLORIDE));
		self::register("chemical_potassium_iodide", $factory->fromTypeId(Ids::CHEMICAL_POTASSIUM_IODIDE));
		self::register("chemical_rubbish", $factory->fromTypeId(Ids::CHEMICAL_RUBBISH));
		self::register("chemical_salt", $factory->fromTypeId(Ids::CHEMICAL_SALT));
		self::register("chemical_soap", $factory->fromTypeId(Ids::CHEMICAL_SOAP));
		self::register("chemical_sodium_acetate", $factory->fromTypeId(Ids::CHEMICAL_SODIUM_ACETATE));
		self::register("chemical_sodium_fluoride", $factory->fromTypeId(Ids::CHEMICAL_SODIUM_FLUORIDE));
		self::register("chemical_sodium_hydride", $factory->fromTypeId(Ids::CHEMICAL_SODIUM_HYDRIDE));
		self::register("chemical_sodium_hydroxide", $factory->fromTypeId(Ids::CHEMICAL_SODIUM_HYDROXIDE));
		self::register("chemical_sodium_hypochlorite", $factory->fromTypeId(Ids::CHEMICAL_SODIUM_HYPOCHLORITE));
		self::register("chemical_sodium_oxide", $factory->fromTypeId(Ids::CHEMICAL_SODIUM_OXIDE));
		self::register("chemical_sugar", $factory->fromTypeId(Ids::CHEMICAL_SUGAR));
		self::register("chemical_sulphate", $factory->fromTypeId(Ids::CHEMICAL_SULPHATE));
		self::register("chemical_tungsten_chloride", $factory->fromTypeId(Ids::CHEMICAL_TUNGSTEN_CHLORIDE));
		self::register("chemical_water", $factory->fromTypeId(Ids::CHEMICAL_WATER));
		self::register("chorus_fruit", $factory->fromTypeId(Ids::CHORUS_FRUIT));
		self::register("clay", $factory->fromTypeId(Ids::CLAY));
		self::register("clock", $factory->fromTypeId(Ids::CLOCK));
		self::register("clownfish", $factory->fromTypeId(Ids::CLOWNFISH));
		self::register("coal", $factory->fromTypeId(Ids::COAL));
		self::register("cocoa_beans", $factory->fromTypeId(Ids::COCOA_BEANS));
		self::register("compass", $factory->fromTypeId(Ids::COMPASS));
		self::register("cooked_chicken", $factory->fromTypeId(Ids::COOKED_CHICKEN));
		self::register("cooked_fish", $factory->fromTypeId(Ids::COOKED_FISH));
		self::register("cooked_mutton", $factory->fromTypeId(Ids::COOKED_MUTTON));
		self::register("cooked_porkchop", $factory->fromTypeId(Ids::COOKED_PORKCHOP));
		self::register("cooked_rabbit", $factory->fromTypeId(Ids::COOKED_RABBIT));
		self::register("cooked_salmon", $factory->fromTypeId(Ids::COOKED_SALMON));
		self::register("cookie", $factory->fromTypeId(Ids::COOKIE));
		self::register("copper_ingot", $factory->fromTypeId(Ids::COPPER_INGOT));
		self::register("coral_fan", $factory->fromTypeId(Ids::CORAL_FAN));
		self::register("crimson_sign", $factory->fromTypeId(Ids::CRIMSON_SIGN));
		self::register("dark_oak_boat", $factory->fromTypeId(Ids::DARK_OAK_BOAT));
		self::register("dark_oak_sign", $factory->fromTypeId(Ids::DARK_OAK_SIGN));
		self::register("diamond", $factory->fromTypeId(Ids::DIAMOND));
		self::register("diamond_axe", $factory->fromTypeId(Ids::DIAMOND_AXE));
		self::register("diamond_boots", $factory->fromTypeId(Ids::DIAMOND_BOOTS));
		self::register("diamond_chestplate", $factory->fromTypeId(Ids::DIAMOND_CHESTPLATE));
		self::register("diamond_helmet", $factory->fromTypeId(Ids::DIAMOND_HELMET));
		self::register("diamond_hoe", $factory->fromTypeId(Ids::DIAMOND_HOE));
		self::register("diamond_leggings", $factory->fromTypeId(Ids::DIAMOND_LEGGINGS));
		self::register("diamond_pickaxe", $factory->fromTypeId(Ids::DIAMOND_PICKAXE));
		self::register("diamond_shovel", $factory->fromTypeId(Ids::DIAMOND_SHOVEL));
		self::register("diamond_sword", $factory->fromTypeId(Ids::DIAMOND_SWORD));
		self::register("disc_fragment_5", $factory->fromTypeId(Ids::DISC_FRAGMENT_5));
		self::register("dragon_breath", $factory->fromTypeId(Ids::DRAGON_BREATH));
		self::register("dried_kelp", $factory->fromTypeId(Ids::DRIED_KELP));
		self::register("dye", $factory->fromTypeId(Ids::DYE));
		self::register("echo_shard", $factory->fromTypeId(Ids::ECHO_SHARD));
		self::register("egg", $factory->fromTypeId(Ids::EGG));
		self::register("emerald", $factory->fromTypeId(Ids::EMERALD));
		self::register("enchanted_golden_apple", $factory->fromTypeId(Ids::ENCHANTED_GOLDEN_APPLE));
		self::register("ender_pearl", $factory->fromTypeId(Ids::ENDER_PEARL));
		self::register("experience_bottle", $factory->fromTypeId(Ids::EXPERIENCE_BOTTLE));
		self::register("feather", $factory->fromTypeId(Ids::FEATHER));
		self::register("fermented_spider_eye", $factory->fromTypeId(Ids::FERMENTED_SPIDER_EYE));
		self::register("fishing_rod", $factory->fromTypeId(Ids::FISHING_ROD));
		self::register("flint", $factory->fromTypeId(Ids::FLINT));
		self::register("flint_and_steel", $factory->fromTypeId(Ids::FLINT_AND_STEEL));
		self::register("ghast_tear", $factory->fromTypeId(Ids::GHAST_TEAR));
		self::register("glass_bottle", $factory->fromTypeId(Ids::GLASS_BOTTLE));
		self::register("glistering_melon", $factory->fromTypeId(Ids::GLISTERING_MELON));
		self::register("glow_ink_sac", $factory->fromTypeId(Ids::GLOW_INK_SAC));
		self::register("glowstone_dust", $factory->fromTypeId(Ids::GLOWSTONE_DUST));
		self::register("gold_ingot", $factory->fromTypeId(Ids::GOLD_INGOT));
		self::register("gold_nugget", $factory->fromTypeId(Ids::GOLD_NUGGET));
		self::register("golden_apple", $factory->fromTypeId(Ids::GOLDEN_APPLE));
		self::register("golden_axe", $factory->fromTypeId(Ids::GOLDEN_AXE));
		self::register("golden_boots", $factory->fromTypeId(Ids::GOLDEN_BOOTS));
		self::register("golden_carrot", $factory->fromTypeId(Ids::GOLDEN_CARROT));
		self::register("golden_chestplate", $factory->fromTypeId(Ids::GOLDEN_CHESTPLATE));
		self::register("golden_helmet", $factory->fromTypeId(Ids::GOLDEN_HELMET));
		self::register("golden_hoe", $factory->fromTypeId(Ids::GOLDEN_HOE));
		self::register("golden_leggings", $factory->fromTypeId(Ids::GOLDEN_LEGGINGS));
		self::register("golden_pickaxe", $factory->fromTypeId(Ids::GOLDEN_PICKAXE));
		self::register("golden_shovel", $factory->fromTypeId(Ids::GOLDEN_SHOVEL));
		self::register("golden_sword", $factory->fromTypeId(Ids::GOLDEN_SWORD));
		self::register("gunpowder", $factory->fromTypeId(Ids::GUNPOWDER));
		self::register("heart_of_the_sea", $factory->fromTypeId(Ids::HEART_OF_THE_SEA));
		self::register("honeycomb", $factory->fromTypeId(Ids::HONEYCOMB));
		self::register("ink_sac", $factory->fromTypeId(Ids::INK_SAC));
		self::register("iron_axe", $factory->fromTypeId(Ids::IRON_AXE));
		self::register("iron_boots", $factory->fromTypeId(Ids::IRON_BOOTS));
		self::register("iron_chestplate", $factory->fromTypeId(Ids::IRON_CHESTPLATE));
		self::register("iron_helmet", $factory->fromTypeId(Ids::IRON_HELMET));
		self::register("iron_hoe", $factory->fromTypeId(Ids::IRON_HOE));
		self::register("iron_ingot", $factory->fromTypeId(Ids::IRON_INGOT));
		self::register("iron_leggings", $factory->fromTypeId(Ids::IRON_LEGGINGS));
		self::register("iron_nugget", $factory->fromTypeId(Ids::IRON_NUGGET));
		self::register("iron_pickaxe", $factory->fromTypeId(Ids::IRON_PICKAXE));
		self::register("iron_shovel", $factory->fromTypeId(Ids::IRON_SHOVEL));
		self::register("iron_sword", $factory->fromTypeId(Ids::IRON_SWORD));
		self::register("jungle_boat", $factory->fromTypeId(Ids::JUNGLE_BOAT));
		self::register("jungle_sign", $factory->fromTypeId(Ids::JUNGLE_SIGN));
		self::register("lapis_lazuli", $factory->fromTypeId(Ids::LAPIS_LAZULI));
		self::register("lava_bucket", $factory->fromTypeId(Ids::LAVA_BUCKET));
		self::register("leather", $factory->fromTypeId(Ids::LEATHER));
		self::register("leather_boots", $factory->fromTypeId(Ids::LEATHER_BOOTS));
		self::register("leather_cap", $factory->fromTypeId(Ids::LEATHER_CAP));
		self::register("leather_pants", $factory->fromTypeId(Ids::LEATHER_PANTS));
		self::register("leather_tunic", $factory->fromTypeId(Ids::LEATHER_TUNIC));
		self::register("magma_cream", $factory->fromTypeId(Ids::MAGMA_CREAM));
		self::register("mangrove_sign", $factory->fromTypeId(Ids::MANGROVE_SIGN));
		self::register("melon", $factory->fromTypeId(Ids::MELON));
		self::register("melon_seeds", $factory->fromTypeId(Ids::MELON_SEEDS));
		self::register("milk_bucket", $factory->fromTypeId(Ids::MILK_BUCKET));
		self::register("minecart", $factory->fromTypeId(Ids::MINECART));
		self::register("mushroom_stew", $factory->fromTypeId(Ids::MUSHROOM_STEW));
		self::register("nautilus_shell", $factory->fromTypeId(Ids::NAUTILUS_SHELL));
		self::register("nether_brick", $factory->fromTypeId(Ids::NETHER_BRICK));
		self::register("nether_quartz", $factory->fromTypeId(Ids::NETHER_QUARTZ));
		self::register("nether_star", $factory->fromTypeId(Ids::NETHER_STAR));
		self::register("oak_boat", $factory->fromTypeId(Ids::OAK_BOAT));
		self::register("oak_sign", $factory->fromTypeId(Ids::OAK_SIGN));
		self::register("painting", $factory->fromTypeId(Ids::PAINTING));
		self::register("paper", $factory->fromTypeId(Ids::PAPER));
		self::register("phantom_membrane", $factory->fromTypeId(Ids::PHANTOM_MEMBRANE));
		self::register("poisonous_potato", $factory->fromTypeId(Ids::POISONOUS_POTATO));
		self::register("popped_chorus_fruit", $factory->fromTypeId(Ids::POPPED_CHORUS_FRUIT));
		self::register("potato", $factory->fromTypeId(Ids::POTATO));
		self::register("potion", $factory->fromTypeId(Ids::POTION));
		self::register("prismarine_crystals", $factory->fromTypeId(Ids::PRISMARINE_CRYSTALS));
		self::register("prismarine_shard", $factory->fromTypeId(Ids::PRISMARINE_SHARD));
		self::register("pufferfish", $factory->fromTypeId(Ids::PUFFERFISH));
		self::register("pumpkin_pie", $factory->fromTypeId(Ids::PUMPKIN_PIE));
		self::register("pumpkin_seeds", $factory->fromTypeId(Ids::PUMPKIN_SEEDS));
		self::register("rabbit_foot", $factory->fromTypeId(Ids::RABBIT_FOOT));
		self::register("rabbit_hide", $factory->fromTypeId(Ids::RABBIT_HIDE));
		self::register("rabbit_stew", $factory->fromTypeId(Ids::RABBIT_STEW));
		self::register("raw_beef", $factory->fromTypeId(Ids::RAW_BEEF));
		self::register("raw_chicken", $factory->fromTypeId(Ids::RAW_CHICKEN));
		self::register("raw_copper", $factory->fromTypeId(Ids::RAW_COPPER));
		self::register("raw_fish", $factory->fromTypeId(Ids::RAW_FISH));
		self::register("raw_gold", $factory->fromTypeId(Ids::RAW_GOLD));
		self::register("raw_iron", $factory->fromTypeId(Ids::RAW_IRON));
		self::register("raw_mutton", $factory->fromTypeId(Ids::RAW_MUTTON));
		self::register("raw_porkchop", $factory->fromTypeId(Ids::RAW_PORKCHOP));
		self::register("raw_rabbit", $factory->fromTypeId(Ids::RAW_RABBIT));
		self::register("raw_salmon", $factory->fromTypeId(Ids::RAW_SALMON));
		self::register("record_11", $factory->fromTypeId(Ids::RECORD_11));
		self::register("record_13", $factory->fromTypeId(Ids::RECORD_13));
		self::register("record_blocks", $factory->fromTypeId(Ids::RECORD_BLOCKS));
		self::register("record_cat", $factory->fromTypeId(Ids::RECORD_CAT));
		self::register("record_chirp", $factory->fromTypeId(Ids::RECORD_CHIRP));
		self::register("record_far", $factory->fromTypeId(Ids::RECORD_FAR));
		self::register("record_mall", $factory->fromTypeId(Ids::RECORD_MALL));
		self::register("record_mellohi", $factory->fromTypeId(Ids::RECORD_MELLOHI));
		self::register("record_stal", $factory->fromTypeId(Ids::RECORD_STAL));
		self::register("record_strad", $factory->fromTypeId(Ids::RECORD_STRAD));
		self::register("record_wait", $factory->fromTypeId(Ids::RECORD_WAIT));
		self::register("record_ward", $factory->fromTypeId(Ids::RECORD_WARD));
		self::register("redstone_dust", $factory->fromTypeId(Ids::REDSTONE_DUST));
		self::register("rotten_flesh", $factory->fromTypeId(Ids::ROTTEN_FLESH));
		self::register("scute", $factory->fromTypeId(Ids::SCUTE));
		self::register("shears", $factory->fromTypeId(Ids::SHEARS));
		self::register("shulker_shell", $factory->fromTypeId(Ids::SHULKER_SHELL));
		self::register("slimeball", $factory->fromTypeId(Ids::SLIMEBALL));
		self::register("snowball", $factory->fromTypeId(Ids::SNOWBALL));
		self::register("spider_eye", $factory->fromTypeId(Ids::SPIDER_EYE));
		self::register("splash_potion", $factory->fromTypeId(Ids::SPLASH_POTION));
		self::register("spruce_boat", $factory->fromTypeId(Ids::SPRUCE_BOAT));
		self::register("spruce_sign", $factory->fromTypeId(Ids::SPRUCE_SIGN));
		self::register("spyglass", $factory->fromTypeId(Ids::SPYGLASS));
		self::register("squid_spawn_egg", $factory->fromTypeId(Ids::SQUID_SPAWN_EGG));
		self::register("steak", $factory->fromTypeId(Ids::STEAK));
		self::register("stick", $factory->fromTypeId(Ids::STICK));
		self::register("stone_axe", $factory->fromTypeId(Ids::STONE_AXE));
		self::register("stone_hoe", $factory->fromTypeId(Ids::STONE_HOE));
		self::register("stone_pickaxe", $factory->fromTypeId(Ids::STONE_PICKAXE));
		self::register("stone_shovel", $factory->fromTypeId(Ids::STONE_SHOVEL));
		self::register("stone_sword", $factory->fromTypeId(Ids::STONE_SWORD));
		self::register("string", $factory->fromTypeId(Ids::STRING));
		self::register("sugar", $factory->fromTypeId(Ids::SUGAR));
		self::register("sweet_berries", $factory->fromTypeId(Ids::SWEET_BERRIES));
		self::register("totem", $factory->fromTypeId(Ids::TOTEM));
		self::register("villager_spawn_egg", $factory->fromTypeId(Ids::VILLAGER_SPAWN_EGG));
		self::register("warped_sign", $factory->fromTypeId(Ids::WARPED_SIGN));
		self::register("water_bucket", $factory->fromTypeId(Ids::WATER_BUCKET));
		self::register("wheat", $factory->fromTypeId(Ids::WHEAT));
		self::register("wheat_seeds", $factory->fromTypeId(Ids::WHEAT_SEEDS));
		self::register("wooden_axe", $factory->fromTypeId(Ids::WOODEN_AXE));
		self::register("wooden_hoe", $factory->fromTypeId(Ids::WOODEN_HOE));
		self::register("wooden_pickaxe", $factory->fromTypeId(Ids::WOODEN_PICKAXE));
		self::register("wooden_shovel", $factory->fromTypeId(Ids::WOODEN_SHOVEL));
		self::register("wooden_sword", $factory->fromTypeId(Ids::WOODEN_SWORD));
		self::register("writable_book", $factory->fromTypeId(Ids::WRITABLE_BOOK));
		self::register("written_book", $factory->fromTypeId(Ids::WRITTEN_BOOK));
		self::register("zombie_spawn_egg", $factory->fromTypeId(Ids::ZOMBIE_SPAWN_EGG));
	}
}
