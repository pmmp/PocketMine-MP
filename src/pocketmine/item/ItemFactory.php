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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use function constant;
use function defined;
use function explode;
use function get_class;
use function gettype;
use function is_numeric;
use function is_object;
use function is_string;
use function str_replace;
use function strtoupper;
use function trim;

/**
 * Manages Item instance creation and registration
 */
class ItemFactory{

	/** @var \SplFixedArray */
	private static $list = null;

	public static function init(){
		self::$list = new \SplFixedArray(65536);

		/*새로운 아이템 추가 작업 */
		self::registerItem(new LingeringPotion());

		self::registerItem(new MedicineBlindness()); /* 247 */

		self::registerItem(new Item(342, 0, "Minecart with Chest"));

		self::registerItem(new Item(381, 0, "Ender Eye"));

		self::registerItem(new CarrotonaStick()); /* 398 */

		self::registerItem(new Item(407, 0, "Minecart with TNT"));
		self::registerItem(new Item(408, 0, "Minecart with Hopper"));

		self::registerItem(new LeatherHorseArmor()); /* 416 */
		self::registerItem(new IronHorseArmor()); /* 417 */
		self::registerItem(new GoldHorseArmor()); /* 418 */
		self::registerItem(new DiamondHorseArmor()); /* 419 */

		self::registerItem(new Item(421, 0, "Name Tag"));

		self::registerItem(new EndCrystal()); /* 426 */

		self::registerItem(new BannerPattern(434, 0, "Banner Pattern"));

		self::registerItem(new Trident()); /* 455 */

		self::registerItem(new TurtleHelmet()); /* 469 */
		self::registerItem(new PhantomMembrane()); /* 470 */
		self::registerItem(new CrossBow()); /* 471 */

		self::registerItem(new SpruceSign());
		self::registerItem(new BirchSign());
		self::registerItem(new JungleSign());
		self::registerItem(new AcaciaSign());
		self::registerItem(new DarkOakSign());

		ItemFactory::registerItem(new Item(-2, 0, "Prismarine Stairrs"), true);
		ItemFactory::registerItem(new Item(-3, 0, "Dark Prismarine Stairrs"), true);
		ItemFactory::registerItem(new Item(-4, 0, 'Prismarine Brikcs Stairrs'), true);
		ItemFactory::registerItem(new Item(-5, 0, ""), true);
		ItemFactory::registerItem(new Item(-6, 0, ""), true);
		ItemFactory::registerItem(new Item(-7, 0, ""), true);
		ItemFactory::registerItem(new Item(-8, 0, ""), true);
		ItemFactory::registerItem(new Item(-9, 0, ""), true);
		ItemFactory::registerItem(new Item(-10, 0, ""), true);
		ItemFactory::registerItem(new Item(-11, 0, ""), true);
		ItemFactory::registerItem(new ItemBlock(266, 0, -11), true);

ItemFactory::registerItem(new Item(-130, 0, ''), true);
ItemFactory::registerItem(new Item(-131, 0, ''), true);
ItemFactory::registerItem(new Item(-132, 0, ''), true);
ItemFactory::registerItem(new Item(-133, 0, ''), true);
ItemFactory::registerItem(new Item(-134, 0, ''), true);
ItemFactory::registerItem(new Item(-135, 0, ''), true);
ItemFactory::registerItem(new Item(-136, 0, ''), true);
ItemFactory::registerItem(new Item(-137, 0, ''), true);
ItemFactory::registerItem(new Item(-138, 0, ''), true);
ItemFactory::registerItem(new Item(-139, 0, ''), true);
ItemFactory::registerItem(new Item(-140, 0, ''), true);
ItemFactory::registerItem(new Item(-141, 0, ''), true);
ItemFactory::registerItem(new Item(-142, 0, ''), true);
ItemFactory::registerItem(new Item(-143, 0, ''), true);
ItemFactory::registerItem(new Item(-144, 0, ''), true);
ItemFactory::registerItem(new Item(-145, 0, ''), true);
ItemFactory::registerItem(new Item(-146, 0, ''), true);
ItemFactory::registerItem(new Item(-147, 0, ''), true);
ItemFactory::registerItem(new Item(-148, 0, ''), true);
ItemFactory::registerItem(new Item(-149, 0, ''), true);
ItemFactory::registerItem(new Item(-150, 0, ''), true);
ItemFactory::registerItem(new Item(-151, 0, ''), true);
ItemFactory::registerItem(new Item(-152, 0, ''), true);
ItemFactory::registerItem(new Item(-153, 0, ''), true);
ItemFactory::registerItem(new Item(-154, 0, ''), true);
ItemFactory::registerItem(new Item(-155, 0, ''), true);
ItemFactory::registerItem(new Item(-156, 0, ''), true);
ItemFactory::registerItem(new Item(-157, 0, ''), true);
ItemFactory::registerItem(new Item(-158, 0, ''), true);
ItemFactory::registerItem(new Item(-159, 0, ''), true);
ItemFactory::registerItem(new Item(-160, 0, ''), true);
ItemFactory::registerItem(new Item(-161, 0, ''), true);
ItemFactory::registerItem(new Item(-162, 0, ''), true);
ItemFactory::registerItem(new Item(-163, 0, ''), true);
ItemFactory::registerItem(new Item(-164, 0, ''), true);
ItemFactory::registerItem(new Item(-165, 0, ''), true);
ItemFactory::registerItem(new Item(-166, 0, ''), true);
ItemFactory::registerItem(new Item(-167, 0, ''), true);
ItemFactory::registerItem(new Item(-168, 0, ''), true);
ItemFactory::registerItem(new Item(-169, 0, ''), true);
ItemFactory::registerItem(new Item(-170, 0, ''), true);
ItemFactory::registerItem(new Item(-171, 0, ''), true);
ItemFactory::registerItem(new Item(-172, 0, ''), true);
ItemFactory::registerItem(new Item(-173, 0, ''), true);
ItemFactory::registerItem(new Item(-174, 0, ''), true);
ItemFactory::registerItem(new Item(-175, 0, ''), true);
ItemFactory::registerItem(new Item(-176, 0, ''), true);
ItemFactory::registerItem(new Item(-177, 0, ''), true);
ItemFactory::registerItem(new Item(-178, 0, ''), true);
ItemFactory::registerItem(new Item(-179, 0, ''), true);
ItemFactory::registerItem(new Item(-180, 0, ''), true);
ItemFactory::registerItem(new Item(-181, 0, ''), true);
ItemFactory::registerItem(new Item(-182, 0, ''), true);
ItemFactory::registerItem(new Item(-183, 0, ''), true);
ItemFactory::registerItem(new Item(-184, 0, ''), true);
ItemFactory::registerItem(new Item(-185, 0, ''), true);
ItemFactory::registerItem(new Item(-186, 0, ''), true);
ItemFactory::registerItem(new Item(-187, 0, ''), true);
ItemFactory::registerItem(new Item(-188, 0, ''), true);
ItemFactory::registerItem(new Item(-189, 0, ''), true);
ItemFactory::registerItem(new Item(-190, 0, ''), true);
ItemFactory::registerItem(new Item(-191, 0, ''), true);
ItemFactory::registerItem(new Item(-192, 0, ''), true);
ItemFactory::registerItem(new Item(-193, 0, ''), true);
ItemFactory::registerItem(new Item(-194, 0, ''), true);
ItemFactory::registerItem(new Item(-195, 0, ''), true);
ItemFactory::registerItem(new Item(-196, 0, ''), true);
ItemFactory::registerItem(new Item(-197, 0, ''), true);
ItemFactory::registerItem(new Item(-198, 0, ''), true);
ItemFactory::registerItem(new Item(-199, 0, ''), true);
ItemFactory::registerItem(new Item(-200, 0, ''), true);
ItemFactory::registerItem(new Item(-201, 0, ''), true);
ItemFactory::registerItem(new Item(-202, 0, ''), true);
ItemFactory::registerItem(new Item(-203, 0, ''), true);
ItemFactory::registerItem(new Item(-204, 0, ''), true);
ItemFactory::registerItem(new Item(-205, 0, ''), true);
ItemFactory::registerItem(new Item(-206, 0, ''), true);
ItemFactory::registerItem(new Item(-207, 0, ''), true);
ItemFactory::registerItem(new Item(-208, 0, ''), true);
ItemFactory::registerItem(new Item(-209, 0, ''), true);
ItemFactory::registerItem(new Item(-210, 0, ''), true);
ItemFactory::registerItem(new Item(-211, 0, ''), true);
ItemFactory::registerItem(new Item(-212, 0, ''), true);
ItemFactory::registerItem(new Item(-213, 0, ''), true);

	/* Element */
	ItemFactory::registerItem(new Item(-12, 0, ''), true);
	ItemFactory::registerItem(new Item(-13, 0, ''), true);
	ItemFactory::registerItem(new Item(-14, 0, ''), true);
	ItemFactory::registerItem(new Item(-15, 0, ''), true);
	ItemFactory::registerItem(new Item(-16, 0, ''), true);
	ItemFactory::registerItem(new Item(-17, 0, ''), true);
	ItemFactory::registerItem(new Item(-18, 0, ''), true);
	ItemFactory::registerItem(new Item(-19, 0, ''), true);
	ItemFactory::registerItem(new Item(-20, 0, ''), true);
	ItemFactory::registerItem(new Item(-21, 0, ''), true);
	ItemFactory::registerItem(new Item(-22, 0, ''), true);
	ItemFactory::registerItem(new Item(-23, 0, ''), true);
	ItemFactory::registerItem(new Item(-24, 0, ''), true);
	ItemFactory::registerItem(new Item(-25, 0, ''), true);
	ItemFactory::registerItem(new Item(-26, 0, ''), true);
	ItemFactory::registerItem(new Item(-27, 0, ''), true);
	ItemFactory::registerItem(new Item(-28, 0, ''), true);
	ItemFactory::registerItem(new Item(-29, 0, ''), true);
	ItemFactory::registerItem(new Item(-30, 0, ''), true);
	ItemFactory::registerItem(new Item(-31, 0, ''), true);
	ItemFactory::registerItem(new Item(-32, 0, ''), true);
	ItemFactory::registerItem(new Item(-33, 0, ''), true);
	ItemFactory::registerItem(new Item(-34, 0, ''), true);
	ItemFactory::registerItem(new Item(-35, 0, ''), true);
	ItemFactory::registerItem(new Item(-36, 0, ''), true);
	ItemFactory::registerItem(new Item(-37, 0, ''), true);
	ItemFactory::registerItem(new Item(-38, 0, ''), true);
	ItemFactory::registerItem(new Item(-39, 0, ''), true);
	ItemFactory::registerItem(new Item(-40, 0, ''), true);
	ItemFactory::registerItem(new Item(-41, 0, ''), true);
	ItemFactory::registerItem(new Item(-42, 0, ''), true);
	ItemFactory::registerItem(new Item(-43, 0, ''), true);
	ItemFactory::registerItem(new Item(-44, 0, ''), true);
	ItemFactory::registerItem(new Item(-45, 0, ''), true);
	ItemFactory::registerItem(new Item(-46, 0, ''), true);
	ItemFactory::registerItem(new Item(-47, 0, ''), true);
	ItemFactory::registerItem(new Item(-48, 0, ''), true);
	ItemFactory::registerItem(new Item(-49, 0, ''), true);
	ItemFactory::registerItem(new Item(-50, 0, ''), true);
	ItemFactory::registerItem(new Item(-51, 0, ''), true);
	ItemFactory::registerItem(new Item(-52, 0, ''), true);
	ItemFactory::registerItem(new Item(-53, 0, ''), true);
	ItemFactory::registerItem(new Item(-54, 0, ''), true);
	ItemFactory::registerItem(new Item(-55, 0, ''), true);
	ItemFactory::registerItem(new Item(-56, 0, ''), true);
	ItemFactory::registerItem(new Item(-57, 0, ''), true);
	ItemFactory::registerItem(new Item(-58, 0, ''), true);
	ItemFactory::registerItem(new Item(-59, 0, ''), true);
	ItemFactory::registerItem(new Item(-60, 0, ''), true);
	ItemFactory::registerItem(new Item(-61, 0, ''), true);
	ItemFactory::registerItem(new Item(-62, 0, ''), true);
	ItemFactory::registerItem(new Item(-63, 0, ''), true);
	ItemFactory::registerItem(new Item(-64, 0, ''), true);
	ItemFactory::registerItem(new Item(-65, 0, ''), true);
	ItemFactory::registerItem(new Item(-66, 0, ''), true);
	ItemFactory::registerItem(new Item(-67, 0, ''), true);
	ItemFactory::registerItem(new Item(-68, 0, ''), true);
	ItemFactory::registerItem(new Item(-69, 0, ''), true);
	ItemFactory::registerItem(new Item(-70, 0, ''), true);
	ItemFactory::registerItem(new Item(-71, 0, ''), true);
	ItemFactory::registerItem(new Item(-72, 0, ''), true);
	ItemFactory::registerItem(new Item(-73, 0, ''), true);
	ItemFactory::registerItem(new Item(-74, 0, ''), true);
	ItemFactory::registerItem(new Item(-75, 0, ''), true);
	ItemFactory::registerItem(new Item(-76, 0, ''), true);
	ItemFactory::registerItem(new Item(-77, 0, ''), true);
	ItemFactory::registerItem(new Item(-78, 0, ''), true);
	ItemFactory::registerItem(new Item(-79, 0, ''), true);
	ItemFactory::registerItem(new Item(-80, 0, ''), true);
	ItemFactory::registerItem(new Item(-81, 0, ''), true);
	ItemFactory::registerItem(new Item(-82, 0, ''), true);
	ItemFactory::registerItem(new Item(-83, 0, ''), true);
	ItemFactory::registerItem(new Item(-84, 0, ''), true);
	ItemFactory::registerItem(new Item(-85, 0, ''), true);
	ItemFactory::registerItem(new Item(-86, 0, ''), true);
	ItemFactory::registerItem(new Item(-87, 0, ''), true);
	ItemFactory::registerItem(new Item(-88, 0, ''), true);
	ItemFactory::registerItem(new Item(-89, 0, ''), true);
	ItemFactory::registerItem(new Item(-90, 0, ''), true);
	ItemFactory::registerItem(new Item(-91, 0, ''), true);
	ItemFactory::registerItem(new Item(-92, 0, ''), true);
	ItemFactory::registerItem(new Item(-93, 0, ''), true);
	ItemFactory::registerItem(new Item(-94, 0, ''), true);
	ItemFactory::registerItem(new Item(-95, 0, ''), true);
	ItemFactory::registerItem(new Item(-96, 0, ''), true);
	ItemFactory::registerItem(new Item(-97, 0, ''), true);
	ItemFactory::registerItem(new Item(-98, 0, ''), true);
	ItemFactory::registerItem(new Item(-99, 0, ''), true);
	ItemFactory::registerItem(new Item(-100, 0, ''), true);
	ItemFactory::registerItem(new Item(-101, 0, ''), true);
	ItemFactory::registerItem(new Item(-102, 0, ''), true);
	ItemFactory::registerItem(new Item(-103, 0, ''), true);
	ItemFactory::registerItem(new Item(-104, 0, ''), true);
	ItemFactory::registerItem(new Item(-105, 0, ''), true);
	ItemFactory::registerItem(new Item(-106, 0, ''), true);
	ItemFactory::registerItem(new Item(-107, 0, ''), true);
	ItemFactory::registerItem(new Item(-108, 0, ''), true);
	ItemFactory::registerItem(new Item(-109, 0, ''), true);
	ItemFactory::registerItem(new Item(-110, 0, ''), true);
	ItemFactory::registerItem(new Item(-111, 0, ''), true);
	ItemFactory::registerItem(new Item(-112, 0, ''), true);
	ItemFactory::registerItem(new Item(-113, 0, ''), true);
	ItemFactory::registerItem(new Item(-114, 0, ''), true);
	ItemFactory::registerItem(new Item(-115, 0, ''), true);
	ItemFactory::registerItem(new Item(-116, 0, ''), true);
	ItemFactory::registerItem(new Item(-117, 0, ''), true);
	ItemFactory::registerItem(new Item(-118, 0, ''), true);
	ItemFactory::registerItem(new Item(-119, 0, ''), true);
	ItemFactory::registerItem(new Item(-120, 0, ''), true);
	ItemFactory::registerItem(new Item(-121, 0, ''), true);
	ItemFactory::registerItem(new Item(-122, 0, ''), true);
	ItemFactory::registerItem(new Item(-123, 0, ''), true);
	ItemFactory::registerItem(new Item(-124, 0, ''), true);
	ItemFactory::registerItem(new Item(-125, 0, ''), true);
	ItemFactory::registerItem(new Item(-126, 0, ''), true);
	ItemFactory::registerItem(new Item(-127, 0, ''), true);
	ItemFactory::registerItem(new Item(-128, 0, ''), true);
	ItemFactory::registerItem(new Item(-129, 0, ''), true);

	/* ELement End */

		self::registerItem(new Shield()); /* 513 */

		/*새로운 아이템 추가 작업 */

		self::registerItem(new Shovel(Item::IRON_SHOVEL, 0, "Iron Shovel", TieredTool::TIER_IRON));
		self::registerItem(new Pickaxe(Item::IRON_PICKAXE, 0, "Iron Pickaxe", TieredTool::TIER_IRON));
		self::registerItem(new Axe(Item::IRON_AXE, 0, "Iron Axe", TieredTool::TIER_IRON));
		self::registerItem(new FlintSteel());
		self::registerItem(new Apple());
		self::registerItem(new Bow());
		self::registerItem(new Arrow());
		self::registerItem(new Coal());
		self::registerItem(new Item(Item::DIAMOND, 0, "Diamond"));
		self::registerItem(new Item(Item::IRON_INGOT, 0, "Iron Ingot"));
		self::registerItem(new Item(Item::GOLD_INGOT, 0, "Gold Ingot"));
		self::registerItem(new Sword(Item::IRON_SWORD, 0, "Iron Sword", TieredTool::TIER_IRON));
		self::registerItem(new Sword(Item::WOODEN_SWORD, 0, "Wooden Sword", TieredTool::TIER_WOODEN));
		self::registerItem(new Shovel(Item::WOODEN_SHOVEL, 0, "Wooden Shovel", TieredTool::TIER_WOODEN));
		self::registerItem(new Pickaxe(Item::WOODEN_PICKAXE, 0, "Wooden Pickaxe", TieredTool::TIER_WOODEN));
		self::registerItem(new Axe(Item::WOODEN_AXE, 0, "Wooden Axe", TieredTool::TIER_WOODEN));
		self::registerItem(new Sword(Item::STONE_SWORD, 0, "Stone Sword", TieredTool::TIER_STONE));
		self::registerItem(new Shovel(Item::STONE_SHOVEL, 0, "Stone Shovel", TieredTool::TIER_STONE));
		self::registerItem(new Pickaxe(Item::STONE_PICKAXE, 0, "Stone Pickaxe", TieredTool::TIER_STONE));
		self::registerItem(new Axe(Item::STONE_AXE, 0, "Stone Axe", TieredTool::TIER_STONE));
		self::registerItem(new Sword(Item::DIAMOND_SWORD, 0, "Diamond Sword", TieredTool::TIER_DIAMOND));
		self::registerItem(new Shovel(Item::DIAMOND_SHOVEL, 0, "Diamond Shovel", TieredTool::TIER_DIAMOND));
		self::registerItem(new Pickaxe(Item::DIAMOND_PICKAXE, 0, "Diamond Pickaxe", TieredTool::TIER_DIAMOND));
		self::registerItem(new Axe(Item::DIAMOND_AXE, 0, "Diamond Axe", TieredTool::TIER_DIAMOND));
		self::registerItem(new Stick());
		self::registerItem(new Bowl());
		self::registerItem(new MushroomStew());
		self::registerItem(new Sword(Item::GOLDEN_SWORD, 0, "Gold Sword", TieredTool::TIER_GOLD));
		self::registerItem(new Shovel(Item::GOLDEN_SHOVEL, 0, "Gold Shovel", TieredTool::TIER_GOLD));
		self::registerItem(new Pickaxe(Item::GOLDEN_PICKAXE, 0, "Gold Pickaxe", TieredTool::TIER_GOLD));
		self::registerItem(new Axe(Item::GOLDEN_AXE, 0, "Gold Axe", TieredTool::TIER_GOLD));
		self::registerItem(new StringItem());
		self::registerItem(new Item(Item::FEATHER, 0, "Feather"));
		self::registerItem(new Item(Item::GUNPOWDER, 0, "Gunpowder"));
		self::registerItem(new Hoe(Item::WOODEN_HOE, 0, "Wooden Hoe", TieredTool::TIER_WOODEN));
		self::registerItem(new Hoe(Item::STONE_HOE, 0, "Stone Hoe", TieredTool::TIER_STONE));
		self::registerItem(new Hoe(Item::IRON_HOE, 0, "Iron Hoe", TieredTool::TIER_IRON));
		self::registerItem(new Hoe(Item::DIAMOND_HOE, 0, "Diamond Hoe", TieredTool::TIER_DIAMOND));
		self::registerItem(new Hoe(Item::GOLDEN_HOE, 0, "Golden Hoe", TieredTool::TIER_GOLD));
		self::registerItem(new WheatSeeds());
		self::registerItem(new Item(Item::WHEAT, 0, "Wheat"));
		self::registerItem(new Bread());
		self::registerItem(new LeatherCap());
		self::registerItem(new LeatherTunic());
		self::registerItem(new LeatherPants());
		self::registerItem(new LeatherBoots());
		self::registerItem(new ChainHelmet());
		self::registerItem(new ChainChestplate());
		self::registerItem(new ChainLeggings());
		self::registerItem(new ChainBoots());
		self::registerItem(new IronHelmet());
		self::registerItem(new IronChestplate());
		self::registerItem(new IronLeggings());
		self::registerItem(new IronBoots());
		self::registerItem(new DiamondHelmet());
		self::registerItem(new DiamondChestplate());
		self::registerItem(new DiamondLeggings());
		self::registerItem(new DiamondBoots());
		self::registerItem(new GoldHelmet());
		self::registerItem(new GoldChestplate());
		self::registerItem(new GoldLeggings());
		self::registerItem(new GoldBoots());
		self::registerItem(new Item(Item::FLINT, 0, "Flint"));
		self::registerItem(new RawPorkchop());
		self::registerItem(new CookedPorkchop());
		self::registerItem(new PaintingItem());
		self::registerItem(new GoldenApple());
		self::registerItem(new Sign());
		self::registerItem(new ItemBlock(Block::OAK_DOOR_BLOCK, 0, Item::OAK_DOOR));
		self::registerItem(new Bucket());

		self::registerItem(new Minecart());
		self::registerItem(new Saddle());
		self::registerItem(new ItemBlock(Block::IRON_DOOR_BLOCK, 0, Item::IRON_DOOR));
		self::registerItem(new Redstone());
		self::registerItem(new Snowball());
		self::registerItem(new Boat());
		self::registerItem(new Item(Item::LEATHER, 0, "Leather"));
		//TODO: KELP
		self::registerItem(new Item(Item::BRICK, 0, "Brick"));
		self::registerItem(new Item(Item::CLAY_BALL, 0, "Clay"));
		self::registerItem(new ItemBlock(Block::SUGARCANE_BLOCK, 0, Item::SUGARCANE));
		self::registerItem(new Item(Item::PAPER, 0, "Paper"));
		self::registerItem(new Book());
		self::registerItem(new Item(Item::SLIME_BALL, 0, "Slimeball"));
		//TODO: CHEST_MINECART

		self::registerItem(new Egg());
		self::registerItem(new Compass());
		self::registerItem(new FishingRod());
		self::registerItem(new Clock());
		self::registerItem(new Item(Item::GLOWSTONE_DUST, 0, "Glowstone Dust"));
		self::registerItem(new RawFish());
		self::registerItem(new CookedFish());
		self::registerItem(new Dye());
		self::registerItem(new Item(Item::BONE, 0, "Bone"));
		self::registerItem(new Item(Item::SUGAR, 0, "Sugar"));
		self::registerItem(new ItemBlock(Block::CAKE_BLOCK, 0, Item::CAKE));
		self::registerItem(new Bed());
		self::registerItem(new ItemBlock(Block::REPEATER_BLOCK, 0, Item::REPEATER));
		self::registerItem(new Cookie());
		self::registerItem(new Map());
		self::registerItem(new Shears());
		self::registerItem(new Melon());
		self::registerItem(new PumpkinSeeds());
		self::registerItem(new MelonSeeds());
		self::registerItem(new RawBeef());
		self::registerItem(new Steak());
		self::registerItem(new RawChicken());
		self::registerItem(new CookedChicken());
		self::registerItem(new RottenFlesh());
		self::registerItem(new EnderPearl());
		self::registerItem(new BlazeRod());
		self::registerItem(new Item(Item::GHAST_TEAR, 0, "Ghast Tear"));
		self::registerItem(new Item(Item::GOLD_NUGGET, 0, "Gold Nugget"));
		self::registerItem(new ItemBlock(Block::NETHER_WART_PLANT, 0, Item::NETHER_WART));
		self::registerItem(new Potion());
		self::registerItem(new GlassBottle());
		self::registerItem(new SpiderEye());
		self::registerItem(new Item(Item::FERMENTED_SPIDER_EYE, 0, "Fermented Spider Eye"));
		self::registerItem(new Item(Item::BLAZE_POWDER, 0, "Blaze Powder"));
		self::registerItem(new Item(Item::MAGMA_CREAM, 0, "Magma Cream"));
		self::registerItem(new ItemBlock(Block::BREWING_STAND_BLOCK, 0, Item::BREWING_STAND));
		self::registerItem(new ItemBlock(Block::CAULDRON_BLOCK, 0, Item::CAULDRON));
		//TODO: ENDER_EYE
		self::registerItem(new Item(Item::GLISTERING_MELON, 0, "Glistering Melon"));
		self::registerItem(new SpawnEgg());
		self::registerItem(new ExperienceBottle());
		//TODO: FIREBALL
		self::registerItem(new WritableBook());
		self::registerItem(new WrittenBook());
		self::registerItem(new Item(Item::EMERALD, 0, "Emerald"));
		self::registerItem(new ItemBlock(Block::ITEM_FRAME_BLOCK, 0, Item::ITEM_FRAME));
		self::registerItem(new ItemBlock(Block::FLOWER_POT_BLOCK, 0, Item::FLOWER_POT));
		self::registerItem(new Carrot());
		self::registerItem(new Potato());
		self::registerItem(new BakedPotato());
		self::registerItem(new PoisonousPotato());
		self::registerItem(new EmptyMap());
		self::registerItem(new GoldenCarrot());
		self::registerItem(new ItemBlock(Block::SKULL_BLOCK, 0, Item::SKULL));
		//TODO: CARROTONASTICK
		self::registerItem(new Item(Item::NETHER_STAR, 0, "Nether Star"));
		self::registerItem(new PumpkinPie());
		self::registerItem(new Fireworks());
		//TODO: FIREWORKSCHARGE
		self::registerItem(new EnchantedBook());
		self::registerItem(new ItemBlock(Block::COMPARATOR_BLOCK, 0, Item::COMPARATOR));
		self::registerItem(new Item(Item::NETHER_BRICK, 0, "Nether Brick"));
		self::registerItem(new Item(Item::NETHER_QUARTZ, 0, "Nether Quartz"));
		//TODO: MINECART_WITH_TNT
		//TODO: HOPPER_MINECART
		self::registerItem(new Item(Item::PRISMARINE_SHARD, 0, "Prismarine Shard"));
		self::registerItem(new ItemBlock(Block::HOPPER_BLOCK, 0, Item::HOPPER));
		self::registerItem(new RawRabbit());
		self::registerItem(new CookedRabbit());
		self::registerItem(new RabbitStew());
		self::registerItem(new Item(Item::RABBIT_FOOT, 0, "Rabbit's Foot"));
		self::registerItem(new Item(Item::RABBIT_HIDE, 0, "Rabbit Hide"));
		//TODO: HORSEARMORLEATHER
		//TODO: HORSEARMORIRON
		//TODO: GOLD_HORSE_ARMOR
		//TODO: DIAMOND_HORSE_ARMOR
		self::registerItem(new Item(Item::LEAD, 0, "Lead"));
		//TODO: NAMETAG
		self::registerItem(new Item(Item::PRISMARINE_CRYSTALS, 0, "Prismarine Crystals"));
		self::registerItem(new RawMutton());
		self::registerItem(new CookedMutton());
		self::registerItem(new ArmorStand());
		//TODO: END_CRYSTAL
		self::registerItem(new ItemBlock(Block::SPRUCE_DOOR_BLOCK, 0, Item::SPRUCE_DOOR));
		self::registerItem(new ItemBlock(Block::BIRCH_DOOR_BLOCK, 0, Item::BIRCH_DOOR));
		self::registerItem(new ItemBlock(Block::JUNGLE_DOOR_BLOCK, 0, Item::JUNGLE_DOOR));
		self::registerItem(new ItemBlock(Block::ACACIA_DOOR_BLOCK, 0, Item::ACACIA_DOOR));
		self::registerItem(new ItemBlock(Block::DARK_OAK_DOOR_BLOCK, 0, Item::DARK_OAK_DOOR));
		self::registerItem(new ChorusFruit());
		self::registerItem(new Item(Item::CHORUS_FRUIT_POPPED, 0, "Popped Chorus Fruit"));

		self::registerItem(new Item(Item::DRAGON_BREATH, 0, "Dragon's Breath"));
		self::registerItem(new SplashPotion());

		//TODO: LINGERING_POTION
		//TODO: SPARKLER
		//TODO: COMMAND_BLOCK_MINECART
		self::registerItem(new Elytra());
		self::registerItem(new Item(Item::SHULKER_SHELL, 0, "Shulker Shell"));
		self::registerItem(new Banner());
		//TODO: MEDICINE
		//TODO: BALLOON
		//TODO: RAPID_FERTILIZER
		self::registerItem(new Totem());
		self::registerItem(new Item(Item::BLEACH, 0, "Bleach")); //EDU
		self::registerItem(new Item(Item::IRON_NUGGET, 0, "Iron Nugget"));
		//TODO: ICE_BOMB

		//TODO: TRIDENT

		self::registerItem(new Beetroot());
		self::registerItem(new BeetrootSeeds());
		self::registerItem(new BeetrootSoup());
		self::registerItem(new RawSalmon());
		self::registerItem(new Clownfish());
		self::registerItem(new Pufferfish());
		self::registerItem(new CookedSalmon());
		self::registerItem(new DriedKelp());
		self::registerItem(new Item(Item::NAUTILUS_SHELL, 0, "Nautilus Shell"));
		self::registerItem(new GoldenAppleEnchanted());
		self::registerItem(new Item(Item::HEART_OF_THE_SEA, 0, "Heart of the Sea"));
		self::registerItem(new Item(Item::TURTLE_SHELL_PIECE, 0, "Scute"));
		//TODO: TURTLE_HELMET

		self::registerItem(new Record(Item::RECORD_13, LevelSoundEventPacket::SOUND_RECORD_13));
		self::registerItem(new Record(Item::RECORD_CAT, LevelSoundEventPacket::SOUND_RECORD_CAT));
		self::registerItem(new Record(Item::RECORD_BLOCKS, LevelSoundEventPacket::SOUND_RECORD_BLOCKS));
		self::registerItem(new Record(Item::RECORD_CHIRP, LevelSoundEventPacket::SOUND_RECORD_CHIRP));
		self::registerItem(new Record(Item::RECORD_FAR, LevelSoundEventPacket::SOUND_RECORD_FAR));
		self::registerItem(new Record(Item::RECORD_MALL, LevelSoundEventPacket::SOUND_RECORD_MALL));
		self::registerItem(new Record(Item::RECORD_MELLOHI, LevelSoundEventPacket::SOUND_RECORD_MELLOHI));
		self::registerItem(new Record(Item::RECORD_STAL, LevelSoundEventPacket::SOUND_RECORD_STAL));
		self::registerItem(new Record(Item::RECORD_STRAD, LevelSoundEventPacket::SOUND_RECORD_STRAD));
		self::registerItem(new Record(Item::RECORD_WARD, LevelSoundEventPacket::SOUND_RECORD_WARD));
		self::registerItem(new Record(Item::RECORD_11, LevelSoundEventPacket::SOUND_RECORD_11));
		self::registerItem(new Record(Item::RECORD_WAIT, LevelSoundEventPacket::SOUND_RECORD_WAIT));
	}

