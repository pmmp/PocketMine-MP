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

	public const ACACIA_BOAT = 20000;
	public const ACACIA_SIGN = 20001;
	public const APPLE = 20002;
	public const ARROW = 20003;
	public const BAKED_POTATO = 20004;
	public const BAMBOO = 20005;
	public const BANNER = 20006;

	public const BEETROOT = 20008;
	public const BEETROOT_SEEDS = 20009;
	public const BEETROOT_SOUP = 20010;
	public const BIRCH_BOAT = 20011;
	public const BIRCH_SIGN = 20012;
	public const BLAZE_POWDER = 20013;
	public const BLAZE_ROD = 20014;
	public const BLEACH = 20015;
	public const BONE = 20016;
	public const BONE_MEAL = 20017;
	public const BOOK = 20018;
	public const BOW = 20019;
	public const BOWL = 20020;
	public const BREAD = 20021;
	public const BRICK = 20022;
	public const BUCKET = 20023;
	public const CARROT = 20024;
	public const CHAINMAIL_BOOTS = 20025;
	public const CHAINMAIL_CHESTPLATE = 20026;
	public const CHAINMAIL_HELMET = 20027;
	public const CHAINMAIL_LEGGINGS = 20028;
	public const CHARCOAL = 20029;
	public const CHEMICAL_ALUMINIUM_OXIDE = 20030;
	public const CHEMICAL_AMMONIA = 20031;
	public const CHEMICAL_BARIUM_SULPHATE = 20032;
	public const CHEMICAL_BENZENE = 20033;
	public const CHEMICAL_BORON_TRIOXIDE = 20034;
	public const CHEMICAL_CALCIUM_BROMIDE = 20035;
	public const CHEMICAL_CALCIUM_CHLORIDE = 20036;
	public const CHEMICAL_CERIUM_CHLORIDE = 20037;
	public const CHEMICAL_CHARCOAL = 20038;
	public const CHEMICAL_CRUDE_OIL = 20039;
	public const CHEMICAL_GLUE = 20040;
	public const CHEMICAL_HYDROGEN_PEROXIDE = 20041;
	public const CHEMICAL_HYPOCHLORITE = 20042;
	public const CHEMICAL_INK = 20043;
	public const CHEMICAL_IRON_SULPHIDE = 20044;
	public const CHEMICAL_LATEX = 20045;
	public const CHEMICAL_LITHIUM_HYDRIDE = 20046;
	public const CHEMICAL_LUMINOL = 20047;
	public const CHEMICAL_MAGNESIUM_NITRATE = 20048;
	public const CHEMICAL_MAGNESIUM_OXIDE = 20049;
	public const CHEMICAL_MAGNESIUM_SALTS = 20050;
	public const CHEMICAL_MERCURIC_CHLORIDE = 20051;
	public const CHEMICAL_POLYETHYLENE = 20052;
	public const CHEMICAL_POTASSIUM_CHLORIDE = 20053;
	public const CHEMICAL_POTASSIUM_IODIDE = 20054;
	public const CHEMICAL_RUBBISH = 20055;
	public const CHEMICAL_SALT = 20056;
	public const CHEMICAL_SOAP = 20057;
	public const CHEMICAL_SODIUM_ACETATE = 20058;
	public const CHEMICAL_SODIUM_FLUORIDE = 20059;
	public const CHEMICAL_SODIUM_HYDRIDE = 20060;
	public const CHEMICAL_SODIUM_HYDROXIDE = 20061;
	public const CHEMICAL_SODIUM_HYPOCHLORITE = 20062;
	public const CHEMICAL_SODIUM_OXIDE = 20063;
	public const CHEMICAL_SUGAR = 20064;
	public const CHEMICAL_SULPHATE = 20065;
	public const CHEMICAL_TUNGSTEN_CHLORIDE = 20066;
	public const CHEMICAL_WATER = 20067;
	public const CHORUS_FRUIT = 20068;
	public const CLAY = 20069;
	public const CLOCK = 20070;
	public const CLOWNFISH = 20071;
	public const COAL = 20072;
	public const COCOA_BEANS = 20073;
	public const COMPASS = 20074;
	public const COOKED_CHICKEN = 20075;
	public const COOKED_FISH = 20076;
	public const COOKED_MUTTON = 20077;
	public const COOKED_PORKCHOP = 20078;
	public const COOKED_RABBIT = 20079;
	public const COOKED_SALMON = 20080;
	public const COOKIE = 20081;
	public const CORAL_FAN = 20082;
	public const DARK_OAK_BOAT = 20083;
	public const DARK_OAK_SIGN = 20084;
	public const DIAMOND = 20085;
	public const DIAMOND_AXE = 20086;
	public const DIAMOND_BOOTS = 20087;
	public const DIAMOND_CHESTPLATE = 20088;
	public const DIAMOND_HELMET = 20089;
	public const DIAMOND_HOE = 20090;
	public const DIAMOND_LEGGINGS = 20091;
	public const DIAMOND_PICKAXE = 20092;
	public const DIAMOND_SHOVEL = 20093;
	public const DIAMOND_SWORD = 20094;
	public const DRAGON_BREATH = 20095;
	public const DRIED_KELP = 20096;
	public const DYE = 20097;
	public const EGG = 20098;
	public const EMERALD = 20099;
	public const ENCHANTED_GOLDEN_APPLE = 20100;
	public const ENDER_PEARL = 20101;
	public const EXPERIENCE_BOTTLE = 20102;
	public const FEATHER = 20103;
	public const FERMENTED_SPIDER_EYE = 20104;
	public const FISHING_ROD = 20105;
	public const FLINT = 20106;
	public const FLINT_AND_STEEL = 20107;
	public const GHAST_TEAR = 20108;
	public const GLASS_BOTTLE = 20109;
	public const GLISTERING_MELON = 20110;
	public const GLOWSTONE_DUST = 20111;
	public const GOLD_INGOT = 20112;
	public const GOLD_NUGGET = 20113;
	public const GOLDEN_APPLE = 20114;
	public const GOLDEN_AXE = 20115;
	public const GOLDEN_BOOTS = 20116;
	public const GOLDEN_CARROT = 20117;
	public const GOLDEN_CHESTPLATE = 20118;
	public const GOLDEN_HELMET = 20119;
	public const GOLDEN_HOE = 20120;
	public const GOLDEN_LEGGINGS = 20121;
	public const GOLDEN_PICKAXE = 20122;
	public const GOLDEN_SHOVEL = 20123;
	public const GOLDEN_SWORD = 20124;
	public const GUNPOWDER = 20125;
	public const HEART_OF_THE_SEA = 20126;
	public const INK_SAC = 20127;
	public const IRON_AXE = 20128;
	public const IRON_BOOTS = 20129;
	public const IRON_CHESTPLATE = 20130;
	public const IRON_HELMET = 20131;
	public const IRON_HOE = 20132;
	public const IRON_INGOT = 20133;
	public const IRON_LEGGINGS = 20134;
	public const IRON_NUGGET = 20135;
	public const IRON_PICKAXE = 20136;
	public const IRON_SHOVEL = 20137;
	public const IRON_SWORD = 20138;
	public const JUNGLE_BOAT = 20139;
	public const JUNGLE_SIGN = 20140;
	public const LAPIS_LAZULI = 20141;
	public const LAVA_BUCKET = 20142;
	public const LEATHER = 20143;
	public const LEATHER_BOOTS = 20144;
	public const LEATHER_CAP = 20145;
	public const LEATHER_PANTS = 20146;
	public const LEATHER_TUNIC = 20147;
	public const MAGMA_CREAM = 20148;
	public const MELON = 20149;
	public const MELON_SEEDS = 20150;
	public const MILK_BUCKET = 20151;
	public const MINECART = 20152;

	public const MUSHROOM_STEW = 20154;
	public const NAUTILUS_SHELL = 20155;
	public const NETHER_BRICK = 20156;
	public const NETHER_QUARTZ = 20157;
	public const NETHER_STAR = 20158;
	public const OAK_BOAT = 20159;
	public const OAK_SIGN = 20160;
	public const PAINTING = 20161;
	public const PAPER = 20162;
	public const POISONOUS_POTATO = 20163;
	public const POPPED_CHORUS_FRUIT = 20164;
	public const POTATO = 20165;
	public const POTION = 20166;
	public const PRISMARINE_CRYSTALS = 20167;
	public const PRISMARINE_SHARD = 20168;
	public const PUFFERFISH = 20169;
	public const PUMPKIN_PIE = 20170;
	public const PUMPKIN_SEEDS = 20171;
	public const RABBIT_FOOT = 20172;
	public const RABBIT_HIDE = 20173;
	public const RABBIT_STEW = 20174;
	public const RAW_BEEF = 20175;
	public const RAW_CHICKEN = 20176;
	public const RAW_FISH = 20177;
	public const RAW_MUTTON = 20178;
	public const RAW_PORKCHOP = 20179;
	public const RAW_RABBIT = 20180;
	public const RAW_SALMON = 20181;
	public const RECORD_11 = 20182;
	public const RECORD_13 = 20183;
	public const RECORD_BLOCKS = 20184;
	public const RECORD_CAT = 20185;
	public const RECORD_CHIRP = 20186;
	public const RECORD_FAR = 20187;
	public const RECORD_MALL = 20188;
	public const RECORD_MELLOHI = 20189;
	public const RECORD_STAL = 20190;
	public const RECORD_STRAD = 20191;
	public const RECORD_WAIT = 20192;
	public const RECORD_WARD = 20193;
	public const REDSTONE_DUST = 20194;
	public const ROTTEN_FLESH = 20195;
	public const SCUTE = 20196;
	public const SHEARS = 20197;
	public const SHULKER_SHELL = 20198;
	public const SLIMEBALL = 20199;
	public const SNOWBALL = 20200;
	public const SPIDER_EYE = 20201;
	public const SPLASH_POTION = 20202;
	public const SPRUCE_BOAT = 20203;
	public const SPRUCE_SIGN = 20204;
	public const SQUID_SPAWN_EGG = 20205;
	public const STEAK = 20206;
	public const STICK = 20207;
	public const STONE_AXE = 20208;
	public const STONE_HOE = 20209;
	public const STONE_PICKAXE = 20210;
	public const STONE_SHOVEL = 20211;
	public const STONE_SWORD = 20212;
	public const STRING = 20213;
	public const SUGAR = 20214;
	public const SWEET_BERRIES = 20215;
	public const TOTEM = 20216;
	public const VILLAGER_SPAWN_EGG = 20217;
	public const WATER_BUCKET = 20218;
	public const WHEAT = 20219;
	public const WHEAT_SEEDS = 20220;
	public const WOODEN_AXE = 20221;
	public const WOODEN_HOE = 20222;
	public const WOODEN_PICKAXE = 20223;
	public const WOODEN_SHOVEL = 20224;
	public const WOODEN_SWORD = 20225;
	public const WRITABLE_BOOK = 20226;
	public const WRITTEN_BOOK = 20227;
	public const ZOMBIE_SPAWN_EGG = 20228;
	public const CRIMSON_SIGN = 20229;
	public const MANGROVE_SIGN = 20230;
	public const WARPED_SIGN = 20231;
	public const AMETHYST_SHARD = 20232;
	public const COPPER_INGOT = 20233;
	public const DISC_FRAGMENT_5 = 20234;
	public const ECHO_SHARD = 20235;
	public const GLOW_INK_SAC = 20236;
	public const HONEY_BOTTLE = 20237;
	public const HONEYCOMB = 20238;
	public const RECORD_5 = 20239;
	public const RECORD_OTHERSIDE = 20240;
	public const RECORD_PIGSTEP = 20241;
	public const NETHERITE_INGOT = 20242;
	public const NETHERITE_AXE = 20243;
	public const NETHERITE_HOE = 20244;
	public const NETHERITE_PICKAXE = 20245;
	public const NETHERITE_SHOVEL = 20246;
	public const NETHERITE_SWORD = 20247;
	public const NETHERITE_BOOTS = 20248;
	public const NETHERITE_CHESTPLATE = 20249;
	public const NETHERITE_HELMET = 20250;
	public const NETHERITE_LEGGINGS = 20251;
	public const PHANTOM_MEMBRANE = 20252;
	public const RAW_COPPER = 20253;
	public const RAW_IRON = 20254;
	public const RAW_GOLD = 20255;
	public const SPYGLASS = 20256;
	public const NETHERITE_SCRAP = 20257;
	public const POWDER_SNOW_BUCKET = 20258;
	public const LINGERING_POTION = 20259;
	public const FIRE_CHARGE = 20260;
	public const SUSPICIOUS_STEW = 20261;
	public const TURTLE_HELMET = 20262;
	public const MEDICINE = 20263;
	public const MANGROVE_BOAT = 20264;
	public const GLOW_BERRIES = 20265;
	public const CHERRY_SIGN = 20266;
	public const ENCHANTED_BOOK = 20267;
	public const TORCHFLOWER_SEEDS = 20268;
	public const NETHERITE_UPGRADE_SMITHING_TEMPLATE = 20269;
	public const SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE = 20270;
	public const VEX_ARMOR_TRIM_SMITHING_TEMPLATE = 20271;
	public const WILD_ARMOR_TRIM_SMITHING_TEMPLATE = 20272;
	public const COAST_ARMOR_TRIM_SMITHING_TEMPLATE = 20273;
	public const DUNE_ARMOR_TRIM_SMITHING_TEMPLATE = 20274;
	public const WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE = 20275;
	public const RAISER_ARMOR_TRIM_SMITHING_TEMPLATE = 20276;
	public const SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE = 20277;
	public const HOST_ARMOR_TRIM_SMITHING_TEMPLATE = 20278;
	public const WARD_ARMOR_TRIM_SMITHING_TEMPLATE = 20279;
	public const SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE = 20280;
	public const TIDE_ARMOR_TRIM_SMITHING_TEMPLATE = 20281;
	public const SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE = 20282;
	public const RIB_ARMOR_TRIM_SMITHING_TEMPLATE = 20283;
	public const EYE_ARMOR_TRIM_SMITHING_TEMPLATE = 20284;
	public const SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE = 20285;
	public const PITCHER_POD = 20286;
	public const NAME_TAG = 20287;
	public const GOAT_HORN = 20288;
	public const END_CRYSTAL = 20289;
	public const ICE_BOMB = 20290;
	public const RECOVERY_COMPASS = 20291;

	public const FIRST_UNUSED_ITEM_ID = 20292;

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
