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
use pocketmine\block\utils\RecordType;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\ItemIdentifier as IID;
use pocketmine\item\ItemTypeIds as Ids;
use pocketmine\math\Vector3;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

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

		$this->register(new Apple(new IID(Ids::APPLE), "Apple"));
		$this->register(new Arrow(new IID(Ids::ARROW), "Arrow"));

		$this->register(new BakedPotato(new IID(Ids::BAKED_POTATO), "Baked Potato"));
		$this->register(new Bamboo(new IID(Ids::BAMBOO), "Bamboo"), true);
		$this->register(new Beetroot(new IID(Ids::BEETROOT), "Beetroot"));
		$this->register(new BeetrootSeeds(new IID(Ids::BEETROOT_SEEDS), "Beetroot Seeds"));
		$this->register(new BeetrootSoup(new IID(Ids::BEETROOT_SOUP), "Beetroot Soup"));
		$this->register(new BlazeRod(new IID(Ids::BLAZE_ROD), "Blaze Rod"));
		$this->register(new Book(new IID(Ids::BOOK), "Book"));
		$this->register(new Bow(new IID(Ids::BOW), "Bow"));
		$this->register(new Bowl(new IID(Ids::BOWL), "Bowl"));
		$this->register(new Bread(new IID(Ids::BREAD), "Bread"));
		$this->register(new Bucket(new IID(Ids::BUCKET), "Bucket"));
		$this->register(new Carrot(new IID(Ids::CARROT), "Carrot"));
		$this->register(new ChorusFruit(new IID(Ids::CHORUS_FRUIT), "Chorus Fruit"));
		$this->register(new Clock(new IID(Ids::CLOCK), "Clock"));
		$this->register(new Clownfish(new IID(Ids::CLOWNFISH), "Clownfish"));
		$this->register(new Coal(new IID(Ids::COAL), "Coal"));

		$this->register(new CoralFan(new IID(Ids::CORAL_FAN)));

		$this->register(new Coal(new IID(Ids::CHARCOAL), "Charcoal"));
		$this->register(new CocoaBeans(new IID(Ids::COCOA_BEANS), "Cocoa Beans"));
		$this->register(new Compass(new IID(Ids::COMPASS), "Compass"));
		$this->register(new CookedChicken(new IID(Ids::COOKED_CHICKEN), "Cooked Chicken"));
		$this->register(new CookedFish(new IID(Ids::COOKED_FISH), "Cooked Fish"));
		$this->register(new CookedMutton(new IID(Ids::COOKED_MUTTON), "Cooked Mutton"));
		$this->register(new CookedPorkchop(new IID(Ids::COOKED_PORKCHOP), "Cooked Porkchop"));
		$this->register(new CookedRabbit(new IID(Ids::COOKED_RABBIT), "Cooked Rabbit"));
		$this->register(new CookedSalmon(new IID(Ids::COOKED_SALMON), "Cooked Salmon"));
		$this->register(new Cookie(new IID(Ids::COOKIE), "Cookie"));
		$this->register(new DriedKelp(new IID(Ids::DRIED_KELP), "Dried Kelp"));
		$this->register(new Egg(new IID(Ids::EGG), "Egg"));
		$this->register(new EnderPearl(new IID(Ids::ENDER_PEARL), "Ender Pearl"));
		$this->register(new ExperienceBottle(new IID(Ids::EXPERIENCE_BOTTLE), "Bottle o' Enchanting"));
		$this->register(new Fertilizer(new IID(Ids::BONE_MEAL), "Bone Meal"));
		$this->register(new FishingRod(new IID(Ids::FISHING_ROD), "Fishing Rod"));
		$this->register(new FlintSteel(new IID(Ids::FLINT_AND_STEEL), "Flint and Steel"));
		$this->register(new GlassBottle(new IID(Ids::GLASS_BOTTLE), "Glass Bottle"));
		$this->register(new GoldenApple(new IID(Ids::GOLDEN_APPLE), "Golden Apple"));
		$this->register(new GoldenAppleEnchanted(new IID(Ids::ENCHANTED_GOLDEN_APPLE), "Enchanted Golden Apple"));
		$this->register(new GoldenCarrot(new IID(Ids::GOLDEN_CARROT), "Golden Carrot"));
		$this->register(new Item(new IID(Ids::AMETHYST_SHARD), "Amethyst Shard"));
		$this->register(new Item(new IID(Ids::BLAZE_POWDER), "Blaze Powder"));
		$this->register(new Item(new IID(Ids::BLEACH), "Bleach")); //EDU
		$this->register(new Item(new IID(Ids::BONE), "Bone"));
		$this->register(new Item(new IID(Ids::BRICK), "Brick"));
		$this->register(new Item(new IID(Ids::POPPED_CHORUS_FRUIT), "Popped Chorus Fruit"));
		$this->register(new Item(new IID(Ids::CLAY), "Clay"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SALT), "Salt"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_OXIDE), "Sodium Oxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_HYDROXIDE), "Sodium Hydroxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_MAGNESIUM_NITRATE), "Magnesium Nitrate"));
		$this->register(new Item(new IID(Ids::CHEMICAL_IRON_SULPHIDE), "Iron Sulphide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_LITHIUM_HYDRIDE), "Lithium Hydride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_HYDRIDE), "Sodium Hydride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CALCIUM_BROMIDE), "Calcium Bromide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_MAGNESIUM_OXIDE), "Magnesium Oxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_ACETATE), "Sodium Acetate"));
		$this->register(new Item(new IID(Ids::CHEMICAL_LUMINOL), "Luminol"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CHARCOAL), "Charcoal")); //??? maybe bug
		$this->register(new Item(new IID(Ids::CHEMICAL_SUGAR), "Sugar")); //??? maybe bug
		$this->register(new Item(new IID(Ids::CHEMICAL_ALUMINIUM_OXIDE), "Aluminium Oxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_BORON_TRIOXIDE), "Boron Trioxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SOAP), "Soap"));
		$this->register(new Item(new IID(Ids::CHEMICAL_POLYETHYLENE), "Polyethylene"));
		$this->register(new Item(new IID(Ids::CHEMICAL_RUBBISH), "Rubbish"));
		$this->register(new Item(new IID(Ids::CHEMICAL_MAGNESIUM_SALTS), "Magnesium Salts"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SULPHATE), "Sulphate"));
		$this->register(new Item(new IID(Ids::CHEMICAL_BARIUM_SULPHATE), "Barium Sulphate"));
		$this->register(new Item(new IID(Ids::CHEMICAL_POTASSIUM_CHLORIDE), "Potassium Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_MERCURIC_CHLORIDE), "Mercuric Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CERIUM_CHLORIDE), "Cerium Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_TUNGSTEN_CHLORIDE), "Tungsten Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CALCIUM_CHLORIDE), "Calcium Chloride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_WATER), "Water")); //???
		$this->register(new Item(new IID(Ids::CHEMICAL_GLUE), "Glue"));
		$this->register(new Item(new IID(Ids::CHEMICAL_HYPOCHLORITE), "Hypochlorite"));
		$this->register(new Item(new IID(Ids::CHEMICAL_CRUDE_OIL), "Crude Oil"));
		$this->register(new Item(new IID(Ids::CHEMICAL_LATEX), "Latex"));
		$this->register(new Item(new IID(Ids::CHEMICAL_POTASSIUM_IODIDE), "Potassium Iodide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_FLUORIDE), "Sodium Fluoride"));
		$this->register(new Item(new IID(Ids::CHEMICAL_BENZENE), "Benzene"));
		$this->register(new Item(new IID(Ids::CHEMICAL_INK), "Ink"));
		$this->register(new Item(new IID(Ids::CHEMICAL_HYDROGEN_PEROXIDE), "Hydrogen Peroxide"));
		$this->register(new Item(new IID(Ids::CHEMICAL_AMMONIA), "Ammonia"));
		$this->register(new Item(new IID(Ids::CHEMICAL_SODIUM_HYPOCHLORITE), "Sodium Hypochlorite"));
		$this->register(new Item(new IID(Ids::DIAMOND), "Diamond"));
		$this->register(new Item(new IID(Ids::DISC_FRAGMENT_5), "Disc Fragment (5)"));
		$this->register(new Item(new IID(Ids::DRAGON_BREATH), "Dragon's Breath"));
		$this->register(new Item(new IID(Ids::GLOW_INK_SAC), "Glow Ink Sac"));
		$this->register(new Item(new IID(Ids::INK_SAC), "Ink Sac"));
		$this->register(new Item(new IID(Ids::LAPIS_LAZULI), "Lapis Lazuli"));
		$this->register(new Item(new IID(Ids::ECHO_SHARD), "Echo Shard"));
		$this->register(new Item(new IID(Ids::EMERALD), "Emerald"));
		$this->register(new Item(new IID(Ids::FEATHER), "Feather"));
		$this->register(new Item(new IID(Ids::FERMENTED_SPIDER_EYE), "Fermented Spider Eye"));
		$this->register(new Item(new IID(Ids::FLINT), "Flint"));
		$this->register(new Item(new IID(Ids::GHAST_TEAR), "Ghast Tear"));
		$this->register(new Item(new IID(Ids::GLISTERING_MELON), "Glistering Melon"));
		$this->register(new Item(new IID(Ids::GLOWSTONE_DUST), "Glowstone Dust"));
		$this->register(new Item(new IID(Ids::GOLD_INGOT), "Gold Ingot"));
		$this->register(new Item(new IID(Ids::GOLD_NUGGET), "Gold Nugget"));
		$this->register(new Item(new IID(Ids::GUNPOWDER), "Gunpowder"));
		$this->register(new Item(new IID(Ids::HEART_OF_THE_SEA), "Heart of the Sea"));
		$this->register(new Item(new IID(Ids::HONEYCOMB), "Honeycomb"));
		$this->register(new Item(new IID(Ids::IRON_INGOT), "Iron Ingot"));
		$this->register(new Item(new IID(Ids::IRON_NUGGET), "Iron Nugget"));
		$this->register(new Item(new IID(Ids::LEATHER), "Leather"));
		$this->register(new Item(new IID(Ids::MAGMA_CREAM), "Magma Cream"));
		$this->register(new Item(new IID(Ids::NAUTILUS_SHELL), "Nautilus Shell"));
		$this->register(new Item(new IID(Ids::NETHER_BRICK), "Nether Brick"));
		$this->register(new Item(new IID(Ids::NETHER_QUARTZ), "Nether Quartz"));
		$this->register(new Item(new IID(Ids::NETHER_STAR), "Nether Star"));
		$this->register(new Item(new IID(Ids::PAPER), "Paper"));
		$this->register(new Item(new IID(Ids::PRISMARINE_CRYSTALS), "Prismarine Crystals"));
		$this->register(new Item(new IID(Ids::PRISMARINE_SHARD), "Prismarine Shard"));
		$this->register(new Item(new IID(Ids::RABBIT_FOOT), "Rabbit's Foot"));
		$this->register(new Item(new IID(Ids::RABBIT_HIDE), "Rabbit Hide"));
		$this->register(new Item(new IID(Ids::SHULKER_SHELL), "Shulker Shell"));
		$this->register(new Item(new IID(Ids::SLIMEBALL), "Slimeball"));
		$this->register(new Item(new IID(Ids::SUGAR), "Sugar"));
		$this->register(new Item(new IID(Ids::SCUTE), "Scute"));
		$this->register(new Item(new IID(Ids::WHEAT), "Wheat"));
		$this->register(new Item(new IID(Ids::COPPER_INGOT), "Copper Ingot"));
		$this->register(new Item(new IID(Ids::RAW_COPPER), "Raw Copper"));
		$this->register(new Item(new IID(Ids::RAW_IRON), "Raw Iron"));
		$this->register(new Item(new IID(Ids::RAW_GOLD), "Raw Gold"));
		$this->register(new Item(new IID(Ids::PHANTOM_MEMBRANE), "Phantom Membrane"));

		//the meta values for buckets are intentionally hardcoded because block IDs will change in the future
		$this->register(new LiquidBucket(new IID(Ids::WATER_BUCKET), "Water Bucket", Blocks::WATER()));
		$this->register(new LiquidBucket(new IID(Ids::LAVA_BUCKET), "Lava Bucket", Blocks::LAVA()));
		$this->register(new Melon(new IID(Ids::MELON), "Melon"));
		$this->register(new MelonSeeds(new IID(Ids::MELON_SEEDS), "Melon Seeds"));
		$this->register(new MilkBucket(new IID(Ids::MILK_BUCKET), "Milk Bucket"));
		$this->register(new Minecart(new IID(Ids::MINECART), "Minecart"));
		$this->register(new MushroomStew(new IID(Ids::MUSHROOM_STEW), "Mushroom Stew"));
		$this->register(new PaintingItem(new IID(Ids::PAINTING), "Painting"));
		$this->register(new PoisonousPotato(new IID(Ids::POISONOUS_POTATO), "Poisonous Potato"));
		$this->register(new Potato(new IID(Ids::POTATO), "Potato"));
		$this->register(new Pufferfish(new IID(Ids::PUFFERFISH), "Pufferfish"));
		$this->register(new PumpkinPie(new IID(Ids::PUMPKIN_PIE), "Pumpkin Pie"));
		$this->register(new PumpkinSeeds(new IID(Ids::PUMPKIN_SEEDS), "Pumpkin Seeds"));
		$this->register(new RabbitStew(new IID(Ids::RABBIT_STEW), "Rabbit Stew"));
		$this->register(new RawBeef(new IID(Ids::RAW_BEEF), "Raw Beef"));
		$this->register(new RawChicken(new IID(Ids::RAW_CHICKEN), "Raw Chicken"));
		$this->register(new RawFish(new IID(Ids::RAW_FISH), "Raw Fish"));
		$this->register(new RawMutton(new IID(Ids::RAW_MUTTON), "Raw Mutton"));
		$this->register(new RawPorkchop(new IID(Ids::RAW_PORKCHOP), "Raw Porkchop"));
		$this->register(new RawRabbit(new IID(Ids::RAW_RABBIT), "Raw Rabbit"));
		$this->register(new RawSalmon(new IID(Ids::RAW_SALMON), "Raw Salmon"));
		$this->register(new Record(new IID(Ids::RECORD_13), RecordType::DISK_13(), "Record 13"));
		$this->register(new Record(new IID(Ids::RECORD_CAT), RecordType::DISK_CAT(), "Record Cat"));
		$this->register(new Record(new IID(Ids::RECORD_BLOCKS), RecordType::DISK_BLOCKS(), "Record Blocks"));
		$this->register(new Record(new IID(Ids::RECORD_CHIRP), RecordType::DISK_CHIRP(), "Record Chirp"));
		$this->register(new Record(new IID(Ids::RECORD_FAR), RecordType::DISK_FAR(), "Record Far"));
		$this->register(new Record(new IID(Ids::RECORD_MALL), RecordType::DISK_MALL(), "Record Mall"));
		$this->register(new Record(new IID(Ids::RECORD_MELLOHI), RecordType::DISK_MELLOHI(), "Record Mellohi"));
		$this->register(new Record(new IID(Ids::RECORD_STAL), RecordType::DISK_STAL(), "Record Stal"));
		$this->register(new Record(new IID(Ids::RECORD_STRAD), RecordType::DISK_STRAD(), "Record Strad"));
		$this->register(new Record(new IID(Ids::RECORD_WARD), RecordType::DISK_WARD(), "Record Ward"));
		$this->register(new Record(new IID(Ids::RECORD_11), RecordType::DISK_11(), "Record 11"));
		$this->register(new Record(new IID(Ids::RECORD_WAIT), RecordType::DISK_WAIT(), "Record Wait"));
		$this->register(new Redstone(new IID(Ids::REDSTONE_DUST), "Redstone"));
		$this->register(new RottenFlesh(new IID(Ids::ROTTEN_FLESH), "Rotten Flesh"));
		$this->register(new Shears(new IID(Ids::SHEARS), "Shears"));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::OAK_SIGN), Blocks::OAK_SIGN(), Blocks::OAK_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::SPRUCE_SIGN), Blocks::SPRUCE_SIGN(), Blocks::SPRUCE_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::BIRCH_SIGN), Blocks::BIRCH_SIGN(), Blocks::BIRCH_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::JUNGLE_SIGN), Blocks::JUNGLE_SIGN(), Blocks::JUNGLE_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::ACACIA_SIGN), Blocks::ACACIA_SIGN(), Blocks::ACACIA_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::DARK_OAK_SIGN), Blocks::DARK_OAK_SIGN(), Blocks::DARK_OAK_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::MANGROVE_SIGN), Blocks::MANGROVE_SIGN(), Blocks::MANGROVE_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::CRIMSON_SIGN), Blocks::CRIMSON_SIGN(), Blocks::CRIMSON_WALL_SIGN()));
		$this->register(new ItemBlockWallOrFloor(new IID(Ids::WARPED_SIGN), Blocks::WARPED_SIGN(), Blocks::WARPED_WALL_SIGN()));
		$this->register(new Snowball(new IID(Ids::SNOWBALL), "Snowball"));
		$this->register(new SpiderEye(new IID(Ids::SPIDER_EYE), "Spider Eye"));
		$this->register(new Spyglass(new IID(Ids::SPYGLASS), "Spyglass"));
		$this->register(new Steak(new IID(Ids::STEAK), "Steak"));
		$this->register(new Stick(new IID(Ids::STICK), "Stick"));
		$this->register(new StringItem(new IID(Ids::STRING), "String"));
		$this->register(new SweetBerries(new IID(Ids::SWEET_BERRIES), "Sweet Berries"));
		$this->register(new Totem(new IID(Ids::TOTEM), "Totem of Undying"));
		$this->register(new WheatSeeds(new IID(Ids::WHEAT_SEEDS), "Wheat Seeds"));
		$this->register(new WritableBook(new IID(Ids::WRITABLE_BOOK), "Book & Quill"));
		$this->register(new WrittenBook(new IID(Ids::WRITTEN_BOOK), "Written Book"));

		//TODO: add interface to dye-colour objects
		$this->register(new Dye(new IID(Ids::DYE), "Dye"));
		$this->register(new Banner(
			new IID(Ids::BANNER),
			Blocks::BANNER(),
			Blocks::WALL_BANNER()
		));

		$this->register(new Potion(new IID(Ids::POTION), "Potion"));
		$this->register(new SplashPotion(new IID(Ids::SPLASH_POTION), "Splash Potion"));

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
			}), $type->getDisplayName() . " Boat", $type));
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
		$this->register(new class(new IID(Ids::ZOMBIE_SPAWN_EGG), "Zombie Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Zombie(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		$this->register(new class(new IID(Ids::SQUID_SPAWN_EGG), "Squid Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Squid(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		$this->register(new class(new IID(Ids::VILLAGER_SPAWN_EGG), "Villager Spawn Egg") extends SpawnEgg{
			public function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Villager(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
	}

	private function registerTierToolItems() : void{
		$this->register(new Axe(new IID(Ids::DIAMOND_AXE), "Diamond Axe", ToolTier::DIAMOND()));
		$this->register(new Axe(new IID(Ids::GOLDEN_AXE), "Golden Axe", ToolTier::GOLD()));
		$this->register(new Axe(new IID(Ids::IRON_AXE), "Iron Axe", ToolTier::IRON()));
		$this->register(new Axe(new IID(Ids::STONE_AXE), "Stone Axe", ToolTier::STONE()));
		$this->register(new Axe(new IID(Ids::WOODEN_AXE), "Wooden Axe", ToolTier::WOOD()));
		$this->register(new Hoe(new IID(Ids::DIAMOND_HOE), "Diamond Hoe", ToolTier::DIAMOND()));
		$this->register(new Hoe(new IID(Ids::GOLDEN_HOE), "Golden Hoe", ToolTier::GOLD()));
		$this->register(new Hoe(new IID(Ids::IRON_HOE), "Iron Hoe", ToolTier::IRON()));
		$this->register(new Hoe(new IID(Ids::STONE_HOE), "Stone Hoe", ToolTier::STONE()));
		$this->register(new Hoe(new IID(Ids::WOODEN_HOE), "Wooden Hoe", ToolTier::WOOD()));
		$this->register(new Pickaxe(new IID(Ids::DIAMOND_PICKAXE), "Diamond Pickaxe", ToolTier::DIAMOND()));
		$this->register(new Pickaxe(new IID(Ids::GOLDEN_PICKAXE), "Golden Pickaxe", ToolTier::GOLD()));
		$this->register(new Pickaxe(new IID(Ids::IRON_PICKAXE), "Iron Pickaxe", ToolTier::IRON()));
		$this->register(new Pickaxe(new IID(Ids::STONE_PICKAXE), "Stone Pickaxe", ToolTier::STONE()));
		$this->register(new Pickaxe(new IID(Ids::WOODEN_PICKAXE), "Wooden Pickaxe", ToolTier::WOOD()));
		$this->register(new Shovel(new IID(Ids::DIAMOND_SHOVEL), "Diamond Shovel", ToolTier::DIAMOND()));
		$this->register(new Shovel(new IID(Ids::GOLDEN_SHOVEL), "Golden Shovel", ToolTier::GOLD()));
		$this->register(new Shovel(new IID(Ids::IRON_SHOVEL), "Iron Shovel", ToolTier::IRON()));
		$this->register(new Shovel(new IID(Ids::STONE_SHOVEL), "Stone Shovel", ToolTier::STONE()));
		$this->register(new Shovel(new IID(Ids::WOODEN_SHOVEL), "Wooden Shovel", ToolTier::WOOD()));
		$this->register(new Sword(new IID(Ids::DIAMOND_SWORD), "Diamond Sword", ToolTier::DIAMOND()));
		$this->register(new Sword(new IID(Ids::GOLDEN_SWORD), "Golden Sword", ToolTier::GOLD()));
		$this->register(new Sword(new IID(Ids::IRON_SWORD), "Iron Sword", ToolTier::IRON()));
		$this->register(new Sword(new IID(Ids::STONE_SWORD), "Stone Sword", ToolTier::STONE()));
		$this->register(new Sword(new IID(Ids::WOODEN_SWORD), "Wooden Sword", ToolTier::WOOD()));
	}

	private function registerArmorItems() : void{
		$this->register(new Armor(new IID(Ids::CHAINMAIL_BOOTS), "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::DIAMOND_BOOTS), "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::GOLDEN_BOOTS), "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::IRON_BOOTS), "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::LEATHER_BOOTS), "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET)));
		$this->register(new Armor(new IID(Ids::CHAINMAIL_CHESTPLATE), "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::DIAMOND_CHESTPLATE), "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::GOLDEN_CHESTPLATE), "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::IRON_CHESTPLATE), "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::LEATHER_TUNIC), "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST)));
		$this->register(new Armor(new IID(Ids::CHAINMAIL_HELMET), "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::DIAMOND_HELMET), "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::GOLDEN_HELMET), "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::IRON_HELMET), "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::LEATHER_CAP), "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD)));
		$this->register(new Armor(new IID(Ids::CHAINMAIL_LEGGINGS), "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new IID(Ids::DIAMOND_LEGGINGS), "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new IID(Ids::GOLDEN_LEGGINGS), "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new IID(Ids::IRON_LEGGINGS), "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS)));
		$this->register(new Armor(new IID(Ids::LEATHER_PANTS), "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS)));
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
		$id = $item->getTypeId();

		if(!$override && $this->isRegistered($id)){
			throw new \RuntimeException("Trying to overwrite an already registered item");
		}

		$this->list[$id] = clone $item;
	}

	private static function itemToBlockId(int $id) : int{
		if($id > 0){
			throw new \InvalidArgumentException("ID $id is not a block ID");
		}
		return -$id;
	}

	/**
	 * @internal
	 */
	public function fromTypeId(int $typeId) : Item{
		if(isset($this->list[$typeId])){
			return clone $this->list[$typeId];
		}
		if($typeId <= 0){
			return BlockFactory::getInstance()->fromTypeId(self::itemToBlockId($typeId))->asItem();
		}

		throw new \InvalidArgumentException("No item with type ID $typeId is registered");
	}

	/**
	 * Returns whether the specified item ID is already registered in the item factory.
	 */
	public function isRegistered(int $id) : bool{
		if($id <= 0){
			return BlockFactory::getInstance()->isRegistered(self::itemToBlockId($id));
		}

		return isset($this->list[$id]);
	}

	/**
	 * @return Item[]
	 */
	public function getAllKnownTypes() : array{
		return $this->list;
	}
}
