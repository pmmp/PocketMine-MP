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

namespace pocketmine\data\bedrock\block\convert;

use pocketmine\block\ActivatorRail;
use pocketmine\block\AmethystCluster;
use pocketmine\block\Anvil;
use pocketmine\block\Bamboo;
use pocketmine\block\BambooSapling;
use pocketmine\block\Barrel;
use pocketmine\block\Bed;
use pocketmine\block\Beetroot;
use pocketmine\block\Bell;
use pocketmine\block\BigDripleafHead;
use pocketmine\block\BigDripleafStem;
use pocketmine\block\Block;
use pocketmine\block\BoneBlock;
use pocketmine\block\BrewingStand;
use pocketmine\block\BrownMushroomBlock;
use pocketmine\block\Button;
use pocketmine\block\Cactus;
use pocketmine\block\Cake;
use pocketmine\block\CakeWithCandle;
use pocketmine\block\CakeWithDyedCandle;
use pocketmine\block\Campfire;
use pocketmine\block\Candle;
use pocketmine\block\Carpet;
use pocketmine\block\Carrot;
use pocketmine\block\CarvedPumpkin;
use pocketmine\block\CaveVines;
use pocketmine\block\Chain;
use pocketmine\block\ChemistryTable;
use pocketmine\block\Chest;
use pocketmine\block\ChiseledBookshelf;
use pocketmine\block\ChorusFlower;
use pocketmine\block\CocoaBlock;
use pocketmine\block\Concrete;
use pocketmine\block\ConcretePowder;
use pocketmine\block\Copper;
use pocketmine\block\CopperBulb;
use pocketmine\block\CopperDoor;
use pocketmine\block\CopperGrate;
use pocketmine\block\CopperSlab;
use pocketmine\block\CopperStairs;
use pocketmine\block\CopperTrapdoor;
use pocketmine\block\Coral;
use pocketmine\block\CoralBlock;
use pocketmine\block\DaylightSensor;
use pocketmine\block\DetectorRail;
use pocketmine\block\Dirt;
use pocketmine\block\Door;
use pocketmine\block\DoublePitcherCrop;
use pocketmine\block\DoublePlant;
use pocketmine\block\DoubleTallGrass;
use pocketmine\block\DyedCandle;
use pocketmine\block\DyedShulkerBox;
use pocketmine\block\EnderChest;
use pocketmine\block\EndPortalFrame;
use pocketmine\block\EndRod;
use pocketmine\block\Farmland;
use pocketmine\block\FenceGate;
use pocketmine\block\FillableCauldron;
use pocketmine\block\Fire;
use pocketmine\block\FloorBanner;
use pocketmine\block\FloorCoralFan;
use pocketmine\block\FloorSign;
use pocketmine\block\Froglight;
use pocketmine\block\FrostedIce;
use pocketmine\block\Furnace;
use pocketmine\block\GlazedTerracotta;
use pocketmine\block\GlowLichen;
use pocketmine\block\HayBale;
use pocketmine\block\Hopper;
use pocketmine\block\ItemFrame;
use pocketmine\block\Ladder;
use pocketmine\block\Lantern;
use pocketmine\block\Lava;
use pocketmine\block\Leaves;
use pocketmine\block\Lectern;
use pocketmine\block\Lever;
use pocketmine\block\Light;
use pocketmine\block\LightningRod;
use pocketmine\block\LitPumpkin;
use pocketmine\block\Loom;
use pocketmine\block\MelonStem;
use pocketmine\block\MobHead;
use pocketmine\block\NetherPortal;
use pocketmine\block\NetherVines;
use pocketmine\block\NetherWartPlant;
use pocketmine\block\PinkPetals;
use pocketmine\block\PitcherCrop;
use pocketmine\block\Potato;
use pocketmine\block\PoweredRail;
use pocketmine\block\PumpkinStem;
use pocketmine\block\Rail;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\RedstoneLamp;
use pocketmine\block\RedstoneOre;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\RedstoneTorch;
use pocketmine\block\RedstoneWire;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\Sapling;
use pocketmine\block\SeaPickle;
use pocketmine\block\SimplePillar;
use pocketmine\block\SimplePressurePlate;
use pocketmine\block\Slab;
use pocketmine\block\SmallDripleaf;
use pocketmine\block\SnowLayer;
use pocketmine\block\SoulCampfire;
use pocketmine\block\Sponge;
use pocketmine\block\StainedGlass;
use pocketmine\block\StainedGlassPane;
use pocketmine\block\StainedHardenedClay;
use pocketmine\block\StainedHardenedGlass;
use pocketmine\block\StainedHardenedGlassPane;
use pocketmine\block\Stair;
use pocketmine\block\StoneButton;
use pocketmine\block\Stonecutter;
use pocketmine\block\StonePressurePlate;
use pocketmine\block\Sugarcane;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\TNT;
use pocketmine\block\Torch;
use pocketmine\block\TorchflowerCrop;
use pocketmine\block\Trapdoor;
use pocketmine\block\TrappedChest;
use pocketmine\block\Tripwire;
use pocketmine\block\TripwireHook;
use pocketmine\block\UnderwaterTorch;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DripleafState;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\block\Vine;
use pocketmine\block\Wall;
use pocketmine\block\WallBanner;
use pocketmine\block\WallCoralFan;
use pocketmine\block\WallSign;
use pocketmine\block\Water;
use pocketmine\block\WeightedPressurePlateHeavy;
use pocketmine\block\WeightedPressurePlateLight;
use pocketmine\block\Wheat;
use pocketmine\block\Wood;
use pocketmine\block\WoodenButton;
use pocketmine\block\WoodenDoor;
use pocketmine\block\WoodenPressurePlate;
use pocketmine\block\WoodenStairs;
use pocketmine\block\WoodenTrapdoor;
use pocketmine\block\Wool;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockStateSerializeException;
use pocketmine\data\bedrock\block\BlockStateSerializer;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateSerializerHelper as Helper;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use function get_class;

final class BlockObjectToStateSerializer implements BlockStateSerializer{
	/**
	 * These callables actually accept Block, but for the sake of type completeness, it has to be never, since we can't
	 * describe the bottom type of a type hierarchy only containing Block.
	 *
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure(never) : Writer>
	 */
	private array $serializers = [];

	/**
	 * @var BlockStateData[]
	 * @phpstan-var array<int, BlockStateData>
	 */
	private array $cache = [];

	public function __construct(){
		$this->registerCandleSerializers();
		$this->registerFlatColorBlockSerializers();
		$this->registerFlatCoralSerializers();
		$this->registerCauldronSerializers();
		$this->registerFlatWoodBlockSerializers();
		$this->registerLeavesSerializers();
		$this->registerSaplingSerializers();
		$this->registerMobHeadSerializers();
		$this->registerSimpleSerializers();
		$this->registerSerializers();
	}

	public function serialize(int $stateId) : BlockStateData{
		//TODO: singleton usage not ideal
		//TODO: we may want to deduplicate cache entries to avoid wasting memory
		return $this->cache[$stateId] ??= $this->serializeBlock(RuntimeBlockStateRegistry::getInstance()->fromStateId($stateId));
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $block
	 * @phpstan-param \Closure(TBlockType) : Writer $serializer
	 */
	public function map(Block $block, \Closure $serializer) : void{
		if(isset($this->serializers[$block->getTypeId()])){
			throw new \InvalidArgumentException("Block type ID " . $block->getTypeId() . " already has a serializer registered");
		}
		$this->serializers[$block->getTypeId()] = $serializer;
	}

	public function mapSimple(Block $block, string $id) : void{
		$this->map($block, fn() => Writer::create($id));
	}

	public function mapSlab(Slab $block, string $singleId, string $doubleId) : void{
		$this->map($block, fn(Slab $block) => Helper::encodeSlab($block, $singleId, $doubleId));
	}

	public function mapStairs(Stair $block, string $id) : void{
		$this->map($block, fn(Stair $block) => Helper::encodeStairs($block, Writer::create($id)));
	}

	public function mapLog(Wood $block, string $unstrippedId, string $strippedId) : void{
		$this->map($block, fn(Wood $block) => Helper::encodeLog($block, $unstrippedId, $strippedId));
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $blockState
	 *
	 * @throws BlockStateSerializeException
	 */
	public function serializeBlock(Block $blockState) : BlockStateData{
		$typeId = $blockState->getTypeId();

		$locatedSerializer = $this->serializers[$typeId] ?? null;
		if($locatedSerializer === null){
			throw new BlockStateSerializeException("No serializer registered for " . get_class($blockState) . " with type ID $typeId");
		}

		/**
		 * TODO: there is no guarantee that this type actually matches that of $blockState - a plugin may have stolen
		 * the type ID of the block (which never makes sense, even in a world where overriding block types is a thing).
		 * In the future we'll need some way to guarantee that type IDs are never reused (perhaps spl_object_id()?)
		 *
		 * @var \Closure $serializer
		 * @phpstan-var \Closure(TBlockType) : Writer $serializer
		 */
		$serializer = $locatedSerializer;

		/** @var Writer $writer */
		$writer = $serializer($blockState);
		return $writer->getBlockStateData();
	}

	private function registerCandleSerializers() : void{
		$this->map(Blocks::CANDLE(), fn(Candle $block) => Helper::encodeCandle($block, new Writer(Ids::CANDLE)));
		$this->map(Blocks::DYED_CANDLE(), fn(DyedCandle $block) => Helper::encodeCandle($block, new Writer(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_CANDLE,
			DyeColor::BLUE => Ids::BLUE_CANDLE,
			DyeColor::BROWN => Ids::BROWN_CANDLE,
			DyeColor::CYAN => Ids::CYAN_CANDLE,
			DyeColor::GRAY => Ids::GRAY_CANDLE,
			DyeColor::GREEN => Ids::GREEN_CANDLE,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_CANDLE,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_CANDLE,
			DyeColor::LIME => Ids::LIME_CANDLE,
			DyeColor::MAGENTA => Ids::MAGENTA_CANDLE,
			DyeColor::ORANGE => Ids::ORANGE_CANDLE,
			DyeColor::PINK => Ids::PINK_CANDLE,
			DyeColor::PURPLE => Ids::PURPLE_CANDLE,
			DyeColor::RED => Ids::RED_CANDLE,
			DyeColor::WHITE => Ids::WHITE_CANDLE,
			DyeColor::YELLOW => Ids::YELLOW_CANDLE,
		})));
		$this->map(Blocks::CAKE_WITH_CANDLE(), fn(CakeWithCandle $block) => Writer::create(Ids::CANDLE_CAKE)
			->writeBool(StateNames::LIT, $block->isLit()));
		$this->map(Blocks::CAKE_WITH_DYED_CANDLE(), fn(CakeWithDyedCandle $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_CANDLE_CAKE,
			DyeColor::BLUE => Ids::BLUE_CANDLE_CAKE,
			DyeColor::BROWN => Ids::BROWN_CANDLE_CAKE,
			DyeColor::CYAN => Ids::CYAN_CANDLE_CAKE,
			DyeColor::GRAY => Ids::GRAY_CANDLE_CAKE,
			DyeColor::GREEN => Ids::GREEN_CANDLE_CAKE,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_CANDLE_CAKE,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_CANDLE_CAKE,
			DyeColor::LIME => Ids::LIME_CANDLE_CAKE,
			DyeColor::MAGENTA => Ids::MAGENTA_CANDLE_CAKE,
			DyeColor::ORANGE => Ids::ORANGE_CANDLE_CAKE,
			DyeColor::PINK => Ids::PINK_CANDLE_CAKE,
			DyeColor::PURPLE => Ids::PURPLE_CANDLE_CAKE,
			DyeColor::RED => Ids::RED_CANDLE_CAKE,
			DyeColor::WHITE => Ids::WHITE_CANDLE_CAKE,
			DyeColor::YELLOW => Ids::YELLOW_CANDLE_CAKE,
		})->writeBool(StateNames::LIT, $block->isLit()));
	}

	public function registerFlatColorBlockSerializers() : void{
		$this->map(Blocks::STAINED_HARDENED_GLASS(), fn(StainedHardenedGlass $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::HARD_BLACK_STAINED_GLASS,
			DyeColor::BLUE => Ids::HARD_BLUE_STAINED_GLASS,
			DyeColor::BROWN => Ids::HARD_BROWN_STAINED_GLASS,
			DyeColor::CYAN => Ids::HARD_CYAN_STAINED_GLASS,
			DyeColor::GRAY => Ids::HARD_GRAY_STAINED_GLASS,
			DyeColor::GREEN => Ids::HARD_GREEN_STAINED_GLASS,
			DyeColor::LIGHT_BLUE => Ids::HARD_LIGHT_BLUE_STAINED_GLASS,
			DyeColor::LIGHT_GRAY => Ids::HARD_LIGHT_GRAY_STAINED_GLASS,
			DyeColor::LIME => Ids::HARD_LIME_STAINED_GLASS,
			DyeColor::MAGENTA => Ids::HARD_MAGENTA_STAINED_GLASS,
			DyeColor::ORANGE => Ids::HARD_ORANGE_STAINED_GLASS,
			DyeColor::PINK => Ids::HARD_PINK_STAINED_GLASS,
			DyeColor::PURPLE => Ids::HARD_PURPLE_STAINED_GLASS,
			DyeColor::RED => Ids::HARD_RED_STAINED_GLASS,
			DyeColor::WHITE => Ids::HARD_WHITE_STAINED_GLASS,
			DyeColor::YELLOW => Ids::HARD_YELLOW_STAINED_GLASS,
		}));

