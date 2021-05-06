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

use pocketmine\utils\CloningRegistryTrait;
use function assert;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
 *
 * @method static Boat ACACIA_BOAT()
 * @method static Apple APPLE()
 * @method static Arrow ARROW()
 * @method static BakedPotato BAKED_POTATO()
 * @method static Beetroot BEETROOT()
 * @method static BeetrootSeeds BEETROOT_SEEDS()
 * @method static BeetrootSoup BEETROOT_SOUP()
 * @method static Boat BIRCH_BOAT()
 * @method static Bed BLACK_BED()
 * @method static Dye BLACK_DYE()
 * @method static Item BLAZE_POWDER()
 * @method static BlazeRod BLAZE_ROD()
 * @method static Item BLEACH()
 * @method static Bed BLUE_BED()
 * @method static Dye BLUE_DYE()
 * @method static Item BONE()
 * @method static Fertilizer BONE_MEAL()
 * @method static Book BOOK()
 * @method static Bow BOW()
 * @method static Bowl BOWL()
 * @method static Bread BREAD()
 * @method static Item BRICK()
 * @method static Bed BROWN_BED()
 * @method static Dye BROWN_DYE()
 * @method static Bucket BUCKET()
 * @method static ItemBlock CAKE()
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
 * @method static Skull CREEPER_HEAD()
 * @method static Bed CYAN_BED()
 * @method static Dye CYAN_DYE()
 * @method static Boat DARK_OAK_BOAT()
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
 * @method static Skull DRAGON_HEAD()
 * @method static DriedKelp DRIED_KELP()
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
 * @method static Bed GRAY_BED()
 * @method static Dye GRAY_DYE()
 * @method static Bed GREEN_BED()
 * @method static Dye GREEN_DYE()
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
 * @method static Item LAPIS_LAZULI()
 * @method static LiquidBucket LAVA_BUCKET()
 * @method static Item LEATHER()
 * @method static Armor LEATHER_BOOTS()
 * @method static Armor LEATHER_CAP()
 * @method static Armor LEATHER_PANTS()
 * @method static Armor LEATHER_TUNIC()
 * @method static Bed LIGHT_BLUE_BED()
 * @method static Dye LIGHT_BLUE_DYE()
 * @method static Bed LIGHT_GRAY_BED()
 * @method static Dye LIGHT_GRAY_DYE()
 * @method static Bed LIME_BED()
 * @method static Dye LIME_DYE()
 * @method static Bed MAGENTA_BED()
 * @method static Dye MAGENTA_DYE()
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
 * @method static ItemBlock NETHER_WART()
 * @method static Boat OAK_BOAT()
 * @method static Bed ORANGE_BED()
 * @method static Dye ORANGE_DYE()
 * @method static PaintingItem PAINTING()
 * @method static Item PAPER()
 * @method static Bed PINK_BED()
 * @method static Dye PINK_DYE()
 * @method static Skull PLAYER_HEAD()
 * @method static PoisonousPotato POISONOUS_POTATO()
 * @method static Item POPPED_CHORUS_FRUIT()
 * @method static Potato POTATO()
 * @method static Potion POTION()
 * @method static Item PRISMARINE_CRYSTALS()
 * @method static Item PRISMARINE_SHARD()
 * @method static Pufferfish PUFFERFISH()
 * @method static PumpkinPie PUMPKIN_PIE()
 * @method static PumpkinSeeds PUMPKIN_SEEDS()
 * @method static Bed PURPLE_BED()
 * @method static Dye PURPLE_DYE()
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
 * @method static Bed RED_BED()
 * @method static Dye RED_DYE()
 * @method static RottenFlesh ROTTEN_FLESH()
 * @method static Item SCUTE()
 * @method static Shears SHEARS()
 * @method static Item SHULKER_SHELL()
 * @method static Skull SKELETON_SKULL()
 * @method static Item SLIMEBALL()
 * @method static Snowball SNOWBALL()
 * @method static SpiderEye SPIDER_EYE()
 * @method static SplashPotion SPLASH_POTION()
 * @method static Boat SPRUCE_BOAT()
 * @method static Steak STEAK()
 * @method static Stick STICK()
 * @method static Axe STONE_AXE()
 * @method static Hoe STONE_HOE()
 * @method static Pickaxe STONE_PICKAXE()
 * @method static Shovel STONE_SHOVEL()
 * @method static Sword STONE_SWORD()
 * @method static StringItem STRING()
 * @method static Item SUGAR()
 * @method static ItemBlock SUGARCANE()
 * @method static Totem TOTEM()
 * @method static LiquidBucket WATER_BUCKET()
 * @method static Item WHEAT()
 * @method static WheatSeeds WHEAT_SEEDS()
 * @method static Bed WHITE_BED()
 * @method static Dye WHITE_DYE()
 * @method static Skull WITHER_SKELETON_SKULL()
 * @method static Axe WOODEN_AXE()
 * @method static Hoe WOODEN_HOE()
 * @method static Pickaxe WOODEN_PICKAXE()
 * @method static Shovel WOODEN_SHOVEL()
 * @method static Sword WOODEN_SWORD()
 * @method static WritableBook WRITABLE_BOOK()
 * @method static WrittenBook WRITTEN_BOOK()
 * @method static Bed YELLOW_BED()
 * @method static Dye YELLOW_DYE()
 * @method static Skull ZOMBIE_HEAD()
 */
