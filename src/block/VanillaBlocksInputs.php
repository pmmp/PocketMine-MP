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

namespace pocketmine\block;

use pocketmine\block\BlockBreakInfo as BreakInfo;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockToolType as ToolType;
use pocketmine\block\BlockTypeInfo as Info;
use pocketmine\block\BlockTypeTags as Tags;
use pocketmine\block\tile\Banner as TileBanner;
use pocketmine\block\tile\Barrel as TileBarrel;
use pocketmine\block\tile\Beacon as TileBeacon;
use pocketmine\block\tile\Bed as TileBed;
use pocketmine\block\tile\Bell as TileBell;
use pocketmine\block\tile\BlastFurnace as TileBlastFurnace;
use pocketmine\block\tile\BrewingStand as TileBrewingStand;
use pocketmine\block\tile\Campfire as TileCampfire;
use pocketmine\block\tile\Cauldron as TileCauldron;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\block\tile\ChiseledBookshelf as TileChiseledBookshelf;
use pocketmine\block\tile\Comparator as TileComparator;
use pocketmine\block\tile\DaylightSensor as TileDaylightSensor;
use pocketmine\block\tile\EnchantTable as TileEnchantingTable;
use pocketmine\block\tile\EnderChest as TileEnderChest;
use pocketmine\block\tile\FlowerPot as TileFlowerPot;
use pocketmine\block\tile\GlowingItemFrame as TileGlowingItemFrame;
use pocketmine\block\tile\HangingSign as TileHangingSign;
use pocketmine\block\tile\Hopper as TileHopper;
use pocketmine\block\tile\ItemFrame as TileItemFrame;
use pocketmine\block\tile\Jukebox as TileJukebox;
use pocketmine\block\tile\Lectern as TileLectern;
use pocketmine\block\tile\MobHead as TileMobHead;
use pocketmine\block\tile\MonsterSpawner as TileMonsterSpawner;
use pocketmine\block\tile\NormalFurnace as TileNormalFurnace;
use pocketmine\block\tile\Note as TileNote;
use pocketmine\block\tile\ShulkerBox as TileShulkerBox;
use pocketmine\block\tile\Sign as TileSign;
use pocketmine\block\tile\Smoker as TileSmoker;
use pocketmine\block\tile\Tile;
use pocketmine\block\utils\AmethystTrait;
use pocketmine\block\utils\LeavesType;
use pocketmine\block\utils\SaplingType;
use pocketmine\block\utils\WoodType;
use pocketmine\crafting\FurnaceType;
use pocketmine\item\enchantment\ItemEnchantmentTags as EnchantmentTags;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\utils\RegistrySource;
use pocketmine\world\generator\object\TreeType;
use function is_int;
use function mb_strtolower;
use function mb_strtoupper;
use function strtolower;

/**
 * Input class for generating {@link VanillaBlocks}
 * All vanilla blocks are registered here for binding in the generated class.
 *
 * @internal
 * @phpstan-extends RegistrySource<Block>
 */
final class VanillaBlocksInputs extends RegistrySource{

	public function getTargetClassName() : string{
		return "VanillaBlocks";
	}

	public function getTargetClassDocComment() : array{
		return [
			"Allows getting a new instance of any block implemented by PocketMine-MP",
			"Every block here also has a constant of the same name in {@link BlockTypeIds} to enable blocks to be identified"
		];
	}

	public function cloneResults() : bool{ return true; }

