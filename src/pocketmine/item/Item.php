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

/**
 * All the Item classes
 */
namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\Fence;
use pocketmine\block\Flower;
use pocketmine\entity\Entity;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\inventory\Fuel;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\level\Level;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\Player;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\NBT;

class Item{

	private static $cachedParser = null;

	/**
	 * @param $tag
	 * @return Compound
	 */
	private static function parseCompoundTag($tag){
		if(self::$cachedParser === null){
			self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
		}

		self::$cachedParser->read($tag);
		return self::$cachedParser->getData();
	}

	/**
	 * @param Compound $tag
	 * @return string
	 */
	private static function writeCompoundTag(Compound $tag){
		if(self::$cachedParser === null){
			self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
		}

		self::$cachedParser->setData($tag);
		return self::$cachedParser->write();
	}

	//All Block IDs are here too
	const AIR = 0;
	const STONE = 1;
	const GRASS = 2;
	const DIRT = 3;
	const COBBLESTONE = 4;
	const COBBLE = 4;
	const PLANK = 5;
	const PLANKS = 5;
	const WOODEN_PLANK = 5;
	const WOODEN_PLANKS = 5;
	const SAPLING = 6;
	const SAPLINGS = 6;
	const BEDROCK = 7;
	const WATER = 8;
	const STILL_WATER = 9;
	const LAVA = 10;
	const STILL_LAVA = 11;
	const SAND = 12;
	const GRAVEL = 13;
	const GOLD_ORE = 14;
	const IRON_ORE = 15;
	const COAL_ORE = 16;
	const LOG = 17;
	const WOOD = 17;
	const TRUNK = 17;
	const LEAVES = 18;
	const LEAVE = 18;
	const SPONGE = 19;
	const GLASS = 20;
	const LAPIS_ORE = 21;
	const LAPIS_BLOCK = 22;

	const SANDSTONE = 24;

	const BED_BLOCK = 26;


	const COBWEB = 30;
	const TALL_GRASS = 31;
	const BUSH = 32;
	const DEAD_BUSH = 32;
	const WOOL = 35;
	const DANDELION = 37;
	const POPPY = 38;
	const ROSE = 38;
	const RED_FLOWER = 38;
	const BROWN_MUSHROOM = 39;
	const RED_MUSHROOM = 40;
	const GOLD_BLOCK = 41;
	const IRON_BLOCK = 42;
	const DOUBLE_SLAB = 43;
	const DOUBLE_SLABS = 43;
	const SLAB = 44;
	const SLABS = 44;
	const BRICKS = 45;
	const BRICKS_BLOCK = 45;
	const TNT = 46;
	const BOOKSHELF = 47;
	const MOSS_STONE = 48;
	const MOSSY_STONE = 48;
	const OBSIDIAN = 49;
	const TORCH = 50;
	const FIRE = 51;
	const MONSTER_SPAWNER = 52;
	const WOOD_STAIRS = 53;
	const WOODEN_STAIRS = 53;
	const OAK_WOOD_STAIRS = 53;
	const OAK_WOODEN_STAIRS = 53;
	const CHEST = 54;

	const DIAMOND_ORE = 56;
	const DIAMOND_BLOCK = 57;
	const CRAFTING_TABLE = 58;
	const WORKBENCH = 58;
	const WHEAT_BLOCK = 59;
	const FARMLAND = 60;
	const FURNACE = 61;
	const BURNING_FURNACE = 62;
	const LIT_FURNACE = 62;
	const SIGN_POST = 63;
	const DOOR_BLOCK = 64;
	const WOODEN_DOOR_BLOCK = 64;
	const WOOD_DOOR_BLOCK = 64;
	const LADDER = 65;

	const COBBLE_STAIRS = 67;
	const COBBLESTONE_STAIRS = 67;
	const WALL_SIGN = 68;

	const IRON_DOOR_BLOCK = 71;

	const REDSTONE_ORE = 73;
	const GLOWING_REDSTONE_ORE = 74;
	const LIT_REDSTONE_ORE = 74;

	const SNOW = 78;
	const SNOW_LAYER = 78;
	const ICE = 79;
	const SNOW_BLOCK = 80;
	const CACTUS = 81;
	const CLAY_BLOCK = 82;
	const REEDS = 83;
	const SUGARCANE_BLOCK = 83;

	const FENCE = 85;
	const PUMPKIN = 86;
	const NETHERRACK = 87;
	const SOUL_SAND = 88;
	const GLOWSTONE = 89;
	const GLOWSTONE_BLOCK = 89;


	const LIT_PUMPKIN = 91;
	const JACK_O_LANTERN = 91;
	const CAKE_BLOCK = 92;

	const TRAPDOOR = 96;

	const STONE_BRICKS = 98;
	const STONE_BRICK = 98;

	const IRON_BAR = 101;
	const IRON_BARS = 101;
	const GLASS_PANE = 102;
	const GLASS_PANEL = 102;
	const MELON_BLOCK = 103;
	const PUMPKIN_STEM = 104;
	const MELON_STEM = 105;
	const VINE = 106;
	const VINES = 106;
	const FENCE_GATE = 107;
	const BRICK_STAIRS = 108;
	const STONE_BRICK_STAIRS = 109;
	const MYCELIUM = 110;
	const WATER_LILY = 111;
	const LILY_PAD = 111;
	const NETHER_BRICKS = 112;
	const NETHER_BRICK_BLOCK = 112;
	const NETHER_BRICK_FENCE = 113;
	const NETHER_BRICKS_STAIRS = 114;

	const ENCHANTING_TABLE = 116;
	const ENCHANT_TABLE = 116;
	const ENCHANTMENT_TABLE = 116;

	const END_PORTAL = 120;
	const END_STONE = 121;

	const SANDSTONE_STAIRS = 128;
	const EMERALD_ORE = 129;

	const EMERALD_BLOCK = 133;
	const SPRUCE_WOOD_STAIRS = 134;
	const SPRUCE_WOODEN_STAIRS = 134;
	const BIRCH_WOOD_STAIRS = 135;
	const BIRCH_WOODEN_STAIRS = 135;
	const JUNGLE_WOOD_STAIRS = 136;
	const JUNGLE_WOODEN_STAIRS = 136;

