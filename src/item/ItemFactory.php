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

use pocketmine\block\BlockFactory;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\RecordType;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\CompoundTypeIds;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ItemIdentifier as IID;
use pocketmine\item\ItemIds as LegacyIds;
use pocketmine\item\ItemTypeIds as Ids;
use pocketmine\math\Vector3;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\World;
use function min;

/**
 * Manages deserializing item types from their legacy ID/metadata.
 * This is primarily needed for loading inventories saved in the world (or playerdata storage).
 */
class ItemFactory{
	use SingletonTrait;

	/** @var Item[] */
	private array $list = [];

	public function __construct(){
		$this->registerArmorItems();
		$this->registerSpawnEggs();
		$this->registerTierToolItems();

		$this->register(new Apple(new IID(Ids::APPLE, LegacyIds::APPLE, 0), "Apple"));
		$this->register(new Arrow(new IID(Ids::ARROW, LegacyIds::ARROW, 0), "Arrow"));

		$this->register(new BakedPotato(new IID(Ids::BAKED_POTATO, LegacyIds::BAKED_POTATO, 0), "Baked Potato"));
		$this->register(new Bamboo(new IID(Ids::BAMBOO, LegacyIds::BAMBOO, 0), "Bamboo"), true);
		$this->register(new Beetroot(new IID(Ids::BEETROOT, LegacyIds::BEETROOT, 0), "Beetroot"));
		$this->register(new BeetrootSeeds(new IID(Ids::BEETROOT_SEEDS, LegacyIds::BEETROOT_SEEDS, 0), "Beetroot Seeds"));
		$this->register(new BeetrootSoup(new IID(Ids::BEETROOT_SOUP, LegacyIds::BEETROOT_SOUP, 0), "Beetroot Soup"));
		$this->register(new BlazeRod(new IID(Ids::BLAZE_ROD, LegacyIds::BLAZE_ROD, 0), "Blaze Rod"));
		$this->register(new Book(new IID(Ids::BOOK, LegacyIds::BOOK, 0), "Book"));
		$this->register(new Bow(new IID(Ids::BOW, LegacyIds::BOW, 0), "Bow"));
		$this->register(new Bowl(new IID(Ids::BOWL, LegacyIds::BOWL, 0), "Bowl"));
		$this->register(new Bread(new IID(Ids::BREAD, LegacyIds::BREAD, 0), "Bread"));
		$this->register(new Bucket(new IID(Ids::BUCKET, LegacyIds::BUCKET, 0), "Bucket"));
		$this->register(new Carrot(new IID(Ids::CARROT, LegacyIds::CARROT, 0), "Carrot"));
		$this->register(new ChorusFruit(new IID(Ids::CHORUS_FRUIT, LegacyIds::CHORUS_FRUIT, 0), "Chorus Fruit"));
		$this->register(new Clock(new IID(Ids::CLOCK, LegacyIds::CLOCK, 0), "Clock"));
		$this->register(new Clownfish(new IID(Ids::CLOWNFISH, LegacyIds::CLOWNFISH, 0), "Clownfish"));
		$this->register(new Coal(new IID(Ids::COAL, LegacyIds::COAL, 0), "Coal"));

		$identifier = new ItemIdentifierFlattened(Ids::CORAL_FAN, LegacyIds::CORAL_FAN, 0, [LegacyIds::CORAL_FAN_DEAD]);
		foreach(CoralType::getAll() as $coralType){
			$this->register((new CoralFan($identifier))->setCoralType($coralType)->setDead(false), true);
			$this->register((new CoralFan($identifier))->setCoralType($coralType)->setDead(true), true);
		}

		$this->register(new Coal(new IID(Ids::CHARCOAL, LegacyIds::COAL, 1), "Charcoal"));
		$this->register(new CocoaBeans(new IID(Ids::COCOA_BEANS, LegacyIds::DYE, 3), "Cocoa Beans"));
		$this->register(new Compass(new IID(Ids::COMPASS, LegacyIds::COMPASS, 0), "Compass"));
		$this->register(new CookedChicken(new IID(Ids::COOKED_CHICKEN, LegacyIds::COOKED_CHICKEN, 0), "Cooked Chicken"));
		$this->register(new CookedFish(new IID(Ids::COOKED_FISH, LegacyIds::COOKED_FISH, 0), "Cooked Fish"));
		$this->register(new CookedMutton(new IID(Ids::COOKED_MUTTON, LegacyIds::COOKED_MUTTON, 0), "Cooked Mutton"));
		$this->register(new CookedPorkchop(new IID(Ids::COOKED_PORKCHOP, LegacyIds::COOKED_PORKCHOP, 0), "Cooked Porkchop"));
		$this->register(new CookedRabbit(new IID(Ids::COOKED_RABBIT, LegacyIds::COOKED_RABBIT, 0), "Cooked Rabbit"));
		$this->register(new CookedSalmon(new IID(Ids::COOKED_SALMON, LegacyIds::COOKED_SALMON, 0), "Cooked Salmon"));
		$this->register(new Cookie(new IID(Ids::COOKIE, LegacyIds::COOKIE, 0), "Cookie"));
		$this->register(new DriedKelp(new IID(Ids::DRIED_KELP, LegacyIds::DRIED_KELP, 0), "Dried Kelp"));
		$this->register(new Egg(new IID(Ids::EGG, LegacyIds::EGG, 0), "Egg"));
		$this->register(new EnderPearl(new IID(Ids::ENDER_PEARL, LegacyIds::ENDER_PEARL, 0), "Ender Pearl"));
		$this->register(new ExperienceBottle(new IID(Ids::EXPERIENCE_BOTTLE, LegacyIds::EXPERIENCE_BOTTLE, 0), "Bottle o' Enchanting"));
		$this->register(new Fertilizer(new IID(Ids::BONE_MEAL, LegacyIds::DYE, 15), "Bone Meal"));
		$this->register(new FishingRod(new IID(Ids::FISHING_ROD, LegacyIds::FISHING_ROD, 0), "Fishing Rod"));
		$this->register(new FlintSteel(new IID(Ids::FLINT_AND_STEEL, LegacyIds::FLINT_STEEL, 0), "Flint and Steel"));
		$this->register(new GlassBottle(new IID(Ids::GLASS_BOTTLE, LegacyIds::GLASS_BOTTLE, 0), "Glass Bottle"));
		$this->register(new GoldenApple(new IID(Ids::GOLDEN_APPLE, LegacyIds::GOLDEN_APPLE, 0), "Golden Apple"));
		$this->register(new GoldenAppleEnchanted(new IID(Ids::ENCHANTED_GOLDEN_APPLE, LegacyIds::ENCHANTED_GOLDEN_APPLE, 0), "Enchanted Golden Apple"));
		$this->register(new GoldenCarrot(new IID(Ids::GOLDEN_CARROT, LegacyIds::GOLDEN_CARROT, 0), "Golden Carrot"));
		$this->register(new Item(new IID(Ids::BLAZE_POWDER, LegacyIds::BLAZE_POWDER, 0), "Blaze Powder"));
		$this->register(new Item(new IID(Ids::BLEACH, LegacyIds::BLEACH, 0), "Bleach")); //EDU
		$this->register(new Item(new IID(Ids::BONE, LegacyIds::BONE, 0), "Bone"));
		$this->register(new Item(new IID(Ids::BRICK, LegacyIds::BRICK, 0), "Brick"));
		$this->register(new Item(new IID(Ids::POPPED_CHORUS_FRUIT, LegacyIds::CHORUS_FRUIT_POPPED, 0), "Popped Chorus Fruit"));
		$this->register(new Item(new IID(Ids::CLAY, LegacyIds::CLAY_BALL, 0), "Clay"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SALT, LegacyIds::COMPOUND, CompoundTypeIds::SALT), "Salt"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_OXIDE, LegacyIds::COMPOUND, CompoundTypeIds::SODIUM_OXIDE), "Sodium Oxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_HYDROXIDE, LegacyIds::COMPOUND, CompoundTypeIds::SODIUM_HYDROXIDE), "Sodium Hydroxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_MAGNESIUM_NITRATE, LegacyIds::COMPOUND, CompoundTypeIds::MAGNESIUM_NITRATE), "Magnesium Nitrate"));
		$this->register(new Item(new IID(Ids::CHEMICAL_IRON_SULPHIDE, LegacyIds::COMPOUND, CompoundTypeIds::IRON_SULPHIDE), "Iron Sulphide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_LITHIUM_HYDRIDE, LegacyIds::COMPOUND, CompoundTypeIds::LITHIUM_HYDRIDE), "Lithium Hydride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_HYDRIDE, LegacyIds::COMPOUND, CompoundTypeIds::SODIUM_HYDRIDE), "Sodium Hydride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CALCIUM_BROMIDE, LegacyIds::COMPOUND, CompoundTypeIds::CALCIUM_BROMIDE), "Calcium Bromide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_MAGNESIUM_OXIDE, LegacyIds::COMPOUND, CompoundTypeIds::MAGNESIUM_OXIDE), "Magnesium Oxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_ACETATE, LegacyIds::COMPOUND, CompoundTypeIds::SODIUM_ACETATE), "Sodium Acetate"));
		$this->register(new Item(new IID(Ids::CHEMICAL_LUMINOL, LegacyIds::COMPOUND, CompoundTypeIds::LUMINOL), "Luminol"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CHARCOAL, LegacyIds::COMPOUND, CompoundTypeIds::CHARCOAL), "Charcoal")); //??? maybe bug
		$this->register(new Item(new IID(Ids::CHEMICAL_SUGAR, LegacyIds::COMPOUND, CompoundTypeIds::SUGAR), "Sugar")); //??? maybe bug
		$this->register(new Item(new IID(Ids::CHEMICAL_ALUMINIUM_OXIDE, LegacyIds::COMPOUND, CompoundTypeIds::ALUMINIUM_OXIDE), "Aluminium Oxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_BORON_TRIOXIDE, LegacyIds::COMPOUND, CompoundTypeIds::BORON_TRIOXIDE), "Boron Trioxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SOAP, LegacyIds::COMPOUND, CompoundTypeIds::SOAP), "Soap"));
		$this->register(new Item(new IID(Ids::CHEMICAL_POLYETHYLENE, LegacyIds::COMPOUND, CompoundTypeIds::POLYETHYLENE), "Polyethylene"));
		$this->register(new Item(new IID(Ids::CHEMICAL_RUBBISH, LegacyIds::COMPOUND, CompoundTypeIds::RUBBISH), "Rubbish"));
		$this->register(new Item(new IID(Ids::CHEMICAL_MAGNESIUM_SALTS, LegacyIds::COMPOUND, CompoundTypeIds::MAGNESIUM_SALTS), "Magnesium Salts"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SULPHATE, LegacyIds::COMPOUND, CompoundTypeIds::SULPHATE), "Sulphate"));
		$this->register(new Item(new IID(Ids::CHEMICAL_BARIUM_SULPHATE, LegacyIds::COMPOUND, CompoundTypeIds::BARIUM_SULPHATE), "Barium Sulphate"));
		$this->register(new Item(new IID(Ids::CHEMICAL_POTASSIUM_CHLORIDE, LegacyIds::COMPOUND, CompoundTypeIds::POTASSIUM_CHLORIDE), "Potassium Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_MERCURIC_CHLORIDE, LegacyIds::COMPOUND, CompoundTypeIds::MERCURIC_CHLORIDE), "Mercuric Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CERIUM_CHLORIDE, LegacyIds::COMPOUND, CompoundTypeIds::CERIUM_CHLORIDE), "Cerium Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_TUNGSTEN_CHLORIDE, LegacyIds::COMPOUND, CompoundTypeIds::TUNGSTEN_CHLORIDE), "Tungsten Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CALCIUM_CHLORIDE, LegacyIds::COMPOUND, CompoundTypeIds::CALCIUM_CHLORIDE), "Calcium Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_WATER, LegacyIds::COMPOUND, CompoundTypeIds::WATER), "Water")); //???
		$this->register(new Item(new IID(Ids::CHEMICAL_GLUE, LegacyIds::COMPOUND, CompoundTypeIds::GLUE), "Glue"));
		$this->register(new Item(new IID(Ids::CHEMICAL_HYPOCHLORITE, LegacyIds::COMPOUND, CompoundTypeIds::HYPOCHLORITE), "Hypochlorite"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CRUDE_OIL, LegacyIds::COMPOUND, CompoundTypeIds::CRUDE_OIL), "Crude Oil"));
		$this->register(new Item(new IID(Ids::CHEMICAL_LATEX, LegacyIds::COMPOUND, CompoundTypeIds::LATEX), "Latex"));
		$this->register(new Item(new IID(Ids::CHEMICAL_POTASSIUM_IODIDE, LegacyIds::COMPOUND, CompoundTypeIds::POTASSIUM_IODIDE), "Potassium Iodide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_FLUORIDE, LegacyIds::COMPOUND, CompoundTypeIds::SODIUM_FLUORIDE), "Sodium Fluoride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_BENZENE, LegacyIds::COMPOUND, CompoundTypeIds::BENZENE), "Benzene"));
		$this->register(new Item(new IID(Ids::CHEMICAL_INK, LegacyIds::COMPOUND, CompoundTypeIds::INK), "Ink"));
		$this->register(new Item(new IID(Ids::CHEMICAL_HYDROGEN_PEROXIDE, LegacyIds::COMPOUND, CompoundTypeIds::HYDROGEN_PEROXIDE), "Hydrogen Peroxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_AMMONIA, LegacyIds::COMPOUND, CompoundTypeIds::AMMONIA), "Ammonia"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_HYPOCHLORITE, LegacyIds::COMPOUND, CompoundTypeIds::SODIUM_HYPOCHLORITE), "Sodium Hypochlorite"));
		$this->register(new Item(new IID(Ids::DIAMOND, LegacyIds::DIAMOND, 0), "Diamond"));
		$this->register(new Item(new IID(Ids::DRAGON_BREATH, LegacyIds::DRAGON_BREATH, 0), "Dragon's Breath"));
		$this->register(new Item(new IID(Ids::INK_SAC, LegacyIds::DYE, 0), "Ink Sac"));
		$this->register(new Item(new IID(Ids::LAPIS_LAZULI, LegacyIds::DYE, 4), "Lapis Lazuli"));
		$this->register(new Item(new IID(Ids::EMERALD, LegacyIds::EMERALD, 0), "Emerald"));
		$this->register(new Item(new IID(Ids::FEATHER, LegacyIds::FEATHER, 0), "Feather"));
		$this->register(new Item(new IID(Ids::FERMENTED_SPIDER_EYE, LegacyIds::FERMENTED_SPIDER_EYE, 0), "Fermented Spider Eye"));
		$this->register(new Item(new IID(Ids::FLINT, LegacyIds::FLINT, 0), "Flint"));
		$this->register(new Item(new IID(Ids::GHAST_TEAR, LegacyIds::GHAST_TEAR, 0), "Ghast Tear"));
		$this->register(new Item(new IID(Ids::GLISTERING_MELON, LegacyIds::GLISTERING_MELON, 0), "Glistering Melon"));
		$this->register(new Item(new IID(Ids::GLOWSTONE_DUST, LegacyIds::GLOWSTONE_DUST, 0), "Glowstone Dust"));
		$this->register(new Item(new IID(Ids::GOLD_INGOT, LegacyIds::GOLD_INGOT, 0), "Gold Ingot"));
		$this->register(new Item(new IID(Ids::GOLD_NUGGET, LegacyIds::GOLD_NUGGET, 0), "Gold Nugget"));
		$this->register(new Item(new IID(Ids::GUNPOWDER, LegacyIds::GUNPOWDER, 0), "Gunpowder"));
		$this->register(new Item(new IID(Ids::HEART_OF_THE_SEA, LegacyIds::HEART_OF_THE_SEA, 0), "Heart of the Sea"));
		$this->register(new Item(new IID(Ids::IRON_INGOT, LegacyIds::IRON_INGOT, 0), "Iron Ingot"));
		$this->register(new Item(new IID(Ids::IRON_NUGGET, LegacyIds::IRON_NUGGET, 0), "Iron Nugget"));
		$this->register(new Item(new IID(Ids::LEATHER, LegacyIds::LEATHER, 0), "Leather"));
		$this->register(new Item(new IID(Ids::MAGMA_CREAM, LegacyIds::MAGMA_CREAM, 0), "Magma Cream"));
		$this->register(new Item(new IID(Ids::NAUTILUS_SHELL, LegacyIds::NAUTILUS_SHELL, 0), "Nautilus Shell"));
		$this->register(new Item(new IID(Ids::NETHER_BRICK, LegacyIds::NETHER_BRICK, 0), "Nether Brick"));
		$this->register(new Item(new IID(Ids::NETHER_QUARTZ, LegacyIds::NETHER_QUARTZ, 0), "Nether Quartz"));
		$this->register(new Item(new IID(Ids::NETHER_STAR, LegacyIds::NETHER_STAR, 0), "Nether Star"));
		$this->register(new Item(new IID(Ids::PAPER, LegacyIds::PAPER, 0), "Paper"));
		$this->register(new Item(new IID(Ids::PRISMARINE_CRYSTALS, LegacyIds::PRISMARINE_CRYSTALS, 0), "Prismarine Crystals"));
		$this->register(new Item(new IID(Ids::PRISMARINE_SHARD, LegacyIds::PRISMARINE_SHARD, 0), "Prismarine Shard"));
		$this->register(new Item(new IID(Ids::RABBIT_FOOT, LegacyIds::RABBIT_FOOT, 0), "Rabbit's Foot"));
		$this->register(new Item(new IID(Ids::RABBIT_HIDE, LegacyIds::RABBIT_HIDE, 0), "Rabbit Hide"));
		$this->register(new Item(new IID(Ids::SHULKER_SHELL, LegacyIds::SHULKER_SHELL, 0), "Shulker Shell"));
		$this->register(new Item(new IID(Ids::SLIMEBALL, LegacyIds::SLIME_BALL, 0), "Slimeball"));
		$this->register(new Item(new IID(Ids::SUGAR, LegacyIds::SUGAR, 0), "Sugar"));
		$this->register(new Item(new IID(Ids::SCUTE, LegacyIds::TURTLE_SHELL_PIECE, 0), "Scute"));
		$this->register(new Item(new IID(Ids::WHEAT, LegacyIds::WHEAT, 0), "Wheat"));

		//these blocks have special legacy item IDs, so they need to be registered explicitly
		$this->register(new ItemBlock(Blocks::ACACIA_DOOR()));
		$this->register(new ItemBlock(Blocks::BIRCH_DOOR()));
		$this->register(new ItemBlock(Blocks::BREWING_STAND()));
		$this->register(new ItemBlock(Blocks::CAKE()));
		$this->register(new ItemBlock(Blocks::REDSTONE_COMPARATOR()));
		$this->register(new ItemBlock(Blocks::DARK_OAK_DOOR()));
		$this->register(new ItemBlock(Blocks::FLOWER_POT()));
		$this->register(new ItemBlock(Blocks::HOPPER()));
		$this->register(new ItemBlock(Blocks::IRON_DOOR()));
		$this->register(new ItemBlock(Blocks::ITEM_FRAME()));
		$this->register(new ItemBlock(Blocks::JUNGLE_DOOR()));
		$this->register(new ItemBlock(Blocks::NETHER_WART()));
		$this->register(new ItemBlock(Blocks::OAK_DOOR()));
		$this->register(new ItemBlock(Blocks::REDSTONE_REPEATER()));
		$this->register(new ItemBlock(Blocks::SPRUCE_DOOR()));
		$this->register(new ItemBlock(Blocks::SUGARCANE()));

		//the meta values for buckets are intentionally hardcoded because block IDs will change in the future
		$waterBucket = new LiquidBucket(new IID(Ids::WATER_BUCKET, LegacyIds::BUCKET, 8), "Water Bucket", Blocks::WATER());
		$this->register($waterBucket);
		$this->remap(LegacyIds::BUCKET, 9, $waterBucket);
		$lavaBucket = new LiquidBucket(new IID(Ids::LAVA_BUCKET, LegacyIds::BUCKET, 10), "Lava Bucket", Blocks::LAVA());
		$this->register($lavaBucket);
		$this->remap(LegacyIds::BUCKET, 11, $lavaBucket);
		$this->register(new Melon(new IID(Ids::MELON, LegacyIds::MELON, 0), "Melon"));
		$this->register(new MelonSeeds(new IID(Ids::MELON_SEEDS, LegacyIds::MELON_SEEDS, 0), "Melon Seeds"));
		$this->register(new MilkBucket(new IID(Ids::MILK_BUCKET, LegacyIds::BUCKET, 1), "Milk Bucket"));
		$this->register(new Minecart(new IID(Ids::MINECART, LegacyIds::MINECART, 0), "Minecart"));
		$this->register(new MushroomStew(new IID(Ids::MUSHROOM_STEW, LegacyIds::MUSHROOM_STEW, 0), "Mushroom Stew"));
		$this->register(new PaintingItem(new IID(Ids::PAINTING, LegacyIds::PAINTING, 0), "Painting"));
		$this->register(new PoisonousPotato(new IID(Ids::POISONOUS_POTATO, LegacyIds::POISONOUS_POTATO, 0), "Poisonous Potato"));
		$this->register(new Potato(new IID(Ids::POTATO, LegacyIds::POTATO, 0), "Potato"));
		$this->register(new Pufferfish(new IID(Ids::PUFFERFISH, LegacyIds::PUFFERFISH, 0), "Pufferfish"));
		$this->register(new PumpkinPie(new IID(Ids::PUMPKIN_PIE, LegacyIds::PUMPKIN_PIE, 0), "Pumpkin Pie"));
		$this->register(new PumpkinSeeds(new IID(Ids::PUMPKIN_SEEDS, LegacyIds::PUMPKIN_SEEDS, 0), "Pumpkin Seeds"));
		$this->register(new RabbitStew(new IID(Ids::RABBIT_STEW, LegacyIds::RABBIT_STEW, 0), "Rabbit Stew"));
		$this->register(new RawBeef(new IID(Ids::RAW_BEEF, LegacyIds::RAW_BEEF, 0), "Raw Beef"));
		$this->register(new RawChicken(new IID(Ids::RAW_CHICKEN, LegacyIds::RAW_CHICKEN, 0), "Raw Chicken"));
		$this->register(new RawFish(new IID(Ids::RAW_FISH, LegacyIds::RAW_FISH, 0), "Raw Fish"));
		$this->register(new RawMutton(new IID(Ids::RAW_MUTTON, LegacyIds::RAW_MUTTON, 0), "Raw Mutton"));
		$this->register(new RawPorkchop(new IID(Ids::RAW_PORKCHOP, LegacyIds::RAW_PORKCHOP, 0), "Raw Porkchop"));
		$this->register(new RawRabbit(new IID(Ids::RAW_RABBIT, LegacyIds::RAW_RABBIT, 0), "Raw Rabbit"));
		$this->register(new RawSalmon(new IID(Ids::RAW_SALMON, LegacyIds::RAW_SALMON, 0), "Raw Salmon"));
		$this->register(new Record(new IID(Ids::RECORD_13, LegacyIds::RECORD_13, 0), RecordType::DISK_13(), "Record 13"));
		$this->register(new Record(new IID(Ids::RECORD_CAT, LegacyIds::RECORD_CAT, 0), RecordType::DISK_CAT(), "Record Cat"));
		$this->register(new Record(new IID(Ids::RECORD_BLOCKS, LegacyIds::RECORD_BLOCKS, 0), RecordType::DISK_BLOCKS(), "Record Blocks"));
		$this->register(new Record(new IID(Ids::RECORD_CHIRP, LegacyIds::RECORD_CHIRP, 0), RecordType::DISK_CHIRP(), "Record Chirp"));
		$this->register(new Record(new IID(Ids::RECORD_FAR, LegacyIds::RECORD_FAR, 0), RecordType::DISK_FAR(), "Record Far"));
		$this->register(new Record(new IID(Ids::RECORD_MALL, LegacyIds::RECORD_MALL, 0), RecordType::DISK_MALL(), "Record Mall"));
		$this->register(new Record(new IID(Ids::RECORD_MELLOHI, LegacyIds::RECORD_MELLOHI, 0), RecordType::DISK_MELLOHI(), "Record Mellohi"));
		$this->register(new Record(new IID(Ids::RECORD_STAL, LegacyIds::RECORD_STAL, 0), RecordType::DISK_STAL(), "Record Stal"));
		$this->register(new Record(new IID(Ids::RECORD_STRAD, LegacyIds::RECORD_STRAD, 0), RecordType::DISK_STRAD(), "Record Strad"));
		$this->register(new Record(new IID(Ids::RECORD_WARD, LegacyIds::RECORD_WARD, 0), RecordType::DISK_WARD(), "Record Ward"));
		$this->register(new Record(new IID(Ids::RECORD_11, LegacyIds::RECORD_11, 0), RecordType::DISK_11(), "Record 11"));
		$this->register(new Record(new IID(Ids::RECORD_WAIT, LegacyIds::RECORD_WAIT, 0), RecordType::DISK_WAIT(), "Record Wait"));
		$this->register(new Redstone(new IID(Ids::REDSTONE_DUST, LegacyIds::REDSTONE, 0), "Redstone"));
		$this->register(new RottenFlesh(new IID(Ids::ROTTEN_FLESH, LegacyIds::ROTTEN_FLESH, 0), "Rotten Flesh"));
		$this->register(new Shears(new IID(Ids::SHEARS, LegacyIds::SHEARS, 0), "Shears"));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::OAK_SIGN, LegacyIds::SIGN, 0), Blocks::OAK_SIGN(), Blocks::OAK_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::SPRUCE_SIGN, LegacyIds::SPRUCE_SIGN, 0), Blocks::SPRUCE_SIGN(), Blocks::SPRUCE_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::BIRCH_SIGN, LegacyIds::BIRCH_SIGN, 0), Blocks::BIRCH_SIGN(), Blocks::BIRCH_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::JUNGLE_SIGN, LegacyIds::JUNGLE_SIGN, 0), Blocks::JUNGLE_SIGN(), Blocks::JUNGLE_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::ACACIA_SIGN, LegacyIds::ACACIA_SIGN, 0), Blocks::ACACIA_SIGN(), Blocks::ACACIA_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::DARK_OAK_SIGN, LegacyIds::DARKOAK_SIGN, 0), Blocks::DARK_OAK_SIGN(), Blocks::DARK_OAK_WALL_SIGN()));
		$this->register(new Snowball(new IID(Ids::SNOWBALL, LegacyIds::SNOWBALL, 0), "Snowball"));
		$this->register(new SpiderEye(new IID(Ids::SPIDER_EYE, LegacyIds::SPIDER_EYE, 0), "Spider Eye"));
		$this->register(new Steak(new IID(Ids::STEAK, LegacyIds::STEAK, 0), "Steak"));
		$this->register(new Stick(new IID(Ids::STICK, LegacyIds::STICK, 0), "Stick"));
		$this->register(new StringItem(new IID(Ids::STRING, LegacyIds::STRING, 0), "String"));
		$this->register(new SweetBerries(new IID(Ids::SWEET_BERRIES, LegacyIds::SWEET_BERRIES, 0), "Sweet Berries"));
		$this->register(new Totem(new IID(Ids::TOTEM, LegacyIds::TOTEM, 0), "Totem of Undying"));
		$this->register(new WheatSeeds(new IID(Ids::WHEAT_SEEDS, LegacyIds::WHEAT_SEEDS, 0), "Wheat Seeds"));
		$this->register(new WritableBook(new IID(Ids::WRITABLE_BOOK, LegacyIds::WRITABLE_BOOK, 0), "Book & Quill"));
		$this->register(new WrittenBook(new IID(Ids::WRITTEN_BOOK, LegacyIds::WRITTEN_BOOK, 0), "Written Book"));

		foreach(DyeColor::getAll() as $color){
			//TODO: use colour object directly
			//TODO: add interface to dye-colour objects
			$this->register((new Dye(new IID(Ids::DYE, LegacyIds::DYE, 0), "Dye"))->setColor($color));
			$this->register((new Banner(
				new IID(Ids::BANNER, LegacyIds::BANNER, 0),
				Blocks::BANNER(),
				Blocks::WALL_BANNER()
			))->setColor($color));
		}

		foreach(PotionType::getAll() as $type){
			$this->register((new Potion(new IID(Ids::POTION, LegacyIds::POTION, 0), "Potion"))->setType($type));
			$this->register((new SplashPotion(new IID(Ids::SPLASH_POTION, LegacyIds::SPLASH_POTION, 0), "Splash Potion"))->setType($type));
		}

		foreach(TreeType::getAll() as $type){
			//TODO: tree type should be dynamic in the future, but we're staying static for now for the sake of consistency
			$this->register(new Boat(new IID(match($type){
				TreeType::OAK() => Ids::OAK_BOAT,
				TreeType::SPRUCE() => Ids::SPRUCE_BOAT,
				TreeType::BIRCH() => Ids::BIRCH_BOAT,
				TreeType::JUNGLE() => Ids::JUNGLE_BOAT,
				TreeType::ACACIA() => Ids::ACACIA_BOAT,
				TreeType::DARK_OAK() => Ids::DARK_OAK_BOAT,
				default => throw new AssumptionFailedError("Unhandled tree type " . $type->name())
			}, LegacyIds::BOAT, $type->getMagicNumber()), $type->getDisplayName() . " Boat", $type));
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
		//TODO: minecraft:tnt_minecart
		//TODO: minecraft:trident
		//TODO: minecraft:turtle_helmet
		//endregion
	}

	private function registerSpawnEggs() : void{
		//TODO: the meta values should probably be hardcoded; they won't change, but the EntityLegacyIds might
		$this->register(new class(new IID(Ids::ZOMBIE_SPAWN_EGG, LegacyIds::SPAWN_EGG, EntityLegacyIds::ZOMBIE), "Zombie Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Zombie(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		$this->register(new class(new IID(Ids::SQUID_SPAWN_EGG, LegacyIds::SPAWN_EGG, EntityLegacyIds::SQUID), "Squid Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Squid(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		$this->register(new class(new IID(Ids::VILLAGER_SPAWN_EGG, LegacyIds::SPAWN_EGG, EntityLegacyIds::VILLAGER), "Villager Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Villager(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
	}

	private function registerTierToolItems() : void{
		$this->register(new Axe(new IID(Ids::DIAMOND_AXE, LegacyIds::DIAMOND_AXE, 0), "Diamond Axe", ToolTier::DIAMOND()));
		$this->register(new Axe(new IID(Ids::GOLDEN_AXE, LegacyIds::GOLDEN_AXE, 0), "Golden Axe", ToolTier::GOLD()));
		$this->register(new Axe(new IID(Ids::IRON_AXE, LegacyIds::IRON_AXE, 0), "Iron Axe", ToolTier::IRON()));
		$this->register(new Axe(new IID(Ids::STONE_AXE, LegacyIds::STONE_AXE, 0), "Stone Axe", ToolTier::STONE()));
		$this->register(new Axe(new IID(Ids::WOODEN_AXE, LegacyIds::WOODEN_AXE, 0), "Wooden Axe", ToolTier::WOOD()));
		$this->register(new Hoe(new IID(Ids::DIAMOND_HOE, LegacyIds::DIAMOND_HOE, 0), "Diamond Hoe", ToolTier::DIAMOND()));
		$this->register(new Hoe(new IID(Ids::GOLDEN_HOE, LegacyIds::GOLDEN_HOE, 0), "Golden Hoe", ToolTier::GOLD()));
		$this->register(new Hoe(new IID(Ids::IRON_HOE, LegacyIds::IRON_HOE, 0), "Iron Hoe", ToolTier::IRON()));
		$this->register(new Hoe(new IID(Ids::STONE_HOE, LegacyIds::STONE_HOE, 0), "Stone Hoe", ToolTier::STONE()));
		$this->register(new Hoe(new IID(Ids::WOODEN_HOE, LegacyIds::WOODEN_HOE, 0), "Wooden Hoe", ToolTier::WOOD()));
		$this->register(new Pickaxe(new IID(Ids::DIAMOND_PICKAXE, LegacyIds::DIAMOND_PICKAXE, 0), "Diamond Pickaxe", ToolTier::DIAMOND()));
		$this->register(new Pickaxe(new IID(Ids::GOLDEN_PICKAXE, LegacyIds::GOLDEN_PICKAXE, 0), "Golden Pickaxe", ToolTier::GOLD()));
		$this->register(new Pickaxe(new IID(Ids::IRON_PICKAXE, LegacyIds::IRON_PICKAXE, 0), "Iron Pickaxe", ToolTier::IRON()));
		$this->register(new Pickaxe(new IID(Ids::STONE_PICKAXE, LegacyIds::STONE_PICKAXE, 0), "Stone Pickaxe", ToolTier::STONE()));
		$this->register(new Pickaxe(new IID(Ids::WOODEN_PICKAXE, LegacyIds::WOODEN_PICKAXE, 0), "Wooden Pickaxe", ToolTier::WOOD()));
		$this->register(new Shovel(new IID(Ids::DIAMOND_SHOVEL, LegacyIds::DIAMOND_SHOVEL, 0), "Diamond Shovel", ToolTier::DIAMOND()));
		$this->register(new Shovel(new IID(Ids::GOLDEN_SHOVEL, LegacyIds::GOLDEN_SHOVEL, 0), "Golden Shovel", ToolTier::GOLD()));
		$this->register(new Shovel(new IID(Ids::IRON_SHOVEL, LegacyIds::IRON_SHOVEL, 0), "Iron Shovel", ToolTier::IRON()));
		$this->register(new Shovel(new IID(Ids::STONE_SHOVEL, LegacyIds::STONE_SHOVEL, 0), "Stone Shovel", ToolTier::STONE()));
		$this->register(new Shovel(new IID(Ids::WOODEN_SHOVEL, LegacyIds::WOODEN_SHOVEL, 0), "Wooden Shovel", ToolTier::WOOD()));
		$this->register(new Sword(new IID(Ids::DIAMOND_SWORD, LegacyIds::DIAMOND_SWORD, 0), "Diamond Sword", ToolTier::DIAMOND()));
		$this->register(new Sword(new IID(Ids::GOLDEN_SWORD, LegacyIds::GOLDEN_SWORD, 0), "Golden Sword", ToolTier::GOLD()));
		$this->register(new Sword(new IID(Ids::IRON_SWORD, LegacyIds::IRON_SWORD, 0), "Iron Sword", ToolTier::IRON()));
		$this->register(new Sword(new IID(Ids::STONE_SWORD, LegacyIds::STONE_SWORD, 0), "Stone Sword", ToolTier::STONE()));
		$this->register(new Sword(new IID(Ids::WOODEN_SWORD, LegacyIds::WOODEN_SWORD, 0), "Wooden Sword", ToolTier::WOOD()));
	}

	private function registerArmorItems() : void{
		$this->register(new Armor(new IID(Ids::CHAINMAIL_BOOTS, LegacyIds::CHAIN_BOOTS, 0), "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::DIAMOND_BOOTS, LegacyIds::DIAMOND_BOOTS, 0), "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::GOLDEN_BOOTS, LegacyIds::GOLDEN_BOOTS, 0), "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::IRON_BOOTS, LegacyIds::IRON_BOOTS, 0), "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::LEATHER_BOOTS, LegacyIds::LEATHER_BOOTS, 0), "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::CHAINMAIL_CHESTPLATE, LegacyIds::CHAIN_CHESTPLATE, 0), "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::DIAMOND_CHESTPLATE, LegacyIds::DIAMOND_CHESTPLATE, 0), "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::GOLDEN_CHESTPLATE, LegacyIds::GOLDEN_CHESTPLATE, 0), "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::IRON_CHESTPLATE, LegacyIds::IRON_CHESTPLATE, 0), "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::LEATHER_TUNIC, LegacyIds::LEATHER_CHESTPLATE, 0), "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::CHAINMAIL_HELMET, LegacyIds::CHAIN_HELMET, 0), "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::DIAMOND_HELMET, LegacyIds::DIAMOND_HELMET, 0), "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::GOLDEN_HELMET, LegacyIds::GOLDEN_HELMET, 0), "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::IRON_HELMET, LegacyIds::IRON_HELMET, 0), "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::LEATHER_CAP, LegacyIds::LEATHER_HELMET, 0), "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::CHAINMAIL_LEGGINGS, LegacyIds::CHAIN_LEGGINGS, 0), "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new IID(Ids::DIAMOND_LEGGINGS, LegacyIds::DIAMOND_LEGGINGS, 0), "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new IID(Ids::GOLDEN_LEGGINGS, LegacyIds::GOLDEN_LEGGINGS, 0), "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new IID(Ids::IRON_LEGGINGS, LegacyIds::IRON_LEGGINGS, 0), "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new IID(Ids::LEATHER_PANTS, LegacyIds::LEATHER_LEGGINGS, 0), "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS)));
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

		if(!$override && $this->isRegistered($id, $variant)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		$this->list[self::getListOffset($id, $variant)] = clone $item;
	}

	public function remap(int $legacyId, int $legacyMeta, Item $item, bool $override = false) : void{
		if(!$override && $this->isRegistered($legacyId, $legacyMeta)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		$this->list[self::getListOffset($legacyId, $legacyMeta)] = clone $item;
	}

	private static function itemToBlockId(int $id) : int{
		return $id < 0 ? 255 - $id : $id;
	}

	/**
	 * @deprecated This method should ONLY be used for deserializing data, e.g. from a config or database. For all other
	 * purposes, use VanillaItems.
	 * @see VanillaItems
	 *
	 * Deserializes an item from the provided legacy ID, legacy meta, count and NBT.
	 *
	 * @throws SavedDataLoadingException
	 */
	public function get(int $id, int $meta = 0, int $count = 1, ?CompoundTag $tags = null) : Item{
		/** @var Item|null $item */
		$item = null;

		if($id < -0x8000 || $id > 0x7fff){
			throw new SavedDataLoadingException("Legacy ID must be in the range " . -0x8000 . " ... " . 0x7fff);
		}
		if($meta < 0 || $meta > 0x7ffe){ //0x7fff would cause problems with recipe wildcards
			throw new SavedDataLoadingException("Meta cannot be negative or larger than " . 0x7ffe);
		}

		if(isset($this->list[$offset = self::getListOffset($id, $meta)])){
			$item = clone $this->list[$offset];
		}elseif(isset($this->list[$zero = self::getListOffset($id, 0)]) && $this->list[$zero] instanceof Durable){
			$item = clone $this->list[$zero];
			$item->setDamage(min($meta, $this->list[$zero]->getMaxDurability()));
		}elseif($id < 256){ //intentionally includes negatives, for extended block IDs
			//TODO: do not assume that item IDs and block IDs are the same or related
			$blockStateData = GlobalBlockStateHandlers::getUpgrader()->upgradeIntIdMeta(self::itemToBlockId($id), $meta & 0xf);
			if($blockStateData !== null){
				try{
					$blockStateId = GlobalBlockStateHandlers::getDeserializer()->deserialize($blockStateData);
					$item = new ItemBlock(BlockFactory::getInstance()->fromFullBlock($blockStateId));
				}catch(BlockStateDeserializeException $e){
					throw new SavedDataLoadingException("Failed to deserialize itemblock: " . $e->getMessage(), 0, $e);
				}
			}
		}

		if($item === null){
			throw new SavedDataLoadingException("No registered item is associated with this ID and meta");
		}

		$item->setCount($count);
		if($tags !== null){
			try{
				$item->setNamedTag($tags);
			}catch(NbtException $e){
				throw new SavedDataLoadingException("Invalid item NBT: " . $e->getMessage(), 0, $e);
			}
		}
		return $item;
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 */
	public function isRegistered(int $id, int $variant = 0) : bool{
		if($id < 256){
			$blockStateData = GlobalBlockStateHandlers::getUpgrader()->upgradeIntIdMeta(self::itemToBlockId($id), $variant & 0xf);
			if($blockStateData === null){
				return false;
			}
			try{
				GlobalBlockStateHandlers::getDeserializer()->deserialize($blockStateData);
				return true;
			}catch(BlockStateDeserializeException){
				return false;
			}
		}

		return isset($this->list[self::getListOffset($id, $variant)]);
	}

	private static function getListOffset(int $id, int $variant) : int{
		if($id < -0x8000 || $id > 0x7fff){
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
