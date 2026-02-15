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

use pocketmine\block\utils\RecordType;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\enchantment\ItemEnchantmentTags as EnchantmentTags;
use pocketmine\item\ItemIdentifier as IID;
use pocketmine\item\VanillaArmorMaterials as ArmorMaterials;
use pocketmine\math\Vector3;
use pocketmine\utils\RegistrySource;
use pocketmine\world\World;
use function is_int;
use function mb_strtoupper;
use function strtolower;

/**
 * @internal
 * @phpstan-extends RegistrySource<Item>
 */
final class VanillaItemsInputs extends RegistrySource{
	public function getTargetClassName() : string{
		return "VanillaItems";
	}

	public function getTargetClassDocComment() : array{
		return [
			"Allows getting a new instance of any item implemented by PocketMine-MP",
			"Every item here also has a constant of the same name in {@link ItemTypeIds} to enable items to be identified"
		];
	}

	public function cloneResults() : bool{ return true; }

	private static function makeIID(string $name) : IID{
		//this sketchy hack allows us to avoid manually writing the constants inline
		//since type IDs are generated from this class anyway, I'm OK with this hack
		//nonetheless, we should try to get rid of it in a future major version (e.g by using string type IDs)
		$reflect = new \ReflectionClass(ItemTypeIds::class);
		$typeId = $reflect->getConstant(mb_strtoupper($name));
		if(!is_int($typeId)){
			//this allows registering new stuff without adding new type ID constants
			//this reduces the number of mandatory steps to test new features in local development
			\GlobalLogger::get()->error(self::class . ": No constant type ID found for $name, generating a new one");
			$typeId = ItemTypeIds::newId();
		}

		return new IID($typeId);
	}

	/**
	 * @phpstan-template TItem of Item
	 * @phpstan-param \Closure(IID) : TItem $createItem
	 * @phpstan-return TItem
	 */
	protected function register(string $name, \Closure $createItem) : Item{
		$item = $createItem(self::makeIID($name));
		self::registerValue($name, $item);
		return $item;
	}