	const COBBLE_WALL = 139;
	const STONE_WALL = 139;
	const COBBLESTONE_WALL = 139;

	const CARROT_BLOCK = 141;
	const POTATO_BLOCK = 142;

	const ANVIL = 145;

	const REDSTONE_BLOCK = 152;

	const QUARTZ_BLOCK = 155;
	const QUARTZ_STAIRS = 156;
	const DOUBLE_WOOD_SLAB = 157;
	const DOUBLE_WOODEN_SLAB = 157;
	const DOUBLE_WOOD_SLABS = 157;
	const DOUBLE_WOODEN_SLABS = 157;
	const WOOD_SLAB = 158;
	const WOODEN_SLAB = 158;
	const WOOD_SLABS = 158;
	const WOODEN_SLABS = 158;
	const STAINED_CLAY = 159;
	const STAINED_HARDENED_CLAY = 159;

	const LEAVES2 = 161;
	const LEAVE2 = 161;
	const WOOD2 = 162;
	const TRUNK2 = 162;
	const LOG2 = 162;
	const ACACIA_WOOD_STAIRS = 163;
	const ACACIA_WOODEN_STAIRS = 163;
	const DARK_OAK_WOOD_STAIRS = 164;
	const DARK_OAK_WOODEN_STAIRS = 164;

	const HAY_BALE = 170;
	const CARPET = 171;
	const HARDENED_CLAY = 172;
	const COAL_BLOCK = 173;

	const DOUBLE_PLANT = 175;

	const FENCE_GATE_SPRUCE = 183;
	const FENCE_GATE_BIRCH = 184;
	const FENCE_GATE_JUNGLE = 185;
	const FENCE_GATE_DARK_OAK = 186;
	const FENCE_GATE_ACACIA = 187;

	const GRASS_PATH = 198;

	const PODZOL = 243;
	const BEETROOT_BLOCK = 244;
	const STONECUTTER = 245;
	const GLOWING_OBSIDIAN = 246;
	const NETHER_REACTOR = 247;


	//Normal Item IDs

	const IRON_SHOVEL = 256; //
	const IRON_PICKAXE = 257; //
	const IRON_AXE = 258; //
	const FLINT_STEEL = 259; //
	const FLINT_AND_STEEL = 259; //
	const APPLE = 260; //
	const BOW = 261;
	const ARROW = 262;
	const COAL = 263; //
	const DIAMOND = 264; //
	const IRON_INGOT = 265; //
	const GOLD_INGOT = 266; //
	const IRON_SWORD = 267;
	const WOODEN_SWORD = 268; //
	const WOODEN_SHOVEL = 269; //
	const WOODEN_PICKAXE = 270; //
	const WOODEN_AXE = 271; //
	const STONE_SWORD = 272;
	const STONE_SHOVEL = 273;
	const STONE_PICKAXE = 274;
	const STONE_AXE = 275;
	const DIAMOND_SWORD = 276;
	const DIAMOND_SHOVEL = 277;
	const DIAMOND_PICKAXE = 278;
	const DIAMOND_AXE = 279;
	const STICK = 280; //
	const STICKS = 280;
	const BOWL = 281; //
	const MUSHROOM_STEW = 282;
	const GOLD_SWORD = 283;
	const GOLD_SHOVEL = 284;
	const GOLD_PICKAXE = 285;
	const GOLD_AXE = 286;
	const GOLDEN_SWORD = 283;
	const GOLDEN_SHOVEL = 284;
	const GOLDEN_PICKAXE = 285;
	const GOLDEN_AXE = 286;
	const STRING = 287;
	const FEATHER = 288; //
	const GUNPOWDER = 289;
	const WOODEN_HOE = 290;
	const STONE_HOE = 291;
	const IRON_HOE = 292; //
	const DIAMOND_HOE = 293;
	const GOLD_HOE = 294;
	const GOLDEN_HOE = 294;
	const SEEDS = 295;
	const WHEAT_SEEDS = 295;
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
	const WOODEN_DOOR = 324;
	const BUCKET = 325;

	const MINECART = 328;

	const IRON_DOOR = 330;
	const REDSTONE = 331;
	const REDSTONE_DUST = 331;
	const SNOWBALL = 332;

	const LEATHER = 334;

	const BRICK = 336;
	const CLAY = 337;
	const SUGARCANE = 338;
	const SUGAR_CANE = 338;
	const SUGAR_CANES = 338;
	const PAPER = 339;
	const BOOK = 340;
	const SLIMEBALL = 341;

	const EGG = 344;
	const COMPASS = 345;

	const CLOCK = 347;
	const GLOWSTONE_DUST = 348;
	const RAW_FISH = 349;
	const COOKED_FISH = 350;
	const DYE = 351;
	const BONE = 352;
	const SUGAR = 353;
	const CAKE = 354;
	const BED = 355;


	const COOKIE = 357;


	const SHEARS = 359;
	const MELON = 360;
	const MELON_SLICE = 360;
	const PUMPKIN_SEEDS = 361;
	const MELON_SEEDS = 362;
	const RAW_BEEF = 363;
	const STEAK = 364;
	const COOKED_BEEF = 364;

	const RAW_CHICKEN = 365;
	const COOKED_CHICKEN = 366;

	const GOLD_NUGGET = 371;
	const GOLDEN_NUGGET = 371;

	const SPAWN_EGG = 383;

	const EMERALD = 388;

	const CARROT = 391;
	const CARROTS = 391;
	const POTATO = 392;
	const POTATOES = 392;
	const BAKED_POTATO = 393;
	const BAKED_POTATOES = 393;

	const PUMPKIN_PIE = 400;

	const NETHER_BRICK = 405;
	const QUARTZ = 406;
	const NETHER_QUARTZ = 406;

	const CAMERA = 456;
	const BEETROOT = 457;
	const BEETROOT_SEEDS = 458;
	const BEETROOT_SEED = 458;
	const BEETROOT_SOUP = 459;


	/** @var \SplFixedArray */
	public static $list = null;
	protected $block;
	protected $id;
	protected $meta;
	private $tags = "";
	private $cachedNBT = null;
	public $count;
	protected $durability = 0;
	protected $name;