final class VanillaItems{
	use CloningRegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, Item $item) : void{
		self::_registryRegister($name, $item);
	}

	public static function fromString(string $name) : Item{
		$result = self::_registryFromString($name);
		assert($result instanceof Item);
		return $result;
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
		self::register("acacia_boat", $factory->get(333, 4));
		self::register("apple", $factory->get(260));
		self::register("arrow", $factory->get(262));
		self::register("baked_potato", $factory->get(393));
		self::register("beetroot", $factory->get(457));
		self::register("beetroot_seeds", $factory->get(458));
		self::register("beetroot_soup", $factory->get(459));
		self::register("birch_boat", $factory->get(333, 2));
		self::register("black_bed", $factory->get(355, 15));
		self::register("black_dye", $factory->get(351, 16));
		self::register("blaze_powder", $factory->get(377));
		self::register("blaze_rod", $factory->get(369));
		self::register("bleach", $factory->get(451));
		self::register("blue_bed", $factory->get(355, 11));
		self::register("blue_dye", $factory->get(351, 18));
		self::register("bone", $factory->get(352));
		self::register("bone_meal", $factory->get(351, 15));
		self::register("book", $factory->get(340));
		self::register("bow", $factory->get(261));
		self::register("bowl", $factory->get(281));
		self::register("bread", $factory->get(297));
		self::register("brick", $factory->get(336));
		self::register("brown_bed", $factory->get(355, 12));
		self::register("brown_dye", $factory->get(351, 17));
		self::register("bucket", $factory->get(325));
		self::register("cake", $factory->get(354));
		self::register("carrot", $factory->get(391));
		self::register("chainmail_boots", $factory->get(305));
		self::register("chainmail_chestplate", $factory->get(303));
		self::register("chainmail_helmet", $factory->get(302));
		self::register("chainmail_leggings", $factory->get(304));
		self::register("charcoal", $factory->get(263, 1));
		self::register("chemical_aluminium_oxide", $factory->get(499, 13));
		self::register("chemical_ammonia", $factory->get(499, 36));
		self::register("chemical_barium_sulphate", $factory->get(499, 20));
		self::register("chemical_benzene", $factory->get(499, 33));
		self::register("chemical_boron_trioxide", $factory->get(499, 14));
		self::register("chemical_calcium_bromide", $factory->get(499, 7));
		self::register("chemical_calcium_chloride", $factory->get(499, 25));
		self::register("chemical_cerium_chloride", $factory->get(499, 23));
		self::register("chemical_charcoal", $factory->get(499, 11));
		self::register("chemical_crude_oil", $factory->get(499, 29));
		self::register("chemical_glue", $factory->get(499, 27));
		self::register("chemical_hydrogen_peroxide", $factory->get(499, 35));
		self::register("chemical_hypochlorite", $factory->get(499, 28));
		self::register("chemical_ink", $factory->get(499, 34));
		self::register("chemical_iron_sulphide", $factory->get(499, 4));
		self::register("chemical_latex", $factory->get(499, 30));
		self::register("chemical_lithium_hydride", $factory->get(499, 5));
		self::register("chemical_luminol", $factory->get(499, 10));
		self::register("chemical_magnesium_nitrate", $factory->get(499, 3));
		self::register("chemical_magnesium_oxide", $factory->get(499, 8));
		self::register("chemical_magnesium_salts", $factory->get(499, 18));
		self::register("chemical_mercuric_chloride", $factory->get(499, 22));
		self::register("chemical_polyethylene", $factory->get(499, 16));
		self::register("chemical_potassium_chloride", $factory->get(499, 21));
		self::register("chemical_potassium_iodide", $factory->get(499, 31));
		self::register("chemical_rubbish", $factory->get(499, 17));
		self::register("chemical_salt", $factory->get(499));
		self::register("chemical_soap", $factory->get(499, 15));
		self::register("chemical_sodium_acetate", $factory->get(499, 9));
		self::register("chemical_sodium_fluoride", $factory->get(499, 32));
		self::register("chemical_sodium_hydride", $factory->get(499, 6));
		self::register("chemical_sodium_hydroxide", $factory->get(499, 2));
		self::register("chemical_sodium_hypochlorite", $factory->get(499, 37));
		self::register("chemical_sodium_oxide", $factory->get(499, 1));
		self::register("chemical_sugar", $factory->get(499, 12));
		self::register("chemical_sulphate", $factory->get(499, 19));
		self::register("chemical_tungsten_chloride", $factory->get(499, 24));
		self::register("chemical_water", $factory->get(499, 26));
		self::register("chorus_fruit", $factory->get(432));
		self::register("clay", $factory->get(337));
		self::register("clock", $factory->get(347));
		self::register("clownfish", $factory->get(461));
		self::register("coal", $factory->get(263));
		self::register("cocoa_beans", $factory->get(351, 3));
		self::register("compass", $factory->get(345));
		self::register("cooked_chicken", $factory->get(366));
		self::register("cooked_fish", $factory->get(350));
		self::register("cooked_mutton", $factory->get(424));
		self::register("cooked_porkchop", $factory->get(320));
		self::register("cooked_rabbit", $factory->get(412));
		self::register("cooked_salmon", $factory->get(463));
		self::register("cookie", $factory->get(357));
		self::register("creeper_head", $factory->get(397, 4));
		self::register("cyan_bed", $factory->get(355, 9));
		self::register("cyan_dye", $factory->get(351, 6));
		self::register("dark_oak_boat", $factory->get(333, 5));
		self::register("diamond", $factory->get(264));
		self::register("diamond_axe", $factory->get(279));
		self::register("diamond_boots", $factory->get(313));
		self::register("diamond_chestplate", $factory->get(311));
		self::register("diamond_helmet", $factory->get(310));
		self::register("diamond_hoe", $factory->get(293));
		self::register("diamond_leggings", $factory->get(312));
		self::register("diamond_pickaxe", $factory->get(278));
		self::register("diamond_shovel", $factory->get(277));
		self::register("diamond_sword", $factory->get(276));
		self::register("dragon_breath", $factory->get(437));
		self::register("dragon_head", $factory->get(397, 5));
		self::register("dried_kelp", $factory->get(464));
		self::register("egg", $factory->get(344));
		self::register("emerald", $factory->get(388));
		self::register("enchanted_golden_apple", $factory->get(466));
		self::register("ender_pearl", $factory->get(368));
		self::register("experience_bottle", $factory->get(384));
		self::register("feather", $factory->get(288));
		self::register("fermented_spider_eye", $factory->get(376));
		self::register("fishing_rod", $factory->get(346));
		self::register("flint", $factory->get(318));
		self::register("flint_and_steel", $factory->get(259));
		self::register("ghast_tear", $factory->get(370));
		self::register("glass_bottle", $factory->get(374));
		self::register("glistering_melon", $factory->get(382));
		self::register("glowstone_dust", $factory->get(348));
		self::register("gold_ingot", $factory->get(266));
		self::register("gold_nugget", $factory->get(371));
		self::register("golden_apple", $factory->get(322));
		self::register("golden_axe", $factory->get(286));
		self::register("golden_boots", $factory->get(317));
		self::register("golden_carrot", $factory->get(396));
		self::register("golden_chestplate", $factory->get(315));
		self::register("golden_helmet", $factory->get(314));
		self::register("golden_hoe", $factory->get(294));
		self::register("golden_leggings", $factory->get(316));
		self::register("golden_pickaxe", $factory->get(285));
		self::register("golden_shovel", $factory->get(284));
		self::register("golden_sword", $factory->get(283));
		self::register("gray_bed", $factory->get(355, 7));
		self::register("gray_dye", $factory->get(351, 8));
		self::register("green_bed", $factory->get(355, 13));
		self::register("green_dye", $factory->get(351, 2));
		self::register("gunpowder", $factory->get(289));
		self::register("heart_of_the_sea", $factory->get(467));
		self::register("ink_sac", $factory->get(351));
		self::register("iron_axe", $factory->get(258));
		self::register("iron_boots", $factory->get(309));
		self::register("iron_chestplate", $factory->get(307));
		self::register("iron_helmet", $factory->get(306));
		self::register("iron_hoe", $factory->get(292));
		self::register("iron_ingot", $factory->get(265));
		self::register("iron_leggings", $factory->get(308));
		self::register("iron_nugget", $factory->get(452));
		self::register("iron_pickaxe", $factory->get(257));
		self::register("iron_shovel", $factory->get(256));
		self::register("iron_sword", $factory->get(267));
		self::register("jungle_boat", $factory->get(333, 3));
		self::register("lapis_lazuli", $factory->get(351, 4));
		self::register("lava_bucket", $factory->get(325, 10));
		self::register("leather", $factory->get(334));
		self::register("leather_boots", $factory->get(301));
		self::register("leather_cap", $factory->get(298));
		self::register("leather_pants", $factory->get(300));
		self::register("leather_tunic", $factory->get(299));
		self::register("light_blue_bed", $factory->get(355, 3));
		self::register("light_blue_dye", $factory->get(351, 12));
		self::register("light_gray_bed", $factory->get(355, 8));
		self::register("light_gray_dye", $factory->get(351, 7));
		self::register("lime_bed", $factory->get(355, 5));
		self::register("lime_dye", $factory->get(351, 10));
		self::register("magenta_bed", $factory->get(355, 2));
		self::register("magenta_dye", $factory->get(351, 13));
		self::register("magma_cream", $factory->get(378));
		self::register("melon", $factory->get(360));
		self::register("melon_seeds", $factory->get(362));
		self::register("milk_bucket", $factory->get(325, 1));
		self::register("minecart", $factory->get(328));
		self::register("mushroom_stew", $factory->get(282));
		self::register("nautilus_shell", $factory->get(465));
		self::register("nether_brick", $factory->get(405));
		self::register("nether_quartz", $factory->get(406));
		self::register("nether_star", $factory->get(399));
		self::register("nether_wart", $factory->get(372));
		self::register("oak_boat", $factory->get(333));
		self::register("orange_bed", $factory->get(355, 1));
		self::register("orange_dye", $factory->get(351, 14));
		self::register("painting", $factory->get(321));
		self::register("paper", $factory->get(339));
		self::register("pink_bed", $factory->get(355, 6));
		self::register("pink_dye", $factory->get(351, 9));
		self::register("player_head", $factory->get(397, 3));
		self::register("poisonous_potato", $factory->get(394));
		self::register("popped_chorus_fruit", $factory->get(433));
		self::register("potato", $factory->get(392));
		self::register("potion", $factory->get(373));
		self::register("prismarine_crystals", $factory->get(422));
		self::register("prismarine_shard", $factory->get(409));
		self::register("pufferfish", $factory->get(462));
		self::register("pumpkin_pie", $factory->get(400));
		self::register("pumpkin_seeds", $factory->get(361));
		self::register("purple_bed", $factory->get(355, 10));
		self::register("purple_dye", $factory->get(351, 5));
		self::register("rabbit_foot", $factory->get(414));
		self::register("rabbit_hide", $factory->get(415));
		self::register("rabbit_stew", $factory->get(413));
		self::register("raw_beef", $factory->get(363));
		self::register("raw_chicken", $factory->get(365));
		self::register("raw_fish", $factory->get(349));
		self::register("raw_mutton", $factory->get(423));
		self::register("raw_porkchop", $factory->get(319));
		self::register("raw_rabbit", $factory->get(411));
		self::register("raw_salmon", $factory->get(460));
		self::register("record_11", $factory->get(510));
		self::register("record_13", $factory->get(500));
		self::register("record_blocks", $factory->get(502));
		self::register("record_cat", $factory->get(501));
		self::register("record_chirp", $factory->get(503));
		self::register("record_far", $factory->get(504));
		self::register("record_mall", $factory->get(505));
		self::register("record_mellohi", $factory->get(506));
		self::register("record_stal", $factory->get(507));
		self::register("record_strad", $factory->get(508));
		self::register("record_wait", $factory->get(511));
		self::register("record_ward", $factory->get(509));
		self::register("red_bed", $factory->get(355, 14));
		self::register("red_dye", $factory->get(351, 1));
		self::register("redstone_dust", $factory->get(331));
		self::register("rotten_flesh", $factory->get(367));
		self::register("scute", $factory->get(468));
		self::register("shears", $factory->get(359));
		self::register("shulker_shell", $factory->get(445));
		self::register("skeleton_skull", $factory->get(397));
		self::register("slimeball", $factory->get(341));
		self::register("snowball", $factory->get(332));
		self::register("spider_eye", $factory->get(375));
		self::register("splash_potion", $factory->get(438));
		self::register("spruce_boat", $factory->get(333, 1));
		self::register("steak", $factory->get(364));
		self::register("stick", $factory->get(280));
		self::register("stone_axe", $factory->get(275));
		self::register("stone_hoe", $factory->get(291));
		self::register("stone_pickaxe", $factory->get(274));
		self::register("stone_shovel", $factory->get(273));
		self::register("stone_sword", $factory->get(272));
		self::register("string", $factory->get(287));
		self::register("sugar", $factory->get(353));
		self::register("sugarcane", $factory->get(338));
		self::register("totem", $factory->get(450));
		self::register("water_bucket", $factory->get(325, 8));
		self::register("wheat", $factory->get(296));
		self::register("wheat_seeds", $factory->get(295));
		self::register("white_bed", $factory->get(355));
		self::register("white_dye", $factory->get(351, 19));
		self::register("wither_skeleton_skull", $factory->get(397, 1));
		self::register("wooden_axe", $factory->get(271));
		self::register("wooden_hoe", $factory->get(290));
		self::register("wooden_pickaxe", $factory->get(270));
		self::register("wooden_shovel", $factory->get(269));
		self::register("wooden_sword", $factory->get(268));
		self::register("writable_book", $factory->get(386));
		self::register("written_book", $factory->get(387));
		self::register("yellow_bed", $factory->get(355, 4));
		self::register("yellow_dye", $factory->get(351, 11));
		self::register("zombie_head", $factory->get(397, 2));
	}
}
