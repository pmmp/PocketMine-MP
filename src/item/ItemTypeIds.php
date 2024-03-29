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

/**
 * Every item in {@link VanillaItems} has a corresponding constant in this class. These constants can be used to
 * identify and compare item types efficiently using {@link Item::getTypeId()}.
 *
 * WARNING: These are NOT a replacement for Minecraft legacy IDs. Do **NOT** hardcode their values, or store them in
 * configs or databases. They will change without warning.
 */
final class ItemTypeIds{

	private function __construct(){
		//NOOP
	}

	public const CHAINMAIL_BOOTS = 20000;
	public const DIAMOND_BOOTS = 20001;
	public const GOLDEN_BOOTS = 20002;
	public const IRON_BOOTS = 20003;
	public const LEATHER_BOOTS = 20004;
	public const NETHERITE_BOOTS = 20005;
	public const CHAINMAIL_CHESTPLATE = 20006;
	public const DIAMOND_CHESTPLATE = 20007;
	public const GOLDEN_CHESTPLATE = 20008;
	public const IRON_CHESTPLATE = 20009;
	public const LEATHER_TUNIC = 20010;
	public const NETHERITE_CHESTPLATE = 20011;
	public const CHAINMAIL_HELMET = 20012;
	public const DIAMOND_HELMET = 20013;
	public const GOLDEN_HELMET = 20014;
	public const IRON_HELMET = 20015;
	public const LEATHER_CAP = 20016;
	public const NETHERITE_HELMET = 20017;
	public const TURTLE_HELMET = 20018;
	public const CHAINMAIL_LEGGINGS = 20019;
	public const DIAMOND_LEGGINGS = 20020;
	public const GOLDEN_LEGGINGS = 20021;
	public const IRON_LEGGINGS = 20022;
	public const LEATHER_PANTS = 20023;
	public const NETHERITE_LEGGINGS = 20024;
	public const ZOMBIE_SPAWN_EGG = 20025;
	public const SQUID_SPAWN_EGG = 20026;
	public const VILLAGER_SPAWN_EGG = 20027;
	public const DIAMOND_AXE = 20028;
	public const GOLDEN_AXE = 20029;
	public const IRON_AXE = 20030;
	public const NETHERITE_AXE = 20031;
	public const STONE_AXE = 20032;
	public const WOODEN_AXE = 20033;
	public const DIAMOND_HOE = 20034;
	public const GOLDEN_HOE = 20035;
	public const IRON_HOE = 20036;
	public const NETHERITE_HOE = 20037;
	public const STONE_HOE = 20038;
	public const WOODEN_HOE = 20039;
	public const DIAMOND_PICKAXE = 20040;
	public const GOLDEN_PICKAXE = 20041;
	public const IRON_PICKAXE = 20042;
	public const NETHERITE_PICKAXE = 20043;
	public const STONE_PICKAXE = 20044;
	public const WOODEN_PICKAXE = 20045;
	public const DIAMOND_SHOVEL = 20046;
	public const GOLDEN_SHOVEL = 20047;
	public const IRON_SHOVEL = 20048;
	public const NETHERITE_SHOVEL = 20049;
	public const STONE_SHOVEL = 20050;
	public const WOODEN_SHOVEL = 20051;
	public const DIAMOND_SWORD = 20052;
	public const GOLDEN_SWORD = 20053;
	public const IRON_SWORD = 20054;
	public const NETHERITE_SWORD = 20055;
	public const STONE_SWORD = 20056;
	public const WOODEN_SWORD = 20057;
	public const NETHERITE_UPGRADE_SMITHING_TEMPLATE = 20058;
	public const COAST_ARMOR_TRIM_SMITHING_TEMPLATE = 20059;
	public const DUNE_ARMOR_TRIM_SMITHING_TEMPLATE = 20060;
	public const EYE_ARMOR_TRIM_SMITHING_TEMPLATE = 20061;
	public const HOST_ARMOR_TRIM_SMITHING_TEMPLATE = 20062;
	public const RAISER_ARMOR_TRIM_SMITHING_TEMPLATE = 20063;
	public const RIB_ARMOR_TRIM_SMITHING_TEMPLATE = 20064;
	public const SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE = 20065;
	public const SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE = 20066;
	public const SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE = 20067;
	public const SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE = 20068;
	public const SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE = 20069;
	public const TIDE_ARMOR_TRIM_SMITHING_TEMPLATE = 20070;
	public const VEX_ARMOR_TRIM_SMITHING_TEMPLATE = 20071;
	public const WARD_ARMOR_TRIM_SMITHING_TEMPLATE = 20072;
	public const WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE = 20073;
	public const WILD_ARMOR_TRIM_SMITHING_TEMPLATE = 20074;
	public const ACACIA_SIGN = 20075;
	public const AMETHYST_SHARD = 20076;
	public const APPLE = 20077;
	public const ARROW = 20078;
	public const BAKED_POTATO = 20079;
	public const BAMBOO = 20080;
	public const BANNER = 20081;
	public const BEETROOT = 20082;
	public const BEETROOT_SEEDS = 20083;
	public const BEETROOT_SOUP = 20084;
	public const BIRCH_SIGN = 20085;
	public const BLAZE_POWDER = 20086;
	public const BLAZE_ROD = 20087;
	public const BLEACH = 20088;
	public const BONE = 20089;
	public const BONE_MEAL = 20090;
	public const BOOK = 20091;
	public const BOW = 20092;
	public const BOWL = 20093;
	public const BREAD = 20094;
	public const BRICK = 20095;
	public const BUCKET = 20096;
	public const CARROT = 20097;
	public const CHARCOAL = 20098;
	public const CHERRY_SIGN = 20099;
	public const CHEMICAL_ALUMINIUM_OXIDE = 20100;
	public const CHEMICAL_AMMONIA = 20101;
	public const CHEMICAL_BARIUM_SULPHATE = 20102;
	public const CHEMICAL_BENZENE = 20103;
	public const CHEMICAL_BORON_TRIOXIDE = 20104;
	public const CHEMICAL_CALCIUM_BROMIDE = 20105;
	public const CHEMICAL_CALCIUM_CHLORIDE = 20106;
	public const CHEMICAL_CERIUM_CHLORIDE = 20107;
	public const CHEMICAL_CHARCOAL = 20108;
	public const CHEMICAL_CRUDE_OIL = 20109;
	public const CHEMICAL_GLUE = 20110;
	public const CHEMICAL_HYDROGEN_PEROXIDE = 20111;
	public const CHEMICAL_HYPOCHLORITE = 20112;
	public const CHEMICAL_INK = 20113;
	public const CHEMICAL_IRON_SULPHIDE = 20114;
	public const CHEMICAL_LATEX = 20115;
	public const CHEMICAL_LITHIUM_HYDRIDE = 20116;
	public const CHEMICAL_LUMINOL = 20117;
	public const CHEMICAL_MAGNESIUM_NITRATE = 20118;
	public const CHEMICAL_MAGNESIUM_OXIDE = 20119;
	public const CHEMICAL_MAGNESIUM_SALTS = 20120;
	public const CHEMICAL_MERCURIC_CHLORIDE = 20121;
	public const CHEMICAL_POLYETHYLENE = 20122;
	public const CHEMICAL_POTASSIUM_CHLORIDE = 20123;
	public const CHEMICAL_POTASSIUM_IODIDE = 20124;
	public const CHEMICAL_RUBBISH = 20125;
	public const CHEMICAL_SALT = 20126;
	public const CHEMICAL_SOAP = 20127;
	public const CHEMICAL_SODIUM_ACETATE = 20128;
	public const CHEMICAL_SODIUM_FLUORIDE = 20129;
	public const CHEMICAL_SODIUM_HYDRIDE = 20130;
	public const CHEMICAL_SODIUM_HYDROXIDE = 20131;
	public const CHEMICAL_SODIUM_HYPOCHLORITE = 20132;
	public const CHEMICAL_SODIUM_OXIDE = 20133;
	public const CHEMICAL_SUGAR = 20134;
	public const CHEMICAL_SULPHATE = 20135;
	public const CHEMICAL_TUNGSTEN_CHLORIDE = 20136;
	public const CHEMICAL_WATER = 20137;
	public const CHORUS_FRUIT = 20138;
	public const CLAY = 20139;
	public const CLOCK = 20140;
	public const CLOWNFISH = 20141;
	public const COAL = 20142;
	public const COCOA_BEANS = 20143;
	public const COMPASS = 20144;
	public const COOKED_CHICKEN = 20145;
	public const COOKED_FISH = 20146;
	public const COOKED_MUTTON = 20147;
	public const COOKED_PORKCHOP = 20148;
	public const COOKED_RABBIT = 20149;
	public const COOKED_SALMON = 20150;
	public const COOKIE = 20151;
	public const COPPER_INGOT = 20152;
	public const CORAL_FAN = 20153;
	public const CRIMSON_SIGN = 20154;
	public const DARK_OAK_SIGN = 20155;
	public const DIAMOND = 20156;
	public const DISC_FRAGMENT_5 = 20157;
	public const DRAGON_BREATH = 20158;
	public const DRIED_KELP = 20159;
	public const DYE = 20160;
	public const ECHO_SHARD = 20161;
	public const EGG = 20162;
	public const EMERALD = 20163;
	public const ENCHANTED_BOOK = 20164;
	public const ENCHANTED_GOLDEN_APPLE = 20165;
	public const ENDER_PEARL = 20166;
	public const EXPERIENCE_BOTTLE = 20167;
	public const FEATHER = 20168;
	public const FERMENTED_SPIDER_EYE = 20169;
	public const FIRE_CHARGE = 20170;
	public const FISHING_ROD = 20171;
	public const FLINT = 20172;
	public const FLINT_AND_STEEL = 20173;
	public const GHAST_TEAR = 20174;
	public const GLASS_BOTTLE = 20175;
	public const GLISTERING_MELON = 20176;
	public const GLOW_BERRIES = 20177;
	public const GLOW_INK_SAC = 20178;
	public const GLOWSTONE_DUST = 20179;
	public const GOLD_INGOT = 20180;
	public const GOLD_NUGGET = 20181;
	public const GOLDEN_APPLE = 20182;
	public const GOLDEN_CARROT = 20183;
	public const GUNPOWDER = 20184;
	public const HEART_OF_THE_SEA = 20185;
	public const HONEY_BOTTLE = 20186;
	public const HONEYCOMB = 20187;
	public const INK_SAC = 20188;
	public const IRON_INGOT = 20189;
	public const IRON_NUGGET = 20190;
	public const JUNGLE_SIGN = 20191;
	public const LAPIS_LAZULI = 20192;
	public const LAVA_BUCKET = 20193;
	public const LEATHER = 20194;
	public const MAGMA_CREAM = 20195;
	public const MANGROVE_SIGN = 20196;
	public const MEDICINE = 20197;
	public const MELON = 20198;
	public const MELON_SEEDS = 20199;
	public const MILK_BUCKET = 20200;
	public const MINECART = 20201;
	public const MUSHROOM_STEW = 20202;
	public const NAME_TAG = 20203;
	public const NAUTILUS_SHELL = 20204;
	public const NETHER_BRICK = 20205;
	public const NETHER_QUARTZ = 20206;
	public const NETHER_STAR = 20207;
	public const NETHERITE_INGOT = 20208;
	public const NETHERITE_SCRAP = 20209;
	public const OAK_SIGN = 20210;
	public const PAINTING = 20211;
	public const PAPER = 20212;
	public const PHANTOM_MEMBRANE = 20213;
	public const PITCHER_POD = 20214;
	public const POISONOUS_POTATO = 20215;
	public const POPPED_CHORUS_FRUIT = 20216;
	public const POTATO = 20217;
	public const POTION = 20218;
	public const PRISMARINE_CRYSTALS = 20219;
	public const PRISMARINE_SHARD = 20220;
	public const PUFFERFISH = 20221;
	public const PUMPKIN_PIE = 20222;
	public const PUMPKIN_SEEDS = 20223;
	public const RABBIT_FOOT = 20224;
	public const RABBIT_HIDE = 20225;
	public const RABBIT_STEW = 20226;
	public const RAW_BEEF = 20227;
	public const RAW_CHICKEN = 20228;
	public const RAW_COPPER = 20229;
	public const RAW_FISH = 20230;
	public const RAW_GOLD = 20231;
	public const RAW_IRON = 20232;
	public const RAW_MUTTON = 20233;
	public const RAW_PORKCHOP = 20234;
	public const RAW_RABBIT = 20235;
	public const RAW_SALMON = 20236;
	public const RECORD_11 = 20237;
	public const RECORD_13 = 20238;
	public const RECORD_5 = 20239;
	public const RECORD_BLOCKS = 20240;
	public const RECORD_CAT = 20241;
	public const RECORD_CHIRP = 20242;
	public const RECORD_FAR = 20243;
	public const RECORD_MALL = 20244;
	public const RECORD_MELLOHI = 20245;
	public const RECORD_OTHERSIDE = 20246;
	public const RECORD_PIGSTEP = 20247;
	public const RECORD_STAL = 20248;
	public const RECORD_STRAD = 20249;
	public const RECORD_WAIT = 20250;
	public const RECORD_WARD = 20251;
	public const REDSTONE_DUST = 20252;
	public const ROTTEN_FLESH = 20253;
	public const SCUTE = 20254;
	public const SHEARS = 20255;
	public const SHULKER_SHELL = 20256;
	public const SLIMEBALL = 20257;
	public const SNOWBALL = 20258;
	public const SPIDER_EYE = 20259;
	public const SPLASH_POTION = 20260;
	public const SPRUCE_SIGN = 20261;
	public const SPYGLASS = 20262;
	public const STEAK = 20263;
	public const STICK = 20264;
	public const STRING = 20265;
	public const SUGAR = 20266;
	public const SUSPICIOUS_STEW = 20267;
	public const SWEET_BERRIES = 20268;
	public const TORCHFLOWER_SEEDS = 20269;
	public const TOTEM = 20270;
	public const WARPED_SIGN = 20271;
	public const WATER_BUCKET = 20272;
	public const WHEAT = 20273;
	public const WHEAT_SEEDS = 20274;
	public const WRITABLE_BOOK = 20275;
	public const WRITTEN_BOOK = 20276;
	public const OAK_BOAT = 20277;
	public const SPRUCE_BOAT = 20278;
	public const BIRCH_BOAT = 20279;
	public const JUNGLE_BOAT = 20280;
	public const ACACIA_BOAT = 20281;
	public const DARK_OAK_BOAT = 20282;
	public const MANGROVE_BOAT = 20283;
	public const POWDER_SNOW_BUCKET = 20284;
	public const LINGERING_POTION = 20285;

	public const FIRST_UNUSED_ITEM_ID = 20286;

	private static int $nextDynamicId = self::FIRST_UNUSED_ITEM_ID;

	/**
	 * Returns a new runtime item type ID, e.g. for use by a custom item.
	 */
	public static function newId() : int{
		return self::$nextDynamicId++;
	}

	public static function fromBlockTypeId(int $blockTypeId) : int{
		if($blockTypeId < 0){
			throw new \InvalidArgumentException("Block type IDs cannot be negative");
		}
		//negative item type IDs are treated as block IDs
		return -$blockTypeId;
	}

	public static function toBlockTypeId(int $itemTypeId) : ?int{
		if($itemTypeId > 0){ //not a blockitem
			return null;
		}
		return -$itemTypeId;
	}
}