	/**
	 * @phpstan-param class-string<covariant Tile> $tileClass
	 */
	private static function makeBID(string $name, ?string $tileClass = null) : BID{
		//this sketchy hack allows us to avoid manually writing the constants inline
		//since type IDs are generated from this class anyway, I'm OK with this hack
		//nonetheless, we should try to get rid of it in a future major version (e.g by using string type IDs)
		$reflect = new \ReflectionClass(BlockTypeIds::class);
		$typeId = $reflect->getConstant(mb_strtoupper($name));
		if(!is_int($typeId)){
			//this allows registering new stuff without adding new type ID constants
			//this reduces the number of mandatory steps to test new features in local development
			\GlobalLogger::get()->error(self::class . ": No constant type ID found for $name, generating a new one");
			$typeId = BlockTypeIds::newId();
		}
		return new BID($typeId, $tileClass);
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-param \Closure(BID) : TBlock $createBlock
	 * @phpstan-param class-string<covariant Tile> $tileClass
	 * @phpstan-return TBlock
	 */
	private function register(string $name, \Closure $createBlock, ?string $tileClass = null) : Block{
		$block = $createBlock(self::makeBID($name, $tileClass));
		self::registerValue($name, $block);

		return $block;
	}

	protected function setup() : void{
		self::register("air", fn(BID $id) => new Air($id, "Air", new Info(BreakInfo::indestructible(-1.0))));

		$railBreakInfo = new Info(new BreakInfo(0.7));
		self::register("activator_rail", fn(BID $id) => new ActivatorRail($id, "Activator Rail", $railBreakInfo));
		self::register("anvil", fn(BID $id) => new Anvil($id, "Anvil", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 6000.0))));
		self::register("azalea", fn(BID $id) => new Azalea($id, "Azalea", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])));
		self::register("flowering_azalea", fn(BID $id) => new Azalea($id, "Flowering Azalea", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])));
		self::register("bamboo", fn(BID $id) => new Bamboo($id, "Bamboo", new Info(new class(1.0, ToolType::AXE) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SWORD){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		}, [Tags::POTTABLE_PLANTS])));
		self::register("bamboo_sapling", fn(BID $id) => new BambooSapling($id, "Bamboo Sapling", new Info(new BreakInfo(1.0))));

		$bannerBreakInfo = new Info(BreakInfo::axe(1.0));
		self::register("banner", fn(BID $id) => new FloorBanner($id, "Banner", $bannerBreakInfo), TileBanner::class);
		self::register("wall_banner", fn(BID $id) => new WallBanner($id, "Wall Banner", $bannerBreakInfo), TileBanner::class);
		self::register("ominous_banner", fn(BID $id) => new OminousFloorBanner($id, "Ominous Banner", $bannerBreakInfo), TileBanner::class);
		self::register("ominous_wall_banner", fn(BID $id) => new OminousWallBanner($id, "Ominous Wall Banner", $bannerBreakInfo), TileBanner::class);
		self::register("barrel", fn(BID $id) => new Barrel($id, "Barrel", new Info(BreakInfo::axe(2.5))), TileBarrel::class);
		self::register("barrier", fn(BID $id) => new Transparent($id, "Barrier", new Info(BreakInfo::indestructible())));
		self::register("beacon", fn(BID $id) => new Beacon($id, "Beacon", new Info(new BreakInfo(3.0))), TileBeacon::class);
		self::register("bed", fn(BID $id) => new Bed($id, "Bed Block", new Info(new BreakInfo(0.2))), TileBed::class);
		self::register("bedrock", fn(BID $id) => new Bedrock($id, "Bedrock", new Info(BreakInfo::indestructible(18000000.0))));

		self::register("beetroots", fn(BID $id) => new Beetroot($id, "Beetroot Block", new Info(BreakInfo::instant())));
		self::register("bell", fn(BID $id) => new Bell($id, "Bell", new Info(BreakInfo::pickaxe(5.0))), TileBell::class);
		self::register("blue_ice", fn(BID $id) => new BlueIce($id, "Blue Ice", new Info(BreakInfo::pickaxe(2.8))));
		self::register("bone_block", fn(BID $id) => new BoneBlock($id, "Bone Block", new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD))));
		self::register("bookshelf", fn(BID $id) => new Bookshelf($id, "Bookshelf", new Info(BreakInfo::axe(1.5))));
		self::register("chiseled_bookshelf", fn(BID $id) => new ChiseledBookshelf($id, "Chiseled Bookshelf", new Info(BreakInfo::axe(1.5))), TileChiseledBookshelf::class);
		self::register("brewing_stand", fn(BID $id) => new BrewingStand($id, "Brewing Stand", new Info(BreakInfo::pickaxe(0.5))), TileBrewingStand::class);

		$bricksBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("brick_stairs", fn(BID $id) => new Stair($id, "Brick Stairs", $bricksBreakInfo));
		self::register("bricks", fn(BID $id) => new Opaque($id, "Bricks", $bricksBreakInfo));

		self::register("brown_mushroom", fn(BID $id) => new BrownMushroom($id, "Brown Mushroom", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])));
		self::register("cactus", fn(BID $id) => new Cactus($id, "Cactus", new Info(new BreakInfo(0.4), [Tags::POTTABLE_PLANTS])));
		self::register("cake", fn(BID $id) => new Cake($id, "Cake", new Info(new BreakInfo(0.5))));

		$campfireBreakInfo = new Info(BreakInfo::axe(2.0));
		self::register("campfire", fn(BID $id) => new Campfire($id, "Campfire", $campfireBreakInfo), TileCampfire::class);
		self::register("soul_campfire", fn(BID $id) => new SoulCampfire($id, "Soul Campfire", $campfireBreakInfo), TileCampfire::class);

		self::register("carrots", fn(BID $id) => new Carrot($id, "Carrot Block", new Info(BreakInfo::instant())));

		$chestBreakInfo = new Info(BreakInfo::axe(2.5));
		self::register("chest", fn(BID $id) => new Chest($id, "Chest", $chestBreakInfo), TileChest::class);
		self::register("clay", fn(BID $id) => new Clay($id, "Clay Block", new Info(BreakInfo::shovel(0.6))));
		self::register("coal", fn(BID $id) => new Coal($id, "Coal Block", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 30.0))));

		$cobblestoneBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		$cobblestone = self::register("cobblestone", fn(BID $id) => new Opaque($id, "Cobblestone", $cobblestoneBreakInfo));
		self::register("mossy_cobblestone", fn(BID $id) => new Opaque($id, "Mossy Cobblestone", $cobblestoneBreakInfo));
		self::register("cobblestone_stairs", fn(BID $id) => new Stair($id, "Cobblestone Stairs", $cobblestoneBreakInfo));
		self::register("mossy_cobblestone_stairs", fn(BID $id) => new Stair($id, "Mossy Cobblestone Stairs", $cobblestoneBreakInfo));

		self::register("cobweb", fn(BID $id) => new Cobweb($id, "Cobweb", new Info(new BreakInfo(4.0, ToolType::SWORD | ToolType::SHEARS, 1))));
		self::register("cocoa_pod", fn(BID $id) => new CocoaBlock($id, "Cocoa Block", new Info(BreakInfo::axe(0.2, null, 15.0))));
		self::register("coral_block", fn(BID $id) => new CoralBlock($id, "Coral Block", new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0))));
		self::register("daylight_sensor", fn(BID $id) => new DaylightSensor($id, "Daylight Sensor", new Info(BreakInfo::axe(0.2))), TileDaylightSensor::class);
		self::register("dead_bush", fn(BID $id) => new DeadBush($id, "Dead Bush", new Info(BreakInfo::instant(ToolType::SHEARS, 1), [Tags::POTTABLE_PLANTS])));
		self::register("detector_rail", fn(BID $id) => new DetectorRail($id, "Detector Rail", $railBreakInfo));

		self::register("diamond", fn(BID $id) => new Opaque($id, "Diamond Block", new Info(BreakInfo::pickaxe(5.0, ToolTier::IRON, 30.0))));
		self::register("dirt", fn(BID $id) => new Dirt($id, "Dirt", new Info(BreakInfo::shovel(0.5), [Tags::DIRT])));
		self::register("sunflower", fn(BID $id) => new DoublePlant($id, "Sunflower", new Info(BreakInfo::instant())));
		self::register("lilac", fn(BID $id) => new DoublePlant($id, "Lilac", new Info(BreakInfo::instant())));
		self::register("rose_bush", fn(BID $id) => new DoublePlant($id, "Rose Bush", new Info(BreakInfo::instant())));
		self::register("peony", fn(BID $id) => new DoublePlant($id, "Peony", new Info(BreakInfo::instant())));
		self::register("pink_petals", fn(BID $id) => new PinkPetals($id, "Pink Petals", new Info(BreakInfo::instant())));
		self::register("double_tallgrass", fn(BID $id) => new DoubleTallGrass($id, "Double Tallgrass", new Info(BreakInfo::instant(ToolType::SHEARS, 1))));
		self::register("large_fern", fn(BID $id) => new DoubleTallGrass($id, "Large Fern", new Info(BreakInfo::instant(ToolType::SHEARS, 1))));
		self::register("pitcher_plant", fn(BID $id) => new DoublePlant($id, "Pitcher Plant", new Info(BreakInfo::instant())));
		self::register("pitcher_crop", fn(BID $id) => new PitcherCrop($id, "Pitcher Crop", new Info(BreakInfo::instant())));
		self::register("double_pitcher_crop", fn(BID $id) => new DoublePitcherCrop($id, "Double Pitcher Crop", new Info(BreakInfo::instant())));
		self::register("dragon_egg", fn(BID $id) => new DragonEgg($id, "Dragon Egg", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD, blastResistance: 45.0))));
		self::register("dried_kelp", fn(BID $id) => new DriedKelp($id, "Dried Kelp Block", new Info(new BreakInfo(0.5, ToolType::NONE, 0, 12.5))));
		self::register("emerald", fn(BID $id) => new Opaque($id, "Emerald Block", new Info(BreakInfo::pickaxe(5.0, ToolTier::IRON, 30.0))));
		self::register("enchanting_table", fn(BID $id) => new EnchantingTable($id, "Enchanting Table", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 6000.0))), TileEnchantingTable::class);
		self::register("end_portal_frame", fn(BID $id) => new EndPortalFrame($id, "End Portal Frame", new Info(BreakInfo::indestructible(18000000.0))));
		self::register("end_rod", fn(BID $id) => new EndRod($id, "End Rod", new Info(BreakInfo::instant())));
		self::register("end_stone", fn(BID $id) => new Opaque($id, "End Stone", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD, 45.0))));

		$endBrickBreakInfo = new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD, 45.0));
		self::register("end_stone_bricks", fn(BID $id) => new Opaque($id, "End Stone Bricks", $endBrickBreakInfo));
		self::register("end_stone_brick_stairs", fn(BID $id) => new Stair($id, "End Stone Brick Stairs", $endBrickBreakInfo));

		self::register("ender_chest", fn(BID $id) => new EnderChest($id, "Ender Chest", new Info(BreakInfo::pickaxe(22.5, blastResistance: 3000.0))), TileEnderChest::class);
		self::register("farmland", fn(BID $id) => new Farmland($id, "Farmland", new Info(BreakInfo::shovel(0.6), [Tags::DIRT])));
		self::register("fire", fn(BID $id) => new Fire($id, "Fire Block", new Info(BreakInfo::instant(), [Tags::FIRE])));

		$flowerTypeInfo = new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS]);
		self::register("dandelion", fn(BID $id) => new Flower($id, "Dandelion", $flowerTypeInfo));
		self::register("poppy", fn(BID $id) => new Flower($id, "Poppy", $flowerTypeInfo));
		self::register("allium", fn(BID $id) => new Flower($id, "Allium", $flowerTypeInfo));
		self::register("azure_bluet", fn(BID $id) => new Flower($id, "Azure Bluet", $flowerTypeInfo));
		self::register("blue_orchid", fn(BID $id) => new Flower($id, "Blue Orchid", $flowerTypeInfo));
		self::register("cornflower", fn(BID $id) => new Flower($id, "Cornflower", $flowerTypeInfo));
		self::register("lily_of_the_valley", fn(BID $id) => new Flower($id, "Lily of the Valley", $flowerTypeInfo));
		self::register("orange_tulip", fn(BID $id) => new Flower($id, "Orange Tulip", $flowerTypeInfo));
		self::register("oxeye_daisy", fn(BID $id) => new Flower($id, "Oxeye Daisy", $flowerTypeInfo));
		self::register("pink_tulip", fn(BID $id) => new Flower($id, "Pink Tulip", $flowerTypeInfo));
		self::register("red_tulip", fn(BID $id) => new Flower($id, "Red Tulip", $flowerTypeInfo));
		self::register("white_tulip", fn(BID $id) => new Flower($id, "White Tulip", $flowerTypeInfo));
		self::register("torchflower", fn(BID $id) => new Flower($id, "Torchflower", $flowerTypeInfo));
		self::register("torchflower_crop", fn(BID $id) => new TorchflowerCrop($id, "Torchflower Crop", new Info(BreakInfo::instant())));
		self::register("flower_pot", fn(BID $id) => new FlowerPot($id, "Flower Pot", new Info(BreakInfo::instant())), TileFlowerPot::class);
		self::register("frosted_ice", fn(BID $id) => new FrostedIce($id, "Frosted Ice", new Info(BreakInfo::pickaxe(0.5))));
		self::register("furnace", fn(BID $id) => new Furnace($id, "Furnace", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD)), FurnaceType::FURNACE), TileNormalFurnace::class);
		self::register("blast_furnace", fn(BID $id) => new Furnace($id, "Blast Furnace", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD)), FurnaceType::BLAST_FURNACE), TileBlastFurnace::class);
		self::register("smoker", fn(BID $id) => new Furnace($id, "Smoker", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD)), FurnaceType::SMOKER), TileSmoker::class);

		$glassBreakInfo = new Info(new BreakInfo(0.3));
		self::register("glass", fn(BID $id) => new Glass($id, "Glass", $glassBreakInfo));
		self::register("glass_pane", fn(BID $id) => new GlassPane($id, "Glass Pane", $glassBreakInfo));
		self::register("glowing_obsidian", fn(BID $id) => new GlowingObsidian($id, "Glowing Obsidian", new Info(BreakInfo::pickaxe(35.0, ToolTier::DIAMOND, 6000.0))));
		self::register("glowstone", fn(BID $id) => new Glowstone($id, "Glowstone", new Info(BreakInfo::pickaxe(0.3))));
		self::register("glow_lichen", fn(BID $id) => new GlowLichen($id, "Glow Lichen", new Info(BreakInfo::axe(0.2))));
		self::register("gold", fn(BID $id) => new Opaque($id, "Gold Block", new Info(BreakInfo::pickaxe(3.0, ToolTier::IRON, 30.0))));

		self::register("grass", fn(BID $id) => new Grass($id, "Grass", new Info(BreakInfo::shovel(0.6), [Tags::DIRT])));
		self::register("grass_path", fn(BID $id) => new GrassPath($id, "Grass Path", new Info(BreakInfo::shovel(0.65))));
		self::register("gravel", fn(BID $id) => new Gravel($id, "Gravel", new Info(BreakInfo::shovel(0.6))));

		self::register("hardened_clay", fn(BID $id) => new HardenedClay($id, "Hardened Clay", new Info(BreakInfo::pickaxe(1.25, ToolTier::WOOD, 21.0))));

		$hardenedGlassBreakInfo = new Info(new BreakInfo(10.0));
		self::register("hardened_glass", fn(BID $id) => new HardenedGlass($id, "Hardened Glass", $hardenedGlassBreakInfo));
		self::register("hardened_glass_pane", fn(BID $id) => new HardenedGlassPane($id, "Hardened Glass Pane", $hardenedGlassBreakInfo));
		self::register("hay_bale", fn(BID $id) => new HayBale($id, "Hay Bale", new Info(new BreakInfo(0.5))));
		self::register("hopper", fn(BID $id) => new Hopper($id, "Hopper", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD, 24.0))), TileHopper::class);
		self::register("ice", fn(BID $id) => new Ice($id, "Ice", new Info(BreakInfo::pickaxe(0.5))));

		$updateBlockBreakInfo = new Info(new BreakInfo(1.0));
		self::register("info_update", fn(BID $id) => new Opaque($id, "update!", $updateBlockBreakInfo));
		self::register("info_update2", fn(BID $id) => new Opaque($id, "ate!upd", $updateBlockBreakInfo));
		self::register("invisible_bedrock", fn(BID $id) => new Transparent($id, "Invisible Bedrock", new Info(BreakInfo::indestructible(18000000.0))));

		$ironBreakInfo = new Info(BreakInfo::pickaxe(5.0, ToolTier::STONE, 30.0));
		self::register("iron", fn(BID $id) => new Opaque($id, "Iron Block", $ironBreakInfo));
		self::register("iron_bars", fn(BID $id) => new Thin($id, "Iron Bars", $ironBreakInfo));
		self::register("copper_bars", fn(BID $id) => new CopperBars($id, "Copper Bars", $ironBreakInfo));

		self::register("iron_door", fn(BID $id) => new Door($id, "Iron Door", new Info(BreakInfo::pickaxe(5.0))));
		self::register("iron_trapdoor", fn(BID $id) => new Trapdoor($id, "Iron Trapdoor", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD))));

		$itemFrameInfo = new Info(new BreakInfo(0.25));
		self::register("item_frame", fn(BID $id) => new ItemFrame($id, "Item Frame", $itemFrameInfo), TileItemFrame::class);
		self::register("glowing_item_frame", fn(BID $id) => new ItemFrame($id, "Glow Item Frame", $itemFrameInfo), TileGlowingItemFrame::class);

		self::register("jukebox", fn(BID $id) => new Jukebox($id, "Jukebox", new Info(BreakInfo::axe(2.0, blastResistance: 30.0))), TileJukebox::class);
		self::register("ladder", fn(BID $id) => new Ladder($id, "Ladder", new Info(BreakInfo::axe(0.4))));

		$lanternBreakInfo = new Info(BreakInfo::pickaxe(3.5));
		self::register("lantern", fn(BID $id) => new Lantern($id, "Lantern", $lanternBreakInfo, 15));
		self::register("soul_lantern", fn(BID $id) => new Lantern($id, "Soul Lantern", $lanternBreakInfo, 10));
		self::register("copper_lantern", fn(BID $id) => new CopperLantern($id, "Copper Lantern", $lanternBreakInfo, 15));

		self::register("lapis_lazuli", fn(BID $id) => new Opaque($id, "Lapis Lazuli Block", new Info(BreakInfo::pickaxe(3.0, ToolTier::STONE))));
		self::register("lava", fn(BID $id) => new Lava($id, "Lava", new Info(BreakInfo::indestructible(500.0))));
		self::register("lectern", fn(BID $id) => new Lectern($id, "Lectern", new Info(BreakInfo::axe(2.5))), TileLectern::class);
		self::register("lever", fn(BID $id) => new Lever($id, "Lever", new Info(new BreakInfo(0.5))));
		self::register("magma", fn(BID $id) => new Magma($id, "Magma Block", new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD))));
		self::register("melon", fn(BID $id) => new Melon($id, "Melon Block", new Info(BreakInfo::axe(1.0))));
		self::register("melon_stem", fn(BID $id) => new MelonStem($id, "Melon Stem", new Info(BreakInfo::instant())));
		self::register("monster_spawner", fn(BID $id) => new MonsterSpawner($id, "Monster Spawner", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD))), TileMonsterSpawner::class);
		self::register("mycelium", fn(BID $id) => new Mycelium($id, "Mycelium", new Info(BreakInfo::shovel(0.6), [Tags::DIRT])));

		$netherBrickBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("nether_bricks", fn(BID $id) => new Opaque($id, "Nether Bricks", $netherBrickBreakInfo));
		self::register("red_nether_bricks", fn(BID $id) => new Opaque($id, "Red Nether Bricks", $netherBrickBreakInfo));
		self::register("nether_brick_fence", fn(BID $id) => new Fence($id, "Nether Brick Fence", $netherBrickBreakInfo));
		self::register("nether_brick_stairs", fn(BID $id) => new Stair($id, "Nether Brick Stairs", $netherBrickBreakInfo));
		self::register("red_nether_brick_stairs", fn(BID $id) => new Stair($id, "Red Nether Brick Stairs", $netherBrickBreakInfo));
		self::register("chiseled_nether_bricks", fn(BID $id) => new Opaque($id, "Chiseled Nether Bricks", $netherBrickBreakInfo));
		self::register("cracked_nether_bricks", fn(BID $id) => new Opaque($id, "Cracked Nether Bricks", $netherBrickBreakInfo));

		self::register("nether_portal", fn(BID $id) => new NetherPortal($id, "Nether Portal", new Info(BreakInfo::indestructible(0.0))));
		self::register("nether_reactor_core", fn(BID $id) => new NetherReactor($id, "Nether Reactor Core", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD))));
		self::register("nether_wart_block", fn(BID $id) => new Opaque($id, "Nether Wart Block", new Info(new BreakInfo(1.0, ToolType::HOE))));
		self::register("nether_wart", fn(BID $id) => new NetherWartPlant($id, "Nether Wart", new Info(BreakInfo::instant())));
		self::register("netherrack", fn(BID $id) => new Netherrack($id, "Netherrack", new Info(BreakInfo::pickaxe(0.4, ToolTier::WOOD))));
		self::register("note_block", fn(BID $id) => new Note($id, "Note Block", new Info(BreakInfo::axe(0.8))), TileNote::class);
		self::register("obsidian", fn(BID $id) => new Opaque($id, "Obsidian", new Info(BreakInfo::pickaxe(35.0 /* 50 in PC */,  ToolTier::DIAMOND, 6000.0))));
		self::register("packed_ice", fn(BID $id) => new PackedIce($id, "Packed Ice", new Info(BreakInfo::pickaxe(0.5))));
		self::register("podzol", fn(BID $id) => new Podzol($id, "Podzol", new Info(BreakInfo::shovel(0.5), [Tags::DIRT])));
		self::register("potatoes", fn(BID $id) => new Potato($id, "Potato Block", new Info(BreakInfo::instant())));
		self::register("powered_rail", fn(BID $id) => new PoweredRail($id, "Powered Rail", $railBreakInfo));

		$prismarineBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("prismarine", fn(BID $id) => new Opaque($id, "Prismarine", $prismarineBreakInfo));
		self::register("dark_prismarine", fn(BID $id) => new Opaque($id, "Dark Prismarine", $prismarineBreakInfo));
		self::register("prismarine_bricks", fn(BID $id) => new Opaque($id, "Prismarine Bricks", $prismarineBreakInfo));
		self::register("prismarine_bricks_stairs", fn(BID $id) => new Stair($id, "Prismarine Bricks Stairs", $prismarineBreakInfo));
		self::register("dark_prismarine_stairs", fn(BID $id) => new Stair($id, "Dark Prismarine Stairs", $prismarineBreakInfo));
		self::register("prismarine_stairs", fn(BID $id) => new Stair($id, "Prismarine Stairs", $prismarineBreakInfo));

		$pumpkinBreakInfo = new Info(BreakInfo::axe(1.0));
		self::register("pumpkin", fn(BID $id) => new Pumpkin($id, "Pumpkin", $pumpkinBreakInfo));
		self::register("carved_pumpkin", fn(BID $id) => new CarvedPumpkin($id, "Carved Pumpkin", new Info(BreakInfo::axe(1.0), enchantmentTags: [EnchantmentTags::MASK])));
		self::register("lit_pumpkin", fn(BID $id) => new LitPumpkin($id, "Jack o'Lantern", $pumpkinBreakInfo));

		self::register("pumpkin_stem", fn(BID $id) => new PumpkinStem($id, "Pumpkin Stem", new Info(BreakInfo::instant())));

		$purpurBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("purpur", fn(BID $id) => new Opaque($id, "Purpur Block", $purpurBreakInfo));
		self::register("purpur_pillar", fn(BID $id) => new SimplePillar($id, "Purpur Pillar", $purpurBreakInfo));
		self::register("purpur_stairs", fn(BID $id) => new Stair($id, "Purpur Stairs", $purpurBreakInfo));

		$quartzBreakInfo = new Info(BreakInfo::pickaxe(0.8, ToolTier::WOOD));
		$smoothQuartzBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("quartz", fn(BID $id) => new Opaque($id, "Quartz Block", $quartzBreakInfo));
		self::register("chiseled_quartz", fn(BID $id) => new SimplePillar($id, "Chiseled Quartz Block", $quartzBreakInfo));
		self::register("quartz_pillar", fn(BID $id) => new SimplePillar($id, "Quartz Pillar", $quartzBreakInfo));
		self::register("smooth_quartz", fn(BID $id) => new Opaque($id, "Smooth Quartz Block", $smoothQuartzBreakInfo));
		self::register("quartz_bricks", fn(BID $id) => new Opaque($id, "Quartz Bricks", $quartzBreakInfo));

		self::register("quartz_stairs", fn(BID $id) => new Stair($id, "Quartz Stairs", $quartzBreakInfo));
		self::register("smooth_quartz_stairs", fn(BID $id) => new Stair($id, "Smooth Quartz Stairs", $smoothQuartzBreakInfo));

		self::register("rail", fn(BID $id) => new Rail($id, "Rail", $railBreakInfo));
		self::register("red_mushroom", fn(BID $id) => new RedMushroom($id, "Red Mushroom", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])));
		self::register("redstone", fn(BID $id) => new Redstone($id, "Redstone Block", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 30.0))));
		self::register("redstone_comparator", fn(BID $id) => new RedstoneComparator($id, "Redstone Comparator", new Info(BreakInfo::instant())), TileComparator::class);
		self::register("redstone_lamp", fn(BID $id) => new RedstoneLamp($id, "Redstone Lamp", new Info(new BreakInfo(0.3))));
		self::register("redstone_repeater", fn(BID $id) => new RedstoneRepeater($id, "Redstone Repeater", new Info(BreakInfo::instant())));
		self::register("redstone_torch", fn(BID $id) => new RedstoneTorch($id, "Redstone Torch", new Info(BreakInfo::instant())));
		self::register("redstone_wire", fn(BID $id) => new RedstoneWire($id, "Redstone", new Info(BreakInfo::instant())));
		self::register("reserved6", fn(BID $id) => new Reserved6($id, "reserved6", new Info(BreakInfo::instant())));

		$sandTypeInfo = new Info(BreakInfo::shovel(0.5), [Tags::SAND]);
		self::register("sand", fn(BID $id) => new Sand($id, "Sand", $sandTypeInfo));
		self::register("red_sand", fn(BID $id) => new Sand($id, "Red Sand", $sandTypeInfo));

		self::register("sea_lantern", fn(BID $id) => new SeaLantern($id, "Sea Lantern", new Info(new BreakInfo(0.3))));
		self::register("sea_pickle", fn(BID $id) => new SeaPickle($id, "Sea Pickle", new Info(BreakInfo::instant())));
		self::register("mob_head", fn(BID $id) => new MobHead($id, "Mob Head", new Info(new BreakInfo(1.0), enchantmentTags: [EnchantmentTags::MASK])), TileMobHead::class);
		self::register("slime", fn(BID $id) => new Slime($id, "Slime Block", new Info(BreakInfo::instant())));
		self::register("snow", fn(BID $id) => new Snow($id, "Snow Block", new Info(BreakInfo::shovel(0.2, ToolTier::WOOD))));
		self::register("snow_layer", fn(BID $id) => new SnowLayer($id, "Snow Layer", new Info(BreakInfo::shovel(0.1, ToolTier::WOOD))));
		self::register("soul_sand", fn(BID $id) => new SoulSand($id, "Soul Sand", new Info(BreakInfo::shovel(0.5))));
		self::register("sponge", fn(BID $id) => new Sponge($id, "Sponge", new Info(new BreakInfo(0.6, ToolType::HOE))));
		$shulkerBoxBreakInfo = new Info(BreakInfo::pickaxe(2));
		self::register("shulker_box", fn(BID $id) => new ShulkerBox($id, "Shulker Box", $shulkerBoxBreakInfo), TileShulkerBox::class);

		$stoneBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		$stone = self::register(
			"stone",
			fn(BID $id) => new class($id, "Stone", $stoneBreakInfo) extends Opaque{
				public function getDropsForCompatibleTool(Item $item) : array{
					return [VanillaBlocks::COBBLESTONE()->asItem()];
				}

				public function isAffectedBySilkTouch() : bool{
					return true;
				}
			}
		);
		self::register("andesite", fn(BID $id) => new Opaque($id, "Andesite", $stoneBreakInfo));
		self::register("diorite", fn(BID $id) => new Opaque($id, "Diorite", $stoneBreakInfo));
		self::register("granite", fn(BID $id) => new Opaque($id, "Granite", $stoneBreakInfo));
		self::register("polished_andesite", fn(BID $id) => new Opaque($id, "Polished Andesite", $stoneBreakInfo));
		self::register("polished_diorite", fn(BID $id) => new Opaque($id, "Polished Diorite", $stoneBreakInfo));
		self::register("polished_granite", fn(BID $id) => new Opaque($id, "Polished Granite", $stoneBreakInfo));

		$stoneBrick = self::register("stone_bricks", fn(BID $id) => new Opaque($id, "Stone Bricks", $stoneBreakInfo));
		$mossyStoneBrick = self::register("mossy_stone_bricks", fn(BID $id) => new Opaque($id, "Mossy Stone Bricks", $stoneBreakInfo));
		$crackedStoneBrick = self::register("cracked_stone_bricks", fn(BID $id) => new Opaque($id, "Cracked Stone Bricks", $stoneBreakInfo));
		$chiseledStoneBrick = self::register("chiseled_stone_bricks", fn(BID $id) => new Opaque($id, "Chiseled Stone Bricks", $stoneBreakInfo));

		$infestedStoneBreakInfo = new Info(BreakInfo::pickaxe(0.75));
		self::register("infested_stone", fn(BID $id) => new InfestedStone($id, "Infested Stone", $infestedStoneBreakInfo, $stone));
		self::register("infested_stone_brick", fn(BID $id) => new InfestedStone($id, "Infested Stone Brick", $infestedStoneBreakInfo, $stoneBrick));
		self::register("infested_cobblestone", fn(BID $id) => new InfestedStone($id, "Infested Cobblestone", new Info(BreakInfo::pickaxe(1.0, blastResistance: 3.75)), $cobblestone));
		self::register("infested_mossy_stone_brick", fn(BID $id) => new InfestedStone($id, "Infested Mossy Stone Brick", $infestedStoneBreakInfo, $mossyStoneBrick));
		self::register("infested_cracked_stone_brick", fn(BID $id) => new InfestedStone($id, "Infested Cracked Stone Brick", $infestedStoneBreakInfo, $crackedStoneBrick));
		self::register("infested_chiseled_stone_brick", fn(BID $id) => new InfestedStone($id, "Infested Chiseled Stone Brick", $infestedStoneBreakInfo, $chiseledStoneBrick));

		self::register("stone_stairs", fn(BID $id) => new Stair($id, "Stone Stairs", $stoneBreakInfo));
		self::register("smooth_stone", fn(BID $id) => new Opaque($id, "Smooth Stone", new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0))));
		self::register("andesite_stairs", fn(BID $id) => new Stair($id, "Andesite Stairs", $stoneBreakInfo));
		self::register("diorite_stairs", fn(BID $id) => new Stair($id, "Diorite Stairs", $stoneBreakInfo));
		self::register("granite_stairs", fn(BID $id) => new Stair($id, "Granite Stairs", $stoneBreakInfo));
		self::register("polished_andesite_stairs", fn(BID $id) => new Stair($id, "Polished Andesite Stairs", $stoneBreakInfo));
		self::register("polished_diorite_stairs", fn(BID $id) => new Stair($id, "Polished Diorite Stairs", $stoneBreakInfo));
		self::register("polished_granite_stairs", fn(BID $id) => new Stair($id, "Polished Granite Stairs", $stoneBreakInfo));
		self::register("stone_brick_stairs", fn(BID $id) => new Stair($id, "Stone Brick Stairs", $stoneBreakInfo));
		self::register("mossy_stone_brick_stairs", fn(BID $id) => new Stair($id, "Mossy Stone Brick Stairs", $stoneBreakInfo));
		self::register("stone_button", fn(BID $id) => new StoneButton($id, "Stone Button", new Info(BreakInfo::pickaxe(0.5))));
		self::register("stonecutter", fn(BID $id) => new Stonecutter($id, "Stonecutter", new Info(BreakInfo::pickaxe(3.5))));
		self::register("stone_pressure_plate", fn(BID $id) => new StonePressurePlate($id, "Stone Pressure Plate", new Info(BreakInfo::pickaxe(0.5))));

		$stoneSlabBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));

		self::register("brick_slab", fn(BID $id) => new Slab($id, "Brick", $stoneSlabBreakInfo));
		self::register("cobblestone_slab", fn(BID $id) => new Slab($id, "Cobblestone", $stoneSlabBreakInfo));
		self::register("fake_wooden_slab", fn(BID $id) => new Slab($id, "Fake Wooden", $stoneSlabBreakInfo));
		self::register("nether_brick_slab", fn(BID $id) => new Slab($id, "Nether Brick", $stoneSlabBreakInfo));
		self::register("quartz_slab", fn(BID $id) => new Slab($id, "Quartz", $stoneSlabBreakInfo));
		self::register("sandstone_slab", fn(BID $id) => new Slab($id, "Sandstone", $stoneSlabBreakInfo));
		self::register("smooth_stone_slab", fn(BID $id) => new Slab($id, "Smooth Stone", $stoneSlabBreakInfo));
		self::register("stone_brick_slab", fn(BID $id) => new Slab($id, "Stone Brick", $stoneSlabBreakInfo));
		self::register("red_nether_brick_slab", fn(BID $id) => new Slab($id, "Red Nether Brick", $stoneSlabBreakInfo));
		self::register("red_sandstone_slab", fn(BID $id) => new Slab($id, "Red Sandstone", $stoneSlabBreakInfo));
		self::register("smooth_sandstone_slab", fn(BID $id) => new Slab($id, "Smooth Sandstone", $stoneSlabBreakInfo));
		self::register("cut_red_sandstone_slab", fn(BID $id) => new Slab($id, "Cut Red Sandstone", $stoneSlabBreakInfo));
		self::register("cut_sandstone_slab", fn(BID $id) => new Slab($id, "Cut Sandstone", $stoneSlabBreakInfo));
		self::register("mossy_cobblestone_slab", fn(BID $id) => new Slab($id, "Mossy Cobblestone", $stoneSlabBreakInfo));
		self::register("purpur_slab", fn(BID $id) => new Slab($id, "Purpur", $stoneSlabBreakInfo));
		self::register("smooth_red_sandstone_slab", fn(BID $id) => new Slab($id, "Smooth Red Sandstone", $stoneSlabBreakInfo));
		self::register("smooth_quartz_slab", fn(BID $id) => new Slab($id, "Smooth Quartz", $stoneSlabBreakInfo));
		self::register("stone_slab", fn(BID $id) => new Slab($id, "Stone", $stoneSlabBreakInfo));

		self::register("end_stone_brick_slab", fn(BID $id) => new Slab($id, "End Stone Brick", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD, 30.0))));

		$lightStoneSlabBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("dark_prismarine_slab", fn(BID $id) => new Slab($id, "Dark Prismarine", $lightStoneSlabBreakInfo));
		self::register("prismarine_slab", fn(BID $id) => new Slab($id, "Prismarine", $lightStoneSlabBreakInfo));
		self::register("prismarine_bricks_slab", fn(BID $id) => new Slab($id, "Prismarine Bricks", $lightStoneSlabBreakInfo));
		self::register("andesite_slab", fn(BID $id) => new Slab($id, "Andesite", $lightStoneSlabBreakInfo));
		self::register("diorite_slab", fn(BID $id) => new Slab($id, "Diorite", $lightStoneSlabBreakInfo));
		self::register("granite_slab", fn(BID $id) => new Slab($id, "Granite", $lightStoneSlabBreakInfo));
		self::register("polished_andesite_slab", fn(BID $id) => new Slab($id, "Polished Andesite", $lightStoneSlabBreakInfo));
		self::register("polished_diorite_slab", fn(BID $id) => new Slab($id, "Polished Diorite", $lightStoneSlabBreakInfo));
		self::register("polished_granite_slab", fn(BID $id) => new Slab($id, "Polished Granite", $lightStoneSlabBreakInfo));
		self::register("mossy_stone_brick_slab", fn(BID $id) => new Slab($id, "Mossy Stone Brick", $lightStoneSlabBreakInfo));

		self::register("legacy_stonecutter", fn(BID $id) => new Opaque($id, "Legacy Stonecutter", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD))));
		self::register("sugarcane", fn(BID $id) => new Sugarcane($id, "Sugarcane", new Info(BreakInfo::instant())));
		self::register("sweet_berry_bush", fn(BID $id) => new SweetBerryBush($id, "Sweet Berry Bush", new Info(BreakInfo::instant())));
		self::register("tnt", fn(BID $id) => new TNT($id, "TNT", new Info(BreakInfo::instant())));
		self::register("fern", fn(BID $id) => new TallGrass($id, "Fern", new Info(BreakInfo::instant(ToolType::SHEARS, 1), [Tags::POTTABLE_PLANTS]), fn() => VanillaBlocks::LARGE_FERN()));
		self::register("tall_grass", fn(BID $id) => new TallGrass($id, "Tall Grass", new Info(BreakInfo::instant(ToolType::SHEARS, 1)), fn() => VanillaBlocks::DOUBLE_TALLGRASS()));

		self::register("blue_torch", fn(BID $id) => new Torch($id, "Blue Torch", new Info(BreakInfo::instant())));
		self::register("copper_torch", fn(BID $id) => new Torch($id, "Copper Torch", new Info(BreakInfo::instant())));
		self::register("purple_torch", fn(BID $id) => new Torch($id, "Purple Torch", new Info(BreakInfo::instant())));
		self::register("red_torch", fn(BID $id) => new Torch($id, "Red Torch", new Info(BreakInfo::instant())));
		self::register("green_torch", fn(BID $id) => new Torch($id, "Green Torch", new Info(BreakInfo::instant())));
		self::register("torch", fn(BID $id) => new Torch($id, "Torch", new Info(BreakInfo::instant())));

		self::register("trapped_chest", fn(BID $id) => new TrappedChest($id, "Trapped Chest", $chestBreakInfo), TileChest::class);
		self::register("tripwire", fn(BID $id) => new Tripwire($id, "Tripwire", new Info(BreakInfo::instant())));
		self::register("tripwire_hook", fn(BID $id) => new TripwireHook($id, "Tripwire Hook", new Info(BreakInfo::instant())));
		self::register("underwater_torch", fn(BID $id) => new UnderwaterTorch($id, "Underwater Torch", new Info(BreakInfo::instant())));
		self::register("vines", fn(BID $id) => new Vine($id, "Vines", new Info(BreakInfo::axe(0.2))));
		self::register("water", fn(BID $id) => new Water($id, "Water", new Info(BreakInfo::indestructible(500.0))));
		self::register("lily_pad", fn(BID $id) => new WaterLily($id, "Lily Pad", new Info(BreakInfo::instant())));

		$weightedPressurePlateBreakInfo = new Info(BreakInfo::pickaxe(0.5));
		self::register("weighted_pressure_plate_heavy", fn(BID $id) => new WeightedPressurePlateHeavy(
			$id,
			"Weighted Pressure Plate Heavy",
			$weightedPressurePlateBreakInfo,
			deactivationDelayTicks: 10,
			signalStrengthFactor: 0.1
		));
		self::register("weighted_pressure_plate_light", fn(BID $id) => new WeightedPressurePlateLight(
			$id,
			"Weighted Pressure Plate Light",
			$weightedPressurePlateBreakInfo,
			deactivationDelayTicks: 10,
			signalStrengthFactor: 1.0
		));
		self::register("wheat", fn(BID $id) => new Wheat($id, "Wheat Block", new Info(BreakInfo::instant())));

		$leavesBreakInfo = new Info(new class(0.2, ToolType::HOE) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				if($item->getBlockToolType() === ToolType::SHEARS){
					return 0.0;
				}
				return parent::getBreakTime($item);
			}
		});
		$saplingTypeInfo = new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS]);

		foreach(SaplingType::cases() as $saplingType){
			$name = $saplingType->getDisplayName();
			self::register(strtolower($saplingType->name) . "_sapling", fn(BID $id) => new Sapling($id, $name . " Sapling", $saplingTypeInfo, $saplingType));
		}
		foreach(LeavesType::cases() as $leavesType){
			$name = $leavesType->getDisplayName();
			self::register(strtolower($leavesType->name) . "_leaves", fn(BID $id) => new Leaves($id, $name . " Leaves", $leavesBreakInfo, $leavesType));
		}

		$sandstoneBreakInfo = new Info(BreakInfo::pickaxe(0.8, ToolTier::WOOD));
		$smoothSandstoneBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("red_sandstone_stairs", fn(BID $id) => new Stair($id, "Red Sandstone Stairs", $sandstoneBreakInfo));
		self::register("smooth_red_sandstone_stairs", fn(BID $id) => new Stair($id, "Smooth Red Sandstone Stairs", $smoothSandstoneBreakInfo));
		self::register("red_sandstone", fn(BID $id) => new Opaque($id, "Red Sandstone", $sandstoneBreakInfo));
		self::register("chiseled_red_sandstone", fn(BID $id) => new Opaque($id, "Chiseled Red Sandstone", $sandstoneBreakInfo));
		self::register("cut_red_sandstone", fn(BID $id) => new Opaque($id, "Cut Red Sandstone", $sandstoneBreakInfo));
		self::register("smooth_red_sandstone", fn(BID $id) => new Opaque($id, "Smooth Red Sandstone", $smoothSandstoneBreakInfo));

		self::register("sandstone_stairs", fn(BID $id) => new Stair($id, "Sandstone Stairs", $sandstoneBreakInfo));
		self::register("smooth_sandstone_stairs", fn(BID $id) => new Stair($id, "Smooth Sandstone Stairs", $smoothSandstoneBreakInfo));
		self::register("sandstone", fn(BID $id) => new Opaque($id, "Sandstone", $sandstoneBreakInfo));
		self::register("chiseled_sandstone", fn(BID $id) => new Opaque($id, "Chiseled Sandstone", $sandstoneBreakInfo));
		self::register("cut_sandstone", fn(BID $id) => new Opaque($id, "Cut Sandstone", $sandstoneBreakInfo));
		self::register("smooth_sandstone", fn(BID $id) => new Opaque($id, "Smooth Sandstone", $smoothSandstoneBreakInfo));

		self::register("glazed_terracotta", fn(BID $id) => new GlazedTerracotta($id, "Glazed Terracotta", new Info(BreakInfo::pickaxe(1.4, ToolTier::WOOD))));
		self::register("dyed_shulker_box", fn(BID $id) => new DyedShulkerBox($id, "Dyed Shulker Box", $shulkerBoxBreakInfo), TileShulkerBox::class);
		self::register("stained_glass", fn(BID $id) => new StainedGlass($id, "Stained Glass", $glassBreakInfo));
		self::register("stained_glass_pane", fn(BID $id) => new StainedGlassPane($id, "Stained Glass Pane", $glassBreakInfo));
		self::register("stained_clay", fn(BID $id) => new StainedHardenedClay($id, "Stained Clay", new Info(BreakInfo::pickaxe(1.25, ToolTier::WOOD, 6.25))));
		self::register("stained_hardened_glass", fn(BID $id) => new StainedHardenedGlass($id, "Stained Hardened Glass", $hardenedGlassBreakInfo));
		self::register("stained_hardened_glass_pane", fn(BID $id) => new StainedHardenedGlassPane($id, "Stained Hardened Glass Pane", $hardenedGlassBreakInfo));
		self::register("carpet", fn(BID $id) => new Carpet($id, "Carpet", new Info(new BreakInfo(0.1))));
		self::register("concrete", fn(BID $id) => new Concrete($id, "Concrete", new Info(BreakInfo::pickaxe(1.8, ToolTier::WOOD))));
		self::register("concrete_powder", fn(BID $id) => new ConcretePowder($id, "Concrete Powder", new Info(BreakInfo::shovel(0.5))));
		self::register("wool", fn(BID $id) => new Wool($id, "Wool", new Info(new class(0.8, ToolType::SHEARS) extends BreakInfo{
			public function getBreakTime(Item $item) : float{
				$time = parent::getBreakTime($item);
				if($item->getBlockToolType() === ToolType::SHEARS){
					$time *= 3; //shears break compatible blocks 15x faster, but wool 5x
				}

				return $time;
			}
		})));

		self::register("end_stone_brick_wall", fn(BID $id) => new Wall($id, "End Stone Brick Wall", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD, 45.0))));

		$brickWallBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		self::register("cobblestone_wall", fn(BID $id) => new Wall($id, "Cobblestone Wall", $brickWallBreakInfo));
		self::register("brick_wall", fn(BID $id) => new Wall($id, "Brick Wall", $brickWallBreakInfo));
		self::register("mossy_cobblestone_wall", fn(BID $id) => new Wall($id, "Mossy Cobblestone Wall", $brickWallBreakInfo));
		self::register("nether_brick_wall", fn(BID $id) => new Wall($id, "Nether Brick Wall", $brickWallBreakInfo));
		self::register("red_nether_brick_wall", fn(BID $id) => new Wall($id, "Red Nether Brick Wall", $brickWallBreakInfo));

		$stoneWallBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("stone_brick_wall", fn(BID $id) => new Wall($id, "Stone Brick Wall", $stoneWallBreakInfo));
		self::register("mossy_stone_brick_wall", fn(BID $id) => new Wall($id, "Mossy Stone Brick Wall", $stoneWallBreakInfo));
		self::register("granite_wall", fn(BID $id) => new Wall($id, "Granite Wall", $stoneWallBreakInfo));
		self::register("diorite_wall", fn(BID $id) => new Wall($id, "Diorite Wall", $stoneWallBreakInfo));
		self::register("andesite_wall", fn(BID $id) => new Wall($id, "Andesite Wall", $stoneWallBreakInfo));
		self::register("prismarine_wall", fn(BID $id) => new Wall($id, "Prismarine Wall", $stoneWallBreakInfo));

		$sandstoneWallBreakInfo = new Info(BreakInfo::pickaxe(0.8, ToolTier::WOOD, 4.0));
		self::register("red_sandstone_wall", fn(BID $id) => new Wall($id, "Red Sandstone Wall", $sandstoneWallBreakInfo));
		self::register("sandstone_wall", fn(BID $id) => new Wall($id, "Sandstone Wall", $sandstoneWallBreakInfo));

		self::registerElements();

		$chemistryTableBreakInfo = new Info(BreakInfo::pickaxe(2.5, ToolTier::WOOD));
		self::register("compound_creator", fn(BID $id) => new ChemistryTable($id, "Compound Creator", $chemistryTableBreakInfo));
		self::register("element_constructor", fn(BID $id) => new ChemistryTable($id, "Element Constructor", $chemistryTableBreakInfo));
		self::register("lab_table", fn(BID $id) => new ChemistryTable($id, "Lab Table", $chemistryTableBreakInfo));
		self::register("material_reducer", fn(BID $id) => new ChemistryTable($id, "Material Reducer", $chemistryTableBreakInfo));

		self::register("chemical_heat", fn(BID $id) => new ChemicalHeat($id, "Heat Block", $chemistryTableBreakInfo));

		self::registerMushroomBlocks();

		self::register("coral", fn(BID $id) => new Coral(
			$id,
			"Coral",
			new Info(BreakInfo::instant()),
		));
		self::register("coral_fan", fn(BID $id) => new FloorCoralFan(
			$id,
			"Coral Fan",
			new Info(BreakInfo::instant()),
		));
		self::register("wall_coral_fan", fn(BID $id) => new WallCoralFan(
			$id,
			"Wall Coral Fan",
			new Info(BreakInfo::instant()),
		));

		self::register("mangrove_roots", fn(BID $id) => new MangroveRoots($id, "Mangrove Roots", new Info(BreakInfo::axe(0.7))));
		self::register("muddy_mangrove_roots", fn(BID $id) => new SimplePillar($id, "Muddy Mangrove Roots", new Info(BreakInfo::shovel(0.7), [Tags::MUD])));
		self::register("froglight", fn(BID $id) => new Froglight($id, "Froglight", new Info(new BreakInfo(0.3))));
		self::register("sculk", fn(BID $id) => new Sculk($id, "Sculk", new Info(new BreakInfo(0.2, ToolType::HOE))));
		self::register("reinforced_deepslate", fn(BID $id) => new class($id, "Reinforced Deepslate", new Info(new BreakInfo(55.0, ToolType::NONE, 0, 6000.0))) extends Opaque{
			public function getDropsForCompatibleTool(Item $item) : array{
				return [];
			}
		});
		self::register("cactus_flower", fn(BID $id) => new CactusFlower($id, "Cactus Flower", new Info(BreakInfo::instant())));

		self::registerBlocksR13();
		self::registerBlocksR14();
		self::registerBlocksR16();
		self::registerBlocksR17();
		self::registerBlocksR18();
		self::registerMudBlocks();
		self::registerResinBlocks();
		self::registerTuffBlocks();

		self::registerCraftingTables();
		self::registerChorusBlocks();
		self::registerOres();
		self::registerWoodenBlocks();
		self::registerCauldronBlocks();
	}

	/**
	 * @phpstan-return \Closure() : Item
	 */
	private static function getSignItemCallback(WoodType $woodType) : \Closure{
		return match ($woodType) {
			WoodType::OAK => VanillaItems::OAK_SIGN(...),
			WoodType::SPRUCE => VanillaItems::SPRUCE_SIGN(...),
			WoodType::BIRCH => VanillaItems::BIRCH_SIGN(...),
			WoodType::JUNGLE => VanillaItems::JUNGLE_SIGN(...),
			WoodType::ACACIA => VanillaItems::ACACIA_SIGN(...),
			WoodType::DARK_OAK => VanillaItems::DARK_OAK_SIGN(...),
			WoodType::MANGROVE => VanillaItems::MANGROVE_SIGN(...),
			WoodType::CRIMSON => VanillaItems::CRIMSON_SIGN(...),
			WoodType::WARPED => VanillaItems::WARPED_SIGN(...),
			WoodType::CHERRY => VanillaItems::CHERRY_SIGN(...),
			WoodType::PALE_OAK => VanillaItems::PALE_OAK_SIGN(...),
			WoodType::BAMBOO => VanillaItems::BAMBOO_SIGN(...),
		};
	}

	/**
	 * @phpstan-return \Closure() : Item
	 */
	private static function getHangingSignItemCallback(WoodType $woodType) : \Closure{
		return match ($woodType) {
			WoodType::OAK => VanillaItems::OAK_HANGING_SIGN(...),
			WoodType::SPRUCE => VanillaItems::SPRUCE_HANGING_SIGN(...),
			WoodType::BIRCH => VanillaItems::BIRCH_HANGING_SIGN(...),
			WoodType::JUNGLE => VanillaItems::JUNGLE_HANGING_SIGN(...),
			WoodType::ACACIA => VanillaItems::ACACIA_HANGING_SIGN(...),
			WoodType::DARK_OAK => VanillaItems::DARK_OAK_HANGING_SIGN(...),
			WoodType::MANGROVE => VanillaItems::MANGROVE_HANGING_SIGN(...),
			WoodType::CRIMSON => VanillaItems::CRIMSON_HANGING_SIGN(...),
			WoodType::WARPED => VanillaItems::WARPED_HANGING_SIGN(...),
			WoodType::CHERRY => VanillaItems::CHERRY_HANGING_SIGN(...),
			WoodType::PALE_OAK => VanillaItems::PALE_OAK_HANGING_SIGN(...),
			WoodType::BAMBOO => VanillaItems::BAMBOO_HANGING_SIGN(...),
		};
	}

	private function registerWoodenBlocks() : void{
		$planksBreakInfo = new Info(BreakInfo::axe(2.0, null, 15.0));
		$signBreakInfo = new Info(BreakInfo::axe(1.0));
		$hangingSignBreakInfo = new Info(BreakInfo::axe(1.0), [Tags::HANGING_SIGN]);
		$logBreakInfo = new Info(BreakInfo::axe(2.0));
		$woodenDoorBreakInfo = new Info(BreakInfo::axe(3.0, null, 15.0));
		$woodenButtonBreakInfo = new Info(BreakInfo::axe(0.5));
		$woodenPressurePlateBreakInfo = new Info(BreakInfo::axe(0.5));

		foreach(WoodType::cases() as $woodType){
			$name = $woodType->getDisplayName();
			$idName = fn(string $suffix) => strtolower($woodType->name) . "_" . $suffix;

			self::register($idName(mb_strtolower($woodType->getStandardLogSuffix() ?? "log", 'US-ASCII')), fn(BID $id) => new Wood($id, $name . " " . ($woodType->getStandardLogSuffix() ?? "Log"), $logBreakInfo, $woodType));
			if($woodType !== WoodType::BAMBOO){
				//TODO: kinda sus hack - there's no all-sided log for bamboo
				//maybe log type and wood type need to be separated
				//we won't be able to do an overloaded accessor for wood until this is addressed
				self::register($idName(mb_strtolower($woodType->getAllSidedLogSuffix() ?? "wood", 'US-ASCII')), fn(BID $id) => new Wood($id, $name . " " . ($woodType->getAllSidedLogSuffix() ?? "Wood"), $logBreakInfo, $woodType));
			}

			self::register($idName("planks"), fn(BID $id) => new Planks($id, $name . " Planks", $planksBreakInfo, $woodType));
			self::register($idName("fence"), fn(BID $id) => new WoodenFence($id, $name . " Fence", $planksBreakInfo, $woodType));
			self::register($idName("slab"), fn(BID $id) => new WoodenSlab($id, $name, $planksBreakInfo, $woodType));

			self::register($idName("fence_gate"), fn(BID $id) => new FenceGate($id, $name . " Fence Gate", $planksBreakInfo, $woodType));
			self::register($idName("stairs"), fn(BID $id) => new WoodenStairs($id, $name . " Stairs", $planksBreakInfo, $woodType));
			self::register($idName("door"), fn(BID $id) => new WoodenDoor($id, $name . " Door", $woodenDoorBreakInfo, $woodType));

			self::register($idName("button"), fn(BID $id) => new WoodenButton($id, $name . " Button", $woodenButtonBreakInfo, $woodType));
			self::register($idName("pressure_plate"), fn(BID $id) => new WoodenPressurePlate($id, $name . " Pressure Plate", $woodenPressurePlateBreakInfo, $woodType, 20));
			self::register($idName("trapdoor"), fn(BID $id) => new WoodenTrapdoor($id, $name . " Trapdoor", $woodenDoorBreakInfo, $woodType));

			self::registerDelayed($idName("sign"), fn(string $idName) : FloorSign => new FloorSign(self::makeBID($idName, TileSign::class), $name . " Sign", $signBreakInfo, $woodType, self::getSignItemCallback($woodType)));
			self::registerDelayed($idName("wall_sign"), fn(string $idName) : WallSign => new WallSign(self::makeBID($idName, TileSign::class), $name . " Wall Sign", $signBreakInfo, $woodType, self::getSignItemCallback($woodType)));

			self::registerDelayed($idName("ceiling_center_hanging_sign"), fn(string $idName) : CeilingCenterHangingSign => new CeilingCenterHangingSign(self::makeBID($idName, TileHangingSign::class), $name . " Center Hanging Sign", $hangingSignBreakInfo, $woodType, self::getHangingSignItemCallback($woodType)));
			self::registerDelayed($idName("ceiling_edges_hanging_sign"), fn(string $idName) : CeilingEdgesHangingSign => new CeilingEdgesHangingSign(self::makeBID($idName, TileHangingSign::class), $name . " Edges Hanging Sign", $hangingSignBreakInfo, $woodType, self::getHangingSignItemCallback($woodType)));
			self::registerDelayed($idName("wall_hanging_sign"), fn(string $idName) : WallHangingSign => new WallHangingSign(self::makeBID($idName, TileHangingSign::class), $name . " Wall Hanging Sign", $hangingSignBreakInfo, $woodType, self::getHangingSignItemCallback($woodType)));
		}

		$mosaicBreakInfo = new Info(BreakInfo::axe(2.0, null, 15.0), [Tags::BAMBOO_MOSAIC]);
		self::register("bamboo_mosaic", fn(BID $id) => new Planks($id, "Bamboo Mosaic", $mosaicBreakInfo, WoodType::BAMBOO));
		self::register("bamboo_mosaic_slab", fn(BID $id) => new WoodenSlab($id, "Bamboo Mosaic", $mosaicBreakInfo, WoodType::BAMBOO));
		self::register("bamboo_mosaic_stairs", fn(BID $id) => new WoodenStairs($id, "Bamboo Mosaic Stairs", $mosaicBreakInfo, WoodType::BAMBOO));
	}

	private function registerMushroomBlocks() : void{
		$mushroomBlockBreakInfo = new Info(BreakInfo::axe(0.2));

		self::register("brown_mushroom_block", fn(BID $id) => new BrownMushroomBlock($id, "Brown Mushroom Block", $mushroomBlockBreakInfo));
		self::register("red_mushroom_block", fn(BID $id) => new RedMushroomBlock($id, "Red Mushroom Block", $mushroomBlockBreakInfo));

		//finally, the stems
		self::register("mushroom_stem", fn(BID $id) => new MushroomStem($id, "Mushroom Stem", $mushroomBlockBreakInfo));
		self::register("all_sided_mushroom_stem", fn(BID $id) => new MushroomStem($id, "All Sided Mushroom Stem", $mushroomBlockBreakInfo));
	}

	private function registerElements() : void{
		$instaBreak = new Info(BreakInfo::instant());
		self::register("element_zero", fn(BID $id) => new Opaque($id, "???", $instaBreak));

		$register = fn(string $name, string $displayName, string $symbol, int $atomicWeight, int $group) =>
			self::register("element_$name", fn(BID $id) => new Element($id, $displayName, $instaBreak, $symbol, $atomicWeight, $group));

		$register("hydrogen", "Hydrogen", "h", 1, 5);
		$register("helium", "Helium", "he", 2, 7);
		$register("lithium", "Lithium", "li", 3, 0);
		$register("beryllium", "Beryllium", "be", 4, 1);
		$register("boron", "Boron", "b", 5, 4);
		$register("carbon", "Carbon", "c", 6, 5);
		$register("nitrogen", "Nitrogen", "n", 7, 5);
		$register("oxygen", "Oxygen", "o", 8, 5);
		$register("fluorine", "Fluorine", "f", 9, 6);
		$register("neon", "Neon", "ne", 10, 7);
		$register("sodium", "Sodium", "na", 11, 0);
		$register("magnesium", "Magnesium", "mg", 12, 1);
		$register("aluminum", "Aluminum", "al", 13, 3);
		$register("silicon", "Silicon", "si", 14, 4);
		$register("phosphorus", "Phosphorus", "p", 15, 5);
		$register("sulfur", "Sulfur", "s", 16, 5);
		$register("chlorine", "Chlorine", "cl", 17, 6);
		$register("argon", "Argon", "ar", 18, 7);
		$register("potassium", "Potassium", "k", 19, 0);
		$register("calcium", "Calcium", "ca", 20, 1);
		$register("scandium", "Scandium", "sc", 21, 2);
		$register("titanium", "Titanium", "ti", 22, 2);
		$register("vanadium", "Vanadium", "v", 23, 2);
		$register("chromium", "Chromium", "cr", 24, 2);
		$register("manganese", "Manganese", "mn", 25, 2);
		$register("iron", "Iron", "fe", 26, 2);
		$register("cobalt", "Cobalt", "co", 27, 2);
		$register("nickel", "Nickel", "ni", 28, 2);
		$register("copper", "Copper", "cu", 29, 2);
		$register("zinc", "Zinc", "zn", 30, 2);
		$register("gallium", "Gallium", "ga", 31, 3);
		$register("germanium", "Germanium", "ge", 32, 4);
		$register("arsenic", "Arsenic", "as", 33, 4);
		$register("selenium", "Selenium", "se", 34, 5);
		$register("bromine", "Bromine", "br", 35, 6);
		$register("krypton", "Krypton", "kr", 36, 7);
		$register("rubidium", "Rubidium", "rb", 37, 0);
		$register("strontium", "Strontium", "sr", 38, 1);
		$register("yttrium", "Yttrium", "y", 39, 2);
		$register("zirconium", "Zirconium", "zr", 40, 2);
		$register("niobium", "Niobium", "nb", 41, 2);
		$register("molybdenum", "Molybdenum", "mo", 42, 2);
		$register("technetium", "Technetium", "tc", 43, 2);
		$register("ruthenium", "Ruthenium", "ru", 44, 2);
		$register("rhodium", "Rhodium", "rh", 45, 2);
		$register("palladium", "Palladium", "pd", 46, 2);
		$register("silver", "Silver", "ag", 47, 2);
		$register("cadmium", "Cadmium", "cd", 48, 2);
		$register("indium", "Indium", "in", 49, 3);
		$register("tin", "Tin", "sn", 50, 3);
		$register("antimony", "Antimony", "sb", 51, 4);
		$register("tellurium", "Tellurium", "te", 52, 4);
		$register("iodine", "Iodine", "i", 53, 6);
		$register("xenon", "Xenon", "xe", 54, 7);
		$register("cesium", "Cesium", "cs", 55, 0);
		$register("barium", "Barium", "ba", 56, 1);
		$register("lanthanum", "Lanthanum", "la", 57, 8);
		$register("cerium", "Cerium", "ce", 58, 8);
		$register("praseodymium", "Praseodymium", "pr", 59, 8);
		$register("neodymium", "Neodymium", "nd", 60, 8);
		$register("promethium", "Promethium", "pm", 61, 8);
		$register("samarium", "Samarium", "sm", 62, 8);
		$register("europium", "Europium", "eu", 63, 8);
		$register("gadolinium", "Gadolinium", "gd", 64, 8);
		$register("terbium", "Terbium", "tb", 65, 8);
		$register("dysprosium", "Dysprosium", "dy", 66, 8);
		$register("holmium", "Holmium", "ho", 67, 8);
		$register("erbium", "Erbium", "er", 68, 8);
		$register("thulium", "Thulium", "tm", 69, 8);
		$register("ytterbium", "Ytterbium", "yb", 70, 8);
		$register("lutetium", "Lutetium", "lu", 71, 8);
		$register("hafnium", "Hafnium", "hf", 72, 2);
		$register("tantalum", "Tantalum", "ta", 73, 2);
		$register("tungsten", "Tungsten", "w", 74, 2);
		$register("rhenium", "Rhenium", "re", 75, 2);
		$register("osmium", "Osmium", "os", 76, 2);
		$register("iridium", "Iridium", "ir", 77, 2);
		$register("platinum", "Platinum", "pt", 78, 2);
		$register("gold", "Gold", "au", 79, 2);
		$register("mercury", "Mercury", "hg", 80, 2);
		$register("thallium", "Thallium", "tl", 81, 3);
		$register("lead", "Lead", "pb", 82, 3);
		$register("bismuth", "Bismuth", "bi", 83, 3);
		$register("polonium", "Polonium", "po", 84, 4);
		$register("astatine", "Astatine", "at", 85, 6);
		$register("radon", "Radon", "rn", 86, 7);
		$register("francium", "Francium", "fr", 87, 0);
		$register("radium", "Radium", "ra", 88, 1);
		$register("actinium", "Actinium", "ac", 89, 9);
		$register("thorium", "Thorium", "th", 90, 9);
		$register("protactinium", "Protactinium", "pa", 91, 9);
		$register("uranium", "Uranium", "u", 92, 9);
		$register("neptunium", "Neptunium", "np", 93, 9);
		$register("plutonium", "Plutonium", "pu", 94, 9);
		$register("americium", "Americium", "am", 95, 9);
		$register("curium", "Curium", "cm", 96, 9);
		$register("berkelium", "Berkelium", "bk", 97, 9);
		$register("californium", "Californium", "cf", 98, 9);
		$register("einsteinium", "Einsteinium", "es", 99, 9);
		$register("fermium", "Fermium", "fm", 100, 9);
		$register("mendelevium", "Mendelevium", "md", 101, 9);
		$register("nobelium", "Nobelium", "no", 102, 9);
		$register("lawrencium", "Lawrencium", "lr", 103, 9);
		$register("rutherfordium", "Rutherfordium", "rf", 104, 2);
		$register("dubnium", "Dubnium", "db", 105, 2);
		$register("seaborgium", "Seaborgium", "sg", 106, 2);
		$register("bohrium", "Bohrium", "bh", 107, 2);
		$register("hassium", "Hassium", "hs", 108, 2);
		$register("meitnerium", "Meitnerium", "mt", 109, 2);
		$register("darmstadtium", "Darmstadtium", "ds", 110, 2);
		$register("roentgenium", "Roentgenium", "rg", 111, 2);
		$register("copernicium", "Copernicium", "cn", 112, 2);
		$register("nihonium", "Nihonium", "nh", 113, 3);
		$register("flerovium", "Flerovium", "fl", 114, 3);
		$register("moscovium", "Moscovium", "mc", 115, 3);
		$register("livermorium", "Livermorium", "lv", 116, 3);
		$register("tennessine", "Tennessine", "ts", 117, 6);
		$register("oganesson", "Oganesson", "og", 118, 7);
	}

	private function registerOres() : void{
		$stoneOreBreakInfo = fn(ToolTier $toolTier) => new Info(BreakInfo::pickaxe(3.0, $toolTier));
		self::register("coal_ore", fn(BID $id) => new CoalOre($id, "Coal Ore", $stoneOreBreakInfo(ToolTier::WOOD)));
		self::register("copper_ore", fn(BID $id) => new CopperOre($id, "Copper Ore", $stoneOreBreakInfo(ToolTier::STONE)));
		self::register("diamond_ore", fn(BID $id) => new DiamondOre($id, "Diamond Ore", $stoneOreBreakInfo(ToolTier::IRON)));
		self::register("emerald_ore", fn(BID $id) => new EmeraldOre($id, "Emerald Ore", $stoneOreBreakInfo(ToolTier::IRON)));
		self::register("gold_ore", fn(BID $id) => new GoldOre($id, "Gold Ore", $stoneOreBreakInfo(ToolTier::IRON)));
		self::register("iron_ore", fn(BID $id) => new IronOre($id, "Iron Ore", $stoneOreBreakInfo(ToolTier::STONE)));
		self::register("lapis_lazuli_ore", fn(BID $id) => new LapisOre($id, "Lapis Lazuli Ore", $stoneOreBreakInfo(ToolTier::STONE)));
		self::register("redstone_ore", fn(BID $id) => new RedstoneOre($id, "Redstone Ore", $stoneOreBreakInfo(ToolTier::IRON)));

		$deepslateOreBreakInfo = fn(ToolTier $toolTier) => new Info(BreakInfo::pickaxe(4.5, $toolTier, 15.0));
		self::register("deepslate_coal_ore", fn(BID $id) => new CoalOre($id, "Deepslate Coal Ore", $deepslateOreBreakInfo(ToolTier::WOOD)));
		self::register("deepslate_copper_ore", fn(BID $id) => new CopperOre($id, "Deepslate Copper Ore", $deepslateOreBreakInfo(ToolTier::STONE)));
		self::register("deepslate_diamond_ore", fn(BID $id) => new DiamondOre($id, "Deepslate Diamond Ore", $deepslateOreBreakInfo(ToolTier::IRON)));
		self::register("deepslate_emerald_ore", fn(BID $id) => new EmeraldOre($id, "Deepslate Emerald Ore", $deepslateOreBreakInfo(ToolTier::IRON)));
		self::register("deepslate_gold_ore", fn(BID $id) => new GoldOre($id, "Deepslate Gold Ore", $deepslateOreBreakInfo(ToolTier::IRON)));
		self::register("deepslate_iron_ore", fn(BID $id) => new IronOre($id, "Deepslate Iron Ore", $deepslateOreBreakInfo(ToolTier::STONE)));
		self::register("deepslate_lapis_lazuli_ore", fn(BID $id) => new LapisOre($id, "Deepslate Lapis Lazuli Ore", $deepslateOreBreakInfo(ToolTier::STONE)));
		self::register("deepslate_redstone_ore", fn(BID $id) => new RedstoneOre($id, "Deepslate Redstone Ore", $deepslateOreBreakInfo(ToolTier::IRON)));

		$netherrackOreBreakInfo = new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD));
		self::register("nether_quartz_ore", fn(BID $id) => new NetherQuartzOre($id, "Nether Quartz Ore", $netherrackOreBreakInfo));
		self::register("nether_gold_ore", fn(BID $id) => new NetherGoldOre($id, "Nether Gold Ore", $netherrackOreBreakInfo));
	}

	private function registerCraftingTables() : void{
		//TODO: this is the same for all wooden crafting blocks
		$craftingBlockBreakInfo = new Info(BreakInfo::axe(2.5));
		self::register("cartography_table", fn(BID $id) => new CartographyTable($id, "Cartography Table", $craftingBlockBreakInfo));
		self::register("crafting_table", fn(BID $id) => new CraftingTable($id, "Crafting Table", $craftingBlockBreakInfo));
		self::register("fletching_table", fn(BID $id) => new FletchingTable($id, "Fletching Table", $craftingBlockBreakInfo));
		self::register("loom", fn(BID $id) => new Loom($id, "Loom", $craftingBlockBreakInfo));
		self::register("smithing_table", fn(BID $id) => new SmithingTable($id, "Smithing Table", $craftingBlockBreakInfo));
	}

	private function registerChorusBlocks() : void{
		$chorusBlockBreakInfo = new Info(BreakInfo::axe(0.4));
		self::register("chorus_plant", fn(BID $id) => new ChorusPlant($id, "Chorus Plant", $chorusBlockBreakInfo));
		self::register("chorus_flower", fn(BID $id) => new ChorusFlower($id, "Chorus Flower", $chorusBlockBreakInfo));
	}

	private function registerBlocksR13() : void{
		self::register("light", fn(BID $id) => new Light($id, "Light Block", new Info(BreakInfo::indestructible())));
		self::register("structure_void", fn(BID $id) => new StructureVoid($id, "Structure Void", new Info(BreakInfo::instant())));
		self::register("wither_rose", fn(BID $id) => new WitherRose($id, "Wither Rose", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])));
	}

	private function registerBlocksR14() : void{
		self::register("honeycomb", fn(BID $id) => new Opaque($id, "Honeycomb Block", new Info(new BreakInfo(0.6))));
	}

	private function registerBlocksR16() : void{
		//for some reason, slabs have weird hardness like the legacy ones
		$slabBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));

		self::register("ancient_debris", fn(BID $id) => new class($id, "Ancient Debris", new Info(BreakInfo::pickaxe(30, ToolTier::DIAMOND, 6000.0))) extends Opaque{
			public function isFireProofAsItem() : bool{ return true; }
		});
		$netheriteBreakInfo = new Info(BreakInfo::pickaxe(50, ToolTier::DIAMOND, 6000.0));
		self::register("netherite", fn(BID $id) => new class($id, "Netherite Block", $netheriteBreakInfo) extends Opaque{
			public function isFireProofAsItem() : bool{ return true; }
		});

		$basaltBreakInfo = new Info(BreakInfo::pickaxe(1.25, ToolTier::WOOD, 21.0));
		self::register("basalt", fn(BID $id) => new SimplePillar($id, "Basalt", $basaltBreakInfo));
		self::register("polished_basalt", fn(BID $id) => new SimplePillar($id, "Polished Basalt", $basaltBreakInfo));
		self::register("smooth_basalt", fn(BID $id) => new Opaque($id, "Smooth Basalt", $basaltBreakInfo));

		$blackstoneBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("blackstone", fn(BID $id) => new Opaque($id, "Blackstone", $blackstoneBreakInfo));
		self::register("blackstone_slab", fn(BID $id) => new Slab($id, "Blackstone", $slabBreakInfo));
		self::register("blackstone_stairs", fn(BID $id) => new Stair($id, "Blackstone Stairs", $blackstoneBreakInfo));
		self::register("blackstone_wall", fn(BID $id) => new Wall($id, "Blackstone Wall", $blackstoneBreakInfo));

		self::register("gilded_blackstone", fn(BID $id) => new GildedBlackstone($id, "Gilded Blackstone", $blackstoneBreakInfo));

		$polishedBlackstoneBreakInfo = new Info(BreakInfo::pickaxe(2.0, ToolTier::WOOD, 30.0));
		$prefix = fn(string $thing) => "Polished Blackstone" . ($thing !== "" ? " $thing" : "");
		self::register("polished_blackstone", fn(BID $id) => new Opaque($id, $prefix(""), $polishedBlackstoneBreakInfo));
		self::register("polished_blackstone_button", fn(BID $id) => new StoneButton($id, $prefix("Button"), new Info(BreakInfo::pickaxe(0.5))));
		self::register("polished_blackstone_pressure_plate", fn(BID $id) => new StonePressurePlate($id, $prefix("Pressure Plate"), new Info(BreakInfo::pickaxe(0.5)), 20));
		self::register("polished_blackstone_slab", fn(BID $id) => new Slab($id, $prefix(""), $slabBreakInfo));
		self::register("polished_blackstone_stairs", fn(BID $id) => new Stair($id, $prefix("Stairs"), $polishedBlackstoneBreakInfo));
		self::register("polished_blackstone_wall", fn(BID $id) => new Wall($id, $prefix("Wall"), $polishedBlackstoneBreakInfo));
		self::register("chiseled_polished_blackstone", fn(BID $id) => new Opaque($id, "Chiseled Polished Blackstone", $blackstoneBreakInfo));

		$prefix = fn(string $thing) => "Polished Blackstone Brick" . ($thing !== "" ? " $thing" : "");
		self::register("polished_blackstone_bricks", fn(BID $id) => new Opaque($id, "Polished Blackstone Bricks", $blackstoneBreakInfo));
		self::register("polished_blackstone_brick_slab", fn(BID $id) => new Slab($id, "Polished Blackstone Brick", $slabBreakInfo));
		self::register("polished_blackstone_brick_stairs", fn(BID $id) => new Stair($id, $prefix("Stairs"), $blackstoneBreakInfo));
		self::register("polished_blackstone_brick_wall", fn(BID $id) => new Wall($id, $prefix("Wall"), $blackstoneBreakInfo));
		self::register("cracked_polished_blackstone_bricks", fn(BID $id) => new Opaque($id, "Cracked Polished Blackstone Bricks", $blackstoneBreakInfo));

		self::register("soul_torch", fn(BID $id) => new Torch($id, "Soul Torch", new Info(BreakInfo::instant())));
		self::register("soul_fire", fn(BID $id) => new SoulFire($id, "Soul Fire", new Info(BreakInfo::instant(), [Tags::FIRE])));

		self::register("soul_soil", fn(BID $id) => new Opaque($id, "Soul Soil", new Info(BreakInfo::shovel(0.5))));

		self::register("shroomlight", fn(BID $id) => new class($id, "Shroomlight", new Info(new BreakInfo(1.0, ToolType::HOE))) extends Opaque{
			public function getLightLevel() : int{ return 15; }
		});

		self::register("warped_wart_block", fn(BID $id) => new Opaque($id, "Warped Wart Block", new Info(new BreakInfo(1.0, ToolType::HOE))));
		self::register("crying_obsidian", fn(BID $id) => new class($id, "Crying Obsidian", new Info(BreakInfo::pickaxe(35.0 /* 50 in Java */, ToolTier::DIAMOND, 6000.0))) extends Opaque{
			public function getLightLevel() : int{ return 10;}
		});

		self::register("twisting_vines", fn(BID $id) => new NetherVines($id, "Twisting Vines", new Info(BreakInfo::instant()), Facing::UP));
		self::register("weeping_vines", fn(BID $id) => new NetherVines($id, "Weeping Vines", new Info(BreakInfo::instant()), Facing::DOWN));

		$netherRootsInfo = new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS]);
		self::register("crimson_roots", fn(BID $id) => new NetherRoots($id, "Crimson Roots", $netherRootsInfo));
		self::register("warped_roots", fn(BID $id) => new NetherRoots($id, "Warped Roots", $netherRootsInfo));

		self::register("chain", fn(BID $id) => new Chain($id, "Chain", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 30.0))));
		self::register("copper_chain", fn(BID $id) => new CopperChain($id, "Copper Chain", new Info(BreakInfo::pickaxe(5.0, ToolTier::WOOD, 30.0))));

		self::register("respawn_anchor", fn(BID $id) => new RespawnAnchor($id, "Respawn Anchor", new Info(BreakInfo::pickaxe(50.0, ToolTier::DIAMOND, 6000.0))));

		$netherFungusInfo = new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS, Tags::HUGE_FUNGUS_REPLACEABLE]);
		self::register("crimson_fungus", fn(BID $id) => new NetherFungus($id, "Crimson Fungus", $netherFungusInfo, BlockTypeIds::CRIMSON_NYLIUM, TreeType::CRIMSON));
		self::register("warped_fungus", fn(BID $id) => new NetherFungus($id, "Warped Fungus", $netherFungusInfo, BlockTypeIds::WARPED_NYLIUM, TreeType::WARPED));

		self::register("nether_sprouts", fn(BID $id) => new NetherSprouts($id, "Nether Sprouts", new Info(BreakInfo::instant(ToolType::SHEARS, 1))));

		$nyliumBreakInfo = new Info(BreakInfo::pickaxe(0.4, ToolTier::WOOD), [Tags::NYLIUM]);
		self::registerDelayed("crimson_nylium", fn(string $name) : Nylium => new Nylium(self::makeBID($name), "Crimson Nylium", $nyliumBreakInfo, [VanillaBlocks::CRIMSON_FUNGUS(), VanillaBlocks::CRIMSON_ROOTS()]));
		self::registerDelayed("warped_nylium", fn(string $name) : Nylium => new Nylium(self::makeBID($name), "Warped Nylium", $nyliumBreakInfo, [VanillaBlocks::WARPED_FUNGUS(), VanillaBlocks::WARPED_ROOTS(), VanillaBlocks::NETHER_SPROUTS()]));
	}

	private function registerBlocksR17() : void{
		//in java this can be acquired using any tool - seems to be a parity issue in bedrock
		$amethystInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD));
		self::register("amethyst", fn(BID $id) => new class($id, "Amethyst", $amethystInfo) extends Opaque{
			use AmethystTrait;
		});
		self::register("budding_amethyst", fn(BID $id) => new BuddingAmethyst($id, "Budding Amethyst", $amethystInfo));
		self::register("amethyst_cluster", fn(BID $id) => new AmethystCluster($id, "Amethyst Cluster", $amethystInfo));

		self::register("calcite", fn(BID $id) => new Opaque($id, "Calcite", new Info(BreakInfo::pickaxe(0.75, ToolTier::WOOD))));

		self::register("raw_copper", fn(BID $id) => new Opaque($id, "Raw Copper Block", new Info(BreakInfo::pickaxe(5, ToolTier::STONE, 30.0))));
		self::register("raw_gold", fn(BID $id) => new Opaque($id, "Raw Gold Block", new Info(BreakInfo::pickaxe(5, ToolTier::IRON, 30.0))));
		self::register("raw_iron", fn(BID $id) => new Opaque($id, "Raw Iron Block", new Info(BreakInfo::pickaxe(5, ToolTier::STONE, 30.0))));

		$deepslateBreakInfo = new Info(BreakInfo::pickaxe(3, ToolTier::WOOD, 30.0));
		$deepslate = self::register("deepslate", fn(BID $id) => new class($id, "Deepslate", $deepslateBreakInfo) extends SimplePillar{
			public function getDropsForCompatibleTool(Item $item) : array{
				return [VanillaBlocks::COBBLED_DEEPSLATE()->asItem()];
			}

			public function isAffectedBySilkTouch() : bool{
				return true;
			}
		});

		//TODO: parity issue here - in Java this has a hardness of 3.0, but in bedrock it's 3.5
		self::register("chiseled_deepslate", fn(BID $id) => new Opaque($id, "Chiseled Deepslate", new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 30.0))));

		$deepslateBrickBreakInfo = new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 30.0));
		self::register("deepslate_bricks", fn(BID $id) => new Opaque($id, "Deepslate Bricks", $deepslateBrickBreakInfo));
		self::register("deepslate_brick_slab", fn(BID $id) => new Slab($id, "Deepslate Brick", $deepslateBrickBreakInfo));
		self::register("deepslate_brick_stairs", fn(BID $id) => new Stair($id, "Deepslate Brick Stairs", $deepslateBrickBreakInfo));
		self::register("deepslate_brick_wall", fn(BID $id) => new Wall($id, "Deepslate Brick Wall", $deepslateBrickBreakInfo));
		self::register("cracked_deepslate_bricks", fn(BID $id) => new Opaque($id, "Cracked Deepslate Bricks", $deepslateBrickBreakInfo));

		$deepslateTilesBreakInfo = new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 30.0));
		self::register("deepslate_tiles", fn(BID $id) => new Opaque($id, "Deepslate Tiles", $deepslateTilesBreakInfo));
		self::register("deepslate_tile_slab", fn(BID $id) => new Slab($id, "Deepslate Tile", $deepslateTilesBreakInfo));
		self::register("deepslate_tile_stairs", fn(BID $id) => new Stair($id, "Deepslate Tile Stairs", $deepslateTilesBreakInfo));
		self::register("deepslate_tile_wall", fn(BID $id) => new Wall($id, "Deepslate Tile Wall", $deepslateTilesBreakInfo));
		self::register("cracked_deepslate_tiles", fn(BID $id) => new Opaque($id, "Cracked Deepslate Tiles", $deepslateTilesBreakInfo));

		$cobbledDeepslateBreakInfo = new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 30.0));
		self::register("cobbled_deepslate", fn(BID $id) => new Opaque($id, "Cobbled Deepslate", $cobbledDeepslateBreakInfo));
		self::register("cobbled_deepslate_slab", fn(BID $id) => new Slab($id, "Cobbled Deepslate", $cobbledDeepslateBreakInfo));
		self::register("cobbled_deepslate_stairs", fn(BID $id) => new Stair($id, "Cobbled Deepslate Stairs", $cobbledDeepslateBreakInfo));
		self::register("cobbled_deepslate_wall", fn(BID $id) => new Wall($id, "Cobbled Deepslate Wall", $cobbledDeepslateBreakInfo));

		$polishedDeepslateBreakInfo = new Info(BreakInfo::pickaxe(3.5, ToolTier::WOOD, 30.0));
		self::register("polished_deepslate", fn(BID $id) => new Opaque($id, "Polished Deepslate", $polishedDeepslateBreakInfo));
		self::register("polished_deepslate_slab", fn(BID $id) => new Slab($id, "Polished Deepslate", $polishedDeepslateBreakInfo));
		self::register("polished_deepslate_stairs", fn(BID $id) => new Stair($id, "Polished Deepslate Stairs", $polishedDeepslateBreakInfo));
		self::register("polished_deepslate_wall", fn(BID $id) => new Wall($id, "Polished Deepslate Wall", $polishedDeepslateBreakInfo));

		self::register("tinted_glass", fn(BID $id) => new TintedGlass($id, "Tinted Glass", new Info(new BreakInfo(0.3))));

		$copperBreakInfo = new Info(BreakInfo::pickaxe(3.0, ToolTier::STONE, 30.0));
		self::register("lightning_rod", fn(BID $id) => new LightningRod($id, "Lightning Rod", $copperBreakInfo));

		self::register("copper", fn(BID $id) => new Copper($id, "Copper Block", $copperBreakInfo));
		self::register("chiseled_copper", fn(BID $id) => new Copper($id, "Chiseled Copper", $copperBreakInfo));
		self::register("copper_grate", fn(BID $id) => new CopperGrate($id, "Copper Grate", $copperBreakInfo));
		self::register("cut_copper", fn(BID $id) => new Copper($id, "Cut Copper Block", $copperBreakInfo));
		self::register("cut_copper_slab", fn(BID $id) => new CopperSlab($id, "Cut Copper Slab", $copperBreakInfo));
		self::register("cut_copper_stairs", fn(BID $id) => new CopperStairs($id, "Cut Copper Stairs", $copperBreakInfo));
		self::register("copper_bulb", fn(BID $id) => new CopperBulb($id, "Copper Bulb", $copperBreakInfo));

		self::register("copper_door", fn(BID $id) => new CopperDoor($id, "Copper Door", new Info(BreakInfo::pickaxe(3.0, blastResistance: 30.0))));
		self::register("copper_trapdoor", fn(BID $id) => new CopperTrapdoor($id, "Copper Trapdoor", new Info(BreakInfo::pickaxe(3.0, ToolTier::STONE, 30.0))));

		$candleBreakInfo = new Info(new BreakInfo(0.1));
		self::register("candle", fn(BID $id) => new Candle($id, "Candle", $candleBreakInfo));
		self::register("dyed_candle", fn(BID $id) => new DyedCandle($id, "Dyed Candle", $candleBreakInfo));

		//TODO: duplicated break info :(
		$cakeBreakInfo = new Info(new BreakInfo(0.5));
		self::register("cake_with_candle", fn(BID $id) => new CakeWithCandle($id, "Cake With Candle", $cakeBreakInfo));
		self::register("cake_with_dyed_candle", fn(BID $id) => new CakeWithDyedCandle($id, "Cake With Dyed Candle", $cakeBreakInfo));

		self::register("hanging_roots", fn(BID $id) => new HangingRoots($id, "Hanging Roots", new Info(BreakInfo::instant(ToolType::SHEARS, 1))));

		self::register("cave_vines", fn(BID $id) => new CaveVines($id, "Cave Vines", new Info(BreakInfo::instant())));

		self::register("small_dripleaf", fn(BID $id) => new SmallDripleaf($id, "Small Dripleaf", new Info(BreakInfo::instant(ToolType::SHEARS, toolHarvestLevel: 1))));
		self::register("big_dripleaf_head", fn(BID $id) => new BigDripleafHead($id, "Big Dripleaf", new Info(new BreakInfo(0.1))));
		self::register("big_dripleaf_stem", fn(BID $id) => new BigDripleafStem($id, "Big Dripleaf Stem", new Info(new BreakInfo(0.1))));

		self::register("infested_deepslate", fn(BID $id) => new InfestedPillar($id, "Infested Deepslate", new Info(BreakInfo::pickaxe(1.5, blastResistance: 3.75)), $deepslate));
	}

	private function registerBlocksR18() : void{
		self::register("spore_blossom", fn(BID $id) => new SporeBlossom($id, "Spore Blossom", new Info(BreakInfo::instant())));
	}

	private function registerMudBlocks() : void{
		self::register("mud", fn(BID $id) => new Opaque($id, "Mud", new Info(BreakInfo::shovel(0.5), [Tags::MUD])));
		self::register("packed_mud", fn(BID $id) => new Opaque($id, "Packed Mud", new Info(BreakInfo::pickaxe(1.0, null, 15.0))));

		$mudBricksBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 15.0));

		self::register("mud_bricks", fn(BID $id) => new Opaque($id, "Mud Bricks", $mudBricksBreakInfo));
		self::register("mud_brick_slab", fn(BID $id) => new Slab($id, "Mud Brick", $mudBricksBreakInfo));
		self::register("mud_brick_stairs", fn(BID $id) => new Stair($id, "Mud Brick Stairs", $mudBricksBreakInfo));
		self::register("mud_brick_wall", fn(BID $id) => new Wall($id, "Mud Brick Wall", $mudBricksBreakInfo));
	}

	private function registerResinBlocks() : void{
		self::register("resin", fn(BID $id) => new Opaque($id, "Block of Resin", new Info(BreakInfo::instant())));
		self::register("resin_clump", fn(BID $id) => new ResinClump($id, "Resin Clump", new Info(BreakInfo::instant())));

		$resinBricksInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));
		self::register("resin_brick_slab", fn(BID $id) => new Slab($id, "Resin Brick", $resinBricksInfo));
		self::register("resin_brick_stairs", fn(BID $id) => new Stair($id, "Resin Brick Stairs", $resinBricksInfo));
		self::register("resin_brick_wall", fn(BID $id) => new Wall($id, "Resin Brick Wall", $resinBricksInfo));
		self::register("resin_bricks", fn(BID $id) => new Opaque($id, "Resin Bricks", $resinBricksInfo));
		self::register("chiseled_resin_bricks", fn(BID $id) => new Opaque($id, "Chiseled Resin Bricks", $resinBricksInfo));
	}

	private function registerTuffBlocks() : void{
		$tuffBreakInfo = new Info(BreakInfo::pickaxe(1.5, ToolTier::WOOD, 30.0));

		self::register("tuff", fn(BID $id) => new Opaque($id, "Tuff", $tuffBreakInfo));
		self::register("tuff_slab", fn(BID $id) => new Slab($id, "Tuff", $tuffBreakInfo));
		self::register("tuff_stairs", fn(BID $id) => new Stair($id, "Tuff Stairs", $tuffBreakInfo));
		self::register("tuff_wall", fn(BID $id) => new Wall($id, "Tuff Wall", $tuffBreakInfo));
		self::register("chiseled_tuff", fn(BID $id) => new Opaque($id, "Chiseled Tuff", $tuffBreakInfo));

		self::register("tuff_bricks", fn(BID $id) => new Opaque($id, "Tuff Bricks", $tuffBreakInfo));
		self::register("tuff_brick_slab", fn(BID $id) => new Slab($id, "Tuff Brick", $tuffBreakInfo));
		self::register("tuff_brick_stairs", fn(BID $id) => new Stair($id, "Tuff Brick Stairs", $tuffBreakInfo));
		self::register("tuff_brick_wall", fn(BID $id) => new Wall($id, "Tuff Brick Wall", $tuffBreakInfo));
		self::register("chiseled_tuff_bricks", fn(BID $id) => new Opaque($id, "Chiseled Tuff Bricks", $tuffBreakInfo));

		self::register("polished_tuff", fn(BID $id) => new Opaque($id, "Polished Tuff", $tuffBreakInfo));
		self::register("polished_tuff_slab", fn(BID $id) => new Slab($id, "Polished Tuff", $tuffBreakInfo));
		self::register("polished_tuff_stairs", fn(BID $id) => new Stair($id, "Polished Tuff Stairs", $tuffBreakInfo));
		self::register("polished_tuff_wall", fn(BID $id) => new Wall($id, "Polished Tuff Wall", $tuffBreakInfo));
	}

	private function registerCauldronBlocks() : void{
		$cauldronBreakInfo = new Info(BreakInfo::pickaxe(2, ToolTier::WOOD));

		self::register("cauldron", fn(BID $id) => new Cauldron($id, "Cauldron", $cauldronBreakInfo), TileCauldron::class);
		self::register("water_cauldron", fn(BID $id) => new WaterCauldron($id, "Water Cauldron", $cauldronBreakInfo), TileCauldron::class);
		self::register("lava_cauldron", fn(BID $id) => new LavaCauldron($id, "Lava Cauldron", $cauldronBreakInfo), TileCauldron::class);
		self::register("potion_cauldron", fn(BID $id) => new PotionCauldron($id, "Potion Cauldron", $cauldronBreakInfo), TileCauldron::class);
	}
}