	protected function setup() : void{
		self::registerArmorItems();
		self::registerSpawnEggs();
		self::registerTierToolItems();
		self::registerSmithingTemplates();

		//this doesn't use the regular register() because it doesn't have an item typeID
		//in the future we'll probably want to dissociate this from the air block and make a proper null item
		self::registerDelayed("air", fn() : Item => Blocks::AIR()->asItem()->setCount(0));

		self::registerDelayed("acacia_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::ACACIA_SIGN(), Blocks::ACACIA_WALL_SIGN()));
		self::registerDelayed("acacia_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Acacia Hanging Sign", Blocks::ACACIA_CEILING_CENTER_HANGING_SIGN(), Blocks::ACACIA_CEILING_EDGES_HANGING_SIGN(), Blocks::ACACIA_WALL_HANGING_SIGN()));
		self::register("amethyst_shard", fn(IID $id) => new Item($id, "Amethyst Shard"));
		self::register("apple", fn(IID $id) => new Apple($id, "Apple"));
		self::register("arrow", fn(IID $id) => new Arrow($id, "Arrow"));
		self::register("baked_potato", fn(IID $id) => new BakedPotato($id, "Baked Potato"));
		self::register("bamboo", fn(IID $id) => new Bamboo($id, "Bamboo"));
		self::registerDelayed("bamboo_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::BAMBOO_SIGN(), Blocks::BAMBOO_WALL_SIGN()));
		self::registerDelayed("bamboo_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Bamboo Hanging Sign", Blocks::BAMBOO_CEILING_CENTER_HANGING_SIGN(), Blocks::BAMBOO_CEILING_EDGES_HANGING_SIGN(), Blocks::BAMBOO_WALL_HANGING_SIGN()));
		self::registerDelayed("banner", fn(string $name) : Banner => new Banner(self::makeIID($name), Blocks::BANNER(), Blocks::WALL_BANNER()));
		self::register("beetroot", fn(IID $id) => new Beetroot($id, "Beetroot"));
		self::register("beetroot_seeds", fn(IID $id) => new BeetrootSeeds($id, "Beetroot Seeds"));
		self::register("beetroot_soup", fn(IID $id) => new BeetrootSoup($id, "Beetroot Soup"));
		self::registerDelayed("birch_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::BIRCH_SIGN(), Blocks::BIRCH_WALL_SIGN()));
		self::registerDelayed("birch_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Birch Hanging Sign", Blocks::BIRCH_CEILING_CENTER_HANGING_SIGN(), Blocks::BIRCH_CEILING_EDGES_HANGING_SIGN(), Blocks::BIRCH_WALL_HANGING_SIGN()));
		self::register("blaze_powder", fn(IID $id) => new Item($id, "Blaze Powder"));
		self::register("blaze_rod", fn(IID $id) => new BlazeRod($id, "Blaze Rod"));
		self::register("bleach", fn(IID $id) => new Item($id, "Bleach"));
		self::register("bone", fn(IID $id) => new Item($id, "Bone"));
		self::register("bone_meal", fn(IID $id) => new Fertilizer($id, "Bone Meal"));
		self::register("book", fn(IID $id) => new Book($id, "Book", [EnchantmentTags::ALL]));
		self::register("bow", fn(IID $id) => new Bow($id, "Bow", [EnchantmentTags::BOW]));
		self::register("bowl", fn(IID $id) => new Bowl($id, "Bowl"));
		self::register("bread", fn(IID $id) => new Bread($id, "Bread"));
		self::register("brick", fn(IID $id) => new Item($id, "Brick"));
		self::register("bucket", fn(IID $id) => new Bucket($id, "Bucket"));
		self::register("carrot", fn(IID $id) => new Carrot($id, "Carrot"));
		self::register("charcoal", fn(IID $id) => new Coal($id, "Charcoal"));
		self::registerDelayed("cherry_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::CHERRY_SIGN(), Blocks::CHERRY_WALL_SIGN()));
		self::registerDelayed("cherry_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Cherry Hanging Sign", Blocks::CHERRY_CEILING_CENTER_HANGING_SIGN(), Blocks::CHERRY_CEILING_EDGES_HANGING_SIGN(), Blocks::CHERRY_WALL_HANGING_SIGN()));
		self::register("chemical_aluminium_oxide", fn(IID $id) => new Item($id, "Aluminium Oxide"));
		self::register("chemical_ammonia", fn(IID $id) => new Item($id, "Ammonia"));
		self::register("chemical_barium_sulphate", fn(IID $id) => new Item($id, "Barium Sulphate"));
		self::register("chemical_benzene", fn(IID $id) => new Item($id, "Benzene"));
		self::register("chemical_boron_trioxide", fn(IID $id) => new Item($id, "Boron Trioxide"));
		self::register("chemical_calcium_bromide", fn(IID $id) => new Item($id, "Calcium Bromide"));
		self::register("chemical_calcium_chloride", fn(IID $id) => new Item($id, "Calcium Chloride"));
		self::register("chemical_cerium_chloride", fn(IID $id) => new Item($id, "Cerium Chloride"));
		self::register("chemical_charcoal", fn(IID $id) => new Item($id, "Charcoal"));
		self::register("chemical_crude_oil", fn(IID $id) => new Item($id, "Crude Oil"));
		self::register("chemical_glue", fn(IID $id) => new Item($id, "Glue"));
		self::register("chemical_hydrogen_peroxide", fn(IID $id) => new Item($id, "Hydrogen Peroxide"));
		self::register("chemical_hypochlorite", fn(IID $id) => new Item($id, "Hypochlorite"));
		self::register("chemical_ink", fn(IID $id) => new Item($id, "Ink"));
		self::register("chemical_iron_sulphide", fn(IID $id) => new Item($id, "Iron Sulphide"));
		self::register("chemical_latex", fn(IID $id) => new Item($id, "Latex"));
		self::register("chemical_lithium_hydride", fn(IID $id) => new Item($id, "Lithium Hydride"));
		self::register("chemical_luminol", fn(IID $id) => new Item($id, "Luminol"));
		self::register("chemical_magnesium_nitrate", fn(IID $id) => new Item($id, "Magnesium Nitrate"));
		self::register("chemical_magnesium_oxide", fn(IID $id) => new Item($id, "Magnesium Oxide"));
		self::register("chemical_magnesium_salts", fn(IID $id) => new Item($id, "Magnesium Salts"));
		self::register("chemical_mercuric_chloride", fn(IID $id) => new Item($id, "Mercuric Chloride"));
		self::register("chemical_polyethylene", fn(IID $id) => new Item($id, "Polyethylene"));
		self::register("chemical_potassium_chloride", fn(IID $id) => new Item($id, "Potassium Chloride"));
		self::register("chemical_potassium_iodide", fn(IID $id) => new Item($id, "Potassium Iodide"));
		self::register("chemical_rubbish", fn(IID $id) => new Item($id, "Rubbish"));
		self::register("chemical_salt", fn(IID $id) => new Item($id, "Salt"));
		self::register("chemical_soap", fn(IID $id) => new Item($id, "Soap"));
		self::register("chemical_sodium_acetate", fn(IID $id) => new Item($id, "Sodium Acetate"));
		self::register("chemical_sodium_fluoride", fn(IID $id) => new Item($id, "Sodium Fluoride"));
		self::register("chemical_sodium_hydride", fn(IID $id) => new Item($id, "Sodium Hydride"));
		self::register("chemical_sodium_hydroxide", fn(IID $id) => new Item($id, "Sodium Hydroxide"));
		self::register("chemical_sodium_hypochlorite", fn(IID $id) => new Item($id, "Sodium Hypochlorite"));
		self::register("chemical_sodium_oxide", fn(IID $id) => new Item($id, "Sodium Oxide"));
		self::register("chemical_sugar", fn(IID $id) => new Item($id, "Sugar"));
		self::register("chemical_sulphate", fn(IID $id) => new Item($id, "Sulphate"));
		self::register("chemical_tungsten_chloride", fn(IID $id) => new Item($id, "Tungsten Chloride"));
		self::register("chemical_water", fn(IID $id) => new Item($id, "Water"));
		self::register("chorus_fruit", fn(IID $id) => new ChorusFruit($id, "Chorus Fruit"));
		self::register("clay", fn(IID $id) => new Item($id, "Clay"));
		self::register("clock", fn(IID $id) => new Clock($id, "Clock"));
		self::register("clownfish", fn(IID $id) => new Clownfish($id, "Clownfish"));
		self::register("coal", fn(IID $id) => new Coal($id, "Coal"));
		self::register("cocoa_beans", fn(IID $id) => new CocoaBeans($id, "Cocoa Beans"));
		self::register("compass", fn(IID $id) => new Compass($id, "Compass", [EnchantmentTags::COMPASS]));
		self::register("cooked_chicken", fn(IID $id) => new CookedChicken($id, "Cooked Chicken"));
		self::register("cooked_fish", fn(IID $id) => new CookedFish($id, "Cooked Fish"));
		self::register("cooked_mutton", fn(IID $id) => new CookedMutton($id, "Cooked Mutton"));
		self::register("cooked_porkchop", fn(IID $id) => new CookedPorkchop($id, "Cooked Porkchop"));
		self::register("cooked_rabbit", fn(IID $id) => new CookedRabbit($id, "Cooked Rabbit"));
		self::register("cooked_salmon", fn(IID $id) => new CookedSalmon($id, "Cooked Salmon"));
		self::register("cookie", fn(IID $id) => new Cookie($id, "Cookie"));
		self::register("copper_ingot", fn(IID $id) => new Item($id, "Copper Ingot"));
		self::register("copper_nugget", fn(IID $id) => new Item($id, "Copper Nugget"));
		self::registerDelayed("coral_fan", fn(string $name) : CoralFan => new CoralFan(self::makeIID($name))); //uses VanillaBlocks in constructor :(
		self::registerDelayed("crimson_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::CRIMSON_SIGN(), Blocks::CRIMSON_WALL_SIGN()));
		self::registerDelayed("crimson_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Crimson Hanging Sign", Blocks::CRIMSON_CEILING_CENTER_HANGING_SIGN(), Blocks::CRIMSON_CEILING_EDGES_HANGING_SIGN(), Blocks::CRIMSON_WALL_HANGING_SIGN()));
		self::registerDelayed("dark_oak_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::DARK_OAK_SIGN(), Blocks::DARK_OAK_WALL_SIGN()));
		self::registerDelayed("dark_oak_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Dark Oak Hanging Sign", Blocks::DARK_OAK_CEILING_CENTER_HANGING_SIGN(), Blocks::DARK_OAK_CEILING_EDGES_HANGING_SIGN(), Blocks::DARK_OAK_WALL_HANGING_SIGN()));
		self::register("diamond", fn(IID $id) => new Item($id, "Diamond"));
		self::register("disc_fragment_5", fn(IID $id) => new Item($id, "Disc Fragment (5)"));
		self::register("dragon_breath", fn(IID $id) => new Item($id, "Dragon's Breath"));
		self::register("dried_kelp", fn(IID $id) => new DriedKelp($id, "Dried Kelp"));
		//TODO: add interface to dye-colour objects
		self::register("dye", fn(IID $id) => new Dye($id, "Dye"));
		self::register("echo_shard", fn(IID $id) => new Item($id, "Echo Shard"));
		self::register("egg", fn(IID $id) => new Egg($id, "Egg"));
		self::register("emerald", fn(IID $id) => new Item($id, "Emerald"));
		self::register("enchanted_book", fn(IID $id) => new EnchantedBook($id, "Enchanted Book", [EnchantmentTags::ALL]));
		self::register("enchanted_golden_apple", fn(IID $id) => new GoldenAppleEnchanted($id, "Enchanted Golden Apple"));
		self::register("end_crystal", fn(IID $id) => new EndCrystal($id, "End Crystal"));
		self::register("ender_pearl", fn(IID $id) => new EnderPearl($id, "Ender Pearl"));
		self::register("experience_bottle", fn(IID $id) => new ExperienceBottle($id, "Bottle o' Enchanting"));
		self::register("feather", fn(IID $id) => new Item($id, "Feather"));
		self::register("fermented_spider_eye", fn(IID $id) => new Item($id, "Fermented Spider Eye"));
		self::register("firework_rocket", fn(IID $id) => new FireworkRocket($id, "Firework Rocket"));
		self::register("firework_star", fn(IID $id) => new FireworkStar($id, "Firework Star"));
		self::register("fire_charge", fn(IID $id) => new FireCharge($id, "Fire Charge"));
		self::register("fishing_rod", fn(IID $id) => new FishingRod($id, "Fishing Rod", [EnchantmentTags::FISHING_ROD]));
		self::register("flint", fn(IID $id) => new Item($id, "Flint"));
		self::register("flint_and_steel", fn(IID $id) => new FlintSteel($id, "Flint and Steel", [EnchantmentTags::FLINT_AND_STEEL]));
		self::register("ghast_tear", fn(IID $id) => new Item($id, "Ghast Tear"));
		self::register("glass_bottle", fn(IID $id) => new GlassBottle($id, "Glass Bottle"));
		self::register("glistering_melon", fn(IID $id) => new Item($id, "Glistering Melon"));
		self::register("glow_berries", fn(IID $id) => new GlowBerries($id, "Glow Berries"));
		self::register("glow_ink_sac", fn(IID $id) => new Item($id, "Glow Ink Sac"));
		self::register("glowstone_dust", fn(IID $id) => new Item($id, "Glowstone Dust"));
		self::register("goat_horn", fn(IID $id) => new GoatHorn($id, "Goat Horn"));
		self::register("gold_ingot", fn(IID $id) => new Item($id, "Gold Ingot"));
		self::register("gold_nugget", fn(IID $id) => new Item($id, "Gold Nugget"));
		self::register("golden_apple", fn(IID $id) => new GoldenApple($id, "Golden Apple"));
		self::register("golden_carrot", fn(IID $id) => new GoldenCarrot($id, "Golden Carrot"));
		self::register("gunpowder", fn(IID $id) => new Item($id, "Gunpowder"));
		self::register("heart_of_the_sea", fn(IID $id) => new Item($id, "Heart of the Sea"));
		self::register("honey_bottle", fn(IID $id) => new HoneyBottle($id, "Honey Bottle"));
		self::register("honeycomb", fn(IID $id) => new Item($id, "Honeycomb"));
		self::register("ice_bomb", fn(IID $id) => new IceBomb($id, "Ice Bomb"));
		self::register("ink_sac", fn(IID $id) => new Item($id, "Ink Sac"));
		self::register("iron_ingot", fn(IID $id) => new Item($id, "Iron Ingot"));
		self::register("iron_nugget", fn(IID $id) => new Item($id, "Iron Nugget"));
		self::registerDelayed("jungle_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::JUNGLE_SIGN(), Blocks::JUNGLE_WALL_SIGN()));
		self::registerDelayed("jungle_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Jungle Hanging Sign", Blocks::JUNGLE_CEILING_CENTER_HANGING_SIGN(), Blocks::JUNGLE_CEILING_EDGES_HANGING_SIGN(), Blocks::JUNGLE_WALL_HANGING_SIGN()));
		self::register("lapis_lazuli", fn(IID $id) => new Item($id, "Lapis Lazuli"));
		self::registerDelayed("lava_bucket", fn(string $name) : LiquidBucket => new LiquidBucket(self::makeIID($name), "Lava Bucket", Blocks::LAVA()));
		self::register("leather", fn(IID $id) => new Item($id, "Leather"));
		self::register("lingering_potion", fn(IID $id) => new SplashPotion($id, "Lingering Potion", linger: true));
		self::register("magma_cream", fn(IID $id) => new Item($id, "Magma Cream"));
		self::registerDelayed("mangrove_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::MANGROVE_SIGN(), Blocks::MANGROVE_WALL_SIGN()));
		self::registerDelayed("mangrove_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Mangrove Hanging Sign", Blocks::MANGROVE_CEILING_CENTER_HANGING_SIGN(), Blocks::MANGROVE_CEILING_EDGES_HANGING_SIGN(), Blocks::MANGROVE_WALL_HANGING_SIGN()));
		self::register("medicine", fn(IID $id) => new Medicine($id, "Medicine"));
		self::register("melon", fn(IID $id) => new Melon($id, "Melon"));
		self::register("melon_seeds", fn(IID $id) => new MelonSeeds($id, "Melon Seeds"));
		self::register("milk_bucket", fn(IID $id) => new MilkBucket($id, "Milk Bucket"));
		self::register("minecart", fn(IID $id) => new Minecart($id, "Minecart"));
		self::register("mushroom_stew", fn(IID $id) => new MushroomStew($id, "Mushroom Stew"));
		self::register("name_tag", fn(IID $id) => new NameTag($id, "Name Tag"));
		self::register("nautilus_shell", fn(IID $id) => new Item($id, "Nautilus Shell"));
		self::register("nether_brick", fn(IID $id) => new Item($id, "Nether Brick"));
		self::register("nether_quartz", fn(IID $id) => new Item($id, "Nether Quartz"));
		self::register("nether_star", fn(IID $id) => new Item($id, "Nether Star"));
		self::register("netherite_ingot", fn(IID $id) => new class($id, "Netherite Ingot") extends Item{
			public function isFireProof() : bool{ return true; }
		});
		self::register("netherite_scrap", fn(IID $id) => new class($id, "Netherite Scrap") extends Item{
			public function isFireProof() : bool{ return true; }
		});
		self::registerDelayed("oak_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::OAK_SIGN(), Blocks::OAK_WALL_SIGN()));
		self::registerDelayed("oak_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Oak Hanging Sign", Blocks::OAK_CEILING_CENTER_HANGING_SIGN(), Blocks::OAK_CEILING_EDGES_HANGING_SIGN(), Blocks::OAK_WALL_HANGING_SIGN()));
		self::registerDelayed("ominous_banner", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::OMINOUS_BANNER(), Blocks::OMINOUS_WALL_BANNER()));
		self::register("painting", fn(IID $id) => new PaintingItem($id, "Painting"));
		self::registerDelayed("pale_oak_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::PALE_OAK_SIGN(), Blocks::PALE_OAK_WALL_SIGN()));
		self::registerDelayed("pale_oak_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Pale Oak Hanging Sign", Blocks::PALE_OAK_CEILING_CENTER_HANGING_SIGN(), Blocks::PALE_OAK_CEILING_EDGES_HANGING_SIGN(), Blocks::PALE_OAK_WALL_HANGING_SIGN()));
		self::register("paper", fn(IID $id) => new Item($id, "Paper"));
		self::register("phantom_membrane", fn(IID $id) => new Item($id, "Phantom Membrane"));
		self::register("pitcher_pod", fn(IID $id) => new PitcherPod($id, "Pitcher Pod"));
		self::register("poisonous_potato", fn(IID $id) => new PoisonousPotato($id, "Poisonous Potato"));
		self::register("popped_chorus_fruit", fn(IID $id) => new Item($id, "Popped Chorus Fruit"));
		self::register("potato", fn(IID $id) => new Potato($id, "Potato"));
		self::register("potion", fn(IID $id) => new Potion($id, "Potion"));
		self::register("prismarine_crystals", fn(IID $id) => new Item($id, "Prismarine Crystals"));
		self::register("prismarine_shard", fn(IID $id) => new Item($id, "Prismarine Shard"));
		self::register("pufferfish", fn(IID $id) => new Pufferfish($id, "Pufferfish"));
		self::register("pumpkin_pie", fn(IID $id) => new PumpkinPie($id, "Pumpkin Pie"));
		self::register("pumpkin_seeds", fn(IID $id) => new PumpkinSeeds($id, "Pumpkin Seeds"));
		self::register("rabbit_foot", fn(IID $id) => new Item($id, "Rabbit's Foot"));
		self::register("rabbit_hide", fn(IID $id) => new Item($id, "Rabbit Hide"));
		self::register("rabbit_stew", fn(IID $id) => new RabbitStew($id, "Rabbit Stew"));
		self::register("raw_beef", fn(IID $id) => new RawBeef($id, "Raw Beef"));
		self::register("raw_chicken", fn(IID $id) => new RawChicken($id, "Raw Chicken"));
		self::register("raw_copper", fn(IID $id) => new Item($id, "Raw Copper"));
		self::register("raw_fish", fn(IID $id) => new RawFish($id, "Raw Fish"));
		self::register("raw_gold", fn(IID $id) => new Item($id, "Raw Gold"));
		self::register("raw_iron", fn(IID $id) => new Item($id, "Raw Iron"));
		self::register("raw_mutton", fn(IID $id) => new RawMutton($id, "Raw Mutton"));
		self::register("raw_porkchop", fn(IID $id) => new RawPorkchop($id, "Raw Porkchop"));
		self::register("raw_rabbit", fn(IID $id) => new RawRabbit($id, "Raw Rabbit"));
		self::register("raw_salmon", fn(IID $id) => new RawSalmon($id, "Raw Salmon"));
		self::register("record_11", fn(IID $id) => new Record($id, RecordType::DISK_11, "Record 11"));
		self::register("record_13", fn(IID $id) => new Record($id, RecordType::DISK_13, "Record 13"));
		self::register("record_5", fn(IID $id) => new Record($id, RecordType::DISK_5, "Record 5"));
		self::register("record_blocks", fn(IID $id) => new Record($id, RecordType::DISK_BLOCKS, "Record Blocks"));
		self::register("record_cat", fn(IID $id) => new Record($id, RecordType::DISK_CAT, "Record Cat"));
		self::register("record_chirp", fn(IID $id) => new Record($id, RecordType::DISK_CHIRP, "Record Chirp"));
		self::register("record_creator", fn(IID $id) => new Record($id, RecordType::DISK_CREATOR, "Record Creator"));
		self::register("record_creator_music_box", fn(IID $id) => new Record($id, RecordType::DISK_CREATOR_MUSIC_BOX, "Record Creator (Music Box)"));
		self::register("record_far", fn(IID $id) => new Record($id, RecordType::DISK_FAR, "Record Far"));
		self::register("record_lava_chicken", fn(IID $id) => new Record($id, RecordType::DISK_LAVA_CHICKEN, "Record Lava Chicken"));
		self::register("record_mall", fn(IID $id) => new Record($id, RecordType::DISK_MALL, "Record Mall"));
		self::register("record_mellohi", fn(IID $id) => new Record($id, RecordType::DISK_MELLOHI, "Record Mellohi"));
		self::register("record_otherside", fn(IID $id) => new Record($id, RecordType::DISK_OTHERSIDE, "Record Otherside"));
		self::register("record_pigstep", fn(IID $id) => new Record($id, RecordType::DISK_PIGSTEP, "Record Pigstep"));
		self::register("record_precipice", fn(IID $id) => new Record($id, RecordType::DISK_PRECIPICE, "Record Precipice"));
		self::register("record_relic", fn(IID $id) => new Record($id, RecordType::DISK_RELIC, "Record Relic"));
		self::register("record_stal", fn(IID $id) => new Record($id, RecordType::DISK_STAL, "Record Stal"));
		self::register("record_strad", fn(IID $id) => new Record($id, RecordType::DISK_STRAD, "Record Strad"));
		self::register("record_wait", fn(IID $id) => new Record($id, RecordType::DISK_WAIT, "Record Wait"));
		self::register("record_ward", fn(IID $id) => new Record($id, RecordType::DISK_WARD, "Record Ward"));
		self::register("recovery_compass", fn(IID $id) => new Item($id, "Recovery Compass"));
		self::register("redstone_dust", fn(IID $id) => new Redstone($id, "Redstone"));
		self::register("resin_brick", fn(IID $id) => new Item($id, "Resin Brick"));
		self::register("rotten_flesh", fn(IID $id) => new RottenFlesh($id, "Rotten Flesh"));
		self::register("scute", fn(IID $id) => new Item($id, "Scute"));
		self::register("shears", fn(IID $id) => new Shears($id, "Shears", [EnchantmentTags::SHEARS]));
		self::register("shulker_shell", fn(IID $id) => new Item($id, "Shulker Shell"));
		self::register("slimeball", fn(IID $id) => new Item($id, "Slimeball"));
		self::register("snowball", fn(IID $id) => new Snowball($id, "Snowball"));
		self::register("spider_eye", fn(IID $id) => new SpiderEye($id, "Spider Eye"));
		self::register("splash_potion", fn(IID $id) => new SplashPotion($id, "Splash Potion"));
		self::registerDelayed("spruce_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::SPRUCE_SIGN(), Blocks::SPRUCE_WALL_SIGN()));
		self::registerDelayed("spruce_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Spruce Hanging Sign", Blocks::SPRUCE_CEILING_CENTER_HANGING_SIGN(), Blocks::SPRUCE_CEILING_EDGES_HANGING_SIGN(), Blocks::SPRUCE_WALL_HANGING_SIGN()));
		self::register("spyglass", fn(IID $id) => new Spyglass($id, "Spyglass"));
		self::register("steak", fn(IID $id) => new Steak($id, "Steak"));
		self::register("stick", fn(IID $id) => new Stick($id, "Stick"));
		self::register("string", fn(IID $id) => new StringItem($id, "String"));
		self::register("sugar", fn(IID $id) => new Item($id, "Sugar"));
		self::register("suspicious_stew", fn(IID $id) => new SuspiciousStew($id, "Suspicious Stew"));
		self::register("sweet_berries", fn(IID $id) => new SweetBerries($id, "Sweet Berries"));
		self::register("torchflower_seeds", fn(IID $id) => new TorchflowerSeeds($id, "Torchflower Seeds"));
		self::register("totem", fn(IID $id) => new Totem($id, "Totem of Undying"));
		self::register("trident", fn(IID $id) => new Trident($id, "Trident"));
		self::registerDelayed("warped_sign", fn(string $name) : ItemBlockWallOrFloor => new ItemBlockWallOrFloor(self::makeIID($name), Blocks::WARPED_SIGN(), Blocks::WARPED_WALL_SIGN()));
		self::registerDelayed("warped_hanging_sign", fn(string $name) : HangingSign => new HangingSign(self::makeIID($name), "Warped Hanging Sign", Blocks::WARPED_CEILING_CENTER_HANGING_SIGN(), Blocks::WARPED_CEILING_EDGES_HANGING_SIGN(), Blocks::WARPED_WALL_HANGING_SIGN()));
		self::registerDelayed("water_bucket", fn(string $name) : LiquidBucket => new LiquidBucket(self::makeIID($name), "Water Bucket", Blocks::WATER()));
		self::register("wheat", fn(IID $id) => new Item($id, "Wheat"));
		self::register("wheat_seeds", fn(IID $id) => new WheatSeeds($id, "Wheat Seeds"));
		self::register("writable_book", fn(IID $id) => new WritableBook($id, "Book & Quill"));
		self::register("written_book", fn(IID $id) => new WrittenBook($id, "Written Book"));

