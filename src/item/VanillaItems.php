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

use pocketmine\utils\RegistryTrait;
use pocketmine\utils\Utils;
use function assert;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see RegistryTrait::_generateMethodAnnotations()
 *
 * @method static Boat ACACIA_BOAT()
 * @method static Apple APPLE()
 * @method static Arrow ARROW()
 * @method static BakedPotato BAKED_POTATO()
 * @method static Beetroot BEETROOT()
 * @method static BeetrootSeeds BEETROOT_SEEDS()
 * @method static BeetrootSoup BEETROOT_SOUP()
 * @method static Boat BIRCH_BOAT()
 * @method static Banner BLACK_BANNER()
 * @method static Bed BLACK_BED()
 * @method static Dye BLACK_DYE()
 * @method static Item BLAZE_POWDER()
 * @method static BlazeRod BLAZE_ROD()
 * @method static Item BLEACH()
 * @method static Banner BLUE_BANNER()
 * @method static Bed BLUE_BED()
 * @method static Dye BLUE_DYE()
 * @method static Item BONE()
 * @method static Fertilizer BONE_MEAL()
 * @method static Book BOOK()
 * @method static Bow BOW()
 * @method static Bowl BOWL()
 * @method static Bread BREAD()
 * @method static Item BRICK()
 * @method static Banner BROWN_BANNER()
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
 * @method static Banner CYAN_BANNER()
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
 * @method static Item GOLD_INGOT()
 * @method static Item GOLD_NUGGET()
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
 * @method static Banner GRAY_BANNER()
 * @method static Bed GRAY_BED()
 * @method static Dye GRAY_DYE()
 * @method static Banner GREEN_BANNER()
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
 * @method static Banner LIGHT_BLUE_BANNER()
 * @method static Bed LIGHT_BLUE_BED()
 * @method static Dye LIGHT_BLUE_DYE()
 * @method static Banner LIGHT_GRAY_BANNER()
 * @method static Bed LIGHT_GRAY_BED()
 * @method static Dye LIGHT_GRAY_DYE()
 * @method static Banner LIME_BANNER()
 * @method static Bed LIME_BED()
 * @method static Dye LIME_DYE()
 * @method static Banner MAGENTA_BANNER()
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
 * @method static Banner ORANGE_BANNER()
 * @method static Bed ORANGE_BED()
 * @method static Dye ORANGE_DYE()
 * @method static PaintingItem PAINTING()
 * @method static Item PAPER()
 * @method static Banner PINK_BANNER()
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
 * @method static Banner PURPLE_BANNER()
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
 * @method static Banner RED_BANNER()
 * @method static Bed RED_BED()
 * @method static Dye RED_DYE()
 * @method static Redstone REDSTONE_DUST()
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
 * @method static Banner WHITE_BANNER()
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
 * @method static Banner YELLOW_BANNER()
 * @method static Bed YELLOW_BED()
 * @method static Dye YELLOW_DYE()
 * @method static Skull ZOMBIE_HEAD()
 */
final class VanillaItems{
	use RegistryTrait;

	private function __construct(){
		//NOOP
	}

	protected static function register(string $name, Item $item) : void{
		self::_registryRegister($name, $item);
	}

	/**
	 * @param string $name
	 *
	 * @return Item
	 */
	public static function fromString(string $name) : Item{
		$result = self::_registryFromString($name);
		assert($result instanceof Item);
		return clone $result;
	}

	/**
	 * @return Item[]
	 */
	public static function getAll() : array{
		return Utils::cloneObjectArray(self::_registryGetAll());
	}

