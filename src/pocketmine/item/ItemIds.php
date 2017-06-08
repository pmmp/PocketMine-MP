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

use pocketmine\block\BlockIds;

interface ItemIds extends BlockIds{

	//All BlockIds are also accessible through this interface.
	const IRON_SHOVEL = 256;
	const IRON_PICKAXE = 257;
	const IRON_AXE = 258;
	const FLINT_AND_STEEL = 259, FLINT_STEEL = 259;
	const APPLE = 260;
	const BOW = 261;
	const ARROW = 262;
	const COAL = 263;
	const DIAMOND = 264;
	const IRON_INGOT = 265;
	const GOLD_INGOT = 266;
	const IRON_SWORD = 267;
	const WOODEN_SWORD = 268;
	const WOODEN_SHOVEL = 269;
	const WOODEN_PICKAXE = 270;
	const WOODEN_AXE = 271;
	const STONE_SWORD = 272;
	const STONE_SHOVEL = 273;
	const STONE_PICKAXE = 274;
	const STONE_AXE = 275;
	const DIAMOND_SWORD = 276;
	const DIAMOND_SHOVEL = 277;
	const DIAMOND_PICKAXE = 278;
	const DIAMOND_AXE = 279;
	const STICK = 280, STICKS = 280;
	const BOWL = 281;
	const MUSHROOM_STEW = 282;
	const GOLD_SWORD = 283, GOLDEN_SWORD = 283;
	const GOLD_SHOVEL = 284, GOLDEN_SHOVEL = 284;
	const GOLD_PICKAXE = 285, GOLDEN_PICKAXE = 285;
	const GOLD_AXE = 286, GOLDEN_AXE = 286;
	const STRING = 287;
	const FEATHER = 288;
	const GUNPOWDER = 289;
	const WOODEN_HOE = 290;
	const STONE_HOE = 291;
	const IRON_HOE = 292;
	const DIAMOND_HOE = 293;
	const GOLD_HOE = 294, GOLDEN_HOE = 294;
	const SEEDS = 295, WHEAT_SEEDS = 295;
	const WHEAT = 296;
	const BREAD = 297;
	const LEATHER_CAP = 298;
	const LEATHER_TUNIC = 299;
	const LEATHER_PANTS = 300;
	const LEATHER_BOOTS = 301;
	const CHAIN_HELMET = 302;
	const CHAIN_CHESTPLATE = 303;
	const CHAIN_LEGGINGS = 304;
	const CHAIN_BOOTS = 305;
	const IRON_HELMET = 306;
	const IRON_CHESTPLATE = 307;
	const IRON_LEGGINGS = 308;
	const IRON_BOOTS = 309;
	const DIAMOND_HELMET = 310;
	const DIAMOND_CHESTPLATE = 311;
	const DIAMOND_LEGGINGS = 312;
	const DIAMOND_BOOTS = 313;
	const GOLD_HELMET = 314;
	const GOLD_CHESTPLATE = 315;
	const GOLD_LEGGINGS = 316;
	const GOLD_BOOTS = 317;
	const FLINT = 318;
	const RAW_PORKCHOP = 319;
	const COOKED_PORKCHOP = 320;
	const PAINTING = 321;
	const GOLDEN_APPLE = 322;
	const SIGN = 323;
	const WOODEN_DOOR = 324, OAK_DOOR = 324;
	const BUCKET = 325;

	const MINECART = 328;
	const SADDLE = 329;
	const IRON_DOOR = 330;
	const REDSTONE = 331, REDSTONE_DUST = 331;
	const SNOWBALL = 332;
	const BOAT = 333;
	const LEATHER = 334;

	const BRICK = 336;
	const CLAY = 337;
	const SUGARCANE = 338, SUGAR_CANE = 338, SUGAR_CANES = 338;
	const PAPER = 339;
	const BOOK = 340;
	const SLIMEBALL = 341;
	const MINECART_WITH_CHEST = 342;