	/**
	 * Registers an item type into the index. Plugins may use this method to register new item types or override existing
	 * ones.
	 *
	 * NOTE: If you are registering a new item type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @param Item $item
	 * @param bool $override
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered item without specifying the
	 * $override parameter.
	 */
	public static function registerItem(Item $item, bool $override = false){
		$id = $item->getId();
		if(!$override and self::isRegistered($id)){
			throw new \RuntimeException("Trying to overwrite an already registered item $id");
		}

		self::$list[self::getListOffset($id)] = clone $item;
	}

	/**
	 * Returns an instance of the Item with the specified id, meta, count and NBT.
	 *
	 * @param int                     $id
	 * @param int                     $meta
	 * @param int                     $count
	 * @param CompoundTag|string|null $tags
	 *
	 * @return Item
	 * @throws \TypeError
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		if(!is_string($tags) and !($tags instanceof CompoundTag) and $tags !== null){
			throw new \TypeError("`tags` argument must be a string or CompoundTag instance, " . (is_object($tags) ? "instance of " . get_class($tags) : gettype($tags)) . " given");
		}

		try{
			/** @var Item|null $listed */
			$listed = self::$list[self::getListOffset($id)];
			if($listed !== null){
				$item = clone $listed;
			}elseif($id >= 0 and $id < 256){ //intentionally excludes negatives because extended blocks aren't supported yet
				/* Blocks must have a damage value 0-15, but items can have damage value -1 to indicate that they are
				 * crafting ingredients with any-damage. */
				$item = new ItemBlock($id, $meta);
			}else{
				$item = new Item($id, $meta);
			}
		}catch(\RuntimeException $e){
			throw new \InvalidArgumentException("Item ID $id is invalid or out of bounds");
		}

		$item->setDamage($meta);
		$item->setCount($count);
		$item->setCompoundTag($tags);
		return $item;
	}

	/**
	 * Tries to parse the specified string into Item ID/meta identifiers, and returns Item instances it created.
	 *
	 * Example accepted formats:
	 * - `diamond_pickaxe:5`
	 * - `minecraft:string`
	 * - `351:4 (lapis lazuli ID:meta)`
	 *
	 * If multiple item instances are to be created, their identifiers must be comma-separated, for example:
	 * `diamond_pickaxe,wooden_shovel:18,iron_ingot`
	 *
	 * @param string $str
	 * @param bool   $multiple
	 *
	 * @return Item[]|Item
	 *
	 * @throws \InvalidArgumentException if the given string cannot be parsed as an item identifier
	 */
	public static function fromString(string $str, bool $multiple = false){
		if($multiple){
			$blocks = [];
			foreach(explode(",", $str) as $b){
				$blocks[] = self::fromString($b, false);
			}

			return $blocks;
		}else{
			$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			}elseif(is_numeric($b[1])){
				$meta = (int) $b[1];
			}else{
				throw new \InvalidArgumentException("Unable to parse \"" . $b[1] . "\" from \"" . $str . "\" as a valid meta value");
			}

			if(is_numeric($b[0])){
				$item = self::get((int) $b[0], $meta);
			}elseif(defined(ItemIds::class . "::" . strtoupper($b[0]))){
				$item = self::get(constant(ItemIds::class . "::" . strtoupper($b[0])), $meta);
			}else{
				throw new \InvalidArgumentException("Unable to resolve \"" . $str . "\" to a valid item");
			}

			return $item;
		}
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function isRegistered(int $id) : bool{
		if($id < 256){
			return BlockFactory::isRegistered($id);
		}
		return self::$list[self::getListOffset($id)] !== null;
	}

	private static function getListOffset(int $id) : int{
		if($id < -0x8000 or $id > 0x7fff){
			throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		return $id & 0xffff;
	}
}