	protected static function setup() : void{
		self::register("acacia_boat", ItemFactory::get(333, 4));
		self::register("apple", ItemFactory::get(260));
		self::register("arrow", ItemFactory::get(262));
		self::register("baked_potato", ItemFactory::get(393));
		self::register("beetroot", ItemFactory::get(457));
		self::register("beetroot_seeds", ItemFactory::get(458));
		self::register("beetroot_soup", ItemFactory::get(459));
		self::register("birch_boat", ItemFactory::get(333, 2));
		self::register("black_banner", ItemFactory::get(446));
		self::register("black_bed", ItemFactory::get(355, 15));
		self::register("black_dye", ItemFactory::get(351, 16));
		self::register("blaze_powder", ItemFactory::get(377));
		self::register("blaze_rod", ItemFactory::get(369));
		self::register("bleach", ItemFactory::get(451));
		self::register("blue_banner", ItemFactory::get(446, 4));
		self::register("blue_bed", ItemFactory::get(355, 11));
		self::register("blue_dye", ItemFactory::get(351, 18));
		self::register("bone", ItemFactory::get(352));
		self::register("bone_meal", ItemFactory::get(351, 15));
		self::register("book", ItemFactory::get(340));
		self::register("bow", ItemFactory::get(261));
		self::register("bowl", ItemFactory::get(281));
		self::register("bread", ItemFactory::get(297));
		self::register("brick", ItemFactory::get(336));
		self::register("brown_banner", ItemFactory::get(446, 3));
		self::register("brown_bed", ItemFactory::get(355, 12));
		self::register("brown_dye", ItemFactory::get(351, 17));
		self::register("bucket", ItemFactory::get(325));
		self::register("cake", ItemFactory::get(354));
		self::register("carrot", ItemFactory::get(391));
		self::register("chainmail_boots", ItemFactory::get(305));
		self::register("chainmail_chestplate", ItemFactory::get(303));
		self::register("chainmail_helmet", ItemFactory::get(302));
		self::register("chainmail_leggings", ItemFactory::get(304));
		self::register("charcoal", ItemFactory::get(263, 1));
		self::register("chemical_aluminium_oxide", ItemFactory::get(499, 13));
		self::register("chemical_ammonia", ItemFactory::get(499, 36));
		self::register("chemical_barium_sulphate", ItemFactory::get(499, 20));
		self::register("chemical_benzene", ItemFactory::get(499, 33));
		self::register("chemical_boron_trioxide", ItemFactory::get(499, 14));
		self::register("chemical_calcium_bromide", ItemFactory::get(499, 7));
		self::register("chemical_calcium_chloride", ItemFactory::get(499, 25));
		self::register("chemical_cerium_chloride", ItemFactory::get(499, 23));
		self::register("chemical_charcoal", ItemFactory::get(499, 11));
		self::register("chemical_crude_oil", ItemFactory::get(499, 29));
		self::register("chemical_glue", ItemFactory::get(499, 27));
		self::register("chemical_hydrogen_peroxide", ItemFactory::get(499, 35));
		self::register("chemical_hypochlorite", ItemFactory::get(499, 28));
		self::register("chemical_ink", ItemFactory::get(499, 34));
		self::register("chemical_iron_sulphide", ItemFactory::get(499, 4));
		self::register("chemical_latex", ItemFactory::get(499, 30));
		self::register("chemical_lithium_hydride", ItemFactory::get(499, 5));
		self::register("chemical_luminol", ItemFactory::get(499, 10));
		self::register("chemical_magnesium_nitrate", ItemFactory::get(499, 3));
		self::register("chemical_magnesium_oxide", ItemFactory::get(499, 8));
		self::register("chemical_magnesium_salts", ItemFactory::get(499, 18));
		self::register("chemical_mercuric_chloride", ItemFactory::get(499, 22));
		self::register("chemical_polyethylene", ItemFactory::get(499, 16));
		self::register("chemical_potassium_chloride", ItemFactory::get(499, 21));
		self::register("chemical_potassium_iodide", ItemFactory::get(499, 31));
		self::register("chemical_rubbish", ItemFactory::get(499, 17));
		self::register("chemical_salt", ItemFactory::get(499));
		self::register("chemical_soap", ItemFactory::get(499, 15));
		self::register("chemical_sodium_acetate", ItemFactory::get(499, 9));
		self::register("chemical_sodium_fluoride", ItemFactory::get(499, 32));
		self::register("chemical_sodium_hydride", ItemFactory::get(499, 6));
		self::register("chemical_sodium_hydroxide", ItemFactory::get(499, 2));
		self::register("chemical_sodium_hypochlorite", ItemFactory::get(499, 37));
		self::register("chemical_sodium_oxide", ItemFactory::get(499, 1));
		self::register("chemical_sugar", ItemFactory::get(499, 12));
		self::register("chemical_sulphate", ItemFactory::get(499, 19));
		self::register("chemical_tungsten_chloride", ItemFactory::get(499, 24));
		self::register("chemical_water", ItemFactory::get(499, 26));
		self::register("chorus_fruit", ItemFactory::get(432));
		self::register("clay", ItemFactory::get(337));
		self::register("clock", ItemFactory::get(347));
		self::register("clownfish", ItemFactory::get(461));
		self::register("coal", ItemFactory::get(263));
		self::register("cocoa_beans", ItemFactory::get(351, 3));
		self::register("compass", ItemFactory::get(345));
		self::register("cooked_chicken", ItemFactory::get(366));
		self::register("cooked_fish", ItemFactory::get(350));
		self::register("cooked_mutton", ItemFactory::get(424));
		self::register("cooked_porkchop", ItemFactory::get(320));
		self::register("cooked_rabbit", ItemFactory::get(412));
		self::register("cooked_salmon", ItemFactory::get(463));
		self::register("cookie", ItemFactory::get(357));
		self::register("creeper_head", ItemFactory::get(397, 4));
		self::register("cyan_banner", ItemFactory::get(446, 6));
		self::register("cyan_bed", ItemFactory::get(355, 9));
		self::register("cyan_dye", ItemFactory::get(351, 6));
		self::register("dark_oak_boat", ItemFactory::get(333, 5));
		self::register("diamond", ItemFactory::get(264));
		self::register("diamond_axe", ItemFactory::get(279));
		self::register("diamond_boots", ItemFactory::get(313));
		self::register("diamond_chestplate", ItemFactory::get(311));
		self::register("diamond_helmet", ItemFactory::get(310));
		self::register("diamond_hoe", ItemFactory::get(293));
		self::register("diamond_leggings", ItemFactory::get(312));
		self::register("diamond_pickaxe", ItemFactory::get(278));
		self::register("diamond_shovel", ItemFactory::get(277));
		self::register("diamond_sword", ItemFactory::get(276));
		self::register("dragon_breath", ItemFactory::get(437));
		self::register("dragon_head", ItemFactory::get(397, 5));
		self::register("dried_kelp", ItemFactory::get(464));
		self::register("egg", ItemFactory::get(344));
		self::register("emerald", ItemFactory::get(388));
		self::register("enchanted_golden_apple", ItemFactory::get(466));
		self::register("ender_pearl", ItemFactory::get(368));
		self::register("experience_bottle", ItemFactory::get(384));
		self::register("feather", ItemFactory::get(288));
		self::register("fermented_spider_eye", ItemFactory::get(376));
		self::register("fishing_rod", ItemFactory::get(346));
		self::register("flint", ItemFactory::get(318));
		self::register("flint_and_steel", ItemFactory::get(259));
		self::register("ghast_tear", ItemFactory::get(370));
		self::register("glass_bottle", ItemFactory::get(374));
		self::register("glistering_melon", ItemFactory::get(382));
		self::register("glowstone_dust", ItemFactory::get(348));
		self::register("gold_ingot", ItemFactory::get(266));
		self::register("gold_nugget", ItemFactory::get(371));
		self::register("golden_apple", ItemFactory::get(322));
		self::register("golden_axe", ItemFactory::get(286));
		self::register("golden_boots", ItemFactory::get(317));
		self::register("golden_carrot", ItemFactory::get(396));
		self::register("golden_chestplate", ItemFactory::get(315));
		self::register("golden_helmet", ItemFactory::get(314));
		self::register("golden_hoe", ItemFactory::get(294));
		self::register("golden_leggings", ItemFactory::get(316));
		self::register("golden_pickaxe", ItemFactory::get(285));
		self::register("golden_shovel", ItemFactory::get(284));
		self::register("golden_sword", ItemFactory::get(283));
		self::register("gray_banner", ItemFactory::get(446, 8));
		self::register("gray_bed", ItemFactory::get(355, 7));
		self::register("gray_dye", ItemFactory::get(351, 8));
		self::register("green_banner", ItemFactory::get(446, 2));
		self::register("green_bed", ItemFactory::get(355, 13));
		self::register("green_dye", ItemFactory::get(351, 2));
		self::register("gunpowder", ItemFactory::get(289));
		self::register("heart_of_the_sea", ItemFactory::get(467));
		self::register("ink_sac", ItemFactory::get(351));
		self::register("iron_axe", ItemFactory::get(258));
		self::register("iron_boots", ItemFactory::get(309));
		self::register("iron_chestplate", ItemFactory::get(307));
		self::register("iron_helmet", ItemFactory::get(306));
		self::register("iron_hoe", ItemFactory::get(292));
		self::register("iron_ingot", ItemFactory::get(265));
		self::register("iron_leggings", ItemFactory::get(308));
		self::register("iron_nugget", ItemFactory::get(452));
		self::register("iron_pickaxe", ItemFactory::get(257));
		self::register("iron_shovel", ItemFactory::get(256));
		self::register("iron_sword", ItemFactory::get(267));
		self::register("jungle_boat", ItemFactory::get(333, 3));
		self::register("lapis_lazuli", ItemFactory::get(351, 4));
		self::register("lava_bucket", ItemFactory::get(325, 10));
		self::register("leather", ItemFactory::get(334));
		self::register("leather_boots", ItemFactory::get(301));
		self::register("leather_cap", ItemFactory::get(298));
		self::register("leather_pants", ItemFactory::get(300));
		self::register("leather_tunic", ItemFactory::get(299));
		self::register("light_blue_banner", ItemFactory::get(446, 12));
		self::register("light_blue_bed", ItemFactory::get(355, 3));
		self::register("light_blue_dye", ItemFactory::get(351, 12));
		self::register("light_gray_banner", ItemFactory::get(446, 7));
		self::register("light_gray_bed", ItemFactory::get(355, 8));
		self::register("light_gray_dye", ItemFactory::get(351, 7));
		self::register("lime_banner", ItemFactory::get(446, 10));
		self::register("lime_bed", ItemFactory::get(355, 5));
		self::register("lime_dye", ItemFactory::get(351, 10));
		self::register("magenta_banner", ItemFactory::get(446, 13));
		self::register("magenta_bed", ItemFactory::get(355, 2));
		self::register("magenta_dye", ItemFactory::get(351, 13));
		self::register("magma_cream", ItemFactory::get(378));
		self::register("melon", ItemFactory::get(360));
		self::register("melon_seeds", ItemFactory::get(362));
		self::register("milk_bucket", ItemFactory::get(325, 1));
		self::register("minecart", ItemFactory::get(328));
		self::register("mushroom_stew", ItemFactory::get(282));
		self::register("nautilus_shell", ItemFactory::get(465));
		self::register("nether_brick", ItemFactory::get(405));
		self::register("nether_quartz", ItemFactory::get(406));
		self::register("nether_star", ItemFactory::get(399));
		self::register("nether_wart", ItemFactory::get(372));
		self::register("oak_boat", ItemFactory::get(333));
		self::register("orange_banner", ItemFactory::get(446, 14));
		self::register("orange_bed", ItemFactory::get(355, 1));
		self::register("orange_dye", ItemFactory::get(351, 14));
		self::register("painting", ItemFactory::get(321));
		self::register("paper", ItemFactory::get(339));
		self::register("pink_banner", ItemFactory::get(446, 9));
		self::register("pink_bed", ItemFactory::get(355, 6));
		self::register("pink_dye", ItemFactory::get(351, 9));
		self::register("player_head", ItemFactory::get(397, 3));
		self::register("poisonous_potato", ItemFactory::get(394));
		self::register("popped_chorus_fruit", ItemFactory::get(433));
		self::register("potato", ItemFactory::get(392));
		self::register("potion", ItemFactory::get(373));
		self::register("prismarine_crystals", ItemFactory::get(422));
		self::register("prismarine_shard", ItemFactory::get(409));
		self::register("pufferfish", ItemFactory::get(462));
		self::register("pumpkin_pie", ItemFactory::get(400));
		self::register("pumpkin_seeds", ItemFactory::get(361));
		self::register("purple_banner", ItemFactory::get(446, 5));
		self::register("purple_bed", ItemFactory::get(355, 10));
		self::register("purple_dye", ItemFactory::get(351, 5));
		self::register("rabbit_foot", ItemFactory::get(414));
		self::register("rabbit_hide", ItemFactory::get(415));
		self::register("rabbit_stew", ItemFactory::get(413));
		self::register("raw_beef", ItemFactory::get(363));
		self::register("raw_chicken", ItemFactory::get(365));
		self::register("raw_fish", ItemFactory::get(349));
		self::register("raw_mutton", ItemFactory::get(423));
		self::register("raw_porkchop", ItemFactory::get(319));
		self::register("raw_rabbit", ItemFactory::get(411));
		self::register("raw_salmon", ItemFactory::get(460));
		self::register("red_banner", ItemFactory::get(446, 1));
		self::register("red_bed", ItemFactory::get(355, 14));
		self::register("red_dye", ItemFactory::get(351, 1));
		self::register("redstone_dust", ItemFactory::get(331));
		self::register("rotten_flesh", ItemFactory::get(367));
		self::register("scute", ItemFactory::get(468));
		self::register("shears", ItemFactory::get(359));
		self::register("shulker_shell", ItemFactory::get(445));
		self::register("skeleton_skull", ItemFactory::get(397));
		self::register("slimeball", ItemFactory::get(341));
		self::register("snowball", ItemFactory::get(332));
		self::register("spider_eye", ItemFactory::get(375));
		self::register("splash_potion", ItemFactory::get(438));
		self::register("spruce_boat", ItemFactory::get(333, 1));
		self::register("steak", ItemFactory::get(364));
		self::register("stick", ItemFactory::get(280));
		self::register("stone_axe", ItemFactory::get(275));
		self::register("stone_hoe", ItemFactory::get(291));
		self::register("stone_pickaxe", ItemFactory::get(274));
		self::register("stone_shovel", ItemFactory::get(273));
		self::register("stone_sword", ItemFactory::get(272));
		self::register("string", ItemFactory::get(287));
		self::register("sugar", ItemFactory::get(353));
		self::register("sugarcane", ItemFactory::get(338));
		self::register("totem", ItemFactory::get(450));
		self::register("water_bucket", ItemFactory::get(325, 8));
		self::register("wheat", ItemFactory::get(296));
		self::register("wheat_seeds", ItemFactory::get(295));
		self::register("white_banner", ItemFactory::get(446, 15));
		self::register("white_bed", ItemFactory::get(355));
		self::register("white_dye", ItemFactory::get(351, 19));
		self::register("wither_skeleton_skull", ItemFactory::get(397, 1));
		self::register("wooden_axe", ItemFactory::get(271));
		self::register("wooden_hoe", ItemFactory::get(290));
		self::register("wooden_pickaxe", ItemFactory::get(270));
		self::register("wooden_shovel", ItemFactory::get(269));
		self::register("wooden_sword", ItemFactory::get(268));
		self::register("writable_book", ItemFactory::get(386));
		self::register("written_book", ItemFactory::get(387));
		self::register("yellow_banner", ItemFactory::get(446, 11));
		self::register("yellow_bed", ItemFactory::get(355, 4));
		self::register("yellow_dye", ItemFactory::get(351, 11));
		self::register("zombie_head", ItemFactory::get(397, 2));
	}
}