		foreach(BoatType::cases() as $type){
			//boat type is static, because different types of wood may have different properties
			self::register(strtolower($type->name) . "_boat", fn(IID $id) => new Boat($id, $type->getDisplayName() . " Boat", $type));
		}
	}

	private function registerSpawnEggs() : void{
		self::register("zombie_spawn_egg", fn(IID $id) => new class($id, "Zombie Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Zombie(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		self::register("squid_spawn_egg", fn(IID $id) => new class($id, "Squid Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Squid(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
		self::register("villager_spawn_egg", fn(IID $id) => new class($id, "Villager Spawn Egg") extends SpawnEgg{
			protected function createEntity(World $world, Vector3 $pos, float $yaw, float $pitch) : Entity{
				return new Villager(Location::fromObject($pos, $world, $yaw, $pitch));
			}
		});
	}

	private function registerTierToolItems() : void{
		foreach([
			[ToolTier::COPPER, "copper", "Copper"],
			[ToolTier::DIAMOND, "diamond", "Diamond"],
			[ToolTier::GOLD, "golden", "Golden"],
			[ToolTier::IRON, "iron", "Iron"],
			[ToolTier::NETHERITE, "netherite", "Netherite"],
			[ToolTier::STONE, "stone", "Stone"],
			[ToolTier::WOOD, "wooden", "Wooden"]
		] as [$tier, $idPrefix, $namePrefix]){
			self::register($idPrefix . "_axe", fn(IID $id) => new Axe($id, $namePrefix . " Axe", $tier, [EnchantmentTags::AXE]));
			self::register($idPrefix . "_hoe", fn(IID $id) => new Hoe($id, $namePrefix . " Hoe", $tier, [EnchantmentTags::HOE]));
			self::register($idPrefix . "_pickaxe", fn(IID $id) => new Pickaxe($id, $namePrefix . " Pickaxe", $tier, [EnchantmentTags::PICKAXE]));
			self::register($idPrefix . "_shovel", fn(IID $id) => new Shovel($id, $namePrefix . " Shovel", $tier, [EnchantmentTags::SHOVEL]));
			self::register($idPrefix . "_sword", fn(IID $id) => new Sword($id, $namePrefix . " Sword", $tier, [EnchantmentTags::SWORD]));
		}
	}

	private function registerArmorItems() : void{
		self::registerDelayed("chainmail_boots", fn($name) : Armor => new Armor(self::makeIID($name), "Chainmail Boots", new ArmorTypeInfo(1, 196, ArmorInventory::SLOT_FEET, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::BOOTS]));
		self::registerDelayed("copper_boots", fn($name) : Armor => new Armor(self::makeIID($name), "Copper Boots", new ArmorTypeInfo(1, 144, ArmorInventory::SLOT_FEET, material: ArmorMaterials::COPPER()), [EnchantmentTags::BOOTS]));
		self::registerDelayed("diamond_boots", fn($name) : Armor => new Armor(self::makeIID($name), "Diamond Boots", new ArmorTypeInfo(3, 430, ArmorInventory::SLOT_FEET, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::BOOTS]));
		self::registerDelayed("golden_boots", fn($name) : Armor => new Armor(self::makeIID($name), "Golden Boots", new ArmorTypeInfo(1, 92, ArmorInventory::SLOT_FEET, material: ArmorMaterials::GOLD()), [EnchantmentTags::BOOTS]));
		self::registerDelayed("iron_boots", fn($name) : Armor => new Armor(self::makeIID($name), "Iron Boots", new ArmorTypeInfo(2, 196, ArmorInventory::SLOT_FEET, material: ArmorMaterials::IRON()), [EnchantmentTags::BOOTS]));
		self::registerDelayed("leather_boots", fn($name) : Armor => new Armor(self::makeIID($name), "Leather Boots", new ArmorTypeInfo(1, 66, ArmorInventory::SLOT_FEET, material: ArmorMaterials::LEATHER()), [EnchantmentTags::BOOTS]));
		self::registerDelayed("netherite_boots", fn($name) : Armor => new Armor(self::makeIID($name), "Netherite Boots", new ArmorTypeInfo(3, 482, ArmorInventory::SLOT_FEET, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::BOOTS]));

		self::registerDelayed("chainmail_chestplate", fn($name) : Armor => new Armor(self::makeIID($name), "Chainmail Chestplate", new ArmorTypeInfo(5, 241, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::CHESTPLATE]));
		self::registerDelayed("copper_chestplate", fn($name) : Armor => new Armor(self::makeIID($name), "Copper Chestplate", new ArmorTypeInfo(4, 177, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::COPPER()), [EnchantmentTags::CHESTPLATE]));
		self::registerDelayed("diamond_chestplate", fn($name) : Armor => new Armor(self::makeIID($name), "Diamond Chestplate", new ArmorTypeInfo(8, 529, ArmorInventory::SLOT_CHEST, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::CHESTPLATE]));
		self::registerDelayed("golden_chestplate", fn($name) : Armor => new Armor(self::makeIID($name), "Golden Chestplate", new ArmorTypeInfo(5, 113, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::GOLD()), [EnchantmentTags::CHESTPLATE]));
		self::registerDelayed("iron_chestplate", fn($name) : Armor => new Armor(self::makeIID($name), "Iron Chestplate", new ArmorTypeInfo(6, 241, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::IRON()), [EnchantmentTags::CHESTPLATE]));
		self::registerDelayed("leather_tunic", fn($name) : Armor => new Armor(self::makeIID($name), "Leather Tunic", new ArmorTypeInfo(3, 81, ArmorInventory::SLOT_CHEST, material: ArmorMaterials::LEATHER()), [EnchantmentTags::CHESTPLATE]));
		self::registerDelayed("netherite_chestplate", fn($name) : Armor => new Armor(self::makeIID($name), "Netherite Chestplate", new ArmorTypeInfo(8, 593, ArmorInventory::SLOT_CHEST, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::CHESTPLATE]));

		self::registerDelayed("chainmail_helmet", fn($name) : Armor => new Armor(self::makeIID($name), "Chainmail Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::HELMET]));
		self::registerDelayed("copper_helmet", fn($name) : Armor => new Armor(self::makeIID($name), "Copper Helmet", new ArmorTypeInfo(2, 122, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::COPPER()), [EnchantmentTags::HELMET]));
		self::registerDelayed("diamond_helmet", fn($name) : Armor => new Armor(self::makeIID($name), "Diamond Helmet", new ArmorTypeInfo(3, 364, ArmorInventory::SLOT_HEAD, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::HELMET]));
		self::registerDelayed("golden_helmet", fn($name) : Armor => new Armor(self::makeIID($name), "Golden Helmet", new ArmorTypeInfo(2, 78, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::GOLD()), [EnchantmentTags::HELMET]));
		self::registerDelayed("iron_helmet", fn($name) : Armor => new Armor(self::makeIID($name), "Iron Helmet", new ArmorTypeInfo(2, 166, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::IRON()), [EnchantmentTags::HELMET]));
		self::registerDelayed("leather_cap", fn($name) : Armor => new Armor(self::makeIID($name), "Leather Cap", new ArmorTypeInfo(1, 56, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::LEATHER()), [EnchantmentTags::HELMET]));
		self::registerDelayed("netherite_helmet", fn($name) : Armor => new Armor(self::makeIID($name), "Netherite Helmet", new ArmorTypeInfo(3, 408, ArmorInventory::SLOT_HEAD, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::HELMET]));
		self::registerDelayed("turtle_helmet", fn($name) : TurtleHelmet => new TurtleHelmet(self::makeIID($name), "Turtle Shell", new ArmorTypeInfo(2, 276, ArmorInventory::SLOT_HEAD, material: ArmorMaterials::TURTLE()), [EnchantmentTags::HELMET]));

		self::registerDelayed("chainmail_leggings", fn($name) : Armor => new Armor(self::makeIID($name), "Chainmail Leggings", new ArmorTypeInfo(4, 226, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::CHAINMAIL()), [EnchantmentTags::LEGGINGS]));
		self::registerDelayed("copper_leggings", fn($name) : Armor => new Armor(self::makeIID($name), "Copper Leggings", new ArmorTypeInfo(3, 166, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::COPPER()), [EnchantmentTags::LEGGINGS]));
		self::registerDelayed("diamond_leggings", fn($name) : Armor => new Armor(self::makeIID($name), "Diamond Leggings", new ArmorTypeInfo(6, 496, ArmorInventory::SLOT_LEGS, 2, material: ArmorMaterials::DIAMOND()), [EnchantmentTags::LEGGINGS]));
		self::registerDelayed("golden_leggings", fn($name) : Armor => new Armor(self::makeIID($name), "Golden Leggings", new ArmorTypeInfo(3, 106, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::GOLD()), [EnchantmentTags::LEGGINGS]));
		self::registerDelayed("iron_leggings", fn($name) : Armor => new Armor(self::makeIID($name), "Iron Leggings", new ArmorTypeInfo(5, 226, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::IRON()), [EnchantmentTags::LEGGINGS]));
		self::registerDelayed("leather_pants", fn($name) : Armor => new Armor(self::makeIID($name), "Leather Pants", new ArmorTypeInfo(2, 76, ArmorInventory::SLOT_LEGS, material: ArmorMaterials::LEATHER()), [EnchantmentTags::LEGGINGS]));
		self::registerDelayed("netherite_leggings", fn($name) : Armor => new Armor(self::makeIID($name), "Netherite Leggings", new ArmorTypeInfo(6, 556, ArmorInventory::SLOT_LEGS, 3, true, material: ArmorMaterials::NETHERITE()), [EnchantmentTags::LEGGINGS]));
	}

	private function registerSmithingTemplates() : void{
		self::register("netherite_upgrade_smithing_template", fn(IID $id) => new Item($id, "Netherite Upgrade Smithing Template"));
		self::register("coast_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Coast Armor Trim Smithing Template"));
		self::register("dune_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Dune Armor Trim Smithing Template"));
		self::register("eye_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Eye Armor Trim Smithing Template"));
		self::register("host_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Host Armor Trim Smithing Template"));
		self::register("raiser_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Raiser Armor Trim Smithing Template"));
		self::register("rib_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Rib Armor Trim Smithing Template"));
		self::register("sentry_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Sentry Armor Trim Smithing Template"));
		self::register("shaper_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Shaper Armor Trim Smithing Template"));
		self::register("silence_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Silence Armor Trim Smithing Template"));
		self::register("snout_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Snout Armor Trim Smithing Template"));
		self::register("spire_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Spire Armor Trim Smithing Template"));
		self::register("tide_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Tide Armor Trim Smithing Template"));
		self::register("vex_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Vex Armor Trim Smithing Template"));
		self::register("ward_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Ward Armor Trim Smithing Template"));
		self::register("wayfinder_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Wayfinder Armor Trim Smithing Template"));
		self::register("wild_armor_trim_smithing_template", fn(IID $id) => new Item($id, "Wild Armor Trim Smithing Template"));
	}

}