	public function canBeActivated(){
		return false;
	}

	public static function init(){
		if(self::$list === null){
			self::$list = new \SplFixedArray(65536);
			self::$list[self::IRON_SHOVEL] = IronShovel::class;
			self::$list[self::IRON_PICKAXE] = IronPickaxe::class;
			self::$list[self::IRON_AXE] = IronAxe::class;
			self::$list[self::FLINT_STEEL] = FlintSteel::class;
			self::$list[self::APPLE] = Apple::class;
			self::$list[self::BOW] = Bow::class;
			self::$list[self::ARROW] = Arrow::class;
			self::$list[self::COAL] = Coal::class;
			self::$list[self::DIAMOND] = Diamond::class;
			self::$list[self::IRON_INGOT] = IronIngot::class;
			self::$list[self::GOLD_INGOT] = GoldIngot::class;
			self::$list[self::IRON_SWORD] = IronSword::class;
			self::$list[self::WOODEN_SWORD] = WoodenSword::class;
			self::$list[self::WOODEN_SHOVEL] = WoodenShovel::class;
			self::$list[self::WOODEN_PICKAXE] = WoodenPickaxe::class;
			self::$list[self::WOODEN_AXE] = WoodenAxe::class;
			self::$list[self::STONE_SWORD] = StoneSword::class;
			self::$list[self::STONE_SHOVEL] = StoneShovel::class;
			self::$list[self::STONE_PICKAXE] = StonePickaxe::class;
			self::$list[self::STONE_AXE] = StoneAxe::class;
			self::$list[self::DIAMOND_SWORD] = DiamondSword::class;
			self::$list[self::DIAMOND_SHOVEL] = DiamondShovel::class;
			self::$list[self::DIAMOND_PICKAXE] = DiamondPickaxe::class;
			self::$list[self::DIAMOND_AXE] = DiamondAxe::class;
			self::$list[self::STICK] = Stick::class;
			self::$list[self::BOWL] = Bowl::class;
			self::$list[self::MUSHROOM_STEW] = MushroomStew::class;
			self::$list[self::GOLD_SWORD] = GoldSword::class;
			self::$list[self::GOLD_SHOVEL] = GoldShovel::class;
			self::$list[self::GOLD_PICKAXE] = GoldPickaxe::class;
			self::$list[self::GOLD_AXE] = GoldAxe::class;
			self::$list[self::STRING] = StringItem::class;
			self::$list[self::FEATHER] = Feather::class;
			self::$list[self::GUNPOWDER] = Gunpowder::class;
			self::$list[self::WOODEN_HOE] = WoodenHoe::class;
			self::$list[self::STONE_HOE] = StoneHoe::class;
			self::$list[self::IRON_HOE] = IronHoe::class;
			self::$list[self::DIAMOND_HOE] = DiamondHoe::class;
			self::$list[self::GOLD_HOE] = GoldHoe::class;
			self::$list[self::WHEAT_SEEDS] = WheatSeeds::class;
			self::$list[self::WHEAT] = Wheat::class;
			self::$list[self::BREAD] = Bread::class;
			self::$list[self::LEATHER_CAP] = LeatherCap::class;
			self::$list[self::LEATHER_TUNIC] = LeatherTunic::class;
			self::$list[self::LEATHER_PANTS] = LeatherPants::class;
			self::$list[self::LEATHER_BOOTS] = LeatherBoots::class;
			self::$list[self::CHAIN_HELMET] = ChainHelmet::class;
			self::$list[self::CHAIN_CHESTPLATE] = ChainChestplate::class;
			self::$list[self::CHAIN_LEGGINGS] = ChainLeggings::class;
			self::$list[self::CHAIN_BOOTS] = ChainBoots::class;
			self::$list[self::IRON_HELMET] = IronHelmet::class;
			self::$list[self::IRON_CHESTPLATE] = IronChestplate::class;
			self::$list[self::IRON_LEGGINGS] = IronLeggings::class;
			self::$list[self::IRON_BOOTS] = IronBoots::class;
			self::$list[self::DIAMOND_HELMET] = DiamondHelmet::class;
			self::$list[self::DIAMOND_CHESTPLATE] = DiamondChestplate::class;
			self::$list[self::DIAMOND_LEGGINGS] = DiamondLeggings::class;
			self::$list[self::DIAMOND_BOOTS] = DiamondBoots::class;
			self::$list[self::GOLD_HELMET] = GoldHelmet::class;
			self::$list[self::GOLD_CHESTPLATE] = GoldChestplate::class;
			self::$list[self::GOLD_LEGGINGS] = GoldLeggings::class;
			self::$list[self::GOLD_BOOTS] = GoldBoots::class;
			self::$list[self::FLINT] = Flint::class;
			self::$list[self::RAW_PORKCHOP] = RawPorkchop::class;
			self::$list[self::COOKED_PORKCHOP] = CookedPorkchop::class;
			self::$list[self::PAINTING] = Painting::class;
			self::$list[self::GOLDEN_APPLE] = GoldenApple::class;
			self::$list[self::SIGN] = Sign::class;
			self::$list[self::WOODEN_DOOR] = WoodenDoor::class;
			self::$list[self::BUCKET] = Bucket::class;
			self::$list[self::MINECART] = Minecart::class;
			self::$list[self::IRON_DOOR] = IronDoor::class;
			self::$list[self::REDSTONE] = Redstone::class;
			self::$list[self::SNOWBALL] = Snowball::class;
			self::$list[self::LEATHER] = Leather::class;
			self::$list[self::BRICK] = Brick::class;
			self::$list[self::CLAY] = Clay::class;
			self::$list[self::SUGARCANE] = Sugarcane::class;
			self::$list[self::PAPER] = Paper::class;
			self::$list[self::BOOK] = Book::class;
			self::$list[self::SLIMEBALL] = Slimeball::class;
			self::$list[self::EGG] = Egg::class;
			self::$list[self::COMPASS] = Compass::class;
			self::$list[self::CLOCK] = Clock::class;
			self::$list[self::GLOWSTONE_DUST] = GlowstoneDust::class;
			self::$list[self::RAW_FISH] = Fish::class;
			self::$list[self::COOKED_FISH] = CookedFish::class;
			self::$list[self::DYE] = Dye::class;
			self::$list[self::BONE] = Bone::class;
			self::$list[self::SUGAR] = Sugar::class;
			self::$list[self::CAKE] = Cake::class;
			self::$list[self::BED] = Bed::class;
			self::$list[self::COOKIE] = Cookie::class;
			self::$list[self::SHEARS] = Shears::class;
			self::$list[self::MELON] = Melon::class;
			self::$list[self::PUMPKIN_SEEDS] = PumpkinSeeds::class;
			self::$list[self::MELON_SEEDS] = MelonSeeds::class;
			self::$list[self::RAW_BEEF] = RawBeef::class;
			self::$list[self::STEAK] = Steak::class;
			self::$list[self::RAW_CHICKEN] = RawChicken::class;
			self::$list[self::COOKED_CHICKEN] = CookedChicken::class;
			self::$list[self::GOLD_NUGGET] = GoldNugget::class;
			self::$list[self::SPAWN_EGG] = SpawnEgg::class;
			self::$list[self::EMERALD] = Emerald::class;
			self::$list[self::CARROT] = Carrot::class;
			self::$list[self::POTATO] = Potato::class;
			self::$list[self::BAKED_POTATO] = BakedPotato::class;
			self::$list[self::PUMPKIN_PIE] = PumpkinPie::class;
			self::$list[self::NETHER_BRICK] = NetherBrick::class;
			self::$list[self::QUARTZ] = Quartz::class;
			self::$list[self::QUARTZ] = NetherQuartz::class;
			// self::$list[self::CAMERA] = Camera::class;
			self::$list[self::BEETROOT] = Beetroot::class;
			self::$list[self::BEETROOT_SEEDS] = BeetrootSeeds::class;
			self::$list[self::BEETROOT_SOUP] = BeetrootSoup::class;

			for($i = 0; $i < 256; ++$i){
				if(Block::$list[$i] !== null){
					self::$list[$i] = Block::$list[$i];
				}
			}
		}

		self::initCreativeItems();
	}

