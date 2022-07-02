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

use pocketmine\item\ItemIds as Ids;
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
 * @method static CoralFan CORAL_FAN()
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
 * @method static Item DRAGON_BREATH()
 * @method static DriedKelp DRIED_KELP()
 * @method static Dye DYE()
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
 * @method static RawFish RAW_FISH()
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
		self::register("acacia_boat", $factory->get(Ids::BOAT, 4));
		self::register("acacia_sign", $factory->get(Ids::ACACIA_SIGN));
		self::register("air", $factory->get(Ids::AIR, 0, 0));
		self::register("apple", $factory->get(Ids::APPLE));
		self::register("arrow", $factory->get(Ids::ARROW));
		self::register("baked_potato", $factory->get(Ids::BAKED_POTATO));
		self::register("bamboo", $factory->get(Ids::BAMBOO));
		self::register("banner", $factory->get(Ids::BANNER));
		self::register("beetroot", $factory->get(Ids::BEETROOT));
		self::register("beetroot_seeds", $factory->get(Ids::BEETROOT_SEEDS));
		self::register("beetroot_soup", $factory->get(Ids::BEETROOT_SOUP));
		self::register("birch_boat", $factory->get(Ids::BOAT, 2));
		self::register("birch_sign", $factory->get(Ids::BIRCH_SIGN));
		self::register("blaze_powder", $factory->get(Ids::BLAZE_POWDER));
		self::register("blaze_rod", $factory->get(Ids::BLAZE_ROD));
		self::register("bleach", $factory->get(Ids::BLEACH));
		self::register("bone", $factory->get(Ids::BONE));
		self::register("bone_meal", $factory->get(Ids::DYE, 15));
		self::register("book", $factory->get(Ids::BOOK));
		self::register("bow", $factory->get(Ids::BOW));
		self::register("bowl", $factory->get(Ids::BOWL));
		self::register("bread", $factory->get(Ids::BREAD));
		self::register("brick", $factory->get(Ids::BRICK));
		self::register("bucket", $factory->get(Ids::BUCKET));
		self::register("carrot", $factory->get(Ids::CARROT));
		self::register("chainmail_boots", $factory->get(Ids::CHAINMAIL_BOOTS));
		self::register("chainmail_chestplate", $factory->get(Ids::CHAINMAIL_CHESTPLATE));
		self::register("chainmail_helmet", $factory->get(Ids::CHAINMAIL_HELMET));
		self::register("chainmail_leggings", $factory->get(Ids::CHAINMAIL_LEGGINGS));
		self::register("charcoal", $factory->get(Ids::COAL, 1));
		self::register("chemical_aluminium_oxide", $factory->get(Ids::COMPOUND, 13));
		self::register("chemical_ammonia", $factory->get(Ids::COMPOUND, 36));
		self::register("chemical_barium_sulphate", $factory->get(Ids::COMPOUND, 20));
		self::register("chemical_benzene", $factory->get(Ids::COMPOUND, 33));
		self::register("chemical_boron_trioxide", $factory->get(Ids::COMPOUND, 14));
		self::register("chemical_calcium_bromide", $factory->get(Ids::COMPOUND, 7));
		self::register("chemical_calcium_chloride", $factory->get(Ids::COMPOUND, 25));
		self::register("chemical_cerium_chloride", $factory->get(Ids::COMPOUND, 23));
		self::register("chemical_charcoal", $factory->get(Ids::COMPOUND, 11));
		self::register("chemical_crude_oil", $factory->get(Ids::COMPOUND, 29));
		self::register("chemical_glue", $factory->get(Ids::COMPOUND, 27));
		self::register("chemical_hydrogen_peroxide", $factory->get(Ids::COMPOUND, 35));
		self::register("chemical_hypochlorite", $factory->get(Ids::COMPOUND, 28));
		self::register("chemical_ink", $factory->get(Ids::COMPOUND, 34));
		self::register("chemical_iron_sulphide", $factory->get(Ids::COMPOUND, 4));
		self::register("chemical_latex", $factory->get(Ids::COMPOUND, 30));
		self::register("chemical_lithium_hydride", $factory->get(Ids::COMPOUND, 5));
		self::register("chemical_luminol", $factory->get(Ids::COMPOUND, 10));
		self::register("chemical_magnesium_nitrate", $factory->get(Ids::COMPOUND, 3));
		self::register("chemical_magnesium_oxide", $factory->get(Ids::COMPOUND, 8));
		self::register("chemical_magnesium_salts", $factory->get(Ids::COMPOUND, 18));
		self::register("chemical_mercuric_chloride", $factory->get(Ids::COMPOUND, 22));
		self::register("chemical_polyethylene", $factory->get(Ids::COMPOUND, 16));
		self::register("chemical_potassium_chloride", $factory->get(Ids::COMPOUND, 21));
		self::register("chemical_potassium_iodide", $factory->get(Ids::COMPOUND, 31));
		self::register("chemical_rubbish", $factory->get(Ids::COMPOUND, 17));
		self::register("chemical_salt", $factory->get(Ids::COMPOUND));
		self::register("chemical_soap", $factory->get(Ids::COMPOUND, 15));
		self::register("chemical_sodium_acetate", $factory->get(Ids::COMPOUND, 9));
		self::register("chemical_sodium_fluoride", $factory->get(Ids::COMPOUND, 32));
		self::register("chemical_sodium_hydride", $factory->get(Ids::COMPOUND, 6));
		self::register("chemical_sodium_hydroxide", $factory->get(Ids::COMPOUND, 2));
		self::register("chemical_sodium_hypochlorite", $factory->get(Ids::COMPOUND, 37));
		self::register("chemical_sodium_oxide", $factory->get(Ids::COMPOUND, 1));
		self::register("chemical_sugar", $factory->get(Ids::COMPOUND, 12));
		self::register("chemical_sulphate", $factory->get(Ids::COMPOUND, 19));
		self::register("chemical_tungsten_chloride", $factory->get(Ids::COMPOUND, 24));
		self::register("chemical_water", $factory->get(Ids::COMPOUND, 26));
		self::register("chorus_fruit", $factory->get(Ids::CHORUS_FRUIT));
		self::register("clay", $factory->get(Ids::CLAY));
		self::register("clock", $factory->get(Ids::CLOCK));
		self::register("clownfish", $factory->get(Ids::CLOWNFISH));
		self::register("coal", $factory->get(Ids::COAL));
		self::register("cocoa_beans", $factory->get(Ids::DYE, 3));
		self::register("compass", $factory->get(Ids::COMPASS));
		self::register("cooked_chicken", $factory->get(Ids::COOKED_CHICKEN));
		self::register("cooked_fish", $factory->get(Ids::COOKED_FISH));
		self::register("cooked_mutton", $factory->get(Ids::COOKED_MUTTON));
		self::register("cooked_porkchop", $factory->get(Ids::COOKED_PORKCHOP));
		self::register("cooked_rabbit", $factory->get(Ids::COOKED_RABBIT));
		self::register("cooked_salmon", $factory->get(Ids::COOKED_SALMON));
		self::register("cookie", $factory->get(Ids::COOKIE));
		self::register("coral_fan", $factory->get(Ids::CORAL_FAN));
		self::register("dark_oak_boat", $factory->get(Ids::BOAT, 5));
		self::register("dark_oak_sign", $factory->get(Ids::DARKOAK_SIGN));
		self::register("diamond", $factory->get(Ids::DIAMOND));
		self::register("diamond_axe", $factory->get(Ids::DIAMOND_AXE));
		self::register("diamond_boots", $factory->get(Ids::DIAMOND_BOOTS));
		self::register("diamond_chestplate", $factory->get(Ids::DIAMOND_CHESTPLATE));
		self::register("diamond_helmet", $factory->get(Ids::DIAMOND_HELMET));
		self::register("diamond_hoe", $factory->get(Ids::DIAMOND_HOE));
		self::register("diamond_leggings", $factory->get(Ids::DIAMOND_LEGGINGS));
		self::register("diamond_pickaxe", $factory->get(Ids::DIAMOND_PICKAXE));
		self::register("diamond_shovel", $factory->get(Ids::DIAMOND_SHOVEL));
		self::register("diamond_sword", $factory->get(Ids::DIAMOND_SWORD));
		self::register("dragon_breath", $factory->get(Ids::DRAGON_BREATH));
		self::register("dried_kelp", $factory->get(Ids::DRIED_KELP));
		self::register("dye", $factory->get(Ids::DYE, 1));
		self::register("egg", $factory->get(Ids::EGG));
		self::register("emerald", $factory->get(Ids::EMERALD));
		self::register("enchanted_golden_apple", $factory->get(Ids::APPLEENCHANTED));
		self::register("ender_pearl", $factory->get(Ids::ENDER_PEARL));
		self::register("experience_bottle", $factory->get(Ids::BOTTLE_O_ENCHANTING));
		self::register("feather", $factory->get(Ids::FEATHER));
		self::register("fermented_spider_eye", $factory->get(Ids::FERMENTED_SPIDER_EYE));
		self::register("fishing_rod", $factory->get(Ids::FISHING_ROD));
		self::register("flint", $factory->get(Ids::FLINT));
		self::register("flint_and_steel", $factory->get(Ids::FLINT_AND_STEEL));
		self::register("ghast_tear", $factory->get(Ids::GHAST_TEAR));
		self::register("glass_bottle", $factory->get(Ids::GLASS_BOTTLE));
		self::register("glistering_melon", $factory->get(Ids::GLISTERING_MELON));
		self::register("glowstone_dust", $factory->get(Ids::GLOWSTONE_DUST));
		self::register("gold_ingot", $factory->get(Ids::GOLD_INGOT));
		self::register("gold_nugget", $factory->get(Ids::GOLDEN_NUGGET));
		self::register("golden_apple", $factory->get(Ids::GOLDEN_APPLE));
		self::register("golden_axe", $factory->get(Ids::GOLDEN_AXE));
		self::register("golden_boots", $factory->get(Ids::GOLDEN_BOOTS));
		self::register("golden_carrot", $factory->get(Ids::GOLDEN_CARROT));
		self::register("golden_chestplate", $factory->get(Ids::GOLDEN_CHESTPLATE));
		self::register("golden_helmet", $factory->get(Ids::GOLDEN_HELMET));
		self::register("golden_hoe", $factory->get(Ids::GOLDEN_HOE));
		self::register("golden_leggings", $factory->get(Ids::GOLDEN_LEGGINGS));
		self::register("golden_pickaxe", $factory->get(Ids::GOLDEN_PICKAXE));
		self::register("golden_shovel", $factory->get(Ids::GOLDEN_SHOVEL));
		self::register("golden_sword", $factory->get(Ids::GOLDEN_SWORD));
		self::register("gunpowder", $factory->get(Ids::GUNPOWDER));
		self::register("heart_of_the_sea", $factory->get(Ids::HEART_OF_THE_SEA));
		self::register("ink_sac", $factory->get(Ids::DYE));
		self::register("iron_axe", $factory->get(Ids::IRON_AXE));
		self::register("iron_boots", $factory->get(Ids::IRON_BOOTS));
		self::register("iron_chestplate", $factory->get(Ids::IRON_CHESTPLATE));
		self::register("iron_helmet", $factory->get(Ids::IRON_HELMET));
		self::register("iron_hoe", $factory->get(Ids::IRON_HOE));
		self::register("iron_ingot", $factory->get(Ids::IRON_INGOT));
		self::register("iron_leggings", $factory->get(Ids::IRON_LEGGINGS));
		self::register("iron_nugget", $factory->get(Ids::IRON_NUGGET));
		self::register("iron_pickaxe", $factory->get(Ids::IRON_PICKAXE));
		self::register("iron_shovel", $factory->get(Ids::IRON_SHOVEL));
		self::register("iron_sword", $factory->get(Ids::IRON_SWORD));
		self::register("jungle_boat", $factory->get(Ids::BOAT, 3));
		self::register("jungle_sign", $factory->get(Ids::JUNGLE_SIGN));
		self::register("lapis_lazuli", $factory->get(Ids::DYE, 4));
		self::register("lava_bucket", $factory->get(Ids::BUCKET, 10));
		self::register("leather", $factory->get(Ids::LEATHER));
		self::register("leather_boots", $factory->get(Ids::LEATHER_BOOTS));
		self::register("leather_cap", $factory->get(Ids::LEATHER_CAP));
		self::register("leather_pants", $factory->get(Ids::LEATHER_LEGGINGS));
		self::register("leather_tunic", $factory->get(Ids::LEATHER_CHESTPLATE));
		self::register("magma_cream", $factory->get(Ids::MAGMA_CREAM));
		self::register("melon", $factory->get(Ids::MELON));
		self::register("melon_seeds", $factory->get(Ids::MELON_SEEDS));
		self::register("milk_bucket", $factory->get(Ids::BUCKET, 1));
		self::register("minecart", $factory->get(Ids::MINECART));
		self::register("mushroom_stew", $factory->get(Ids::MUSHROOM_STEW));
		self::register("nautilus_shell", $factory->get(Ids::NAUTILUS_SHELL));
		self::register("nether_brick", $factory->get(Ids::NETHERBRICK));
		self::register("nether_quartz", $factory->get(Ids::NETHER_QUARTZ));
		self::register("nether_star", $factory->get(Ids::NETHERSTAR));
		self::register("oak_boat", $factory->get(Ids::BOAT));
		self::register("oak_sign", $factory->get(Ids::SIGN));
		self::register("painting", $factory->get(Ids::PAINTING));
		self::register("paper", $factory->get(Ids::PAPER));
		self::register("poisonous_potato", $factory->get(Ids::POISONOUS_POTATO));
		self::register("popped_chorus_fruit", $factory->get(Ids::CHORUS_FRUIT_POPPED));
		self::register("potato", $factory->get(Ids::POTATO));
		self::register("potion", $factory->get(Ids::POTION));
		self::register("prismarine_crystals", $factory->get(Ids::PRISMARINE_CRYSTALS));
		self::register("prismarine_shard", $factory->get(Ids::PRISMARINE_SHARD));
		self::register("pufferfish", $factory->get(Ids::PUFFERFISH));
		self::register("pumpkin_pie", $factory->get(Ids::PUMPKIN_PIE));
		self::register("pumpkin_seeds", $factory->get(Ids::PUMPKIN_SEEDS));
		self::register("rabbit_foot", $factory->get(Ids::RABBIT_FOOT));
		self::register("rabbit_hide", $factory->get(Ids::RABBIT_HIDE));
		self::register("rabbit_stew", $factory->get(Ids::RABBIT_STEW));
		self::register("raw_beef", $factory->get(Ids::BEEF));
		self::register("raw_chicken", $factory->get(Ids::CHICKEN));
		self::register("raw_fish", $factory->get(Ids::FISH));
		self::register("raw_mutton", $factory->get(Ids::MUTTON));
		self::register("raw_porkchop", $factory->get(Ids::PORKCHOP));
		self::register("raw_rabbit", $factory->get(Ids::RABBIT));
		self::register("raw_salmon", $factory->get(Ids::RAW_SALMON));
		self::register("record_11", $factory->get(Ids::RECORD_11));
		self::register("record_13", $factory->get(Ids::RECORD_13));
		self::register("record_blocks", $factory->get(Ids::RECORD_BLOCKS));
		self::register("record_cat", $factory->get(Ids::RECORD_CAT));
		self::register("record_chirp", $factory->get(Ids::RECORD_CHIRP));
		self::register("record_far", $factory->get(Ids::RECORD_FAR));
		self::register("record_mall", $factory->get(Ids::RECORD_MALL));
		self::register("record_mellohi", $factory->get(Ids::RECORD_MELLOHI));
		self::register("record_stal", $factory->get(Ids::RECORD_STAL));
		self::register("record_strad", $factory->get(Ids::RECORD_STRAD));
		self::register("record_wait", $factory->get(Ids::RECORD_WAIT));
		self::register("record_ward", $factory->get(Ids::RECORD_WARD));
		self::register("redstone_dust", $factory->get(Ids::REDSTONE));
		self::register("rotten_flesh", $factory->get(Ids::ROTTEN_FLESH));
		self::register("scute", $factory->get(Ids::TURTLE_SHELL_PIECE));
		self::register("shears", $factory->get(Ids::SHEARS));
		self::register("shulker_shell", $factory->get(Ids::SHULKER_SHELL));
		self::register("slimeball", $factory->get(Ids::SLIMEBALL));
		self::register("snowball", $factory->get(Ids::SNOWBALL));
		self::register("spider_eye", $factory->get(Ids::SPIDER_EYE));
		self::register("splash_potion", $factory->get(Ids::SPLASH_POTION));
		self::register("spruce_boat", $factory->get(Ids::BOAT, 1));
		self::register("spruce_sign", $factory->get(Ids::SPRUCE_SIGN));
		self::register("squid_spawn_egg", $factory->get(Ids::SPAWN_EGG, 17));
		self::register("steak", $factory->get(Ids::COOKED_BEEF));
		self::register("stick", $factory->get(Ids::STICK));
		self::register("stone_axe", $factory->get(Ids::STONE_AXE));
		self::register("stone_hoe", $factory->get(Ids::STONE_HOE));
		self::register("stone_pickaxe", $factory->get(Ids::STONE_PICKAXE));
		self::register("stone_shovel", $factory->get(Ids::STONE_SHOVEL));
		self::register("stone_sword", $factory->get(Ids::STONE_SWORD));
		self::register("string", $factory->get(Ids::STRING));
		self::register("sugar", $factory->get(Ids::SUGAR));
		self::register("sweet_berries", $factory->get(Ids::SWEET_BERRIES));
		self::register("totem", $factory->get(Ids::TOTEM));
		self::register("villager_spawn_egg", $factory->get(Ids::SPAWN_EGG, 15));
		self::register("water_bucket", $factory->get(Ids::BUCKET, 8));
		self::register("wheat", $factory->get(Ids::WHEAT));
		self::register("wheat_seeds", $factory->get(Ids::SEEDS));
		self::register("wooden_axe", $factory->get(Ids::WOODEN_AXE));
		self::register("wooden_hoe", $factory->get(Ids::WOODEN_HOE));
		self::register("wooden_pickaxe", $factory->get(Ids::WOODEN_PICKAXE));
		self::register("wooden_shovel", $factory->get(Ids::WOODEN_SHOVEL));
		self::register("wooden_sword", $factory->get(Ids::WOODEN_SWORD));
		self::register("writable_book", $factory->get(Ids::WRITABLE_BOOK));
		self::register("written_book", $factory->get(Ids::WRITTEN_BOOK));
		self::register("zombie_spawn_egg", $factory->get(Ids::SPAWN_EGG, 32));
	}
}