	const EGG = 344;
	const COMPASS = 345;
	const FISHING_ROD = 346;
	const CLOCK = 347;
	const GLOWSTONE_DUST = 348;
	const RAW_FISH = 349;
	const COOKED_FISH = 350;
	const DYE = 351;
	const BONE = 352;
	const SUGAR = 353;
	const CAKE = 354;
	const BED = 355;
	const REPEATER = 356;
	const COOKIE = 357;
	const FILLED_MAP = 358;
	const SHEARS = 359;
	const MELON = 360, MELON_SLICE = 360;
	const PUMPKIN_SEEDS = 361;
	const MELON_SEEDS = 362;
	const RAW_BEEF = 363;
	const STEAK = 364, COOKED_BEEF = 364;
	const RAW_CHICKEN = 365;
	const COOKED_CHICKEN = 366;
	const ROTTEN_FLESH = 367;

	const BLAZE_ROD = 369;
	const GHAST_TEAR = 370;
	const GOLD_NUGGET = 371, GOLDEN_NUGGET = 371;
	const NETHER_WART = 372;
	const POTION = 373;
	const GLASS_BOTTLE = 374;
	const SPIDER_EYE = 375;
	const FERMENTED_SPIDER_EYE = 376;
	const BLAZE_POWDER = 377;
	const MAGMA_CREAM = 378;
	const BREWING_STAND = 379;
	const CAULDRON = 380;

	const GLISTERING_MELON = 382;
	const SPAWN_EGG = 383;
	const BOTTLE_O_ENCHANTING = 384, ENCHANTING_BOTTLE = 384;
	const FIRE_CHARGE = 385;

	const EMERALD = 388;
	const ITEM_FRAME = 389;
	const FLOWER_POT = 390;
	const CARROT = 391, CARROTS = 391;
	const POTATO = 392, POTATOES = 392;
	const BAKED_POTATO = 393, BAKED_POTATOES = 393;
	const POISONOUS_POTATO = 394;
	const MAP = 395, EMPTY_MAP = 395;
	const GOLDEN_CARROT = 396;
	const MOB_HEAD = 397, SKULL = 397;
	const CARROT_ON_A_STICK = 398;
	const NETHER_STAR = 399;
	const PUMPKIN_PIE = 400;

	const ENCHANTED_BOOK = 403;
	const COMPARATOR = 404;
	const NETHER_BRICK = 405;
	const QUARTZ = 406;
	const NETHER_QUARTZ = 406;
	const MINECART_WITH_TNT = 407;
	const MINECART_WITH_HOPPER = 408;
	const PRISMARINE_SHARD = 409;
	const HOPPER = 410;
	const RAW_RABBIT = 411;
	const COOKED_RABBIT = 412;
	const RABBIT_STEW = 413;
	const RABBIT_FOOT = 414;
	const RABBIT_HIDE = 415;
	const LEATHER_HORSE_ARMOR = 416; //I hate being forced to spell this wrong
	const IRON_HORSE_ARMOR = 417;
	const GOLD_HORSE_ARMOR = 418;
	const DIAMOND_HORSE_ARMOR = 419;
	const LEAD = 420, LEASH = 420;
	const NAMETAG = 421;
	const PRISMARINE_CRYSTALS = 422;
	const RAW_MUTTON = 423;
	const COOKED_MUTTON = 424;

	const SPRUCE_DOOR = 427;
	const BIRCH_DOOR = 428;
	const JUNGLE_DOOR = 429;
	const ACACIA_DOOR = 430;
	const DARK_OAK_DOOR = 431;

	const SPLASH_POTION = 438;

	const BEETROOT = 457;
	const BEETROOT_SEEDS = 458, BEETROOT_SEED = 458;
	const BEETROOT_SOUP = 459;
	const RAW_SALMON = 460;
	const CLOWN_FISH = 461;
	const PUFFER_FISH = 462;
	const COOKED_SALMON = 463;

	const ENCHANTED_GOLDEN_APPLE = 466;

	const CAMERA = 498; #blamemojang
}
