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
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\RecordType;
use pocketmine\block\utils\SkullType;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\inventory\ArmorInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

/**
 * Manages deserializing item types from their legacy ID/metadata.
 * This is primarily needed for loading inventories saved in the world (or playerdata storage).
 */
class ItemFactory{
	use SingletonTrait;

	/** @var Item[] */
	private $list = [];

	public function __construct(){
		$this->registerArmorItems();
		$this->registerSpawnEggs();
		$this->registerTierToolItems();

		$this->register(new Apple(new ItemIdentifier(ItemIds::APPLE, 0), "Apple"));
		$this->register(new Arrow(new ItemIdentifier(ItemIds::ARROW, 0), "Arrow"));

		$this->register(new BakedPotato(new ItemIdentifier(ItemIds::BAKED_POTATO, 0), "Baked Potato"));
		$this->register(new Bamboo(new ItemIdentifier(ItemIds::BAMBOO, 0), "Bamboo"), true);
		$this->register(new Beetroot(new ItemIdentifier(ItemIds::BEETROOT, 0), "Beetroot"));
		$this->register(new BeetrootSeeds(new ItemIdentifier(ItemIds::BEETROOT_SEEDS, 0), "Beetroot Seeds"));
		$this->register(new BeetrootSoup(new ItemIdentifier(ItemIds::BEETROOT_SOUP, 0), "Beetroot Soup"));
		$this->register(new BlazeRod(new ItemIdentifier(ItemIds::BLAZE_ROD, 0), "Blaze Rod"));
		$this->register(new Book(new ItemIdentifier(ItemIds::BOOK, 0), "Book"));
		$this->register(new Bow(new ItemIdentifier(ItemIds::BOW, 0), "Bow"));
		$this->register(new Bowl(new ItemIdentifier(ItemIds::BOWL, 0), "Bowl"));
		$this->register(new Bread(new ItemIdentifier(ItemIds::BREAD, 0), "Bread"));
		$this->register(new Bucket(new ItemIdentifier(ItemIds::BUCKET, 0), "Bucket"));
		$this->register(new Carrot(new ItemIdentifier(ItemIds::CARROT, 0), "Carrot"));
		$this->register(new ChorusFruit(new ItemIdentifier(ItemIds::CHORUS_FRUIT, 0), "Chorus Fruit"));
		$this->register(new Clock(new ItemIdentifier(ItemIds::CLOCK, 0), "Clock"));
		$this->register(new Clownfish(new ItemIdentifier(ItemIds::CLOWNFISH, 0), "Clownfish"));
		$this->register(new Coal(new ItemIdentifier(ItemIds::COAL, 0), "Coal"));
		$this->register(new ItemBlockWallOrFloor(new ItemIdentifier(ItemIds::CORAL_FAN, 0), VanillaBlocks::TUBE_CORAL_FAN(), VanillaBlocks::TUBE_WALL_CORAL_FAN()), true);
		$this->register(new ItemBlockWallOrFloor(new ItemIdentifier(ItemIds::CORAL_FAN, 1), VanillaBlocks::BRAIN_CORAL_FAN(), VanillaBlocks::BRAIN_WALL_CORAL_FAN()), true);
		$this->register(new ItemBlockWallOrFloor(new ItemIdentifier(ItemIds::CORAL_FAN, 2), VanillaBlocks::BUBBLE_CORAL_FAN(), VanillaBlocks::BUBBLE_WALL_CORAL_FAN()), true);
		$this->register(new ItemBlockWallOrFloor(new ItemIdentifier(ItemIds::CORAL_FAN, 3), VanillaBlocks::FIRE_CORAL_FAN(), VanillaBlocks::FIRE_WALL_CORAL_FAN()), true);
		$this->register(new ItemBlockWallOrFloor(new ItemIdentifier(ItemIds::CORAL_FAN, 4), VanillaBlocks::HORN_CORAL_FAN(), VanillaBlocks::HORN_WALL_CORAL_FAN()), true);
		$this->register(new Coal(new ItemIdentifier(ItemIds::COAL, 1), "Charcoal"));
		$this->register(new CocoaBeans(new ItemIdentifier(ItemIds::DYE, 3), "Cocoa Beans"));
		$this->register(new Compass(new ItemIdentifier(ItemIds::COMPASS, 0), "Compass"));
		$this->register(new CookedChicken(new ItemIdentifier(ItemIds::COOKED_CHICKEN, 0), "Cooked Chicken"));
		$this->register(new CookedFish(new ItemIdentifier(ItemIds::COOKED_FISH, 0), "Cooked Fish"));
		$this->register(new CookedMutton(new ItemIdentifier(ItemIds::COOKED_MUTTON, 0), "Cooked Mutton"));
		$this->register(new CookedPorkchop(new ItemIdentifier(ItemIds::COOKED_PORKCHOP, 0), "Cooked Porkchop"));
		$this->register(new CookedRabbit(new ItemIdentifier(ItemIds::COOKED_RABBIT, 0), "Cooked Rabbit"));
		$this->register(new CookedSalmon(new ItemIdentifier(ItemIds::COOKED_SALMON, 0), "Cooked Salmon"));
		$this->register(new Cookie(new ItemIdentifier(ItemIds::COOKIE, 0), "Cookie"));
		$this->register(new DriedKelp(new ItemIdentifier(ItemIds::DRIED_KELP, 0), "Dried Kelp"));
		$this->register(new Egg(new ItemIdentifier(ItemIds::EGG, 0), "Egg"));
		$this->register(new EnderPearl(new ItemIdentifier(ItemIds::ENDER_PEARL, 0), "Ender Pearl"));
		$this->register(new ExperienceBottle(new ItemIdentifier(ItemIds::EXPERIENCE_BOTTLE, 0), "Bottle o' Enchanting"));
		$this->register(new Fertilizer(new ItemIdentifier(ItemIds::DYE, 15), "Bone Meal"));
		$this->register(new FishingRod(new ItemIdentifier(ItemIds::FISHING_ROD, 0), "Fishing Rod"));
		$this->register(new FlintSteel(new ItemIdentifier(ItemIds::FLINT_STEEL, 0), "Flint and Steel"));
		$this->register(new GlassBottle(new ItemIdentifier(ItemIds::GLASS_BOTTLE, 0), "Glass Bottle"));
		$this->register(new GoldenApple(new ItemIdentifier(ItemIds::GOLDEN_APPLE, 0), "Golden Apple"));
		$this->register(new GoldenAppleEnchanted(new ItemIdentifier(ItemIds::ENCHANTED_GOLDEN_APPLE, 0), "Enchanted Golden Apple"));
		$this->register(new GoldenCarrot(new ItemIdentifier(ItemIds::GOLDEN_CARROT, 0), "Golden Carrot"));
		$this->register(new Item(new ItemIdentifier(ItemIds::BLAZE_POWDER, 0), "Blaze Powder"));
		$this->register(new Item(new ItemIdentifier(ItemIds::BLEACH, 0), "Bleach")); //EDU
		$this->register(new Item(new ItemIdentifier(ItemIds::BONE, 0), "Bone"));
		$this->register(new Item(new ItemIdentifier(ItemIds::BRICK, 0), "Brick"));
		$this->register(new Item(new ItemIdentifier(ItemIds::CHORUS_FRUIT_POPPED, 0), "Popped Chorus Fruit"));
		$this->register(new Item(new ItemIdentifier(ItemIds::CLAY_BALL, 0), "Clay"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 0), "Salt"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 1), "Sodium Oxide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 2), "Sodium Hydroxide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 3), "Magnesium Nitrate"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 4), "Iron Sulphide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 5), "Lithium Hydride"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 6), "Sodium Hydride"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 7), "Calcium Bromide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 8), "Magnesium Oxide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 9), "Sodium Acetate"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 10), "Luminol"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 11), "Charcoal")); //??? maybe bug
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 12), "Sugar")); //??? maybe bug
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 13), "Aluminium Oxide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 14), "Boron Trioxide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 15), "Soap"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 16), "Polyethylene"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 17), "Rubbish"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 18), "Magnesium Salts"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 19), "Sulphate"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 20), "Barium Sulphate"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 21), "Potassium Chloride"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 22), "Mercuric Chloride"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 23), "Cerium Chloride"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 24), "Tungsten Chloride"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 25), "Calcium Chloride"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 26), "Water")); //???
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 27), "Glue"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 28), "Hypochlorite"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 29), "Crude Oil"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 30), "Latex"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 31), "Potassium Iodide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 32), "Sodium Fluoride"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 33), "Benzene"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 34), "Ink"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 35), "Hydrogen Peroxide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 36), "Ammonia"));
		$this->register(new Item(new ItemIdentifier(ItemIds::COMPOUND, 37), "Sodium Hypochlorite"));
		$this->register(new Item(new ItemIdentifier(ItemIds::DIAMOND, 0), "Diamond"));
		$this->register(new Item(new ItemIdentifier(ItemIds::DRAGON_BREATH, 0), "Dragon's Breath"));
		$this->register(new Item(new ItemIdentifier(ItemIds::DYE, 0), "Ink Sac"));
		$this->register(new Item(new ItemIdentifier(ItemIds::DYE, 4), "Lapis Lazuli"));
		$this->register(new Item(new ItemIdentifier(ItemIds::EMERALD, 0), "Emerald"));
		$this->register(new Item(new ItemIdentifier(ItemIds::FEATHER, 0), "Feather"));
		$this->register(new Item(new ItemIdentifier(ItemIds::FERMENTED_SPIDER_EYE, 0), "Fermented Spider Eye"));
		$this->register(new Item(new ItemIdentifier(ItemIds::FLINT, 0), "Flint"));
		$this->register(new Item(new ItemIdentifier(ItemIds::GHAST_TEAR, 0), "Ghast Tear"));
		$this->register(new Item(new ItemIdentifier(ItemIds::GLISTERING_MELON, 0), "Glistering Melon"));
		$this->register(new Item(new ItemIdentifier(ItemIds::GLOWSTONE_DUST, 0), "Glowstone Dust"));
		$this->register(new Item(new ItemIdentifier(ItemIds::GOLD_INGOT, 0), "Gold Ingot"));
		$this->register(new Item(new ItemIdentifier(ItemIds::GOLD_NUGGET, 0), "Gold Nugget"));
		$this->register(new Item(new ItemIdentifier(ItemIds::GUNPOWDER, 0), "Gunpowder"));
		$this->register(new Item(new ItemIdentifier(ItemIds::HEART_OF_THE_SEA, 0), "Heart of the Sea"));
		$this->register(new Item(new ItemIdentifier(ItemIds::IRON_INGOT, 0), "Iron Ingot"));
		$this->register(new Item(new ItemIdentifier(ItemIds::IRON_NUGGET, 0), "Iron Nugget"));
		$this->register(new Item(new ItemIdentifier(ItemIds::LEATHER, 0), "Leather"));
		$this->register(new Item(new ItemIdentifier(ItemIds::MAGMA_CREAM, 0), "Magma Cream"));
		$this->register(new Item(new ItemIdentifier(ItemIds::NAUTILUS_SHELL, 0), "Nautilus Shell"));
		$this->register(new Item(new ItemIdentifier(ItemIds::NETHER_BRICK, 0), "Nether Brick"));
		$this->register(new Item(new ItemIdentifier(ItemIds::NETHER_QUARTZ, 0), "Nether Quartz"));
		$this->register(new Item(new ItemIdentifier(ItemIds::NETHER_STAR, 0), "Nether Star"));
		$this->register(new Item(new ItemIdentifier(ItemIds::PAPER, 0), "Paper"));
		$this->register(new Item(new ItemIdentifier(ItemIds::PRISMARINE_CRYSTALS, 0), "Prismarine Crystals"));
		$this->register(new Item(new ItemIdentifier(ItemIds::PRISMARINE_SHARD, 0), "Prismarine Shard"));
		$this->register(new Item(new ItemIdentifier(ItemIds::RABBIT_FOOT, 0), "Rabbit's Foot"));
		$this->register(new Item(new ItemIdentifier(ItemIds::RABBIT_HIDE, 0), "Rabbit Hide"));
		$this->register(new Item(new ItemIdentifier(ItemIds::SHULKER_SHELL, 0), "Shulker Shell"));
		$this->register(new Item(new ItemIdentifier(ItemIds::SLIME_BALL, 0), "Slimeball"));
		$this->register(new Item(new ItemIdentifier(ItemIds::SUGAR, 0), "Sugar"));
		$this->register(new Item(new ItemIdentifier(ItemIds::TURTLE_SHELL_PIECE, 0), "Scute"));
		$this->register(new Item(new ItemIdentifier(ItemIds::WHEAT, 0), "Wheat"));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::ACACIA_DOOR, 0), VanillaBlocks::ACACIA_DOOR()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::BIRCH_DOOR, 0), VanillaBlocks::BIRCH_DOOR()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::BREWING_STAND, 0), VanillaBlocks::BREWING_STAND()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::CAKE, 0), VanillaBlocks::CAKE()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::COMPARATOR, 0), VanillaBlocks::REDSTONE_COMPARATOR()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::DARK_OAK_DOOR, 0), VanillaBlocks::DARK_OAK_DOOR()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::FLOWER_POT, 0), VanillaBlocks::FLOWER_POT()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::HOPPER, 0), VanillaBlocks::HOPPER()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::IRON_DOOR, 0), VanillaBlocks::IRON_DOOR()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::ITEM_FRAME, 0), VanillaBlocks::ITEM_FRAME()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::JUNGLE_DOOR, 0), VanillaBlocks::JUNGLE_DOOR()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::NETHER_WART, 0), VanillaBlocks::NETHER_WART()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::OAK_DOOR, 0), VanillaBlocks::OAK_DOOR()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::REPEATER, 0), VanillaBlocks::REDSTONE_REPEATER()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::SPRUCE_DOOR, 0), VanillaBlocks::SPRUCE_DOOR()));
		$this->register(new ItemBlock(new ItemIdentifier(ItemIds::SUGARCANE, 0), VanillaBlocks::SUGARCANE()));

		//the meta values for buckets are intentionally hardcoded because block IDs will change in the future
		$waterBucket = new LiquidBucket(new ItemIdentifier(ItemIds::BUCKET, 8), "Water Bucket", VanillaBlocks::WATER());
		$this->register($waterBucket);
		$this->remap(new ItemIdentifier(ItemIds::BUCKET, 9), $waterBucket);
		$lavaBucket = new LiquidBucket(new ItemIdentifier(ItemIds::BUCKET, 10), "Lava Bucket", VanillaBlocks::LAVA());
		$this->register($lavaBucket);
		$this->remap(new ItemIdentifier(ItemIds::BUCKET, 11), $lavaBucket);
		$this->register(new Melon(new ItemIdentifier(ItemIds::MELON, 0), "Melon"));
		$this->register(new MelonSeeds(new ItemIdentifier(ItemIds::MELON_SEEDS, 0), "Melon Seeds"));
		$this->register(new MilkBucket(new ItemIdentifier(ItemIds::BUCKET, 1), "Milk Bucket"));
		$this->register(new Minecart(new ItemIdentifier(ItemIds::MINECART, 0), "Minecart"));
		$this->register(new MushroomStew(new ItemIdentifier(ItemIds::MUSHROOM_STEW, 0), "Mushroom Stew"));
		$this->register(new PaintingItem(new ItemIdentifier(ItemIds::PAINTING, 0), "Painting"));
		$this->register(new PoisonousPotato(new ItemIdentifier(ItemIds::POISONOUS_POTATO, 0), "Poisonous Potato"));
		$this->register(new Potato(new ItemIdentifier(ItemIds::POTATO, 0), "Potato"));
		$this->register(new Pufferfish(new ItemIdentifier(ItemIds::PUFFERFISH, 0), "Pufferfish"));
		$this->register(new PumpkinPie(new ItemIdentifier(ItemIds::PUMPKIN_PIE, 0), "Pumpkin Pie"));
		$this->register(new PumpkinSeeds(new ItemIdentifier(ItemIds::PUMPKIN_SEEDS, 0), "Pumpkin Seeds"));
		$this->register(new RabbitStew(new ItemIdentifier(ItemIds::RABBIT_STEW, 0), "Rabbit Stew"));
		$this->register(new RawBeef(new ItemIdentifier(ItemIds::RAW_BEEF, 0), "Raw Beef"));
		$this->register(new RawChicken(new ItemIdentifier(ItemIds::RAW_CHICKEN, 0), "Raw Chicken"));
		$this->register(new RawFish(new ItemIdentifier(ItemIds::RAW_FISH, 0), "Raw Fish"));
		$this->register(new RawMutton(new ItemIdentifier(ItemIds::RAW_MUTTON, 0), "Raw Mutton"));
		$this->register(new RawPorkchop(new ItemIdentifier(ItemIds::RAW_PORKCHOP, 0), "Raw Porkchop"));
		$this->register(new RawRabbit(new ItemIdentifier(ItemIds::RAW_RABBIT, 0), "Raw Rabbit"));
		$this->register(new RawSalmon(new ItemIdentifier(ItemIds::RAW_SALMON, 0), "Raw Salmon"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_13, 0), RecordType::DISK_13(), "Record 13"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_CAT, 0), RecordType::DISK_CAT(), "Record Cat"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_BLOCKS, 0), RecordType::DISK_BLOCKS(), "Record Blocks"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_CHIRP, 0), RecordType::DISK_CHIRP(), "Record Chirp"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_FAR, 0), RecordType::DISK_FAR(), "Record Far"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_MALL, 0), RecordType::DISK_MALL(), "Record Mall"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_MELLOHI, 0), RecordType::DISK_MELLOHI(), "Record Mellohi"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_STAL, 0), RecordType::DISK_STAL(), "Record Stal"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_STRAD, 0), RecordType::DISK_STRAD(), "Record Strad"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_WARD, 0), RecordType::DISK_WARD(), "Record Ward"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_11, 0), RecordType::DISK_11(), "Record 11"));
		$this->register(new Record(new ItemIdentifier(ItemIds::RECORD_WAIT, 0), RecordType::DISK_WAIT(), "Record Wait"));
		$this->register(new Redstone(new ItemIdentifier(ItemIds::REDSTONE, 0), "Redstone"));
		$this->register(new RottenFlesh(new ItemIdentifier(ItemIds::ROTTEN_FLESH, 0), "Rotten Flesh"));
		$this->register(new Shears(new ItemIdentifier(ItemIds::SHEARS, 0), "Shears"));
		$this->register(new Sign(new ItemIdentifier(ItemIds::SIGN, 0), VanillaBlocks::OAK_SIGN(), VanillaBlocks::OAK_WALL_SIGN()));
		$this->register(new Sign(new ItemIdentifier(ItemIds::SPRUCE_SIGN, 0), VanillaBlocks::SPRUCE_SIGN(), VanillaBlocks::SPRUCE_WALL_SIGN()));
		$this->register(new Sign(new ItemIdentifier(ItemIds::BIRCH_SIGN, 0), VanillaBlocks::BIRCH_SIGN(), VanillaBlocks::BIRCH_WALL_SIGN()));
		$this->register(new Sign(new ItemIdentifier(ItemIds::JUNGLE_SIGN, 0), VanillaBlocks::JUNGLE_SIGN(), VanillaBlocks::JUNGLE_WALL_SIGN()));
		$this->register(new Sign(new ItemIdentifier(ItemIds::ACACIA_SIGN, 0), VanillaBlocks::ACACIA_SIGN(), VanillaBlocks::ACACIA_WALL_SIGN()));
		$this->register(new Sign(new ItemIdentifier(ItemIds::DARKOAK_SIGN, 0), VanillaBlocks::DARK_OAK_SIGN(), VanillaBlocks::DARK_OAK_WALL_SIGN()));
		$this->register(new Snowball(new ItemIdentifier(ItemIds::SNOWBALL, 0), "Snowball"));
		$this->register(new SpiderEye(new ItemIdentifier(ItemIds::SPIDER_EYE, 0), "Spider Eye"));
		$this->register(new Steak(new ItemIdentifier(ItemIds::STEAK, 0), "Steak"));
		$this->register(new Stick(new ItemIdentifier(ItemIds::STICK, 0), "Stick"));
		$this->register(new StringItem(new ItemIdentifier(ItemIds::STRING, 0), "String"));
		$this->register(new Totem(new ItemIdentifier(ItemIds::TOTEM, 0), "Totem of Undying"));
		$this->register(new WheatSeeds(new ItemIdentifier(ItemIds::WHEAT_SEEDS, 0), "Wheat Seeds"));
		$this->register(new WritableBook(new ItemIdentifier(ItemIds::WRITABLE_BOOK, 0), "Book & Quill"));
		$this->register(new WrittenBook(new ItemIdentifier(ItemIds::WRITTEN_BOOK, 0), "Written Book"));

		foreach(SkullType::getAll() as $skullType){
			$this->register(new Skull(new ItemIdentifier(ItemIds::SKULL, $skullType->getMagicNumber()), $skullType->getDisplayName(), $skullType));
		}

		$dyeMap = [
			DyeColor::BLACK()->id() => 16,
			DyeColor::BROWN()->id() => 17,
			DyeColor::BLUE()->id() => 18,
			DyeColor::WHITE()->id() => 19
		];
		$colorIdMap = DyeColorIdMap::getInstance();
		foreach(DyeColor::getAll() as $color){
			//TODO: use colour object directly
			//TODO: add interface to dye-colour objects
			$this->register(new Dye(new ItemIdentifier(ItemIds::DYE, $dyeMap[$color->id()] ?? $colorIdMap->toInvertedId($color)), $color->getDisplayName() . " Dye", $color));
			$this->register(new Bed(new ItemIdentifier(ItemIds::BED, $colorIdMap->toId($color)), $color->getDisplayName() . " Bed", $color));
			$this->register((new Banner(
				new ItemIdentifier(ItemIds::BANNER, 0),
				VanillaBlocks::BANNER(),
				VanillaBlocks::WALL_BANNER()
			))->setColor($color));
		}

		foreach(Potion::ALL as $type){
			$this->register(new Potion(new ItemIdentifier(ItemIds::POTION, $type), "Potion", $type));
			$this->register(new SplashPotion(new ItemIdentifier(ItemIds::SPLASH_POTION, $type), "Splash Potion", $type));
		}

		foreach(TreeType::getAll() as $type){
			$this->register(new Boat(new ItemIdentifier(ItemIds::BOAT, $type->getMagicNumber()), $type->getDisplayName() . " Boat", $type));
		}

		//region --- auto-generated TODOs ---
		//TODO: minecraft:armor_stand
		//TODO: minecraft:balloon
		//TODO: minecraft:banner_pattern
		//TODO: minecraft:campfire
		//TODO: minecraft:carrotOnAStick
		//TODO: minecraft:chest_minecart
		//TODO: minecraft:command_block_minecart
		//TODO: minecraft:crossbow
		//TODO: minecraft:elytra
		//TODO: minecraft:emptyMap
		//TODO: minecraft:enchanted_book
		//TODO: minecraft:end_crystal
		//TODO: minecraft:ender_eye
		//TODO: minecraft:fireball
		//TODO: minecraft:fireworks
		//TODO: minecraft:fireworksCharge
		//TODO: minecraft:glow_stick
		//TODO: minecraft:hopper_minecart
		//TODO: minecraft:horsearmordiamond
		//TODO: minecraft:horsearmorgold
		//TODO: minecraft:horsearmoriron
		//TODO: minecraft:horsearmorleather
		//TODO: minecraft:ice_bomb
		//TODO: minecraft:kelp
		//TODO: minecraft:lead
		//TODO: minecraft:lingering_potion
		//TODO: minecraft:map
		//TODO: minecraft:medicine
		//TODO: minecraft:name_tag
		//TODO: minecraft:phantom_membrane
		//TODO: minecraft:rapid_fertilizer
		//TODO: minecraft:record_pigstep
		//TODO: minecraft:saddle
		//TODO: minecraft:shield
		//TODO: minecraft:sparkler
		//TODO: minecraft:spawn_egg
		//TODO: minecraft:sweet_berries
		//TODO: minecraft:tnt_minecart
		//TODO: minecraft:trident
		//TODO: minecraft:turtle_helmet
		//endregion
	}

	private function registerSpawnEggs() : void{
		//TODO: the meta values should probably be hardcoded; they won't change, but the EntityLegacyIds might
		$this->register(new class(new ItemIdentifier(ItemIds::SPAWN_EGG, EntityLegacyIds::ZOMBIE), "Zombie Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Zombie(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		$this->register(new class(new ItemIdentifier(ItemIds::SPAWN_EGG, EntityLegacyIds::SQUID), "Squid Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Squid(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		$this->register(new class(new ItemIdentifier(ItemIds::SPAWN_EGG, EntityLegacyIds::VILLAGER), "Villager Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Villager(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
	}

	private function registerTierToolItems() : void{
		$this->register(new Axe(new ItemIdentifier(ItemIds::DIAMOND_AXE, 0), "Diamond Axe", ToolTier::DIAMOND()));
		$this->register(new Axe(new ItemIdentifier(ItemIds::GOLDEN_AXE, 0), "Golden Axe", ToolTier::GOLD()));
		$this->register(new Axe(new ItemIdentifier(ItemIds::IRON_AXE, 0), "Iron Axe", ToolTier::IRON()));
		$this->register(new Axe(new ItemIdentifier(ItemIds::STONE_AXE, 0), "Stone Axe", ToolTier::STONE()));
		$this->register(new Axe(new ItemIdentifier(ItemIds::WOODEN_AXE, 0), "Wooden Axe", ToolTier::WOOD()));
		$this->register(new Hoe(new ItemIdentifier(ItemIds::DIAMOND_HOE, 0), "Diamond Hoe", ToolTier::DIAMOND()));
		$this->register(new Hoe(new ItemIdentifier(ItemIds::GOLDEN_HOE, 0), "Golden Hoe", ToolTier::GOLD()));
		$this->register(new Hoe(new ItemIdentifier(ItemIds::IRON_HOE, 0), "Iron Hoe", ToolTier::IRON()));
		$this->register(new Hoe(new ItemIdentifier(ItemIds::STONE_HOE, 0), "Stone Hoe", ToolTier::STONE()));
		$this->register(new Hoe(new ItemIdentifier(ItemIds::WOODEN_HOE, 0), "Wooden Hoe", ToolTier::WOOD()));
		$this->register(new Pickaxe(new ItemIdentifier(ItemIds::DIAMOND_PICKAXE, 0), "Diamond Pickaxe", ToolTier::DIAMOND()));
		$this->register(new Pickaxe(new ItemIdentifier(ItemIds::GOLDEN_PICKAXE, 0), "Golden Pickaxe", ToolTier::GOLD()));
		$this->register(new Pickaxe(new ItemIdentifier(ItemIds::IRON_PICKAXE, 0), "Iron Pickaxe", ToolTier::IRON()));
		$this->register(new Pickaxe(new ItemIdentifier(ItemIds::STONE_PICKAXE, 0), "Stone Pickaxe", ToolTier::STONE()));
		$this->register(new Pickaxe(new ItemIdentifier(ItemIds::WOODEN_PICKAXE, 0), "Wooden Pickaxe", ToolTier::WOOD()));
		$this->register(new Shovel(new ItemIdentifier(ItemIds::DIAMOND_SHOVEL, 0), "Diamond Shovel", ToolTier::DIAMOND()));
		$this->register(new Shovel(new ItemIdentifier(ItemIds::GOLDEN_SHOVEL, 0), "Golden Shovel", ToolTier::GOLD()));
		$this->register(new Shovel(new ItemIdentifier(ItemIds::IRON_SHOVEL, 0), "Iron Shovel", ToolTier::IRON()));
		$this->register(new Shovel(new ItemIdentifier(ItemIds::STONE_SHOVEL, 0), "Stone Shovel", ToolTier::STONE()));
		$this->register(new Shovel(new ItemIdentifier(ItemIds::WOODEN_SHOVEL, 0), "Wooden Shovel", ToolTier::WOOD()));
		$this->register(new Sword(new ItemIdentifier(ItemIds::DIAMOND_SWORD, 0), "Diamond Sword", ToolTier::DIAMOND()));
		$this->register(new Sword(new ItemIdentifier(ItemIds::GOLDEN_SWORD, 0), "Golden Sword", ToolTier::GOLD()));
		$this->register(new Sword(new ItemIdentifier(ItemIds::IRON_SWORD, 0), "Iron Sword", ToolTier::IRON()));
		$this->register(new Sword(new ItemIdentifier(ItemIds::STONE_SWORD, 0), "Stone Sword", ToolTier::STONE()));
		$this->register(new Sword(new ItemIdentifier(ItemIds::WOODEN_SWORD, 0), "Wooden Sword", ToolTier::WOOD()));
	}

	private function registerArmorItems() : void{
		$this->register(new Armor(new ItemIdentifier(ItemIds::CHAIN_BOOTS, 0), "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::DIAMOND_BOOTS, 0), "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::GOLDEN_BOOTS, 0), "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::IRON_BOOTS, 0), "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::LEATHER_BOOTS, 0), "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::CHAIN_CHESTPLATE, 0), "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::DIAMOND_CHESTPLATE, 0), "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::GOLDEN_CHESTPLATE, 0), "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::IRON_CHESTPLATE, 0), "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::LEATHER_CHESTPLATE, 0), "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::CHAIN_HELMET, 0), "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::DIAMOND_HELMET, 0), "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::GOLDEN_HELMET, 0), "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::IRON_HELMET, 0), "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::LEATHER_HELMET, 0), "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::CHAIN_LEGGINGS, 0), "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::DIAMOND_LEGGINGS, 0), "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::GOLDEN_LEGGINGS, 0), "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::IRON_LEGGINGS, 0), "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new ItemIdentifier(ItemIds::LEATHER_LEGGINGS, 0), "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS)));
	}

	/**
	 * Maps an item type to its corresponding ID. This is necessary to ensure that the item is correctly loaded when
	 * reading data from disk storage.
	 *
	 * NOTE: If you are registering a new item type, you will need to add it to the creative inventory yourself - it
	 * will not automatically appear there.
	 *
	 * @throws \RuntimeException if something attempted to override an already-registered item without specifying the
	 * $override parameter.
	 */
	public function register(Item $item, bool $override = false) : void{
		$id = $item->getId();
		$variant = $item->getMeta();

		if(!$override and $this->isRegistered($id, $variant)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		$this->list[self::getListOffset($id, $variant)] = clone $item;
	}

	public function remap(ItemIdentifier $identifier, Item $item, bool $override = false) : void{
		if(!$override && $this->isRegistered($identifier->getId(), $identifier->getMeta())){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		$this->list[self::getListOffset($identifier->getId(), $identifier->getMeta())] = clone $item;
	}

	/**
	 * @deprecated This method should ONLY be used for deserializing data, e.g. from a config or database. For all other
	 * purposes, use VanillaItems.
	 * @see VanillaItems
	 *
	 * Deserializes an item from the provided legacy ID, legacy meta, count and NBT.
	 *
	 * @throws \InvalidArgumentException
	 */
	public function get(int $id, int $meta = 0, int $count = 1, ?CompoundTag $tags = null) : Item{
		/** @var Item|null $item */
		$item = null;
		if($meta !== -1){
			if(isset($this->list[$offset = self::getListOffset($id, $meta)])){
				$item = clone $this->list[$offset];
			}elseif(isset($this->list[$zero = self::getListOffset($id, 0)]) and $this->list[$zero] instanceof Durable){
				if($meta <= $this->list[$zero]->getMaxDurability()){
					$item = clone $this->list[$zero];
					$item->setDamage($meta);
				}else{
					$item = new Item(new ItemIdentifier($id, $meta));
				}
			}elseif($id < 256){ //intentionally includes negatives, for extended block IDs
				//TODO: do not assume that item IDs and block IDs are the same or related
				$item = new ItemBlock(new ItemIdentifier($id, $meta), BlockFactory::getInstance()->get($id < 0 ? 255 - $id : $id, $meta & 0xf));
			}
		}

		if($item === null){
			//negative damage values will fallthru to here, to avoid crazy shit with crafting wildcard hacks
			$item = new Item(new ItemIdentifier($id, $meta));
		}

		$item->setCount($count);
		if($tags !== null){
			$item->setNamedTag($tags);
		}
		return $item;
	}

	public static function air() : Item{
		return self::getInstance()->get(ItemIds::AIR, 0, 0);
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 */
	public function isRegistered(int $id, int $variant = 0) : bool{
		if($id < 256){
			return BlockFactory::getInstance()->isRegistered($id);
		}

		return isset($this->list[self::getListOffset($id, $variant)]);
	}

	private static function getListOffset(int $id, int $variant) : int{
		if($id < -0x8000 or $id > 0x7fff){
			throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		return (($id & 0xffff) << 16) | ($variant & 0xffff);
	}

	/**
	 * @return Item[]
	 */
	public function getAllRegistered() : array{
		return $this->list;
	}
}