	private static $creative = [];

	private static function initCreativeItems(){
		self::clearCreativeItems();
		//Building
		self::addCreativeItem(Item::get(Item::COBBLESTONE, 0));
		self::addCreativeItem(Item::get(Item::STONE_BRICKS, 0));
		self::addCreativeItem(Item::get(Item::STONE_BRICKS, 1));
		self::addCreativeItem(Item::get(Item::STONE_BRICKS, 2));
		self::addCreativeItem(Item::get(Item::STONE_BRICKS, 3));
		self::addCreativeItem(Item::get(Item::MOSS_STONE, 0));
		self::addCreativeItem(Item::get(Item::WOODEN_PLANKS, 0));
		self::addCreativeItem(Item::get(Item::WOODEN_PLANKS, 1));
		self::addCreativeItem(Item::get(Item::WOODEN_PLANKS, 2));
		self::addCreativeItem(Item::get(Item::WOODEN_PLANKS, 3));
		self::addCreativeItem(Item::get(Item::WOODEN_PLANKS, 4));
		self::addCreativeItem(Item::get(Item::WOODEN_PLANKS, 5));
		self::addCreativeItem(Item::get(Item::BRICKS, 0));

		self::addCreativeItem(Item::get(Item::STONE, 0));
		self::addCreativeItem(Item::get(Item::STONE, 1));
		self::addCreativeItem(Item::get(Item::STONE, 2));
		self::addCreativeItem(Item::get(Item::STONE, 3));
		self::addCreativeItem(Item::get(Item::STONE, 4));
		self::addCreativeItem(Item::get(Item::STONE, 5));
		self::addCreativeItem(Item::get(Item::STONE, 6));
		self::addCreativeItem(Item::get(Item::DIRT, 0));
		self::addCreativeItem(Item::get(Item::PODZOL, 0));
		self::addCreativeItem(Item::get(Item::GRASS, 0));
		self::addCreativeItem(Item::get(Item::MYCELIUM, 0));
		self::addCreativeItem(Item::get(Item::CLAY_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::HARDENED_CLAY, 0));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 0));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 7));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 6));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 5));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 4));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 3));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 2));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 1));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 15));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 14));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 13));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 12));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 11));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 10));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 9));
		self::addCreativeItem(Item::get(Item::STAINED_CLAY, 8));
		self::addCreativeItem(Item::get(Item::SANDSTONE, 0));
		self::addCreativeItem(Item::get(Item::SANDSTONE, 1));
		self::addCreativeItem(Item::get(Item::SANDSTONE, 2));
		self::addCreativeItem(Item::get(Item::SAND, 0));
		self::addCreativeItem(Item::get(Item::SAND, 1));
		self::addCreativeItem(Item::get(Item::GRAVEL, 0));
		self::addCreativeItem(Item::get(Item::TRUNK, 0));
		self::addCreativeItem(Item::get(Item::TRUNK, 1));
		self::addCreativeItem(Item::get(Item::TRUNK, 2));
		self::addCreativeItem(Item::get(Item::TRUNK, 3));
		self::addCreativeItem(Item::get(Item::TRUNK2, 0));
		self::addCreativeItem(Item::get(Item::TRUNK2, 1));
		self::addCreativeItem(Item::get(Item::NETHER_BRICKS, 0));
		self::addCreativeItem(Item::get(Item::NETHERRACK, 0));
		self::addCreativeItem(Item::get(Item::BEDROCK, 0));
		self::addCreativeItem(Item::get(Item::COBBLESTONE_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::OAK_WOODEN_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::SPRUCE_WOODEN_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::BIRCH_WOODEN_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::JUNGLE_WOODEN_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::ACACIA_WOODEN_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::DARK_OAK_WOODEN_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::BRICK_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::SANDSTONE_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::STONE_BRICK_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::NETHER_BRICKS_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::QUARTZ_STAIRS, 0));
		self::addCreativeItem(Item::get(Item::SLAB, 0));
		self::addCreativeItem(Item::get(Item::SLAB, 1));
		self::addCreativeItem(Item::get(Item::WOODEN_SLAB, 0));
		self::addCreativeItem(Item::get(Item::WOODEN_SLAB, 1));
		self::addCreativeItem(Item::get(Item::WOODEN_SLAB, 2));
		self::addCreativeItem(Item::get(Item::WOODEN_SLAB, 3));
		self::addCreativeItem(Item::get(Item::WOODEN_SLAB, 4));
		self::addCreativeItem(Item::get(Item::WOODEN_SLAB, 5));
		self::addCreativeItem(Item::get(Item::SLAB, 3));
		self::addCreativeItem(Item::get(Item::SLAB, 4));
		self::addCreativeItem(Item::get(Item::SLAB, 5));
		self::addCreativeItem(Item::get(Item::SLAB, 6));
		self::addCreativeItem(Item::get(Item::QUARTZ_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::QUARTZ_BLOCK, 1));
		self::addCreativeItem(Item::get(Item::QUARTZ_BLOCK, 2));
		self::addCreativeItem(Item::get(Item::COAL_ORE, 0));
		self::addCreativeItem(Item::get(Item::IRON_ORE, 0));
		self::addCreativeItem(Item::get(Item::GOLD_ORE, 0));
		self::addCreativeItem(Item::get(Item::DIAMOND_ORE, 0));
		self::addCreativeItem(Item::get(Item::LAPIS_ORE, 0));
		self::addCreativeItem(Item::get(Item::REDSTONE_ORE, 0));
		self::addCreativeItem(Item::get(Item::EMERALD_ORE, 0));
		self::addCreativeItem(Item::get(Item::OBSIDIAN, 0));
		self::addCreativeItem(Item::get(Item::ICE, 0));
		self::addCreativeItem(Item::get(Item::SNOW_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::END_STONE, 0));
                self::addCreativeItem(Item::get(Item::QUARTZ, 0));

		//Decoration
		self::addCreativeItem(Item::get(Item::COBBLESTONE_WALL, 0));
		self::addCreativeItem(Item::get(Item::COBBLESTONE_WALL, 1));
		self::addCreativeItem(Item::get(Item::WATER_LILY, 0));
		self::addCreativeItem(Item::get(Item::GOLD_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::IRON_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::DIAMOND_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::LAPIS_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::COAL_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::EMERALD_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::REDSTONE_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::SNOW_LAYER, 0));
		self::addCreativeItem(Item::get(Item::GLASS, 0));
		self::addCreativeItem(Item::get(Item::GLOWSTONE_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::VINES, 0));
		self::addCreativeItem(Item::get(Item::NETHER_REACTOR, 0));
		self::addCreativeItem(Item::get(Item::LADDER, 0));
		self::addCreativeItem(Item::get(Item::SPONGE, 0));
		self::addCreativeItem(Item::get(Item::GLASS_PANE, 0));
		self::addCreativeItem(Item::get(Item::WOODEN_DOOR, 0));
		self::addCreativeItem(Item::get(Item::TRAPDOOR, 0));
		self::addCreativeItem(Item::get(Item::FENCE, Fence::FENCE_OAK));
		self::addCreativeItem(Item::get(Item::FENCE, Fence::FENCE_SPRUCE));
		self::addCreativeItem(Item::get(Item::FENCE, Fence::FENCE_BIRCH));
		self::addCreativeItem(Item::get(Item::FENCE, Fence::FENCE_JUNGLE));
		self::addCreativeItem(Item::get(Item::FENCE, Fence::FENCE_ACACIA));
		self::addCreativeItem(Item::get(Item::FENCE, Fence::FENCE_DARKOAK));
		self::addCreativeItem(Item::get(Item::NETHER_BRICK_FENCE, 0));
		self::addCreativeItem(Item::get(Item::FENCE_GATE, 0));
		self::addCreativeItem(Item::get(Item::FENCE_GATE_BIRCH, 0));
		self::addCreativeItem(Item::get(Item::FENCE_GATE_SPRUCE, 0));
		self::addCreativeItem(Item::get(Item::FENCE_GATE_DARK_OAK, 0));
		self::addCreativeItem(Item::get(Item::FENCE_GATE_JUNGLE, 0));
		self::addCreativeItem(Item::get(Item::FENCE_GATE_ACACIA, 0));
		self::addCreativeItem(Item::get(Item::IRON_BARS, 0));
		self::addCreativeItem(Item::get(Item::BED, 0));
		self::addCreativeItem(Item::get(Item::BOOKSHELF, 0));
		self::addCreativeItem(Item::get(Item::PAINTING, 0));
		self::addCreativeItem(Item::get(Item::WORKBENCH, 0));
		self::addCreativeItem(Item::get(Item::STONECUTTER, 0));
		self::addCreativeItem(Item::get(Item::CHEST, 0));
		self::addCreativeItem(Item::get(Item::FURNACE, 0));
		self::addCreativeItem(Item::get(Item::END_PORTAL, 0));
		self::addCreativeItem(Item::get(Item::DANDELION, 0));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_POPPY));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_BLUE_ORCHID));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_ALLIUM));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_AZURE_BLUET));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_RED_TULIP));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_ORANGE_TULIP));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_WHITE_TULIP));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_PINK_TULIP));
		self::addCreativeItem(Item::get(Item::RED_FLOWER, Flower::TYPE_OXEYE_DAISY));
		//TODO: Lilac
		//TODO: Double Tallgrass
		//TODO: Large Fern
		//TODO: Rose Bush
		//TODO: Peony
		self::addCreativeItem(Item::get(Item::BROWN_MUSHROOM, 0));
		self::addCreativeItem(Item::get(Item::RED_MUSHROOM, 0));
		//TODO: Mushroom block (brown, cover)
		//TODO: Mushroom block (red, cover)
		//TODO: Mushroom block (brown, stem)
		//TODO: Mushroom block (red, stem)
		self::addCreativeItem(Item::get(Item::CACTUS, 0));
		self::addCreativeItem(Item::get(Item::MELON_BLOCK, 0));
		self::addCreativeItem(Item::get(Item::PUMPKIN, 0));
		self::addCreativeItem(Item::get(Item::LIT_PUMPKIN, 0));
		self::addCreativeItem(Item::get(Item::COBWEB, 0));
		self::addCreativeItem(Item::get(Item::HAY_BALE, 0));
		self::addCreativeItem(Item::get(Item::TALL_GRASS, 1));
		self::addCreativeItem(Item::get(Item::TALL_GRASS, 2));
		self::addCreativeItem(Item::get(Item::DEAD_BUSH, 0));
		self::addCreativeItem(Item::get(Item::SAPLING, 0));
		self::addCreativeItem(Item::get(Item::SAPLING, 1));
		self::addCreativeItem(Item::get(Item::SAPLING, 2));
		self::addCreativeItem(Item::get(Item::SAPLING, 3));
		self::addCreativeItem(Item::get(Item::SAPLING, 4));
		self::addCreativeItem(Item::get(Item::SAPLING, 5));
		self::addCreativeItem(Item::get(Item::LEAVES, 0));
		self::addCreativeItem(Item::get(Item::LEAVES, 1));
		self::addCreativeItem(Item::get(Item::LEAVES, 2));
		self::addCreativeItem(Item::get(Item::LEAVES, 3));
		self::addCreativeItem(Item::get(Item::LEAVES2, 0));
		self::addCreativeItem(Item::get(Item::LEAVES2, 1));
		self::addCreativeItem(Item::get(Item::CAKE, 0));
		self::addCreativeItem(Item::get(Item::SIGN, 0));
		self::addCreativeItem(Item::get(Item::MONSTER_SPAWNER, 0));
		self::addCreativeItem(Item::get(Item::WOOL, 0));
		self::addCreativeItem(Item::get(Item::WOOL, 7));
		self::addCreativeItem(Item::get(Item::WOOL, 6));
		self::addCreativeItem(Item::get(Item::WOOL, 5));
		self::addCreativeItem(Item::get(Item::WOOL, 4));
		self::addCreativeItem(Item::get(Item::WOOL, 3));
		self::addCreativeItem(Item::get(Item::WOOL, 2));
		self::addCreativeItem(Item::get(Item::WOOL, 1));
		self::addCreativeItem(Item::get(Item::WOOL, 15));
		self::addCreativeItem(Item::get(Item::WOOL, 14));
		self::addCreativeItem(Item::get(Item::WOOL, 13));
		self::addCreativeItem(Item::get(Item::WOOL, 12));
		self::addCreativeItem(Item::get(Item::WOOL, 11));
		self::addCreativeItem(Item::get(Item::WOOL, 10));
		self::addCreativeItem(Item::get(Item::WOOL, 9));
		self::addCreativeItem(Item::get(Item::WOOL, 8));
		self::addCreativeItem(Item::get(Item::CARPET, 0));
		self::addCreativeItem(Item::get(Item::CARPET, 7));
		self::addCreativeItem(Item::get(Item::CARPET, 6));
		self::addCreativeItem(Item::get(Item::CARPET, 5));
		self::addCreativeItem(Item::get(Item::CARPET, 4));
		self::addCreativeItem(Item::get(Item::CARPET, 3));
		self::addCreativeItem(Item::get(Item::CARPET, 2));
		self::addCreativeItem(Item::get(Item::CARPET, 1));
		self::addCreativeItem(Item::get(Item::CARPET, 15));
		self::addCreativeItem(Item::get(Item::CARPET, 14));
		self::addCreativeItem(Item::get(Item::CARPET, 13));
		self::addCreativeItem(Item::get(Item::CARPET, 12));
		self::addCreativeItem(Item::get(Item::CARPET, 11));
		self::addCreativeItem(Item::get(Item::CARPET, 10));
		self::addCreativeItem(Item::get(Item::CARPET, 9));
		self::addCreativeItem(Item::get(Item::CARPET, 8));


		self::addCreativeItem(Item::get(Item::ANVIL, 0));
		self::addCreativeItem(Item::get(Item::ANVIL, 4));
		self::addCreativeItem(Item::get(Item::ANVIL, 8));

		//Tools
		//TODO self::addCreativeItem(Item::get(Item::RAILS, 0));
		//TODO self::addCreativeItem(Item::get(Item::POWERED_RAILS, 0));
		self::addCreativeItem(Item::get(Item::TORCH, 0));
		self::addCreativeItem(Item::get(Item::BUCKET, 0));
		self::addCreativeItem(Item::get(Item::BUCKET, 1));
		self::addCreativeItem(Item::get(Item::BUCKET, 8));
		self::addCreativeItem(Item::get(Item::BUCKET, 10));
		self::addCreativeItem(Item::get(Item::TNT, 0));
		self::addCreativeItem(Item::get(Item::IRON_HOE, 0));
		self::addCreativeItem(Item::get(Item::IRON_SHOVEL, 0));
		self::addCreativeItem(Item::get(Item::IRON_SWORD, 0));
		self::addCreativeItem(Item::get(Item::BOW, 0));
		self::addCreativeItem(Item::get(Item::SHEARS, 0));
		self::addCreativeItem(Item::get(Item::FLINT_AND_STEEL, 0));
		self::addCreativeItem(Item::get(Item::CLOCK, 0));
		self::addCreativeItem(Item::get(Item::COMPASS, 0));
		self::addCreativeItem(Item::get(Item::MINECART, 0));
		self::addCreativeItem(Item::get(Item::SPAWN_EGG, Villager::NETWORK_ID));
		//self::addCreativeItem(Item::get(Item::SPAWN_EGG, 10)); //Chicken
		//self::addCreativeItem(Item::get(Item::SPAWN_EGG, 11)); //Cow
		//self::addCreativeItem(Item::get(Item::SPAWN_EGG, 12)); //Pig
		//self::addCreativeItem(Item::get(Item::SPAWN_EGG, 13)); //Sheep
		//TODO: Wolf
		//TODO: Mooshroom
		//TODO: Creeper
		//TODO: Enderman
		//TODO: Silverfish
		//TODO: Skeleton
		//TODO: Slime
		self::addCreativeItem(Item::get(Item::SPAWN_EGG, Zombie::NETWORK_ID));
		//TODO: PigZombie
		self::addCreativeItem(Item::get(Item::SPAWN_EGG, Squid::NETWORK_ID));

		self::addCreativeItem(Item::get(Item::SNOWBALL));


		//Seeds
		self::addCreativeItem(Item::get(Item::SUGARCANE, 0));
		self::addCreativeItem(Item::get(Item::WHEAT, 0));
		self::addCreativeItem(Item::get(Item::SEEDS, 0));
		self::addCreativeItem(Item::get(Item::MELON_SEEDS, 0));
		self::addCreativeItem(Item::get(Item::PUMPKIN_SEEDS, 0));
		self::addCreativeItem(Item::get(Item::CARROT, 0));
		self::addCreativeItem(Item::get(Item::POTATO, 0));
		self::addCreativeItem(Item::get(Item::BEETROOT_SEEDS, 0));
		self::addCreativeItem(Item::get(Item::EGG, 0));
		self::addCreativeItem(Item::get(Item::RAW_FISH, 0));
		self::addCreativeItem(Item::get(Item::RAW_FISH, 1));
		self::addCreativeItem(Item::get(Item::RAW_FISH, 2));
		self::addCreativeItem(Item::get(Item::RAW_FISH, 3));
		self::addCreativeItem(Item::get(Item::COOKED_FISH, 0));
		self::addCreativeItem(Item::get(Item::COOKED_FISH, 1));
		self::addCreativeItem(Item::get(Item::DYE, 0));
		self::addCreativeItem(Item::get(Item::DYE, 7));
		self::addCreativeItem(Item::get(Item::DYE, 6));
		self::addCreativeItem(Item::get(Item::DYE, 5));
		self::addCreativeItem(Item::get(Item::DYE, 4));
		self::addCreativeItem(Item::get(Item::DYE, 3));
		self::addCreativeItem(Item::get(Item::DYE, 2));
		self::addCreativeItem(Item::get(Item::DYE, 1));
		self::addCreativeItem(Item::get(Item::DYE, 15));
		self::addCreativeItem(Item::get(Item::DYE, 14));
		self::addCreativeItem(Item::get(Item::DYE, 13));
		self::addCreativeItem(Item::get(Item::DYE, 12));
		self::addCreativeItem(Item::get(Item::DYE, 11));
		self::addCreativeItem(Item::get(Item::DYE, 10));
		self::addCreativeItem(Item::get(Item::DYE, 9));
		self::addCreativeItem(Item::get(Item::DYE, 8));
	}

	public static function clearCreativeItems(){
		Item::$creative = [];
	}

	public static function getCreativeItems(){
		return Item::$creative;
	}

	public static function addCreativeItem(Item $item){
		Item::$creative[] = Item::get($item->getId(), $item->getDamage());
	}

	public static function removeCreativeItem(Item $item){
		$index = self::getCreativeItemIndex($item);
		if($index !== -1){
			unset(Item::$creative[$index]);
		}
	}

	public static function isCreativeItem(Item $item){
		foreach(Item::$creative as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $index
	 * @return Item
	 */
	public static function getCreativeItem($index){
		return isset(Item::$creative[$index]) ? Item::$creative[$index] : null;
	}

	/**
	 * @param Item $item
	 * @return int
	 */
	public static function getCreativeItemIndex(Item $item){
		foreach(Item::$creative as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return $i;
			}
		}

		return -1;
	}

	public static function get($id, $meta = 0, $count = 1, $tags = ""){
		try{
			$class = self::$list[$id];
			if($class === null){
				return (new Item($id, $meta, $count))->setCompoundTag($tags);
			}elseif($id < 256){
				return (new ItemBlock(new $class($meta), $meta, $count))->setCompoundTag($tags);
			}else{
				return (new $class($meta, $count))->setCompoundTag($tags);
			}
		}catch(\RuntimeException $e){
			return (new Item($id, $meta, $count))->setCompoundTag($tags);
		}
	}

	public static function fromString($str, $multiple = false){
		if($multiple === true){
			$blocks = [];
			foreach(explode(",", $str) as $b){
				$blocks[] = self::fromString($b, false);
			}

			return $blocks;
		}else{
			$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			}else{
				$meta = $b[1] & 0xFFFF;
			}

			if(defined(Item::class . "::" . strtoupper($b[0]))){
				$item = self::get(constant(Item::class . "::" . strtoupper($b[0])), $meta);
				if($item->getId() === self::AIR and strtoupper($b[0]) !== "AIR"){
					$item = self::get($b[0] & 0xFFFF, $meta);
				}
			}else{
				$item = self::get($b[0] & 0xFFFF, $meta);
			}

			return $item;
		}
	}

	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown"){
		$this->id = $id & 0xffff;
		$this->meta = $meta !== null ? $meta & 0xffff : null;
		$this->count = (int) $count;
		$this->name = $name;
		if(!isset($this->block) and $this->id <= 0xff and isset(Block::$list[$this->id])){
			$this->block = Block::get($this->id, $this->meta);
			$this->name = $this->block->getName();
		}
	}

	public function setCompoundTag($tags){
		if($tags instanceof Compound){
			$this->setNamedTag($tags);
		}else{
			$this->tags = $tags;
			$this->cachedNBT = null;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCompoundTag(){
		return $this->tags;
	}

	public function hasCompoundTag(){
		return $this->tags !== "" and $this->tags !== null;
	}

	public function hasCustomBlockData(){
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof Compound){
			return true;
		}

		return false;
	}

	public function clearCustomBlockData(){
		if(!$this->hasCompoundTag()){
			return $this;
		}
		$tag = $this->getNamedTag();

		if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof Compound){
			unset($tag->display->BlockEntityTag);
			$this->setNamedTag($tag);
		}

		return $this;
	}

	public function setCustomBlockData(Compound $compound){
		$tags = clone $compound;
		$tags->setName("BlockEntityTag");

		if(!$this->hasCompoundTag()){
			$tag = new Compound("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		$tag->BlockEntityTag = $tags;
		$this->setNamedTag($tag);

		return $this;
	}

	public function getCustomBlockData(){
		if(!$this->hasCompoundTag()){
			return null;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof Compound){
			return $tag->BlockEntityTag;
		}

		return null;
	}

	public function hasEnchantments(){
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->ench)){
			$tag = $tag->ench;
			if($tag instanceof Enum){
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $id
	 * @return Enchantment|null
	 */
	public function getEnchantment($id){
		if(!$this->hasEnchantments()){
			return null;
		}

		foreach($this->getNamedTag()->ench as $entry){
			if($entry["id"] === $id){
				$e = Enchantment::getEnchantment($entry["id"]);
				$e->setLevel($entry["lvl"]);
				return $e;
			}
		}

		return null;
	}

	/**
	 * @param Enchantment $ench
	 */
	public function addEnchantment(Enchantment $ench){
		if(!$this->hasCompoundTag()){
			$tag = new Compound("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		if(!isset($tag->ench)){
			$tag->ench = new Enum("ench", []);
			$tag->ench->setTagType(NBT::TAG_Compound);
		}

		$found = false;

		foreach($tag->ench as $k => $entry){
			if($entry["id"] === $ench->getId()){
				$tag->ench->{$k} = new Compound("", [
					"id" => new Short("id", $ench->getId()),
					"lvl" => new Short("lvl", $ench->getLevel())
				]);
				$found = true;
				break;
			}
		}

		if(!$found){
			$tag->ench->{count($tag->ench) + 1} = new Compound("", [
				"id" => new Short("id", $ench->getId()),
				"lvl" => new Short("lvl", $ench->getLevel())
			]);
		}

		$this->setNamedTag($tag);
	}

	/**
	 * @return Enchantment[]
	 */
	public function getEnchantments(){
		if(!$this->hasEnchantments()){
			return [];
		}

		$enchantments = [];

		foreach($this->getNamedTag()->ench as $entry){
			$e = Enchantment::getEnchantment($entry["id"]);
			$e->setLevel($entry["lvl"]);
			$enchantments[] = $e;
		}

		return $enchantments;
	}

	public function hasCustomName(){
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->display;
			if($tag instanceof Compound and isset($tag->Name) and $tag->Name instanceof String){
				return true;
			}
		}

		return false;
	}

	public function getCustomName(){
		if(!$this->hasCompoundTag()){
			return "";
		}

		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->display;
			if($tag instanceof Compound and isset($tag->Name) and $tag->Name instanceof String){
				return $tag->Name->getValue();
			}
		}

		return "";
	}

	public function setCustomName($name){
		if((string) $name === ""){
			$this->clearCustomName();
		}

		if(!$this->hasCompoundTag()){
			$tag = new Compound("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		if(isset($tag->display) and $tag->display instanceof Compound){
			$tag->display->Name = new String("Name", $name);
		}else{
			$tag->display = new Compound("display", [
				"Name" => new String("Name", $name)
			]);
		}

		return $this;
	}

	public function clearCustomName(){
		if(!$this->hasCompoundTag()){
			return $this;
		}
		$tag = $this->getNamedTag();

		if(isset($tag->display) and $tag->display instanceof Compound){
			unset($tag->display->Name);
			if($tag->display->getCount() === 0){
				unset($tag->display);
			}

			$this->setNamedTag($tag);
		}

		return $this;
	}

	public function getNamedTagEntry($name){
		$tag = $this->getNamedTag();
		if($tag !== null){
			return isset($tag->{$name}) ? $tag->{$name} : null;
		}

		return null;
	}

	public function getNamedTag(){
		if(!$this->hasCompoundTag()){
			return null;
		}elseif($this->cachedNBT !== null){
			return $this->cachedNBT;
		}
		return $this->cachedNBT = self::parseCompoundTag($this->tags);
	}

	public function setNamedTag(Compound $tag){
		if($tag->getCount() === 0){
			return $this->clearNamedTag();
		}

		$this->cachedNBT = $tag;
		$this->tags = self::writeCompoundTag($tag);

		return $this;
	}

	public function clearNamedTag(){
		return $this->setCompoundTag("");
	}

	public function getCount(){
		return $this->count;
	}

	public function setCount($count){
		$this->count = (int) $count;
	}

	final public function getName(){
		return $this->hasCustomName() ? $this->getCustomName() : $this->name;
	}

	final public function canBePlaced(){
		return $this->block !== null and $this->block->canBePlaced();
	}

	public function getBlock(){
		if($this->block instanceof Block){
			return clone $this->block;
		}else{
			return Block::get(self::AIR);
		}
	}

	final public function getId(){
		return $this->id;
	}

	final public function getDamage(){
		return $this->meta;
	}

	public function setDamage($meta){
		$this->meta = $meta !== null ? $meta & 0xFFFF : null;
	}

	public function getMaxStackSize(){
		return 64;
	}

	final public function getFuelTime(){
		if(!isset(Fuel::$duration[$this->id])){
			return null;
		}
		if($this->id !== self::BUCKET or $this->meta === 10){
			return Fuel::$duration[$this->id];
		}

		return null;
	}

	/**
	 * @param Entity|Block $object
	 *
	 * @return bool
	 */
	public function useOn($object){
		return false;
	}

	/**
	 * @return bool
	 */
	public function isTool(){
		return false;
	}

	/**
	 * @return int|bool
	 */
	public function getMaxDurability(){
		return false;
	}

	public function isPickaxe(){
		return false;
	}

	public function isAxe(){
		return false;
	}

	public function isSword(){
		return false;
	}

	public function isShovel(){
		return false;
	}

	public function isHoe(){
		return false;
	}

	public function isShears(){
		return false;
	}

	final public function __toString(){
		return "Item " . $this->name . " (" . $this->id . ":" . ($this->meta === null ? "?" : $this->meta) . ")x" . $this->count . ($this->hasCompoundTag() ? " tags:0x".bin2hex($this->getCompoundTag()) : "");
	}

	public function getDestroySpeed(Block $block, Player $player){
		return 1;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		return false;
	}

	public final function equals(Item $item, $checkDamage = true, $checkCompound = true){
		return $this->id === $item->getId() and ($checkDamage === false or $this->getDamage() === $item->getDamage()) and ($checkCompound === false or $this->getCompoundTag() === $item->getCompoundTag());
	}

	public final function deepEquals(Item $item, $checkDamage = true, $checkCompound = true){
		if($item->equals($item, $checkDamage, $checkCompound)){
			return true;
		}elseif($item->hasCompoundTag() or $this->hasCompoundTag()){
			return NBT::matchTree($this->getNamedTag(), $item->getNamedTag());
		}

		return false;
	}

}