		$this->map(Blocks::STAINED_HARDENED_GLASS_PANE(), fn(StainedHardenedGlassPane $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::HARD_BLACK_STAINED_GLASS_PANE,
			DyeColor::BLUE => Ids::HARD_BLUE_STAINED_GLASS_PANE,
			DyeColor::BROWN => Ids::HARD_BROWN_STAINED_GLASS_PANE,
			DyeColor::CYAN => Ids::HARD_CYAN_STAINED_GLASS_PANE,
			DyeColor::GRAY => Ids::HARD_GRAY_STAINED_GLASS_PANE,
			DyeColor::GREEN => Ids::HARD_GREEN_STAINED_GLASS_PANE,
			DyeColor::LIGHT_BLUE => Ids::HARD_LIGHT_BLUE_STAINED_GLASS_PANE,
			DyeColor::LIGHT_GRAY => Ids::HARD_LIGHT_GRAY_STAINED_GLASS_PANE,
			DyeColor::LIME => Ids::HARD_LIME_STAINED_GLASS_PANE,
			DyeColor::MAGENTA => Ids::HARD_MAGENTA_STAINED_GLASS_PANE,
			DyeColor::ORANGE => Ids::HARD_ORANGE_STAINED_GLASS_PANE,
			DyeColor::PINK => Ids::HARD_PINK_STAINED_GLASS_PANE,
			DyeColor::PURPLE => Ids::HARD_PURPLE_STAINED_GLASS_PANE,
			DyeColor::RED => Ids::HARD_RED_STAINED_GLASS_PANE,
			DyeColor::WHITE => Ids::HARD_WHITE_STAINED_GLASS_PANE,
			DyeColor::YELLOW => Ids::HARD_YELLOW_STAINED_GLASS_PANE,
		}));

		$this->map(Blocks::GLAZED_TERRACOTTA(), function(GlazedTerracotta $block) : Writer{
			return Writer::create(match($block->getColor()){
				DyeColor::BLACK => Ids::BLACK_GLAZED_TERRACOTTA,
				DyeColor::BLUE => Ids::BLUE_GLAZED_TERRACOTTA,
				DyeColor::BROWN => Ids::BROWN_GLAZED_TERRACOTTA,
				DyeColor::CYAN => Ids::CYAN_GLAZED_TERRACOTTA,
				DyeColor::GRAY => Ids::GRAY_GLAZED_TERRACOTTA,
				DyeColor::GREEN => Ids::GREEN_GLAZED_TERRACOTTA,
				DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_GLAZED_TERRACOTTA,
				DyeColor::LIGHT_GRAY => Ids::SILVER_GLAZED_TERRACOTTA,
				DyeColor::LIME => Ids::LIME_GLAZED_TERRACOTTA,
				DyeColor::MAGENTA => Ids::MAGENTA_GLAZED_TERRACOTTA,
				DyeColor::ORANGE => Ids::ORANGE_GLAZED_TERRACOTTA,
				DyeColor::PINK => Ids::PINK_GLAZED_TERRACOTTA,
				DyeColor::PURPLE => Ids::PURPLE_GLAZED_TERRACOTTA,
				DyeColor::RED => Ids::RED_GLAZED_TERRACOTTA,
				DyeColor::WHITE => Ids::WHITE_GLAZED_TERRACOTTA,
				DyeColor::YELLOW => Ids::YELLOW_GLAZED_TERRACOTTA,
			})
				->writeHorizontalFacing($block->getFacing());
		});

		$this->map(Blocks::WOOL(), fn(Wool $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_WOOL,
			DyeColor::BLUE => Ids::BLUE_WOOL,
			DyeColor::BROWN => Ids::BROWN_WOOL,
			DyeColor::CYAN => Ids::CYAN_WOOL,
			DyeColor::GRAY => Ids::GRAY_WOOL,
			DyeColor::GREEN => Ids::GREEN_WOOL,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_WOOL,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_WOOL,
			DyeColor::LIME => Ids::LIME_WOOL,
			DyeColor::MAGENTA => Ids::MAGENTA_WOOL,
			DyeColor::ORANGE => Ids::ORANGE_WOOL,
			DyeColor::PINK => Ids::PINK_WOOL,
			DyeColor::PURPLE => Ids::PURPLE_WOOL,
			DyeColor::RED => Ids::RED_WOOL,
			DyeColor::WHITE => Ids::WHITE_WOOL,
			DyeColor::YELLOW => Ids::YELLOW_WOOL,
		}));

		$this->map(Blocks::CARPET(), fn(Carpet $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_CARPET,
			DyeColor::BLUE => Ids::BLUE_CARPET,
			DyeColor::BROWN => Ids::BROWN_CARPET,
			DyeColor::CYAN => Ids::CYAN_CARPET,
			DyeColor::GRAY => Ids::GRAY_CARPET,
			DyeColor::GREEN => Ids::GREEN_CARPET,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_CARPET,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_CARPET,
			DyeColor::LIME => Ids::LIME_CARPET,
			DyeColor::MAGENTA => Ids::MAGENTA_CARPET,
			DyeColor::ORANGE => Ids::ORANGE_CARPET,
			DyeColor::PINK => Ids::PINK_CARPET,
			DyeColor::PURPLE => Ids::PURPLE_CARPET,
			DyeColor::RED => Ids::RED_CARPET,
			DyeColor::WHITE => Ids::WHITE_CARPET,
			DyeColor::YELLOW => Ids::YELLOW_CARPET,
		}));

		$this->map(Blocks::DYED_SHULKER_BOX(), fn(DyedShulkerBox $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_SHULKER_BOX,
			DyeColor::BLUE => Ids::BLUE_SHULKER_BOX,
			DyeColor::BROWN => Ids::BROWN_SHULKER_BOX,
			DyeColor::CYAN => Ids::CYAN_SHULKER_BOX,
			DyeColor::GRAY => Ids::GRAY_SHULKER_BOX,
			DyeColor::GREEN => Ids::GREEN_SHULKER_BOX,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_SHULKER_BOX,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_SHULKER_BOX,
			DyeColor::LIME => Ids::LIME_SHULKER_BOX,
			DyeColor::MAGENTA => Ids::MAGENTA_SHULKER_BOX,
			DyeColor::ORANGE => Ids::ORANGE_SHULKER_BOX,
			DyeColor::PINK => Ids::PINK_SHULKER_BOX,
			DyeColor::PURPLE => Ids::PURPLE_SHULKER_BOX,
			DyeColor::RED => Ids::RED_SHULKER_BOX,
			DyeColor::WHITE => Ids::WHITE_SHULKER_BOX,
			DyeColor::YELLOW => Ids::YELLOW_SHULKER_BOX,
		}));

		$this->map(Blocks::CONCRETE(), fn(Concrete $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_CONCRETE,
			DyeColor::BLUE => Ids::BLUE_CONCRETE,
			DyeColor::BROWN => Ids::BROWN_CONCRETE,
			DyeColor::CYAN => Ids::CYAN_CONCRETE,
			DyeColor::GRAY => Ids::GRAY_CONCRETE,
			DyeColor::GREEN => Ids::GREEN_CONCRETE,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_CONCRETE,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_CONCRETE,
			DyeColor::LIME => Ids::LIME_CONCRETE,
			DyeColor::MAGENTA => Ids::MAGENTA_CONCRETE,
			DyeColor::ORANGE => Ids::ORANGE_CONCRETE,
			DyeColor::PINK => Ids::PINK_CONCRETE,
			DyeColor::PURPLE => Ids::PURPLE_CONCRETE,
			DyeColor::RED => Ids::RED_CONCRETE,
			DyeColor::WHITE => Ids::WHITE_CONCRETE,
			DyeColor::YELLOW => Ids::YELLOW_CONCRETE,
		}));

		$this->map(Blocks::CONCRETE_POWDER(), fn(ConcretePowder $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_CONCRETE_POWDER,
			DyeColor::BLUE => Ids::BLUE_CONCRETE_POWDER,
			DyeColor::BROWN => Ids::BROWN_CONCRETE_POWDER,
			DyeColor::CYAN => Ids::CYAN_CONCRETE_POWDER,
			DyeColor::GRAY => Ids::GRAY_CONCRETE_POWDER,
			DyeColor::GREEN => Ids::GREEN_CONCRETE_POWDER,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_CONCRETE_POWDER,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_CONCRETE_POWDER,
			DyeColor::LIME => Ids::LIME_CONCRETE_POWDER,
			DyeColor::MAGENTA => Ids::MAGENTA_CONCRETE_POWDER,
			DyeColor::ORANGE => Ids::ORANGE_CONCRETE_POWDER,
			DyeColor::PINK => Ids::PINK_CONCRETE_POWDER,
			DyeColor::PURPLE => Ids::PURPLE_CONCRETE_POWDER,
			DyeColor::RED => Ids::RED_CONCRETE_POWDER,
			DyeColor::WHITE => Ids::WHITE_CONCRETE_POWDER,
			DyeColor::YELLOW => Ids::YELLOW_CONCRETE_POWDER,
		}));

		$this->map(Blocks::STAINED_CLAY(), fn(StainedHardenedClay $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_TERRACOTTA,
			DyeColor::BLUE => Ids::BLUE_TERRACOTTA,
			DyeColor::BROWN => Ids::BROWN_TERRACOTTA,
			DyeColor::CYAN => Ids::CYAN_TERRACOTTA,
			DyeColor::GRAY => Ids::GRAY_TERRACOTTA,
			DyeColor::GREEN => Ids::GREEN_TERRACOTTA,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_TERRACOTTA,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_TERRACOTTA,
			DyeColor::LIME => Ids::LIME_TERRACOTTA,
			DyeColor::MAGENTA => Ids::MAGENTA_TERRACOTTA,
			DyeColor::ORANGE => Ids::ORANGE_TERRACOTTA,
			DyeColor::PINK => Ids::PINK_TERRACOTTA,
			DyeColor::PURPLE => Ids::PURPLE_TERRACOTTA,
			DyeColor::RED => Ids::RED_TERRACOTTA,
			DyeColor::WHITE => Ids::WHITE_TERRACOTTA,
			DyeColor::YELLOW => Ids::YELLOW_TERRACOTTA,
		}));

		$this->map(Blocks::STAINED_GLASS(), fn(StainedGlass $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_STAINED_GLASS,
			DyeColor::BLUE => Ids::BLUE_STAINED_GLASS,
			DyeColor::BROWN => Ids::BROWN_STAINED_GLASS,
			DyeColor::CYAN => Ids::CYAN_STAINED_GLASS,
			DyeColor::GRAY => Ids::GRAY_STAINED_GLASS,
			DyeColor::GREEN => Ids::GREEN_STAINED_GLASS,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_STAINED_GLASS,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_STAINED_GLASS,
			DyeColor::LIME => Ids::LIME_STAINED_GLASS,
			DyeColor::MAGENTA => Ids::MAGENTA_STAINED_GLASS,
			DyeColor::ORANGE => Ids::ORANGE_STAINED_GLASS,
			DyeColor::PINK => Ids::PINK_STAINED_GLASS,
			DyeColor::PURPLE => Ids::PURPLE_STAINED_GLASS,
			DyeColor::RED => Ids::RED_STAINED_GLASS,
			DyeColor::WHITE => Ids::WHITE_STAINED_GLASS,
			DyeColor::YELLOW => Ids::YELLOW_STAINED_GLASS,
		}));

		$this->map(Blocks::STAINED_GLASS_PANE(), fn(StainedGlassPane $block) => Writer::create(match($block->getColor()){
			DyeColor::BLACK => Ids::BLACK_STAINED_GLASS_PANE,
			DyeColor::BLUE => Ids::BLUE_STAINED_GLASS_PANE,
			DyeColor::BROWN => Ids::BROWN_STAINED_GLASS_PANE,
			DyeColor::CYAN => Ids::CYAN_STAINED_GLASS_PANE,
			DyeColor::GRAY => Ids::GRAY_STAINED_GLASS_PANE,
			DyeColor::GREEN => Ids::GREEN_STAINED_GLASS_PANE,
			DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_STAINED_GLASS_PANE,
			DyeColor::LIGHT_GRAY => Ids::LIGHT_GRAY_STAINED_GLASS_PANE,
			DyeColor::LIME => Ids::LIME_STAINED_GLASS_PANE,
			DyeColor::MAGENTA => Ids::MAGENTA_STAINED_GLASS_PANE,
			DyeColor::ORANGE => Ids::ORANGE_STAINED_GLASS_PANE,
			DyeColor::PINK => Ids::PINK_STAINED_GLASS_PANE,
			DyeColor::PURPLE => Ids::PURPLE_STAINED_GLASS_PANE,
			DyeColor::RED => Ids::RED_STAINED_GLASS_PANE,
			DyeColor::WHITE => Ids::WHITE_STAINED_GLASS_PANE,
			DyeColor::YELLOW => Ids::YELLOW_STAINED_GLASS_PANE,
		}));
	}

	private function registerFlatCoralSerializers() : void{
		$this->map(Blocks::CORAL(), fn(Coral $block) => Writer::create(
			match($block->getCoralType()){
				CoralType::BRAIN => $block->isDead() ? Ids::DEAD_BRAIN_CORAL : Ids::BRAIN_CORAL,
				CoralType::BUBBLE => $block->isDead() ? Ids::DEAD_BUBBLE_CORAL : Ids::BUBBLE_CORAL,
				CoralType::FIRE => $block->isDead() ? Ids::DEAD_FIRE_CORAL : Ids::FIRE_CORAL,
				CoralType::HORN => $block->isDead() ? Ids::DEAD_HORN_CORAL : Ids::HORN_CORAL,
				CoralType::TUBE => $block->isDead() ? Ids::DEAD_TUBE_CORAL : Ids::TUBE_CORAL,
			}
		));

		$this->map(Blocks::CORAL_FAN(), fn(FloorCoralFan $block) => Writer::create(
			match($block->getCoralType()){
				CoralType::BRAIN => $block->isDead() ? Ids::DEAD_BRAIN_CORAL_FAN : Ids::BRAIN_CORAL_FAN,
				CoralType::BUBBLE => $block->isDead() ? Ids::DEAD_BUBBLE_CORAL_FAN : Ids::BUBBLE_CORAL_FAN,
				CoralType::FIRE => $block->isDead() ? Ids::DEAD_FIRE_CORAL_FAN : Ids::FIRE_CORAL_FAN,
				CoralType::HORN => $block->isDead() ? Ids::DEAD_HORN_CORAL_FAN : Ids::HORN_CORAL_FAN,
				CoralType::TUBE => $block->isDead() ? Ids::DEAD_TUBE_CORAL_FAN : Ids::TUBE_CORAL_FAN,
			})
			->writeInt(StateNames::CORAL_FAN_DIRECTION, match($axis = $block->getAxis()){
				Axis::X => 0,
				Axis::Z => 1,
				default => throw new BlockStateSerializeException("Invalid axis {$axis}"),
			}));

		$this->map(Blocks::CORAL_BLOCK(), fn(CoralBlock $block) => Writer::create(
			match($block->getCoralType()){
				CoralType::BRAIN => $block->isDead() ? Ids::DEAD_BRAIN_CORAL_BLOCK : Ids::BRAIN_CORAL_BLOCK,
				CoralType::BUBBLE => $block->isDead() ? Ids::DEAD_BUBBLE_CORAL_BLOCK : Ids::BUBBLE_CORAL_BLOCK,
				CoralType::FIRE => $block->isDead() ? Ids::DEAD_FIRE_CORAL_BLOCK : Ids::FIRE_CORAL_BLOCK,
				CoralType::HORN => $block->isDead() ? Ids::DEAD_HORN_CORAL_BLOCK : Ids::HORN_CORAL_BLOCK,
				CoralType::TUBE => $block->isDead() ? Ids::DEAD_TUBE_CORAL_BLOCK : Ids::TUBE_CORAL_BLOCK,
			}
		));

		$this->map(Blocks::WALL_CORAL_FAN(), fn(WallCoralFan $block) => Writer::create(
			match($block->getCoralType()){
				CoralType::TUBE => $block->isDead() ? Ids::DEAD_TUBE_CORAL_WALL_FAN : Ids::TUBE_CORAL_WALL_FAN,
				CoralType::BRAIN => $block->isDead() ? Ids::DEAD_BRAIN_CORAL_WALL_FAN : Ids::BRAIN_CORAL_WALL_FAN,
				CoralType::BUBBLE => $block->isDead() ? Ids::DEAD_BUBBLE_CORAL_WALL_FAN : Ids::BUBBLE_CORAL_WALL_FAN,
				CoralType::FIRE => $block->isDead() ? Ids::DEAD_FIRE_CORAL_WALL_FAN : Ids::FIRE_CORAL_WALL_FAN,
				CoralType::HORN => $block->isDead() ? Ids::DEAD_HORN_CORAL_WALL_FAN : Ids::HORN_CORAL_WALL_FAN,
			})
			->writeCoralFacing($block->getFacing())
		);
	}

	private function registerCauldronSerializers() : void{
		$this->map(Blocks::CAULDRON(), fn() => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, 0));
		$this->map(Blocks::LAVA_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_LAVA, $b->getFillLevel()));
		//potion cauldrons store their real information in the block actor data
		$this->map(Blocks::POTION_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, $b->getFillLevel()));
		$this->map(Blocks::WATER_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, $b->getFillLevel()));
	}

	private function registerFlatWoodBlockSerializers() : void{
		$this->map(Blocks::ACACIA_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::ACACIA_BUTTON)));
		$this->map(Blocks::ACACIA_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::ACACIA_DOOR)));
		$this->map(Blocks::ACACIA_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::ACACIA_FENCE_GATE)));
		$this->map(Blocks::ACACIA_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::ACACIA_PRESSURE_PLATE)));
		$this->map(Blocks::ACACIA_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::ACACIA_STANDING_SIGN)));
		$this->map(Blocks::ACACIA_STAIRS(), fn(WoodenStairs $block) => Helper::encodeStairs($block, new Writer(Ids::ACACIA_STAIRS)));
		$this->map(Blocks::ACACIA_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::ACACIA_TRAPDOOR)));
		$this->map(Blocks::ACACIA_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::ACACIA_WALL_SIGN)));
		$this->mapLog(Blocks::ACACIA_LOG(), Ids::ACACIA_LOG, Ids::STRIPPED_ACACIA_LOG);
		$this->mapLog(Blocks::ACACIA_WOOD(), Ids::ACACIA_WOOD, Ids::STRIPPED_ACACIA_WOOD);
		$this->mapSimple(Blocks::ACACIA_FENCE(), Ids::ACACIA_FENCE);
		$this->mapSimple(Blocks::ACACIA_PLANKS(), Ids::ACACIA_PLANKS);
		$this->mapSlab(Blocks::ACACIA_SLAB(), Ids::ACACIA_SLAB, Ids::ACACIA_DOUBLE_SLAB);

		$this->map(Blocks::BIRCH_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::BIRCH_BUTTON)));
		$this->map(Blocks::BIRCH_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::BIRCH_DOOR)));
		$this->map(Blocks::BIRCH_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::BIRCH_FENCE_GATE)));
		$this->map(Blocks::BIRCH_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::BIRCH_PRESSURE_PLATE)));
		$this->map(Blocks::BIRCH_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::BIRCH_STANDING_SIGN)));
		$this->map(Blocks::BIRCH_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::BIRCH_TRAPDOOR)));
		$this->map(Blocks::BIRCH_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::BIRCH_WALL_SIGN)));
		$this->mapLog(Blocks::BIRCH_LOG(), Ids::BIRCH_LOG, Ids::STRIPPED_BIRCH_LOG);
		$this->mapLog(Blocks::BIRCH_WOOD(), Ids::BIRCH_WOOD, Ids::STRIPPED_BIRCH_WOOD);
		$this->mapSimple(Blocks::BIRCH_FENCE(), Ids::BIRCH_FENCE);
		$this->mapSimple(Blocks::BIRCH_PLANKS(), Ids::BIRCH_PLANKS);
		$this->mapSlab(Blocks::BIRCH_SLAB(), Ids::BIRCH_SLAB, Ids::BIRCH_DOUBLE_SLAB);
		$this->mapStairs(Blocks::BIRCH_STAIRS(), Ids::BIRCH_STAIRS);

		$this->map(Blocks::CHERRY_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::CHERRY_BUTTON)));
		$this->map(Blocks::CHERRY_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::CHERRY_DOOR)));
		$this->map(Blocks::CHERRY_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::CHERRY_FENCE_GATE)));
		$this->map(Blocks::CHERRY_LOG(), fn(Wood $block) => Helper::encodeLog($block, Ids::CHERRY_LOG, Ids::STRIPPED_CHERRY_LOG));
		$this->map(Blocks::CHERRY_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::CHERRY_PRESSURE_PLATE)));
		$this->map(Blocks::CHERRY_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::CHERRY_STANDING_SIGN)));
		$this->map(Blocks::CHERRY_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::CHERRY_TRAPDOOR)));
		$this->map(Blocks::CHERRY_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::CHERRY_WALL_SIGN)));
		$this->mapSimple(Blocks::CHERRY_FENCE(), Ids::CHERRY_FENCE);
		$this->mapSimple(Blocks::CHERRY_PLANKS(), Ids::CHERRY_PLANKS);
		$this->mapSlab(Blocks::CHERRY_SLAB(), Ids::CHERRY_SLAB, Ids::CHERRY_DOUBLE_SLAB);
		$this->mapStairs(Blocks::CHERRY_STAIRS(), Ids::CHERRY_STAIRS);
		$this->mapLog(Blocks::CHERRY_WOOD(), Ids::CHERRY_WOOD, Ids::STRIPPED_CHERRY_WOOD);

		$this->map(Blocks::CRIMSON_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::CRIMSON_BUTTON)));
		$this->map(Blocks::CRIMSON_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::CRIMSON_DOOR)));
		$this->map(Blocks::CRIMSON_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::CRIMSON_FENCE_GATE)));
		$this->map(Blocks::CRIMSON_HYPHAE(), fn(Wood $block) => Helper::encodeLog($block, Ids::CRIMSON_HYPHAE, Ids::STRIPPED_CRIMSON_HYPHAE));
		$this->map(Blocks::CRIMSON_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::CRIMSON_PRESSURE_PLATE)));
		$this->map(Blocks::CRIMSON_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::CRIMSON_STANDING_SIGN)));
		$this->map(Blocks::CRIMSON_STEM(), fn(Wood $block) => Helper::encodeLog($block, Ids::CRIMSON_STEM, Ids::STRIPPED_CRIMSON_STEM));
		$this->map(Blocks::CRIMSON_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::CRIMSON_TRAPDOOR)));
		$this->map(Blocks::CRIMSON_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::CRIMSON_WALL_SIGN)));
		$this->mapSimple(Blocks::CRIMSON_FENCE(), Ids::CRIMSON_FENCE);
		$this->mapSimple(Blocks::CRIMSON_PLANKS(), Ids::CRIMSON_PLANKS);
		$this->mapSlab(Blocks::CRIMSON_SLAB(), Ids::CRIMSON_SLAB, Ids::CRIMSON_DOUBLE_SLAB);
		$this->mapStairs(Blocks::CRIMSON_STAIRS(), Ids::CRIMSON_STAIRS);

		$this->map(Blocks::DARK_OAK_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::DARK_OAK_BUTTON)));
		$this->map(Blocks::DARK_OAK_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::DARK_OAK_DOOR)));
		$this->map(Blocks::DARK_OAK_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::DARK_OAK_FENCE_GATE)));
		$this->map(Blocks::DARK_OAK_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::DARK_OAK_PRESSURE_PLATE)));
		$this->map(Blocks::DARK_OAK_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::DARKOAK_STANDING_SIGN)));
		$this->map(Blocks::DARK_OAK_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::DARK_OAK_TRAPDOOR)));
		$this->map(Blocks::DARK_OAK_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::DARKOAK_WALL_SIGN)));
		$this->mapLog(Blocks::DARK_OAK_LOG(), Ids::DARK_OAK_LOG, Ids::STRIPPED_DARK_OAK_LOG);
		$this->mapLog(Blocks::DARK_OAK_WOOD(), Ids::DARK_OAK_WOOD, Ids::STRIPPED_DARK_OAK_WOOD);
		$this->mapSimple(Blocks::DARK_OAK_FENCE(), Ids::DARK_OAK_FENCE);
		$this->mapSimple(Blocks::DARK_OAK_PLANKS(), Ids::DARK_OAK_PLANKS);
		$this->mapSlab(Blocks::DARK_OAK_SLAB(), Ids::DARK_OAK_SLAB, Ids::DARK_OAK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::DARK_OAK_STAIRS(), Ids::DARK_OAK_STAIRS);

		$this->map(Blocks::JUNGLE_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::JUNGLE_BUTTON)));
		$this->map(Blocks::JUNGLE_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::JUNGLE_DOOR)));
		$this->map(Blocks::JUNGLE_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::JUNGLE_FENCE_GATE)));
		$this->map(Blocks::JUNGLE_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::JUNGLE_PRESSURE_PLATE)));
		$this->map(Blocks::JUNGLE_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::JUNGLE_STANDING_SIGN)));
		$this->map(Blocks::JUNGLE_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::JUNGLE_TRAPDOOR)));
		$this->map(Blocks::JUNGLE_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::JUNGLE_WALL_SIGN)));
		$this->mapLog(Blocks::JUNGLE_LOG(), Ids::JUNGLE_LOG, Ids::STRIPPED_JUNGLE_LOG);
		$this->mapLog(Blocks::JUNGLE_WOOD(), Ids::JUNGLE_WOOD, Ids::STRIPPED_JUNGLE_WOOD);
		$this->mapSimple(Blocks::JUNGLE_FENCE(), Ids::JUNGLE_FENCE);
		$this->mapSimple(Blocks::JUNGLE_PLANKS(), Ids::JUNGLE_PLANKS);
		$this->mapSlab(Blocks::JUNGLE_SLAB(), Ids::JUNGLE_SLAB, Ids::JUNGLE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::JUNGLE_STAIRS(), Ids::JUNGLE_STAIRS);

		$this->map(Blocks::MANGROVE_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::MANGROVE_BUTTON)));
		$this->map(Blocks::MANGROVE_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::MANGROVE_DOOR)));
		$this->map(Blocks::MANGROVE_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::MANGROVE_FENCE_GATE)));
		$this->map(Blocks::MANGROVE_LOG(), fn(Wood $block) => Helper::encodeLog($block, Ids::MANGROVE_LOG, Ids::STRIPPED_MANGROVE_LOG));
		$this->map(Blocks::MANGROVE_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::MANGROVE_PRESSURE_PLATE)));
		$this->map(Blocks::MANGROVE_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::MANGROVE_STANDING_SIGN)));
		$this->map(Blocks::MANGROVE_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::MANGROVE_TRAPDOOR)));
		$this->map(Blocks::MANGROVE_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::MANGROVE_WALL_SIGN)));
		$this->mapSimple(Blocks::MANGROVE_FENCE(), Ids::MANGROVE_FENCE);
		$this->mapSimple(Blocks::MANGROVE_PLANKS(), Ids::MANGROVE_PLANKS);
		$this->mapSlab(Blocks::MANGROVE_SLAB(), Ids::MANGROVE_SLAB, Ids::MANGROVE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::MANGROVE_STAIRS(), Ids::MANGROVE_STAIRS);
		$this->mapLog(Blocks::MANGROVE_WOOD(), Ids::MANGROVE_WOOD, Ids::STRIPPED_MANGROVE_WOOD);

		$this->map(Blocks::OAK_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::WOODEN_BUTTON)));
		$this->map(Blocks::OAK_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::WOODEN_DOOR)));
		$this->map(Blocks::OAK_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::FENCE_GATE)));
		$this->map(Blocks::OAK_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::WOODEN_PRESSURE_PLATE)));
		$this->map(Blocks::OAK_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::STANDING_SIGN)));
		$this->map(Blocks::OAK_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::TRAPDOOR)));
		$this->map(Blocks::OAK_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::WALL_SIGN)));
		$this->mapLog(Blocks::OAK_LOG(), Ids::OAK_LOG, Ids::STRIPPED_OAK_LOG);
		$this->mapLog(Blocks::OAK_WOOD(), Ids::OAK_WOOD, Ids::STRIPPED_OAK_WOOD);
		$this->mapSimple(Blocks::OAK_FENCE(), Ids::OAK_FENCE);
		$this->mapSimple(Blocks::OAK_PLANKS(), Ids::OAK_PLANKS);
		$this->mapSlab(Blocks::OAK_SLAB(), Ids::OAK_SLAB, Ids::OAK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::OAK_STAIRS(), Ids::OAK_STAIRS);

		$this->map(Blocks::SPRUCE_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::SPRUCE_BUTTON)));
		$this->map(Blocks::SPRUCE_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::SPRUCE_DOOR)));
		$this->map(Blocks::SPRUCE_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::SPRUCE_FENCE_GATE)));
		$this->map(Blocks::SPRUCE_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::SPRUCE_PRESSURE_PLATE)));
		$this->map(Blocks::SPRUCE_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::SPRUCE_STANDING_SIGN)));
		$this->map(Blocks::SPRUCE_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::SPRUCE_TRAPDOOR)));
		$this->map(Blocks::SPRUCE_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::SPRUCE_WALL_SIGN)));
		$this->mapLog(Blocks::SPRUCE_LOG(), Ids::SPRUCE_LOG, Ids::STRIPPED_SPRUCE_LOG);
		$this->mapLog(Blocks::SPRUCE_WOOD(), Ids::SPRUCE_WOOD, Ids::STRIPPED_SPRUCE_WOOD);
		$this->mapSimple(Blocks::SPRUCE_FENCE(), Ids::SPRUCE_FENCE);
		$this->mapSimple(Blocks::SPRUCE_PLANKS(), Ids::SPRUCE_PLANKS);
		$this->mapSlab(Blocks::SPRUCE_SLAB(), Ids::SPRUCE_SLAB, Ids::SPRUCE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::SPRUCE_STAIRS(), Ids::SPRUCE_STAIRS);
		//wood and slabs still use the old way of storing wood type

		$this->map(Blocks::WARPED_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::WARPED_BUTTON)));
		$this->map(Blocks::WARPED_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::WARPED_DOOR)));
		$this->map(Blocks::WARPED_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::WARPED_FENCE_GATE)));
		$this->map(Blocks::WARPED_HYPHAE(), fn(Wood $block) => Helper::encodeLog($block, Ids::WARPED_HYPHAE, Ids::STRIPPED_WARPED_HYPHAE));
		$this->map(Blocks::WARPED_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::WARPED_PRESSURE_PLATE)));
		$this->map(Blocks::WARPED_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::WARPED_STANDING_SIGN)));
		$this->map(Blocks::WARPED_STEM(), fn(Wood $block) => Helper::encodeLog($block, Ids::WARPED_STEM, Ids::STRIPPED_WARPED_STEM));
		$this->map(Blocks::WARPED_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::WARPED_TRAPDOOR)));
		$this->map(Blocks::WARPED_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::WARPED_WALL_SIGN)));
		$this->mapSimple(Blocks::WARPED_FENCE(), Ids::WARPED_FENCE);
		$this->mapSimple(Blocks::WARPED_PLANKS(), Ids::WARPED_PLANKS);
		$this->mapSlab(Blocks::WARPED_SLAB(), Ids::WARPED_SLAB, Ids::WARPED_DOUBLE_SLAB);
		$this->mapStairs(Blocks::WARPED_STAIRS(), Ids::WARPED_STAIRS);
	}

	private function registerLeavesSerializers() : void{
		//flattened IDs
		$this->map(Blocks::AZALEA_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::AZALEA_LEAVES)));
		$this->map(Blocks::CHERRY_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::CHERRY_LEAVES)));
		$this->map(Blocks::FLOWERING_AZALEA_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::AZALEA_LEAVES_FLOWERED)));
		$this->map(Blocks::MANGROVE_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::MANGROVE_LEAVES)));

		//legacy mess
		$this->map(Blocks::ACACIA_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::ACACIA_LEAVES)));
		$this->map(Blocks::BIRCH_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::BIRCH_LEAVES)));
		$this->map(Blocks::DARK_OAK_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::DARK_OAK_LEAVES)));
		$this->map(Blocks::JUNGLE_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::JUNGLE_LEAVES)));
		$this->map(Blocks::OAK_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::OAK_LEAVES)));
		$this->map(Blocks::SPRUCE_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves($block, new Writer(Ids::SPRUCE_LEAVES)));
	}

	private function registerSaplingSerializers() : void{
		foreach([
			Ids::ACACIA_SAPLING => Blocks::ACACIA_SAPLING(),
			Ids::BIRCH_SAPLING => Blocks::BIRCH_SAPLING(),
			Ids::DARK_OAK_SAPLING => Blocks::DARK_OAK_SAPLING(),
			Ids::JUNGLE_SAPLING => Blocks::JUNGLE_SAPLING(),
			Ids::OAK_SAPLING => Blocks::OAK_SAPLING(),
			Ids::SPRUCE_SAPLING => Blocks::SPRUCE_SAPLING(),
		] as $id => $block){
			$this->map($block, fn(Sapling $block) => Helper::encodeSapling($block, new Writer($id)));
		}
	}

	private function registerMobHeadSerializers() : void{
		$this->map(Blocks::MOB_HEAD(), fn(MobHead $block) => Writer::create(match ($block->getMobHeadType()){
			MobHeadType::CREEPER => Ids::CREEPER_HEAD,
			MobHeadType::DRAGON => Ids::DRAGON_HEAD,
			MobHeadType::PIGLIN => Ids::PIGLIN_HEAD,
			MobHeadType::PLAYER => Ids::PLAYER_HEAD,
			MobHeadType::SKELETON => Ids::SKELETON_SKULL,
			MobHeadType::WITHER_SKELETON => Ids::WITHER_SKELETON_SKULL,
			MobHeadType::ZOMBIE => Ids::ZOMBIE_HEAD,
		})->writeFacingWithoutDown($block->getFacing()));
	}

	private function registerSimpleSerializers() : void{
		$this->mapSimple(Blocks::AIR(), Ids::AIR);
		$this->mapSimple(Blocks::AMETHYST(), Ids::AMETHYST_BLOCK);
		$this->mapSimple(Blocks::ANCIENT_DEBRIS(), Ids::ANCIENT_DEBRIS);
		$this->mapSimple(Blocks::ANDESITE(), Ids::ANDESITE);
		$this->mapSimple(Blocks::BARRIER(), Ids::BARRIER);
		$this->mapSimple(Blocks::BEACON(), Ids::BEACON);
		$this->mapSimple(Blocks::BLACKSTONE(), Ids::BLACKSTONE);
		$this->mapSimple(Blocks::BLUE_ICE(), Ids::BLUE_ICE);
		$this->mapSimple(Blocks::BOOKSHELF(), Ids::BOOKSHELF);
		$this->mapSimple(Blocks::BRICKS(), Ids::BRICK_BLOCK);
		$this->mapSimple(Blocks::BROWN_MUSHROOM(), Ids::BROWN_MUSHROOM);
		$this->mapSimple(Blocks::BUDDING_AMETHYST(), Ids::BUDDING_AMETHYST);
		$this->mapSimple(Blocks::CALCITE(), Ids::CALCITE);
		$this->mapSimple(Blocks::CARTOGRAPHY_TABLE(), Ids::CARTOGRAPHY_TABLE);
		$this->mapSimple(Blocks::CHEMICAL_HEAT(), Ids::CHEMICAL_HEAT);
		$this->mapSimple(Blocks::CHISELED_DEEPSLATE(), Ids::CHISELED_DEEPSLATE);
		$this->mapSimple(Blocks::CHISELED_NETHER_BRICKS(), Ids::CHISELED_NETHER_BRICKS);
		$this->mapSimple(Blocks::CHISELED_POLISHED_BLACKSTONE(), Ids::CHISELED_POLISHED_BLACKSTONE);
		$this->mapSimple(Blocks::CHISELED_RED_SANDSTONE(), Ids::CHISELED_RED_SANDSTONE);
		$this->mapSimple(Blocks::CHISELED_SANDSTONE(), Ids::CHISELED_SANDSTONE);
		$this->mapSimple(Blocks::CHISELED_STONE_BRICKS(), Ids::CHISELED_STONE_BRICKS);
		$this->mapSimple(Blocks::CHISELED_TUFF(), Ids::CHISELED_TUFF);
		$this->mapSimple(Blocks::CHISELED_TUFF_BRICKS(), Ids::CHISELED_TUFF_BRICKS);
		$this->mapSimple(Blocks::CHORUS_PLANT(), Ids::CHORUS_PLANT);
		$this->mapSimple(Blocks::CLAY(), Ids::CLAY);
		$this->mapSimple(Blocks::COAL(), Ids::COAL_BLOCK);
		$this->mapSimple(Blocks::COAL_ORE(), Ids::COAL_ORE);
		$this->mapSimple(Blocks::COBBLED_DEEPSLATE(), Ids::COBBLED_DEEPSLATE);
		$this->mapSimple(Blocks::COBBLESTONE(), Ids::COBBLESTONE);
		$this->mapSimple(Blocks::COBWEB(), Ids::WEB);
		$this->mapSimple(Blocks::COPPER_ORE(), Ids::COPPER_ORE);
		$this->mapSimple(Blocks::CRACKED_DEEPSLATE_BRICKS(), Ids::CRACKED_DEEPSLATE_BRICKS);
		$this->mapSimple(Blocks::CRACKED_DEEPSLATE_TILES(), Ids::CRACKED_DEEPSLATE_TILES);
		$this->mapSimple(Blocks::CRACKED_NETHER_BRICKS(), Ids::CRACKED_NETHER_BRICKS);
		$this->mapSimple(Blocks::CRACKED_POLISHED_BLACKSTONE_BRICKS(), Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS);
		$this->mapSimple(Blocks::CRACKED_STONE_BRICKS(), Ids::CRACKED_STONE_BRICKS);
		$this->mapSimple(Blocks::CRAFTING_TABLE(), Ids::CRAFTING_TABLE);
		$this->mapSimple(Blocks::CRIMSON_ROOTS(), Ids::CRIMSON_ROOTS);
		$this->mapSimple(Blocks::CRYING_OBSIDIAN(), Ids::CRYING_OBSIDIAN);
		$this->mapSimple(Blocks::DANDELION(), Ids::DANDELION);
		$this->mapSimple(Blocks::CUT_RED_SANDSTONE(), Ids::CUT_RED_SANDSTONE);
		$this->mapSimple(Blocks::CUT_SANDSTONE(), Ids::CUT_SANDSTONE);
		$this->mapSimple(Blocks::DARK_PRISMARINE(), Ids::DARK_PRISMARINE);
		$this->mapSimple(Blocks::DEAD_BUSH(), Ids::DEADBUSH);
		$this->mapSimple(Blocks::DEEPSLATE_BRICKS(), Ids::DEEPSLATE_BRICKS);
		$this->mapSimple(Blocks::DEEPSLATE_COAL_ORE(), Ids::DEEPSLATE_COAL_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_COPPER_ORE(), Ids::DEEPSLATE_COPPER_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_DIAMOND_ORE(), Ids::DEEPSLATE_DIAMOND_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_EMERALD_ORE(), Ids::DEEPSLATE_EMERALD_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_GOLD_ORE(), Ids::DEEPSLATE_GOLD_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_IRON_ORE(), Ids::DEEPSLATE_IRON_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_LAPIS_LAZULI_ORE(), Ids::DEEPSLATE_LAPIS_ORE);
		$this->mapSimple(Blocks::DEEPSLATE_TILES(), Ids::DEEPSLATE_TILES);
		$this->mapSimple(Blocks::DIAMOND(), Ids::DIAMOND_BLOCK);
		$this->mapSimple(Blocks::DIAMOND_ORE(), Ids::DIAMOND_ORE);
		$this->mapSimple(Blocks::DIORITE(), Ids::DIORITE);
		$this->mapSimple(Blocks::DRAGON_EGG(), Ids::DRAGON_EGG);
		$this->mapSimple(Blocks::DRIED_KELP(), Ids::DRIED_KELP_BLOCK);
		$this->mapSimple(Blocks::ELEMENT_ACTINIUM(), Ids::ELEMENT_89);
		$this->mapSimple(Blocks::ELEMENT_ALUMINUM(), Ids::ELEMENT_13);
		$this->mapSimple(Blocks::ELEMENT_AMERICIUM(), Ids::ELEMENT_95);
		$this->mapSimple(Blocks::ELEMENT_ANTIMONY(), Ids::ELEMENT_51);
		$this->mapSimple(Blocks::ELEMENT_ARGON(), Ids::ELEMENT_18);
		$this->mapSimple(Blocks::ELEMENT_ARSENIC(), Ids::ELEMENT_33);
		$this->mapSimple(Blocks::ELEMENT_ASTATINE(), Ids::ELEMENT_85);
		$this->mapSimple(Blocks::ELEMENT_BARIUM(), Ids::ELEMENT_56);
		$this->mapSimple(Blocks::ELEMENT_BERKELIUM(), Ids::ELEMENT_97);
		$this->mapSimple(Blocks::ELEMENT_BERYLLIUM(), Ids::ELEMENT_4);
		$this->mapSimple(Blocks::ELEMENT_BISMUTH(), Ids::ELEMENT_83);
		$this->mapSimple(Blocks::ELEMENT_BOHRIUM(), Ids::ELEMENT_107);
		$this->mapSimple(Blocks::ELEMENT_BORON(), Ids::ELEMENT_5);
		$this->mapSimple(Blocks::ELEMENT_BROMINE(), Ids::ELEMENT_35);
		$this->mapSimple(Blocks::ELEMENT_CADMIUM(), Ids::ELEMENT_48);
		$this->mapSimple(Blocks::ELEMENT_CALCIUM(), Ids::ELEMENT_20);
		$this->mapSimple(Blocks::ELEMENT_CALIFORNIUM(), Ids::ELEMENT_98);
		$this->mapSimple(Blocks::ELEMENT_CARBON(), Ids::ELEMENT_6);
		$this->mapSimple(Blocks::ELEMENT_CERIUM(), Ids::ELEMENT_58);
		$this->mapSimple(Blocks::ELEMENT_CESIUM(), Ids::ELEMENT_55);
		$this->mapSimple(Blocks::ELEMENT_CHLORINE(), Ids::ELEMENT_17);
		$this->mapSimple(Blocks::ELEMENT_CHROMIUM(), Ids::ELEMENT_24);
		$this->mapSimple(Blocks::ELEMENT_COBALT(), Ids::ELEMENT_27);
		$this->mapSimple(Blocks::ELEMENT_COPERNICIUM(), Ids::ELEMENT_112);
		$this->mapSimple(Blocks::ELEMENT_COPPER(), Ids::ELEMENT_29);
		$this->mapSimple(Blocks::ELEMENT_CURIUM(), Ids::ELEMENT_96);
		$this->mapSimple(Blocks::ELEMENT_DARMSTADTIUM(), Ids::ELEMENT_110);
		$this->mapSimple(Blocks::ELEMENT_DUBNIUM(), Ids::ELEMENT_105);
		$this->mapSimple(Blocks::ELEMENT_DYSPROSIUM(), Ids::ELEMENT_66);
		$this->mapSimple(Blocks::ELEMENT_EINSTEINIUM(), Ids::ELEMENT_99);
		$this->mapSimple(Blocks::ELEMENT_ERBIUM(), Ids::ELEMENT_68);
		$this->mapSimple(Blocks::ELEMENT_EUROPIUM(), Ids::ELEMENT_63);
		$this->mapSimple(Blocks::ELEMENT_FERMIUM(), Ids::ELEMENT_100);
		$this->mapSimple(Blocks::ELEMENT_FLEROVIUM(), Ids::ELEMENT_114);
		$this->mapSimple(Blocks::ELEMENT_FLUORINE(), Ids::ELEMENT_9);
		$this->mapSimple(Blocks::ELEMENT_FRANCIUM(), Ids::ELEMENT_87);
		$this->mapSimple(Blocks::ELEMENT_GADOLINIUM(), Ids::ELEMENT_64);
		$this->mapSimple(Blocks::ELEMENT_GALLIUM(), Ids::ELEMENT_31);
		$this->mapSimple(Blocks::ELEMENT_GERMANIUM(), Ids::ELEMENT_32);
		$this->mapSimple(Blocks::ELEMENT_GOLD(), Ids::ELEMENT_79);
		$this->mapSimple(Blocks::ELEMENT_HAFNIUM(), Ids::ELEMENT_72);
		$this->mapSimple(Blocks::ELEMENT_HASSIUM(), Ids::ELEMENT_108);
		$this->mapSimple(Blocks::ELEMENT_HELIUM(), Ids::ELEMENT_2);
		$this->mapSimple(Blocks::ELEMENT_HOLMIUM(), Ids::ELEMENT_67);
		$this->mapSimple(Blocks::ELEMENT_HYDROGEN(), Ids::ELEMENT_1);
		$this->mapSimple(Blocks::ELEMENT_INDIUM(), Ids::ELEMENT_49);
		$this->mapSimple(Blocks::ELEMENT_IODINE(), Ids::ELEMENT_53);
		$this->mapSimple(Blocks::ELEMENT_IRIDIUM(), Ids::ELEMENT_77);
		$this->mapSimple(Blocks::ELEMENT_IRON(), Ids::ELEMENT_26);
		$this->mapSimple(Blocks::ELEMENT_KRYPTON(), Ids::ELEMENT_36);
		$this->mapSimple(Blocks::ELEMENT_LANTHANUM(), Ids::ELEMENT_57);
		$this->mapSimple(Blocks::ELEMENT_LAWRENCIUM(), Ids::ELEMENT_103);
		$this->mapSimple(Blocks::ELEMENT_LEAD(), Ids::ELEMENT_82);
		$this->mapSimple(Blocks::ELEMENT_LITHIUM(), Ids::ELEMENT_3);
		$this->mapSimple(Blocks::ELEMENT_LIVERMORIUM(), Ids::ELEMENT_116);
		$this->mapSimple(Blocks::ELEMENT_LUTETIUM(), Ids::ELEMENT_71);
		$this->mapSimple(Blocks::ELEMENT_MAGNESIUM(), Ids::ELEMENT_12);
		$this->mapSimple(Blocks::ELEMENT_MANGANESE(), Ids::ELEMENT_25);
		$this->mapSimple(Blocks::ELEMENT_MEITNERIUM(), Ids::ELEMENT_109);
		$this->mapSimple(Blocks::ELEMENT_MENDELEVIUM(), Ids::ELEMENT_101);
		$this->mapSimple(Blocks::ELEMENT_MERCURY(), Ids::ELEMENT_80);
		$this->mapSimple(Blocks::ELEMENT_MOLYBDENUM(), Ids::ELEMENT_42);
		$this->mapSimple(Blocks::ELEMENT_MOSCOVIUM(), Ids::ELEMENT_115);
		$this->mapSimple(Blocks::ELEMENT_NEODYMIUM(), Ids::ELEMENT_60);
		$this->mapSimple(Blocks::ELEMENT_NEON(), Ids::ELEMENT_10);
		$this->mapSimple(Blocks::ELEMENT_NEPTUNIUM(), Ids::ELEMENT_93);
		$this->mapSimple(Blocks::ELEMENT_NICKEL(), Ids::ELEMENT_28);
		$this->mapSimple(Blocks::ELEMENT_NIHONIUM(), Ids::ELEMENT_113);
		$this->mapSimple(Blocks::ELEMENT_NIOBIUM(), Ids::ELEMENT_41);
		$this->mapSimple(Blocks::ELEMENT_NITROGEN(), Ids::ELEMENT_7);
		$this->mapSimple(Blocks::ELEMENT_NOBELIUM(), Ids::ELEMENT_102);
		$this->mapSimple(Blocks::ELEMENT_OGANESSON(), Ids::ELEMENT_118);
		$this->mapSimple(Blocks::ELEMENT_OSMIUM(), Ids::ELEMENT_76);
		$this->mapSimple(Blocks::ELEMENT_OXYGEN(), Ids::ELEMENT_8);
		$this->mapSimple(Blocks::ELEMENT_PALLADIUM(), Ids::ELEMENT_46);
		$this->mapSimple(Blocks::ELEMENT_PHOSPHORUS(), Ids::ELEMENT_15);
		$this->mapSimple(Blocks::ELEMENT_PLATINUM(), Ids::ELEMENT_78);
		$this->mapSimple(Blocks::ELEMENT_PLUTONIUM(), Ids::ELEMENT_94);
		$this->mapSimple(Blocks::ELEMENT_POLONIUM(), Ids::ELEMENT_84);
		$this->mapSimple(Blocks::ELEMENT_POTASSIUM(), Ids::ELEMENT_19);
		$this->mapSimple(Blocks::ELEMENT_PRASEODYMIUM(), Ids::ELEMENT_59);
		$this->mapSimple(Blocks::ELEMENT_PROMETHIUM(), Ids::ELEMENT_61);
		$this->mapSimple(Blocks::ELEMENT_PROTACTINIUM(), Ids::ELEMENT_91);
		$this->mapSimple(Blocks::ELEMENT_RADIUM(), Ids::ELEMENT_88);
		$this->mapSimple(Blocks::ELEMENT_RADON(), Ids::ELEMENT_86);
		$this->mapSimple(Blocks::ELEMENT_RHENIUM(), Ids::ELEMENT_75);
		$this->mapSimple(Blocks::ELEMENT_RHODIUM(), Ids::ELEMENT_45);
		$this->mapSimple(Blocks::ELEMENT_ROENTGENIUM(), Ids::ELEMENT_111);
		$this->mapSimple(Blocks::ELEMENT_RUBIDIUM(), Ids::ELEMENT_37);
		$this->mapSimple(Blocks::ELEMENT_RUTHENIUM(), Ids::ELEMENT_44);
		$this->mapSimple(Blocks::ELEMENT_RUTHERFORDIUM(), Ids::ELEMENT_104);
		$this->mapSimple(Blocks::ELEMENT_SAMARIUM(), Ids::ELEMENT_62);
		$this->mapSimple(Blocks::ELEMENT_SCANDIUM(), Ids::ELEMENT_21);
		$this->mapSimple(Blocks::ELEMENT_SEABORGIUM(), Ids::ELEMENT_106);
		$this->mapSimple(Blocks::ELEMENT_SELENIUM(), Ids::ELEMENT_34);
		$this->mapSimple(Blocks::ELEMENT_SILICON(), Ids::ELEMENT_14);
		$this->mapSimple(Blocks::ELEMENT_SILVER(), Ids::ELEMENT_47);
		$this->mapSimple(Blocks::ELEMENT_SODIUM(), Ids::ELEMENT_11);
		$this->mapSimple(Blocks::ELEMENT_STRONTIUM(), Ids::ELEMENT_38);
		$this->mapSimple(Blocks::ELEMENT_SULFUR(), Ids::ELEMENT_16);
		$this->mapSimple(Blocks::ELEMENT_TANTALUM(), Ids::ELEMENT_73);
		$this->mapSimple(Blocks::ELEMENT_TECHNETIUM(), Ids::ELEMENT_43);
		$this->mapSimple(Blocks::ELEMENT_TELLURIUM(), Ids::ELEMENT_52);
		$this->mapSimple(Blocks::ELEMENT_TENNESSINE(), Ids::ELEMENT_117);
		$this->mapSimple(Blocks::ELEMENT_TERBIUM(), Ids::ELEMENT_65);
		$this->mapSimple(Blocks::ELEMENT_THALLIUM(), Ids::ELEMENT_81);
		$this->mapSimple(Blocks::ELEMENT_THORIUM(), Ids::ELEMENT_90);
		$this->mapSimple(Blocks::ELEMENT_THULIUM(), Ids::ELEMENT_69);
		$this->mapSimple(Blocks::ELEMENT_TIN(), Ids::ELEMENT_50);
		$this->mapSimple(Blocks::ELEMENT_TITANIUM(), Ids::ELEMENT_22);
		$this->mapSimple(Blocks::ELEMENT_TUNGSTEN(), Ids::ELEMENT_74);
		$this->mapSimple(Blocks::ELEMENT_URANIUM(), Ids::ELEMENT_92);
		$this->mapSimple(Blocks::ELEMENT_VANADIUM(), Ids::ELEMENT_23);
		$this->mapSimple(Blocks::ELEMENT_XENON(), Ids::ELEMENT_54);
		$this->mapSimple(Blocks::ELEMENT_YTTERBIUM(), Ids::ELEMENT_70);
		$this->mapSimple(Blocks::ELEMENT_YTTRIUM(), Ids::ELEMENT_39);
		$this->mapSimple(Blocks::ELEMENT_ZERO(), Ids::ELEMENT_0);
		$this->mapSimple(Blocks::ELEMENT_ZINC(), Ids::ELEMENT_30);
		$this->mapSimple(Blocks::ELEMENT_ZIRCONIUM(), Ids::ELEMENT_40);
		$this->mapSimple(Blocks::EMERALD(), Ids::EMERALD_BLOCK);
		$this->mapSimple(Blocks::EMERALD_ORE(), Ids::EMERALD_ORE);
		$this->mapSimple(Blocks::ENCHANTING_TABLE(), Ids::ENCHANTING_TABLE);
		$this->mapSimple(Blocks::END_STONE(), Ids::END_STONE);
		$this->mapSimple(Blocks::END_STONE_BRICKS(), Ids::END_BRICKS);
		$this->mapSimple(Blocks::FERN(), Ids::FERN);
		$this->mapSimple(Blocks::FLETCHING_TABLE(), Ids::FLETCHING_TABLE);
		$this->mapSimple(Blocks::GILDED_BLACKSTONE(), Ids::GILDED_BLACKSTONE);
		$this->mapSimple(Blocks::GLASS(), Ids::GLASS);
		$this->mapSimple(Blocks::GLASS_PANE(), Ids::GLASS_PANE);
		$this->mapSimple(Blocks::GLOWING_OBSIDIAN(), Ids::GLOWINGOBSIDIAN);
		$this->mapSimple(Blocks::GLOWSTONE(), Ids::GLOWSTONE);
		$this->mapSimple(Blocks::GOLD(), Ids::GOLD_BLOCK);
		$this->mapSimple(Blocks::GOLD_ORE(), Ids::GOLD_ORE);
		$this->mapSimple(Blocks::GRANITE(), Ids::GRANITE);
		$this->mapSimple(Blocks::GRASS(), Ids::GRASS_BLOCK);
		$this->mapSimple(Blocks::GRASS_PATH(), Ids::GRASS_PATH);
		$this->mapSimple(Blocks::GRAVEL(), Ids::GRAVEL);
		$this->mapSimple(Blocks::HANGING_ROOTS(), Ids::HANGING_ROOTS);
		$this->mapSimple(Blocks::HARDENED_CLAY(), Ids::HARDENED_CLAY);
		$this->mapSimple(Blocks::HARDENED_GLASS(), Ids::HARD_GLASS);
		$this->mapSimple(Blocks::HARDENED_GLASS_PANE(), Ids::HARD_GLASS_PANE);
		$this->mapSimple(Blocks::HONEYCOMB(), Ids::HONEYCOMB_BLOCK);
		$this->mapSimple(Blocks::ICE(), Ids::ICE);
		$this->mapSimple(Blocks::INFESTED_CHISELED_STONE_BRICK(), Ids::INFESTED_CHISELED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_COBBLESTONE(), Ids::INFESTED_COBBLESTONE);
		$this->mapSimple(Blocks::INFESTED_CRACKED_STONE_BRICK(), Ids::INFESTED_CRACKED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_MOSSY_STONE_BRICK(), Ids::INFESTED_MOSSY_STONE_BRICKS);
		$this->mapSimple(Blocks::INFESTED_STONE(), Ids::INFESTED_STONE);
		$this->mapSimple(Blocks::INFESTED_STONE_BRICK(), Ids::INFESTED_STONE_BRICKS);
		$this->mapSimple(Blocks::INFO_UPDATE(), Ids::INFO_UPDATE);
		$this->mapSimple(Blocks::INFO_UPDATE2(), Ids::INFO_UPDATE2);
		$this->mapSimple(Blocks::INVISIBLE_BEDROCK(), Ids::INVISIBLE_BEDROCK);
		$this->mapSimple(Blocks::IRON(), Ids::IRON_BLOCK);
		$this->mapSimple(Blocks::IRON_BARS(), Ids::IRON_BARS);
		$this->mapSimple(Blocks::IRON_ORE(), Ids::IRON_ORE);
		$this->mapSimple(Blocks::JUKEBOX(), Ids::JUKEBOX);
		$this->mapSimple(Blocks::LAPIS_LAZULI(), Ids::LAPIS_BLOCK);
		$this->mapSimple(Blocks::LAPIS_LAZULI_ORE(), Ids::LAPIS_ORE);
		$this->mapSimple(Blocks::LEGACY_STONECUTTER(), Ids::STONECUTTER);
		$this->mapSimple(Blocks::LILY_PAD(), Ids::WATERLILY);
		$this->mapSimple(Blocks::MAGMA(), Ids::MAGMA);
		$this->mapSimple(Blocks::MANGROVE_ROOTS(), Ids::MANGROVE_ROOTS);
		$this->mapSimple(Blocks::MELON(), Ids::MELON_BLOCK);
		$this->mapSimple(Blocks::MONSTER_SPAWNER(), Ids::MOB_SPAWNER);
		$this->mapSimple(Blocks::MOSSY_COBBLESTONE(), Ids::MOSSY_COBBLESTONE);
		$this->mapSimple(Blocks::MOSSY_STONE_BRICKS(), Ids::MOSSY_STONE_BRICKS);
		$this->mapSimple(Blocks::MUD(), Ids::MUD);
		$this->mapSimple(Blocks::MUD_BRICKS(), Ids::MUD_BRICKS);
		$this->mapSimple(Blocks::MYCELIUM(), Ids::MYCELIUM);
		$this->mapSimple(Blocks::NETHERITE(), Ids::NETHERITE_BLOCK);
		$this->mapSimple(Blocks::NETHERRACK(), Ids::NETHERRACK);
		$this->mapSimple(Blocks::NETHER_BRICKS(), Ids::NETHER_BRICK);
		$this->mapSimple(Blocks::NETHER_BRICK_FENCE(), Ids::NETHER_BRICK_FENCE);
		$this->mapSimple(Blocks::NETHER_GOLD_ORE(), Ids::NETHER_GOLD_ORE);
		$this->mapSimple(Blocks::NETHER_QUARTZ_ORE(), Ids::QUARTZ_ORE);
		$this->mapSimple(Blocks::NETHER_REACTOR_CORE(), Ids::NETHERREACTOR);
		$this->mapSimple(Blocks::NETHER_WART_BLOCK(), Ids::NETHER_WART_BLOCK);
		$this->mapSimple(Blocks::NOTE_BLOCK(), Ids::NOTEBLOCK);
		$this->mapSimple(Blocks::OBSIDIAN(), Ids::OBSIDIAN);
		$this->mapSimple(Blocks::PACKED_ICE(), Ids::PACKED_ICE);
		$this->mapSimple(Blocks::PACKED_MUD(), Ids::PACKED_MUD);
		$this->mapSimple(Blocks::PODZOL(), Ids::PODZOL);
		$this->mapSimple(Blocks::POLISHED_ANDESITE(), Ids::POLISHED_ANDESITE);
		$this->mapSimple(Blocks::POLISHED_BLACKSTONE(), Ids::POLISHED_BLACKSTONE);
		$this->mapSimple(Blocks::POLISHED_BLACKSTONE_BRICKS(), Ids::POLISHED_BLACKSTONE_BRICKS);
		$this->mapSimple(Blocks::POLISHED_DEEPSLATE(), Ids::POLISHED_DEEPSLATE);
		$this->mapSimple(Blocks::POLISHED_DIORITE(), Ids::POLISHED_DIORITE);
		$this->mapSimple(Blocks::POLISHED_GRANITE(), Ids::POLISHED_GRANITE);
		$this->mapSimple(Blocks::POLISHED_TUFF(), Ids::POLISHED_TUFF);
		$this->mapSimple(Blocks::PRISMARINE(), Ids::PRISMARINE);
		$this->mapSimple(Blocks::PRISMARINE_BRICKS(), Ids::PRISMARINE_BRICKS);
		$this->mapSimple(Blocks::QUARTZ_BRICKS(), Ids::QUARTZ_BRICKS);
		$this->mapSimple(Blocks::RAW_COPPER(), Ids::RAW_COPPER_BLOCK);
		$this->mapSimple(Blocks::RAW_GOLD(), Ids::RAW_GOLD_BLOCK);
		$this->mapSimple(Blocks::RAW_IRON(), Ids::RAW_IRON_BLOCK);
		$this->mapSimple(Blocks::REDSTONE(), Ids::REDSTONE_BLOCK);
		$this->mapSimple(Blocks::RED_MUSHROOM(), Ids::RED_MUSHROOM);
		$this->mapSimple(Blocks::RED_NETHER_BRICKS(), Ids::RED_NETHER_BRICK);
		$this->mapSimple(Blocks::RED_SAND(), Ids::RED_SAND);
		$this->mapSimple(Blocks::RED_SANDSTONE(), Ids::RED_SANDSTONE);
		$this->mapSimple(Blocks::REINFORCED_DEEPSLATE(), Ids::REINFORCED_DEEPSLATE);
		$this->mapSimple(Blocks::RESERVED6(), Ids::RESERVED6);
		$this->mapSimple(Blocks::SAND(), Ids::SAND);
		$this->mapSimple(Blocks::SANDSTONE(), Ids::SANDSTONE);
		$this->mapSimple(Blocks::SCULK(), Ids::SCULK);
		$this->mapSimple(Blocks::SEA_LANTERN(), Ids::SEA_LANTERN);
		$this->mapSimple(Blocks::SHROOMLIGHT(), Ids::SHROOMLIGHT);
		$this->mapSimple(Blocks::SHULKER_BOX(), Ids::UNDYED_SHULKER_BOX);
		$this->mapSimple(Blocks::SLIME(), Ids::SLIME);
		$this->mapSimple(Blocks::SMITHING_TABLE(), Ids::SMITHING_TABLE);
		$this->mapSimple(Blocks::SMOOTH_BASALT(), Ids::SMOOTH_BASALT);
		$this->mapSimple(Blocks::SMOOTH_RED_SANDSTONE(), Ids::SMOOTH_RED_SANDSTONE);
		$this->mapSimple(Blocks::SMOOTH_SANDSTONE(), Ids::SMOOTH_SANDSTONE);
		$this->mapSimple(Blocks::SMOOTH_STONE(), Ids::SMOOTH_STONE);
		$this->mapSimple(Blocks::SNOW(), Ids::SNOW);
		$this->mapSimple(Blocks::SOUL_SAND(), Ids::SOUL_SAND);
		$this->mapSimple(Blocks::SOUL_SOIL(), Ids::SOUL_SOIL);
		$this->mapSimple(Blocks::SPORE_BLOSSOM(), Ids::SPORE_BLOSSOM);
		$this->mapSimple(Blocks::STONE(), Ids::STONE);
		$this->mapSimple(Blocks::STONE_BRICKS(), Ids::STONE_BRICKS);
		$this->mapSimple(Blocks::TALL_GRASS(), Ids::SHORT_GRASS);  //no, this is not a typo - tall_grass is now the double block, just to be confusing :(
		$this->mapSimple(Blocks::TINTED_GLASS(), Ids::TINTED_GLASS);
		$this->mapSimple(Blocks::TORCHFLOWER(), Ids::TORCHFLOWER);
		$this->mapSimple(Blocks::TUFF(), Ids::TUFF);
		$this->mapSimple(Blocks::TUFF_BRICKS(), Ids::TUFF_BRICKS);
		$this->mapSimple(Blocks::WARPED_WART_BLOCK(), Ids::WARPED_WART_BLOCK);
		$this->mapSimple(Blocks::WARPED_ROOTS(), Ids::WARPED_ROOTS);
		$this->mapSimple(Blocks::WITHER_ROSE(), Ids::WITHER_ROSE);

		$this->mapSimple(Blocks::ALLIUM(), Ids::ALLIUM);
		$this->mapSimple(Blocks::CORNFLOWER(), Ids::CORNFLOWER);
		$this->mapSimple(Blocks::AZURE_BLUET(), Ids::AZURE_BLUET);
		$this->mapSimple(Blocks::LILY_OF_THE_VALLEY(), Ids::LILY_OF_THE_VALLEY);
		$this->mapSimple(Blocks::BLUE_ORCHID(), Ids::BLUE_ORCHID);
		$this->mapSimple(Blocks::OXEYE_DAISY(), Ids::OXEYE_DAISY);
		$this->mapSimple(Blocks::POPPY(), Ids::POPPY);
		$this->mapSimple(Blocks::ORANGE_TULIP(), Ids::ORANGE_TULIP);
		$this->mapSimple(Blocks::PINK_TULIP(), Ids::PINK_TULIP);
		$this->mapSimple(Blocks::RED_TULIP(), Ids::RED_TULIP);
		$this->mapSimple(Blocks::WHITE_TULIP(), Ids::WHITE_TULIP);
	}

	private function registerSerializers() : void{
		$this->map(Blocks::ACTIVATOR_RAIL(), function(ActivatorRail $block) : Writer{
			return Writer::create(Ids::ACTIVATOR_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isPowered())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(Blocks::ALL_SIDED_MUSHROOM_STEM(), fn() => Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM));
		$this->map(Blocks::AMETHYST_CLUSTER(), fn(AmethystCluster $block) => Writer::create(
			match($stage = $block->getStage()){
				AmethystCluster::STAGE_SMALL_BUD => Ids::SMALL_AMETHYST_BUD,
				AmethystCluster::STAGE_MEDIUM_BUD => Ids::MEDIUM_AMETHYST_BUD,
				AmethystCluster::STAGE_LARGE_BUD => Ids::LARGE_AMETHYST_BUD,
				AmethystCluster::STAGE_CLUSTER => Ids::AMETHYST_CLUSTER,
				default => throw new BlockStateSerializeException("Invalid Amethyst Cluster stage $stage"),
			})
			->writeBlockFace($block->getFacing())
		);
		$this->mapSlab(Blocks::ANDESITE_SLAB(), Ids::ANDESITE_SLAB, Ids::ANDESITE_DOUBLE_SLAB);
		$this->map(Blocks::ANDESITE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::ANDESITE_STAIRS)));
		$this->map(Blocks::ANDESITE_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::ANDESITE_WALL)));
		$this->map(Blocks::ANVIL(), fn(Anvil $block) : Writer => Writer::create(
			match($damage = $block->getDamage()){
				0 => Ids::ANVIL,
				1 => Ids::CHIPPED_ANVIL,
				2 => Ids::DAMAGED_ANVIL,
				default => throw new BlockStateSerializeException("Invalid Anvil damage {$damage}"),
			})
			->writeCardinalHorizontalFacing($block->getFacing())
		);
		$this->map(Blocks::BAMBOO(), function(Bamboo $block) : Writer{
			return Writer::create(Ids::BAMBOO)
				->writeBool(StateNames::AGE_BIT, $block->isReady())
				->writeString(StateNames::BAMBOO_LEAF_SIZE, match($block->getLeafSize()){
					Bamboo::NO_LEAVES => StringValues::BAMBOO_LEAF_SIZE_NO_LEAVES,
					Bamboo::SMALL_LEAVES => StringValues::BAMBOO_LEAF_SIZE_SMALL_LEAVES,
					Bamboo::LARGE_LEAVES => StringValues::BAMBOO_LEAF_SIZE_LARGE_LEAVES,
					default => throw new BlockStateSerializeException("Invalid Bamboo leaf thickness " . $block->getLeafSize()),
				})
				->writeString(StateNames::BAMBOO_STALK_THICKNESS, $block->isThick() ? StringValues::BAMBOO_STALK_THICKNESS_THICK : StringValues::BAMBOO_STALK_THICKNESS_THIN);
		});
		$this->map(Blocks::BAMBOO_SAPLING(), function(BambooSapling $block) : Writer{
			return Writer::create(Ids::BAMBOO_SAPLING)
				->writeBool(StateNames::AGE_BIT, $block->isReady());
		});
		$this->map(Blocks::BANNER(), function(FloorBanner $block) : Writer{
			return Writer::create(Ids::STANDING_BANNER)
				->writeInt(StateNames::GROUND_SIGN_DIRECTION, $block->getRotation());
		});
		$this->map(Blocks::BARREL(), function(Barrel $block) : Writer{
			return Writer::create(Ids::BARREL)
				->writeBool(StateNames::OPEN_BIT, $block->isOpen())
				->writeFacingDirection($block->getFacing());
		});
		$this->map(Blocks::BASALT(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::BASALT)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::BED(), function(Bed $block) : Writer{
			return Writer::create(Ids::BED)
				->writeBool(StateNames::HEAD_PIECE_BIT, $block->isHeadPart())
				->writeBool(StateNames::OCCUPIED_BIT, $block->isOccupied())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::BEDROCK(), function(Block $block) : Writer{
			return Writer::create(Ids::BEDROCK)
				->writeBool(StateNames::INFINIBURN_BIT, $block->burnsForever());
		});
		$this->map(Blocks::BEETROOTS(), fn(Beetroot $block) => Helper::encodeCrops($block, new Writer(Ids::BEETROOT)));
		$this->map(Blocks::BELL(), function(Bell $block) : Writer{
			return Writer::create(Ids::BELL)
				->writeBellAttachmentType($block->getAttachmentType())
				->writeBool(StateNames::TOGGLE_BIT, false) //we don't care about this; it's just to keep MCPE happy
				->writeLegacyHorizontalFacing($block->getFacing());

		});
		$this->map(Blocks::BIG_DRIPLEAF_HEAD(), function(BigDripleafHead $block) : Writer{
			return Writer::create(Ids::BIG_DRIPLEAF)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeString(StateNames::BIG_DRIPLEAF_TILT, match($block->getLeafState()){
					DripleafState::STABLE => StringValues::BIG_DRIPLEAF_TILT_NONE,
					DripleafState::UNSTABLE => StringValues::BIG_DRIPLEAF_TILT_UNSTABLE,
					DripleafState::PARTIAL_TILT => StringValues::BIG_DRIPLEAF_TILT_PARTIAL_TILT,
					DripleafState::FULL_TILT => StringValues::BIG_DRIPLEAF_TILT_FULL_TILT,
				})
				->writeBool(StateNames::BIG_DRIPLEAF_HEAD, true);
		});
		$this->map(Blocks::BIG_DRIPLEAF_STEM(), function(BigDripleafStem $block) : Writer{
			return Writer::create(Ids::BIG_DRIPLEAF)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeString(StateNames::BIG_DRIPLEAF_TILT, StringValues::BIG_DRIPLEAF_TILT_NONE)
				->writeBool(StateNames::BIG_DRIPLEAF_HEAD, false);
		});
		$this->mapSlab(Blocks::BLACKSTONE_SLAB(), Ids::BLACKSTONE_SLAB, Ids::BLACKSTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::BLACKSTONE_STAIRS(), Ids::BLACKSTONE_STAIRS);
		$this->map(Blocks::BLACKSTONE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::BLACKSTONE_WALL)));
		$this->map(Blocks::BLAST_FURNACE(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::BLAST_FURNACE, Ids::LIT_BLAST_FURNACE));
		$this->map(Blocks::BLUE_TORCH(), fn(Torch $block) => Helper::encodeTorch($block, Writer::create(Ids::COLORED_TORCH_BLUE)));
		$this->map(Blocks::BONE_BLOCK(), function(BoneBlock $block) : Writer{
			return Writer::create(Ids::BONE_BLOCK)
				->writeInt(StateNames::DEPRECATED, 0)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::BREWING_STAND(), function(BrewingStand $block) : Writer{
			return Writer::create(Ids::BREWING_STAND)
				->writeBool(StateNames::BREWING_STAND_SLOT_A_BIT, $block->hasSlot(BrewingStandSlot::EAST))
				->writeBool(StateNames::BREWING_STAND_SLOT_B_BIT, $block->hasSlot(BrewingStandSlot::SOUTHWEST))
				->writeBool(StateNames::BREWING_STAND_SLOT_C_BIT, $block->hasSlot(BrewingStandSlot::NORTHWEST));
		});
		$this->mapSlab(Blocks::BRICK_SLAB(), Ids::BRICK_SLAB, Ids::BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::BRICK_STAIRS(), Ids::BRICK_STAIRS);
		$this->map(Blocks::BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::BRICK_WALL)));
		$this->map(Blocks::BROWN_MUSHROOM_BLOCK(), fn(BrownMushroomBlock $block) => Helper::encodeMushroomBlock($block, new Writer(Ids::BROWN_MUSHROOM_BLOCK)));
		$this->map(Blocks::CACTUS(), function(Cactus $block) : Writer{
			return Writer::create(Ids::CACTUS)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::CAKE(), function(Cake $block) : Writer{
			return Writer::create(Ids::CAKE)
				->writeInt(StateNames::BITE_COUNTER, $block->getBites());
		});
		$this->map(Blocks::CAMPFIRE(), function(Campfire $block) : Writer{
			return Writer::create(Ids::CAMPFIRE)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeBool(StateNames::EXTINGUISHED, !$block->isLit());
		});
		$this->map(Blocks::CARROTS(), fn(Carrot $block) => Helper::encodeCrops($block, new Writer(Ids::CARROTS)));
		$this->map(Blocks::CARVED_PUMPKIN(), function(CarvedPumpkin $block) : Writer{
			return Writer::create(Ids::CARVED_PUMPKIN)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::CAVE_VINES(), function(CaveVines $block) : Writer{
			//I have no idea why this only has 3 IDs - there are 4 in Java and 4 visually distinct states in Bedrock
			return Writer::create($block->hasBerries() ?
				($block->isHead() ?
					Ids::CAVE_VINES_HEAD_WITH_BERRIES :
					Ids::CAVE_VINES_BODY_WITH_BERRIES
				) :
				Ids::CAVE_VINES
			)
				->writeInt(StateNames::GROWING_PLANT_AGE, $block->getAge());
		});
		$this->map(Blocks::CHAIN(), function(Chain $block) : Writer{
			return Writer::create(Ids::CHAIN)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::CHEST(), function(Chest $block) : Writer{
			return Writer::create(Ids::CHEST)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::CHISELED_BOOKSHELF(), function(ChiseledBookshelf $block) : Writer{
			$flags = 0;
			foreach($block->getSlots() as $slot){
				$flags |= 1 << $slot->value;
			}
			return Writer::create(Ids::CHISELED_BOOKSHELF)
				->writeLegacyHorizontalFacing($block->getFacing())
				->writeInt(StateNames::BOOKS_STORED, $flags);
		});
		$this->map(Blocks::CHISELED_QUARTZ(), fn(SimplePillar $block) => Helper::encodeQuartz($block->getAxis(), Writer::create(Ids::CHISELED_QUARTZ_BLOCK)));
		$this->map(Blocks::CHORUS_FLOWER(), function(ChorusFlower $block) : Writer{
			return Writer::create(Ids::CHORUS_FLOWER)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->mapSlab(Blocks::COBBLED_DEEPSLATE_SLAB(), Ids::COBBLED_DEEPSLATE_SLAB, Ids::COBBLED_DEEPSLATE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::COBBLED_DEEPSLATE_STAIRS(), Ids::COBBLED_DEEPSLATE_STAIRS);
		$this->map(Blocks::COBBLED_DEEPSLATE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::COBBLED_DEEPSLATE_WALL)));
		$this->mapSlab(Blocks::COBBLESTONE_SLAB(), Ids::COBBLESTONE_SLAB, Ids::COBBLESTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::COBBLESTONE_STAIRS(), Ids::STONE_STAIRS);
		$this->map(Blocks::COBBLESTONE_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::COBBLESTONE_WALL)));
		$this->map(Blocks::COPPER(), function(Copper $block) : Writer{
			$oxidation = $block->getOxidation();
			return new Writer($block->isWaxed() ?
				Helper::selectCopperId($oxidation, Ids::WAXED_COPPER, Ids::WAXED_EXPOSED_COPPER, Ids::WAXED_WEATHERED_COPPER, Ids::WAXED_OXIDIZED_COPPER) :
				Helper::selectCopperId($oxidation, Ids::COPPER_BLOCK, Ids::EXPOSED_COPPER, Ids::WEATHERED_COPPER, Ids::OXIDIZED_COPPER)
			);
		});
		$this->map(Blocks::CHISELED_COPPER(), function(Copper $block) : Writer{
			$oxidation = $block->getOxidation();
			return new Writer($block->isWaxed() ?
				Helper::selectCopperId($oxidation,
					Ids::WAXED_CHISELED_COPPER,
					Ids::WAXED_EXPOSED_CHISELED_COPPER,
					Ids::WAXED_WEATHERED_CHISELED_COPPER,
					Ids::WAXED_OXIDIZED_CHISELED_COPPER
				) :
				Helper::selectCopperId($oxidation,
					Ids::CHISELED_COPPER,
					Ids::EXPOSED_CHISELED_COPPER,
					Ids::WEATHERED_CHISELED_COPPER,
					Ids::OXIDIZED_CHISELED_COPPER
				)
			);
		});
		$this->map(Blocks::COPPER_GRATE(), function(CopperGrate $block) : Writer{
			$oxidation = $block->getOxidation();
			return new Writer($block->isWaxed() ?
				Helper::selectCopperId($oxidation,
					Ids::WAXED_COPPER_GRATE,
					Ids::WAXED_EXPOSED_COPPER_GRATE,
					Ids::WAXED_WEATHERED_COPPER_GRATE,
					Ids::WAXED_OXIDIZED_COPPER_GRATE
				) :
				Helper::selectCopperId($oxidation,
					Ids::COPPER_GRATE,
					Ids::EXPOSED_COPPER_GRATE,
					Ids::WEATHERED_COPPER_GRATE,
					Ids::OXIDIZED_COPPER_GRATE
				)
			);
		});
		$this->map(Blocks::CUT_COPPER(), function(Copper $block) : Writer{
			$oxidation = $block->getOxidation();
			return new Writer($block->isWaxed() ?
				Helper::selectCopperId($oxidation, Ids::WAXED_CUT_COPPER, Ids::WAXED_EXPOSED_CUT_COPPER, Ids::WAXED_WEATHERED_CUT_COPPER, Ids::WAXED_OXIDIZED_CUT_COPPER) :
				Helper::selectCopperId($oxidation, Ids::CUT_COPPER, Ids::EXPOSED_CUT_COPPER, Ids::WEATHERED_CUT_COPPER, Ids::OXIDIZED_CUT_COPPER)
			);
		});
		$this->map(Blocks::CUT_COPPER_SLAB(), function(CopperSlab $block) : Writer{
			$oxidation = $block->getOxidation();
			return Helper::encodeSlab(
				$block,
				($block->isWaxed() ?
					Helper::selectCopperId(
						$oxidation,
						Ids::WAXED_CUT_COPPER_SLAB,
						Ids::WAXED_EXPOSED_CUT_COPPER_SLAB,
						Ids::WAXED_WEATHERED_CUT_COPPER_SLAB,
						Ids::WAXED_OXIDIZED_CUT_COPPER_SLAB
					) :
					Helper::selectCopperId(
						$oxidation,
						Ids::CUT_COPPER_SLAB,
						Ids::EXPOSED_CUT_COPPER_SLAB,
						Ids::WEATHERED_CUT_COPPER_SLAB,
						Ids::OXIDIZED_CUT_COPPER_SLAB
					)
				),
				($block->isWaxed() ?
					Helper::selectCopperId(
						$oxidation,
						Ids::WAXED_DOUBLE_CUT_COPPER_SLAB,
						Ids::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB,
						Ids::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB,
						Ids::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB
					) :
					Helper::selectCopperId(
						$oxidation,
						Ids::DOUBLE_CUT_COPPER_SLAB,
						Ids::EXPOSED_DOUBLE_CUT_COPPER_SLAB,
						Ids::WEATHERED_DOUBLE_CUT_COPPER_SLAB,
						Ids::OXIDIZED_DOUBLE_CUT_COPPER_SLAB
					)
				)
			);
		});
		$this->map(Blocks::CUT_COPPER_STAIRS(), function(CopperStairs $block) : Writer{
			$oxidation = $block->getOxidation();
			return Helper::encodeStairs(
				$block,
				new Writer($block->isWaxed() ?
					Helper::selectCopperId(
						$oxidation,
						Ids::WAXED_CUT_COPPER_STAIRS,
						Ids::WAXED_EXPOSED_CUT_COPPER_STAIRS,
						Ids::WAXED_WEATHERED_CUT_COPPER_STAIRS,
						Ids::WAXED_OXIDIZED_CUT_COPPER_STAIRS
					) :
					Helper::selectCopperId(
						$oxidation,
						Ids::CUT_COPPER_STAIRS,
						Ids::EXPOSED_CUT_COPPER_STAIRS,
						Ids::WEATHERED_CUT_COPPER_STAIRS,
						Ids::OXIDIZED_CUT_COPPER_STAIRS
					)
				)
			);
		});
		$this->map(Blocks::COPPER_BULB(), function(CopperBulb $block) : Writer{
			$oxidation = $block->getOxidation();
			return Writer::create($block->isWaxed() ?
				Helper::selectCopperId($oxidation,
					Ids::WAXED_COPPER_BULB,
					Ids::WAXED_EXPOSED_COPPER_BULB,
					Ids::WAXED_WEATHERED_COPPER_BULB,
					Ids::WAXED_OXIDIZED_COPPER_BULB) :
				Helper::selectCopperId($oxidation,
					Ids::COPPER_BULB,
					Ids::EXPOSED_COPPER_BULB,
					Ids::WEATHERED_COPPER_BULB,
					Ids::OXIDIZED_COPPER_BULB
				))
				->writeBool(StateNames::LIT, $block->isLit())
				->writeBool(StateNames::POWERED_BIT, $block->isPowered());
		});
		$this->map(Blocks::COPPER_DOOR(), function(CopperDoor $block) : Writer{
			$oxidation = $block->getOxidation();
			return Helper::encodeDoor(
				$block,
				new Writer($block->isWaxed() ?
					Helper::selectCopperId(
						$oxidation,
						Ids::WAXED_COPPER_DOOR,
						Ids::WAXED_EXPOSED_COPPER_DOOR,
						Ids::WAXED_WEATHERED_COPPER_DOOR,
						Ids::WAXED_OXIDIZED_COPPER_DOOR
					) :
					Helper::selectCopperId(
						$oxidation,
						Ids::COPPER_DOOR,
						Ids::EXPOSED_COPPER_DOOR,
						Ids::WEATHERED_COPPER_DOOR,
						Ids::OXIDIZED_COPPER_DOOR
					)
				)
			);
		});
		$this->map(Blocks::COPPER_TRAPDOOR(), function(CopperTrapdoor $block) : Writer{
			$oxidation = $block->getOxidation();
			return Helper::encodeTrapdoor(
				$block,
				new Writer($block->isWaxed() ?
					Helper::selectCopperId(
						$oxidation,
						Ids::WAXED_COPPER_TRAPDOOR,
						Ids::WAXED_EXPOSED_COPPER_TRAPDOOR,
						Ids::WAXED_WEATHERED_COPPER_TRAPDOOR,
						Ids::WAXED_OXIDIZED_COPPER_TRAPDOOR
					) :
					Helper::selectCopperId(
						$oxidation,
						Ids::COPPER_TRAPDOOR,
						Ids::EXPOSED_COPPER_TRAPDOOR,
						Ids::WEATHERED_COPPER_TRAPDOOR,
						Ids::OXIDIZED_COPPER_TRAPDOOR
					)
				)
			);
		});
		$this->map(Blocks::COCOA_POD(), function(CocoaBlock $block) : Writer{
			return Writer::create(Ids::COCOA)
				->writeInt(StateNames::AGE, $block->getAge())
				->writeLegacyHorizontalFacing(Facing::opposite($block->getFacing()));
		});
		$this->map(Blocks::COMPOUND_CREATOR(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::COMPOUND_CREATOR)));
		$this->mapSlab(Blocks::CUT_RED_SANDSTONE_SLAB(), Ids::CUT_RED_SANDSTONE_SLAB, Ids::CUT_RED_SANDSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::CUT_SANDSTONE_SLAB(), Ids::CUT_SANDSTONE_SLAB, Ids::CUT_SANDSTONE_DOUBLE_SLAB);
		$this->mapSlab(Blocks::DARK_PRISMARINE_SLAB(), Ids::DARK_PRISMARINE_SLAB, Ids::DARK_PRISMARINE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::DARK_PRISMARINE_STAIRS(), Ids::DARK_PRISMARINE_STAIRS);
		$this->map(Blocks::DAYLIGHT_SENSOR(), function(DaylightSensor $block) : Writer{
			return Writer::create($block->isInverted() ? Ids::DAYLIGHT_DETECTOR_INVERTED : Ids::DAYLIGHT_DETECTOR)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::DEEPSLATE(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::DEEPSLATE)
				->writePillarAxis($block->getAxis());
		});
		$this->mapSlab(Blocks::DEEPSLATE_BRICK_SLAB(), Ids::DEEPSLATE_BRICK_SLAB, Ids::DEEPSLATE_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::DEEPSLATE_BRICK_STAIRS(), Ids::DEEPSLATE_BRICK_STAIRS);
		$this->map(Blocks::DEEPSLATE_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::DEEPSLATE_BRICK_WALL)));
		$this->map(Blocks::DEEPSLATE_REDSTONE_ORE(), fn(RedstoneOre $block) => new Writer($block->isLit() ? Ids::LIT_DEEPSLATE_REDSTONE_ORE : Ids::DEEPSLATE_REDSTONE_ORE));
		$this->mapSlab(Blocks::DEEPSLATE_TILE_SLAB(), Ids::DEEPSLATE_TILE_SLAB, Ids::DEEPSLATE_TILE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::DEEPSLATE_TILE_STAIRS(), Ids::DEEPSLATE_TILE_STAIRS);
		$this->map(Blocks::DEEPSLATE_TILE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::DEEPSLATE_TILE_WALL)));
		$this->map(Blocks::DETECTOR_RAIL(), function(DetectorRail $block) : Writer{
			return Writer::create(Ids::DETECTOR_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isActivated())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->mapSlab(Blocks::DIORITE_SLAB(), Ids::DIORITE_SLAB, Ids::DIORITE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::DIORITE_STAIRS(), Ids::DIORITE_STAIRS);
		$this->map(Blocks::DIORITE_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::DIORITE_WALL)));
		$this->map(Blocks::DIRT(), function(Dirt $block) : Writer{
			return Writer::create(match($block->getDirtType()){
				DirtType::NORMAL => Ids::DIRT,
				DirtType::COARSE => Ids::COARSE_DIRT,
				DirtType::ROOTED => Ids::DIRT_WITH_ROOTS,
			});
		});
		$this->map(Blocks::DOUBLE_TALLGRASS(), fn(DoubleTallGrass $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::TALL_GRASS)));
		$this->map(Blocks::ELEMENT_CONSTRUCTOR(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::ELEMENT_CONSTRUCTOR)));
		$this->map(Blocks::ENDER_CHEST(), function(EnderChest $block) : Writer{
			return Writer::create(Ids::ENDER_CHEST)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::END_PORTAL_FRAME(), function(EndPortalFrame $block) : Writer{
			return Writer::create(Ids::END_PORTAL_FRAME)
				->writeBool(StateNames::END_PORTAL_EYE_BIT, $block->hasEye())
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::END_ROD(), function(EndRod $block) : Writer{
			return Writer::create(Ids::END_ROD)
				->writeEndRodFacingDirection($block->getFacing());
		});
		$this->mapSlab(Blocks::END_STONE_BRICK_SLAB(), Ids::END_STONE_BRICK_SLAB, Ids::END_STONE_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::END_STONE_BRICK_STAIRS(), Ids::END_BRICK_STAIRS);
		$this->map(Blocks::END_STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::END_STONE_BRICK_WALL)));
		$this->mapSlab(Blocks::FAKE_WOODEN_SLAB(), Ids::PETRIFIED_OAK_SLAB, Ids::PETRIFIED_OAK_DOUBLE_SLAB);
		$this->map(Blocks::FARMLAND(), function(Farmland $block) : Writer{
			return Writer::create(Ids::FARMLAND)
				->writeInt(StateNames::MOISTURIZED_AMOUNT, $block->getWetness());
		});
		$this->map(Blocks::FIRE(), function(Fire $block) : Writer{
			return Writer::create(Ids::FIRE)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::FLOWER_POT(), function() : Writer{
			return Writer::create(Ids::FLOWER_POT)
				->writeBool(StateNames::UPDATE_BIT, false); //to keep MCPE happy
		});
		$this->map(Blocks::FROGLIGHT(), function(Froglight $block){
			return Writer::create(match($block->getFroglightType()){
				FroglightType::OCHRE => Ids::OCHRE_FROGLIGHT,
				FroglightType::PEARLESCENT => Ids::PEARLESCENT_FROGLIGHT,
				FroglightType::VERDANT => Ids::VERDANT_FROGLIGHT,
			})
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::FROSTED_ICE(), function(FrostedIce $block) : Writer{
			return Writer::create(Ids::FROSTED_ICE)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::FURNACE(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::FURNACE, Ids::LIT_FURNACE));
		$this->map(Blocks::GLOW_LICHEN(), function(GlowLichen $block) : Writer{
			return Writer::create(Ids::GLOW_LICHEN)
				->writeFacingFlags($block->getFaces());
		});
		$this->map(Blocks::GLOWING_ITEM_FRAME(), fn(ItemFrame $block) => Helper::encodeItemFrame($block, Ids::GLOW_FRAME));
		$this->mapSlab(Blocks::GRANITE_SLAB(), Ids::GRANITE_SLAB, Ids::GRANITE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::GRANITE_STAIRS(), Ids::GRANITE_STAIRS);
		$this->map(Blocks::GRANITE_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::GRANITE_WALL)));
		$this->map(Blocks::GREEN_TORCH(), fn(Torch $block) => Helper::encodeTorch($block, Writer::create(Ids::COLORED_TORCH_GREEN)));
		$this->map(Blocks::HAY_BALE(), function(HayBale $block) : Writer{
			return Writer::create(Ids::HAY_BLOCK)
				->writeInt(StateNames::DEPRECATED, 0)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::HOPPER(), function(Hopper $block) : Writer{
			return Writer::create(Ids::HOPPER)
				->writeBool(StateNames::TOGGLE_BIT, $block->isPowered())
				->writeFacingWithoutUp($block->getFacing());
		});
		$this->map(Blocks::IRON_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::IRON_DOOR)));
		$this->map(Blocks::IRON_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::IRON_TRAPDOOR)));
		$this->map(Blocks::ITEM_FRAME(), fn(ItemFrame $block) => Helper::encodeItemFrame($block, Ids::FRAME));
		$this->map(Blocks::LAB_TABLE(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::LAB_TABLE)));
		$this->map(Blocks::LADDER(), function(Ladder $block) : Writer{
			return Writer::create(Ids::LADDER)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::LANTERN(), function(Lantern $block) : Writer{
			return Writer::create(Ids::LANTERN)
				->writeBool(StateNames::HANGING, $block->isHanging());
		});
		$this->map(Blocks::LARGE_FERN(), fn(DoubleTallGrass $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::LARGE_FERN)));
		$this->map(Blocks::LAVA(), fn(Lava $block) => Helper::encodeLiquid($block, Ids::LAVA, Ids::FLOWING_LAVA));
		$this->map(Blocks::LECTERN(), function(Lectern $block) : Writer{
			return Writer::create(Ids::LECTERN)
				->writeBool(StateNames::POWERED_BIT, $block->isProducingSignal())
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::LEVER(), function(Lever $block) : Writer{
			return Writer::create(Ids::LEVER)
				->writeBool(StateNames::OPEN_BIT, $block->isActivated())
				->writeString(StateNames::LEVER_DIRECTION, match($block->getFacing()){
					LeverFacing::DOWN_AXIS_Z => StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH,
					LeverFacing::DOWN_AXIS_X => StringValues::LEVER_DIRECTION_DOWN_EAST_WEST,
					LeverFacing::UP_AXIS_Z => StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH,
					LeverFacing::UP_AXIS_X => StringValues::LEVER_DIRECTION_UP_EAST_WEST,
					LeverFacing::NORTH => StringValues::LEVER_DIRECTION_NORTH,
					LeverFacing::SOUTH => StringValues::LEVER_DIRECTION_SOUTH,
					LeverFacing::WEST => StringValues::LEVER_DIRECTION_WEST,
					LeverFacing::EAST => StringValues::LEVER_DIRECTION_EAST,
				});
		});
		$this->map(Blocks::LIGHT(), function(Light $block) : Writer{
			return Writer::create(match($block->getLightLevel()){
				0 => Ids::LIGHT_BLOCK_0,
				1 => Ids::LIGHT_BLOCK_1,
				2 => Ids::LIGHT_BLOCK_2,
				3 => Ids::LIGHT_BLOCK_3,
				4 => Ids::LIGHT_BLOCK_4,
				5 => Ids::LIGHT_BLOCK_5,
				6 => Ids::LIGHT_BLOCK_6,
				7 => Ids::LIGHT_BLOCK_7,
				8 => Ids::LIGHT_BLOCK_8,
				9 => Ids::LIGHT_BLOCK_9,
				10 => Ids::LIGHT_BLOCK_10,
				11 => Ids::LIGHT_BLOCK_11,
				12 => Ids::LIGHT_BLOCK_12,
				13 => Ids::LIGHT_BLOCK_13,
				14 => Ids::LIGHT_BLOCK_14,
				15 => Ids::LIGHT_BLOCK_15,
				default => throw new BlockStateSerializeException("Invalid light level " . $block->getLightLevel()),
			});
		});
		$this->map(Blocks::LIGHTNING_ROD(), function(LightningRod $block) : Writer{
			return Writer::create(Ids::LIGHTNING_ROD)
				->writeFacingDirection($block->getFacing());
		});
		$this->map(Blocks::LILAC(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::LILAC)));
		$this->map(Blocks::LIT_PUMPKIN(), function(LitPumpkin $block) : Writer{
			return Writer::create(Ids::LIT_PUMPKIN)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::LOOM(), function(Loom $block) : Writer{
			return Writer::create(Ids::LOOM)
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::MATERIAL_REDUCER(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::MATERIAL_REDUCER)));
		$this->map(Blocks::MELON_STEM(), fn(MelonStem $block) => Helper::encodeStem($block, new Writer(Ids::MELON_STEM)));
		$this->mapSlab(Blocks::MOSSY_COBBLESTONE_SLAB(), Ids::MOSSY_COBBLESTONE_SLAB, Ids::MOSSY_COBBLESTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::MOSSY_COBBLESTONE_STAIRS(), Ids::MOSSY_COBBLESTONE_STAIRS);
		$this->map(Blocks::MOSSY_COBBLESTONE_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::MOSSY_COBBLESTONE_WALL)));
		$this->mapSlab(Blocks::MOSSY_STONE_BRICK_SLAB(), Ids::MOSSY_STONE_BRICK_SLAB, Ids::MOSSY_STONE_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::MOSSY_STONE_BRICK_STAIRS(), Ids::MOSSY_STONE_BRICK_STAIRS);
		$this->map(Blocks::MOSSY_STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::MOSSY_STONE_BRICK_WALL)));
		$this->mapSlab(Blocks::MUD_BRICK_SLAB(), Ids::MUD_BRICK_SLAB, Ids::MUD_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::MUD_BRICK_STAIRS(), Ids::MUD_BRICK_STAIRS);
		$this->map(Blocks::MUD_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::MUD_BRICK_WALL)));
		$this->map(Blocks::MUDDY_MANGROVE_ROOTS(), fn(SimplePillar $block) => Writer::create(Ids::MUDDY_MANGROVE_ROOTS)
				->writePillarAxis($block->getAxis()));
		$this->map(Blocks::MUSHROOM_STEM(), fn() => Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_STEM));
		$this->mapSlab(Blocks::NETHER_BRICK_SLAB(), Ids::NETHER_BRICK_SLAB, Ids::NETHER_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::NETHER_BRICK_STAIRS(), Ids::NETHER_BRICK_STAIRS);
		$this->map(Blocks::NETHER_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::NETHER_BRICK_WALL)));
		$this->map(Blocks::NETHER_PORTAL(), function(NetherPortal $block) : Writer{
			return Writer::create(Ids::PORTAL)
				->writeString(StateNames::PORTAL_AXIS, match($block->getAxis()){
					Axis::X => StringValues::PORTAL_AXIS_X,
					Axis::Z => StringValues::PORTAL_AXIS_Z,
					default => throw new BlockStateSerializeException("Invalid Nether Portal axis " . $block->getAxis()),
				});
		});
		$this->map(Blocks::NETHER_WART(), function(NetherWartPlant $block) : Writer{
			return Writer::create(Ids::NETHER_WART)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::PEONY(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::PEONY)));
		$this->map(Blocks::PINK_PETALS(), function(PinkPetals $block) : Writer{
			return Writer::create(Ids::PINK_PETALS)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeInt(StateNames::GROWTH, $block->getCount() - 1);
		});
		$this->map(Blocks::PITCHER_PLANT(), function(DoublePlant $block) : Writer{
			return Writer::create(Ids::PITCHER_PLANT)
				->writeBool(StateNames::UPPER_BLOCK_BIT, $block->isTop());
		});
		$this->map(Blocks::PITCHER_CROP(), function(PitcherCrop $block) : Writer{
			return Writer::create(Ids::PITCHER_CROP)
				->writeInt(StateNames::GROWTH, $block->getAge())
				->writeBool(StateNames::UPPER_BLOCK_BIT, false);
		});
		$this->map(Blocks::DOUBLE_PITCHER_CROP(), function(DoublePitcherCrop $block) : Writer{
			return Writer::create(Ids::PITCHER_CROP)
				->writeInt(StateNames::GROWTH, $block->getAge() + 1 + PitcherCrop::MAX_AGE)
				->writeBool(StateNames::UPPER_BLOCK_BIT, $block->isTop());
		});
		$this->mapSlab(Blocks::POLISHED_ANDESITE_SLAB(), Ids::POLISHED_ANDESITE_SLAB, Ids::POLISHED_ANDESITE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_ANDESITE_STAIRS(), Ids::POLISHED_ANDESITE_STAIRS);
		$this->map(Blocks::POLISHED_BASALT(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::POLISHED_BASALT)
				->writePillarAxis($block->getAxis());
		});
		$this->mapSlab(Blocks::POLISHED_BLACKSTONE_BRICK_SLAB(), Ids::POLISHED_BLACKSTONE_BRICK_SLAB, Ids::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_BLACKSTONE_BRICK_STAIRS(), Ids::POLISHED_BLACKSTONE_BRICK_STAIRS);
		$this->map(Blocks::POLISHED_BLACKSTONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::POLISHED_BLACKSTONE_BRICK_WALL)));
		$this->map(Blocks::POLISHED_BLACKSTONE_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::POLISHED_BLACKSTONE_BUTTON)));
		$this->map(Blocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE)));
		$this->mapSlab(Blocks::POLISHED_BLACKSTONE_SLAB(), Ids::POLISHED_BLACKSTONE_SLAB, Ids::POLISHED_BLACKSTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_BLACKSTONE_STAIRS(), Ids::POLISHED_BLACKSTONE_STAIRS);
		$this->map(Blocks::POLISHED_BLACKSTONE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::POLISHED_BLACKSTONE_WALL)));
		$this->mapSlab(Blocks::POLISHED_DEEPSLATE_SLAB(), Ids::POLISHED_DEEPSLATE_SLAB, Ids::POLISHED_DEEPSLATE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_DEEPSLATE_STAIRS(), Ids::POLISHED_DEEPSLATE_STAIRS);
		$this->map(Blocks::POLISHED_DEEPSLATE_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::POLISHED_DEEPSLATE_WALL)));
		$this->mapSlab(Blocks::POLISHED_DIORITE_SLAB(), Ids::POLISHED_DIORITE_SLAB, Ids::POLISHED_DIORITE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_DIORITE_STAIRS(), Ids::POLISHED_DIORITE_STAIRS);
		$this->mapSlab(Blocks::POLISHED_GRANITE_SLAB(), Ids::POLISHED_GRANITE_SLAB, Ids::POLISHED_GRANITE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_GRANITE_STAIRS(), Ids::POLISHED_GRANITE_STAIRS);
		$this->mapSlab(Blocks::POLISHED_TUFF_SLAB(), Ids::POLISHED_TUFF_SLAB, Ids::POLISHED_TUFF_DOUBLE_SLAB);
		$this->mapStairs(Blocks::POLISHED_TUFF_STAIRS(), Ids::POLISHED_TUFF_STAIRS);
		$this->map(Blocks::POLISHED_TUFF_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::POLISHED_TUFF_WALL)));
		$this->map(Blocks::POTATOES(), fn(Potato $block) => Helper::encodeCrops($block, new Writer(Ids::POTATOES)));
		$this->map(Blocks::POWERED_RAIL(), function(PoweredRail $block) : Writer{
			return Writer::create(Ids::GOLDEN_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isPowered())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->mapSlab(Blocks::PRISMARINE_BRICKS_SLAB(), Ids::PRISMARINE_BRICK_SLAB, Ids::PRISMARINE_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::PRISMARINE_BRICKS_STAIRS(), Ids::PRISMARINE_BRICKS_STAIRS);
		$this->mapSlab(Blocks::PRISMARINE_SLAB(), Ids::PRISMARINE_SLAB, Ids::PRISMARINE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::PRISMARINE_STAIRS(), Ids::PRISMARINE_STAIRS);
		$this->map(Blocks::PRISMARINE_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::PRISMARINE_WALL)));
		$this->map(Blocks::PUMPKIN(), function() : Writer{
			return Writer::create(Ids::PUMPKIN)
				->writeCardinalHorizontalFacing(Facing::SOUTH); //no longer used
		});
		$this->map(Blocks::PUMPKIN_STEM(), fn(PumpkinStem $block) => Helper::encodeStem($block, new Writer(Ids::PUMPKIN_STEM)));
		$this->map(Blocks::PURPUR(), fn() => Writer::create(Ids::PURPUR_BLOCK)->writePillarAxis(Axis::Y));
		$this->map(Blocks::PURPLE_TORCH(), fn(Torch $block) => Helper::encodeTorch($block, Writer::create(Ids::COLORED_TORCH_PURPLE)));
		$this->map(Blocks::PURPUR_PILLAR(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::PURPUR_PILLAR)
				->writePillarAxis($block->getAxis());
		});
		$this->mapSlab(Blocks::PURPUR_SLAB(), Ids::PURPUR_SLAB, Ids::PURPUR_DOUBLE_SLAB);
		$this->mapStairs(Blocks::PURPUR_STAIRS(), Ids::PURPUR_STAIRS);
		$this->map(Blocks::QUARTZ(), fn() => Helper::encodeQuartz(Axis::Y, Writer::create(Ids::QUARTZ_BLOCK)));
		$this->map(Blocks::QUARTZ_PILLAR(), fn(SimplePillar $block) => Helper::encodeQuartz($block->getAxis(), Writer::create(Ids::QUARTZ_PILLAR)));
		$this->mapSlab(Blocks::QUARTZ_SLAB(), Ids::QUARTZ_SLAB, Ids::QUARTZ_DOUBLE_SLAB);
		$this->mapStairs(Blocks::QUARTZ_STAIRS(), Ids::QUARTZ_STAIRS);
		$this->map(Blocks::RAIL(), function(Rail $block) : Writer{
			return Writer::create(Ids::RAIL)
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(Blocks::REDSTONE_COMPARATOR(), function(RedstoneComparator $block) : Writer{
			return Writer::create($block->isPowered() ? Ids::POWERED_COMPARATOR : Ids::UNPOWERED_COMPARATOR)
				->writeBool(StateNames::OUTPUT_LIT_BIT, $block->isPowered())
				->writeBool(StateNames::OUTPUT_SUBTRACT_BIT, $block->isSubtractMode())
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::REDSTONE_LAMP(), fn(RedstoneLamp $block) => new Writer($block->isPowered() ? Ids::LIT_REDSTONE_LAMP : Ids::REDSTONE_LAMP));
		$this->map(Blocks::REDSTONE_ORE(), fn(RedstoneOre $block) => new Writer($block->isLit() ? Ids::LIT_REDSTONE_ORE : Ids::REDSTONE_ORE));
		$this->map(Blocks::REDSTONE_REPEATER(), function(RedstoneRepeater $block) : Writer{
			return Writer::create($block->isPowered() ? Ids::POWERED_REPEATER : Ids::UNPOWERED_REPEATER)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeInt(StateNames::REPEATER_DELAY, $block->getDelay() - 1);
		});
		$this->map(Blocks::REDSTONE_TORCH(), function(RedstoneTorch $block) : Writer{
			return Writer::create($block->isLit() ? Ids::REDSTONE_TORCH : Ids::UNLIT_REDSTONE_TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(Blocks::REDSTONE_WIRE(), function(RedstoneWire $block) : Writer{
			return Writer::create(Ids::REDSTONE_WIRE)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::RED_MUSHROOM_BLOCK(), fn(RedMushroomBlock $block) => Helper::encodeMushroomBlock($block, new Writer(Ids::RED_MUSHROOM_BLOCK)));
		$this->mapSlab(Blocks::RED_NETHER_BRICK_SLAB(), Ids::RED_NETHER_BRICK_SLAB, Ids::RED_NETHER_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::RED_NETHER_BRICK_STAIRS(), Ids::RED_NETHER_BRICK_STAIRS);
		$this->map(Blocks::RED_NETHER_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::RED_NETHER_BRICK_WALL)));
		$this->mapSlab(Blocks::RED_SANDSTONE_SLAB(), Ids::RED_SANDSTONE_SLAB, Ids::RED_SANDSTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::RED_SANDSTONE_STAIRS(), Ids::RED_SANDSTONE_STAIRS);
		$this->map(Blocks::RED_SANDSTONE_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::RED_SANDSTONE_WALL)));
		$this->map(Blocks::RED_TORCH(), fn(Torch $block) => Helper::encodeTorch($block, Writer::create(Ids::COLORED_TORCH_RED)));
		$this->map(Blocks::ROSE_BUSH(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::ROSE_BUSH)));
		$this->mapSlab(Blocks::SANDSTONE_SLAB(), Ids::SANDSTONE_SLAB, Ids::SANDSTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::SANDSTONE_STAIRS(), Ids::SANDSTONE_STAIRS);
		$this->map(Blocks::SANDSTONE_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::SANDSTONE_WALL)));
		$this->map(Blocks::SEA_PICKLE(), function(SeaPickle $block) : Writer{
			return Writer::create(Ids::SEA_PICKLE)
				->writeBool(StateNames::DEAD_BIT, !$block->isUnderwater())
				->writeInt(StateNames::CLUSTER_COUNT, $block->getCount() - 1);
		});
		$this->map(Blocks::SMALL_DRIPLEAF(), function(SmallDripleaf $block) : Writer{
			return Writer::create(Ids::SMALL_DRIPLEAF_BLOCK)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeBool(StateNames::UPPER_BLOCK_BIT, $block->isTop());
		});
		$this->map(Blocks::SMOKER(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::SMOKER, Ids::LIT_SMOKER));
		$this->map(Blocks::SMOOTH_QUARTZ(), fn() => Helper::encodeQuartz(Axis::Y, Writer::create(Ids::SMOOTH_QUARTZ)));
		$this->mapSlab(Blocks::SMOOTH_QUARTZ_SLAB(), Ids::SMOOTH_QUARTZ_SLAB, Ids::SMOOTH_QUARTZ_DOUBLE_SLAB);
		$this->mapStairs(Blocks::SMOOTH_QUARTZ_STAIRS(), Ids::SMOOTH_QUARTZ_STAIRS);
		$this->mapSlab(Blocks::SMOOTH_RED_SANDSTONE_SLAB(), Ids::SMOOTH_RED_SANDSTONE_SLAB, Ids::SMOOTH_RED_SANDSTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::SMOOTH_RED_SANDSTONE_STAIRS(), Ids::SMOOTH_RED_SANDSTONE_STAIRS);
		$this->mapSlab(Blocks::SMOOTH_SANDSTONE_SLAB(), Ids::SMOOTH_SANDSTONE_SLAB, Ids::SMOOTH_SANDSTONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::SMOOTH_SANDSTONE_STAIRS(), Ids::SMOOTH_SANDSTONE_STAIRS);
		$this->mapSlab(Blocks::SMOOTH_STONE_SLAB(), Ids::SMOOTH_STONE_SLAB, Ids::SMOOTH_STONE_DOUBLE_SLAB);
		$this->map(Blocks::SNOW_LAYER(), function(SnowLayer $block) : Writer{
			return Writer::create(Ids::SNOW_LAYER)
				->writeBool(StateNames::COVERED_BIT, false)
				->writeInt(StateNames::HEIGHT, $block->getLayers() - 1);
		});
		$this->map(Blocks::SOUL_CAMPFIRE(), function(SoulCampfire $block) : Writer{
			return Writer::create(Ids::SOUL_CAMPFIRE)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeBool(StateNames::EXTINGUISHED, !$block->isLit());
		});
		$this->map(Blocks::SOUL_FIRE(), function() : Writer{
			return Writer::create(Ids::SOUL_FIRE)
				->writeInt(StateNames::AGE, 0); //useless for soul fire, we don't track it
		});
		$this->map(Blocks::SOUL_LANTERN(), function(Lantern $block) : Writer{
			return Writer::create(Ids::SOUL_LANTERN)
				->writeBool(StateNames::HANGING, $block->isHanging());
		});
		$this->map(Blocks::SOUL_TORCH(), function(Torch $block) : Writer{
			return Writer::create(Ids::SOUL_TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(Blocks::SPONGE(), fn(Sponge $block) => Writer::create($block->isWet() ? Ids::WET_SPONGE : Ids::SPONGE));
		$this->map(Blocks::STONECUTTER(), fn(Stonecutter $block) => Writer::create(Ids::STONECUTTER_BLOCK)
			->writeCardinalHorizontalFacing($block->getFacing()));
		$this->mapSlab(Blocks::STONE_BRICK_SLAB(), Ids::STONE_BRICK_SLAB, Ids::STONE_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::STONE_BRICK_STAIRS(), Ids::STONE_BRICK_STAIRS);
		$this->map(Blocks::STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, Writer::create(Ids::STONE_BRICK_WALL)));
		$this->map(Blocks::STONE_BUTTON(), fn(StoneButton $block) => Helper::encodeButton($block, new Writer(Ids::STONE_BUTTON)));
		$this->map(Blocks::STONE_PRESSURE_PLATE(), fn(StonePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::STONE_PRESSURE_PLATE)));
		$this->mapSlab(Blocks::STONE_SLAB(), Ids::NORMAL_STONE_SLAB, Ids::NORMAL_STONE_DOUBLE_SLAB);
		$this->mapStairs(Blocks::STONE_STAIRS(), Ids::NORMAL_STONE_STAIRS);
		$this->map(Blocks::SUGARCANE(), function(Sugarcane $block) : Writer{
			return Writer::create(Ids::REEDS)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::SUNFLOWER(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::SUNFLOWER)));
		$this->map(Blocks::SWEET_BERRY_BUSH(), function(SweetBerryBush $block) : Writer{
			return Writer::create(Ids::SWEET_BERRY_BUSH)
				->writeInt(StateNames::GROWTH, $block->getAge());
		});
		$this->map(Blocks::TNT(), fn(TNT $block) => Writer::create($block->worksUnderwater() ? Ids::UNDERWATER_TNT : Ids::TNT)
				->writeBool(StateNames::EXPLODE_BIT, $block->isUnstable())
		);
		$this->map(Blocks::TORCH(), function(Torch $block) : Writer{
			return Writer::create(Ids::TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(Blocks::TORCHFLOWER_CROP(), function(TorchflowerCrop $block){
			return Writer::create(Ids::TORCHFLOWER_CROP)
				->writeInt(StateNames::GROWTH, $block->isReady() ? 1 : 0);
		});
		$this->map(Blocks::TRAPPED_CHEST(), function(TrappedChest $block) : Writer{
			return Writer::create(Ids::TRAPPED_CHEST)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::TRIPWIRE(), function(Tripwire $block) : Writer{
			return Writer::create(Ids::TRIP_WIRE)
				->writeBool(StateNames::ATTACHED_BIT, $block->isConnected())
				->writeBool(StateNames::DISARMED_BIT, $block->isDisarmed())
				->writeBool(StateNames::POWERED_BIT, $block->isTriggered())
				->writeBool(StateNames::SUSPENDED_BIT, $block->isSuspended());
		});
		$this->map(Blocks::TRIPWIRE_HOOK(), function(TripwireHook $block) : Writer{
			return Writer::create(Ids::TRIPWIRE_HOOK)
				->writeBool(StateNames::ATTACHED_BIT, $block->isConnected())
				->writeBool(StateNames::POWERED_BIT, $block->isPowered())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->mapSlab(Blocks::TUFF_BRICK_SLAB(), Ids::TUFF_BRICK_SLAB, Ids::TUFF_BRICK_DOUBLE_SLAB);
		$this->mapStairs(Blocks::TUFF_BRICK_STAIRS(), Ids::TUFF_BRICK_STAIRS);
		$this->map(Blocks::TUFF_BRICK_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::TUFF_BRICK_WALL)));
		$this->mapSlab(Blocks::TUFF_SLAB(), Ids::TUFF_SLAB, Ids::TUFF_DOUBLE_SLAB);
		$this->mapStairs(Blocks::TUFF_STAIRS(), Ids::TUFF_STAIRS);
		$this->map(Blocks::TUFF_WALL(), fn(Wall $block) => Helper::encodeWall($block, new Writer(Ids::TUFF_WALL)));
		$this->map(Blocks::TWISTING_VINES(), function(NetherVines $block) : Writer{
			return Writer::create(Ids::TWISTING_VINES)
				->writeInt(StateNames::TWISTING_VINES_AGE, $block->getAge());
		});
		$this->map(Blocks::UNDERWATER_TORCH(), function(UnderwaterTorch $block) : Writer{
			return Writer::create(Ids::UNDERWATER_TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(Blocks::VINES(), function(Vine $block) : Writer{
			return Writer::create(Ids::VINE)
				->writeInt(StateNames::VINE_DIRECTION_BITS, ($block->hasFace(Facing::NORTH) ? BlockLegacyMetadata::VINE_FLAG_NORTH : 0) | ($block->hasFace(Facing::SOUTH) ? BlockLegacyMetadata::VINE_FLAG_SOUTH : 0) | ($block->hasFace(Facing::WEST) ? BlockLegacyMetadata::VINE_FLAG_WEST : 0) | ($block->hasFace(Facing::EAST) ? BlockLegacyMetadata::VINE_FLAG_EAST : 0));
		});
		$this->map(Blocks::WALL_BANNER(), function(WallBanner $block) : Writer{
			return Writer::create(Ids::WALL_BANNER)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(Blocks::WATER(), fn(Water $block) => Helper::encodeLiquid($block, Ids::WATER, Ids::FLOWING_WATER));
		$this->map(Blocks::WEEPING_VINES(), function(NetherVines $block) : Writer{
			return Writer::create(Ids::WEEPING_VINES)
				->writeInt(StateNames::WEEPING_VINES_AGE, $block->getAge());
		});
		$this->map(Blocks::WEIGHTED_PRESSURE_PLATE_HEAVY(), function(WeightedPressurePlateHeavy $block) : Writer{
			return Writer::create(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT(), function(WeightedPressurePlateLight $block) : Writer{
			return Writer::create(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::WHEAT(), fn(Wheat $block) => Helper::encodeCrops($block, new Writer(Ids::WHEAT)));
	}
}
