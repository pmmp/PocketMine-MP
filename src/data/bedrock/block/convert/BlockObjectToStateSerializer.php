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
use pocketmine\block\Carrot;
use pocketmine\block\CarvedPumpkin;
use pocketmine\block\CaveVines;
use pocketmine\block\Chain;
use pocketmine\block\ChemistryTable;
use pocketmine\block\Chest;
use pocketmine\block\ChiseledBookshelf;
use pocketmine\block\ChorusFlower;
use pocketmine\block\CocoaBlock;
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
use pocketmine\block\EnderChest;
use pocketmine\block\EndPortalFrame;
use pocketmine\block\EndRod;
use pocketmine\block\Farmland;
use pocketmine\block\FillableCauldron;
use pocketmine\block\Fire;
use pocketmine\block\FloorBanner;
use pocketmine\block\FloorCoralFan;
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
use pocketmine\block\Lectern;
use pocketmine\block\Lever;
use pocketmine\block\Light;
use pocketmine\block\LightningRod;
use pocketmine\block\LitPumpkin;
use pocketmine\block\Loom;
use pocketmine\block\MelonStem;
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
use pocketmine\block\ResinClump;
use pocketmine\block\RespawnAnchor;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\SeaPickle;
use pocketmine\block\SimplePillar;
use pocketmine\block\SimplePressurePlate;
use pocketmine\block\Slab;
use pocketmine\block\SmallDripleaf;
use pocketmine\block\SnowLayer;
use pocketmine\block\SoulCampfire;
use pocketmine\block\Sponge;
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
use pocketmine\block\utils\Colored;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DripleafState;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\block\Vine;
use pocketmine\block\WallBanner;
use pocketmine\block\WallCoralFan;
use pocketmine\block\Water;
use pocketmine\block\WeightedPressurePlateHeavy;
use pocketmine\block\WeightedPressurePlateLight;
use pocketmine\block\Wheat;
use pocketmine\block\Wood;
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
	 * @var (\Closure|BlockStateData)[]
	 * @phpstan-var array<int, \Closure(never) : (Writer|BlockStateData)|BlockStateData>
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
		$this->registerCopperSerializers();
		$this->registerSerializers();
		new BlockSerializerDeserializerRegistrar(null, $this);
	}

	public function serialize(int $stateId) : BlockStateData{
		//TODO: singleton usage not ideal
		//TODO: we may want to deduplicate cache entries to avoid wasting memory
		return $this->cache[$stateId] ??= $this->serializeBlock(RuntimeBlockStateRegistry::getInstance()->fromStateId($stateId));
	}

	public function isRegistered(Block $block) : bool{
		return isset($this->serializers[$block->getTypeId()]);
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $block
	 * @phpstan-param \Closure(TBlockType) : (Writer|BlockStateData)|Writer|BlockStateData $serializer
	 */
	public function map(Block $block, \Closure|Writer|BlockStateData $serializer) : void{
		if(isset($this->serializers[$block->getTypeId()])){
			throw new \InvalidArgumentException("Block type ID " . $block->getTypeId() . " already has a serializer registered");
		}
		//writer accepted for convenience only
		$this->serializers[$block->getTypeId()] = $serializer instanceof Writer ? $serializer->getBlockStateData() : $serializer;
	}

	public function mapSimple(Block $block, string $id) : void{
		$this->map($block, BlockStateData::current($id, []));
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

		if($locatedSerializer instanceof BlockStateData){ //static data, not dependent on state
			return $locatedSerializer;
		}

		/**
		 * TODO: there is no guarantee that this type actually matches that of $blockState - a plugin may have stolen
		 * the type ID of the block (which never makes sense, even in a world where overriding block types is a thing).
		 * In the future we'll need some way to guarantee that type IDs are never reused (perhaps spl_object_id()?)
		 *
		 * @var \Closure $locatedSerializer
		 * @phpstan-var \Closure(TBlockType) : (Writer|BlockStateData) $locatedSerializer
		 */
		$result = $locatedSerializer($blockState);

		return $result instanceof Writer ? $result->getBlockStateData() : $result;
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-template TEnum of \UnitEnum
	 *
	 * @phpstan-param TBlock                   $block
	 * @phpstan-param StringEnumMap<TEnum>     $mapProperty
	 * @phpstan-param \Closure(TBlock) : TEnum $getProperty
	 * @phpstan-param ?\Closure(TBlock, Writer) : Writer $extra
	 */
	public function mapFlattenedEnum(
		Block $block,
		StringEnumMap $mapProperty,
		string $prefix,
		string $suffix,
		\Closure $getProperty,
		?\Closure $extra = null
	) : void{
		if($extra !== null){
			$this->map($block, function(Block $block) use ($getProperty, $mapProperty, $prefix, $suffix, $extra) : Writer{
				$property = $getProperty($block);
				$infix = $mapProperty->enumToValue($property);
				$writer = new Writer($prefix . $infix . $suffix);
				$extra($block, $writer);
				return $writer;
			});
		}else{
			$this->map($block, function(Block $block) use ($getProperty, $mapProperty, $prefix, $suffix) : BlockStateData{
				$property = $getProperty($block);
				$infix = $mapProperty->enumToValue($property);
				return BlockStateData::current($prefix . $infix . $suffix, []);
			});
		}
	}

	/**
	 * @phpstan-template TBlock of Block&Colored
	 * @phpstan-param TBlock $block
	 * @phpstan-param ?\Closure(TBlock, Writer) : Writer $extra
	 */
	public function mapColored(
		Block $block,
		string $prefix,
		string $suffix,
		?\Closure $extra = null
	) : void{
		$this->mapFlattenedEnum(
			$block,
			ValueMappings::getInstance()->getEnumMap(DyeColor::class),
			$prefix,
			$suffix,
			fn(Colored $block) => $block->getColor(),
			$extra
		);
	}

	private function registerCandleSerializers() : void{
		$this->map(Blocks::CANDLE(), fn(Candle $block) => Helper::encodeCandle($block, new Writer(Ids::CANDLE)));
		$this->mapColored(
			Blocks::DYED_CANDLE(),
			"minecraft:",
			"_candle",
			Helper::encodeCandle(...)
		);
		$this->map(Blocks::CAKE_WITH_CANDLE(), fn(CakeWithCandle $block) => Writer::create(Ids::CANDLE_CAKE)
			->writeBool(StateNames::LIT, $block->isLit()));
		$this->mapColored(
			Blocks::CAKE_WITH_DYED_CANDLE(),
			"minecraft:",
			"_candle_cake",
			fn(CakeWithDyedCandle $block, Writer $writer) => $writer->writeBool(StateNames::LIT, $block->isLit())
		);
	}

	public function registerFlatColorBlockSerializers() : void{
		$this->map(Blocks::GLAZED_TERRACOTTA(), function(GlazedTerracotta $block) : Writer{
			return Writer::create(match($block->getColor()){
				DyeColor::BLACK => Ids::BLACK_GLAZED_TERRACOTTA,
				DyeColor::BLUE => Ids::BLUE_GLAZED_TERRACOTTA,
				DyeColor::BROWN => Ids::BROWN_GLAZED_TERRACOTTA,
				DyeColor::CYAN => Ids::CYAN_GLAZED_TERRACOTTA,
				DyeColor::GRAY => Ids::GRAY_GLAZED_TERRACOTTA,
				DyeColor::GREEN => Ids::GREEN_GLAZED_TERRACOTTA,
				DyeColor::LIGHT_BLUE => Ids::LIGHT_BLUE_GLAZED_TERRACOTTA,
				DyeColor::LIGHT_GRAY => Ids::SILVER_GLAZED_TERRACOTTA, //minecraft sadness
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
	}

	private function registerFlatCoralSerializers() : void{
		$this->map(Blocks::CORAL(), fn(Coral $block) => BlockStateData::current(match($block->getCoralType()){
			CoralType::BRAIN => $block->isDead() ? Ids::DEAD_BRAIN_CORAL : Ids::BRAIN_CORAL,
			CoralType::BUBBLE => $block->isDead() ? Ids::DEAD_BUBBLE_CORAL : Ids::BUBBLE_CORAL,
			CoralType::FIRE => $block->isDead() ? Ids::DEAD_FIRE_CORAL : Ids::FIRE_CORAL,
			CoralType::HORN => $block->isDead() ? Ids::DEAD_HORN_CORAL : Ids::HORN_CORAL,
			CoralType::TUBE => $block->isDead() ? Ids::DEAD_TUBE_CORAL : Ids::TUBE_CORAL,
		}, []));

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

		$this->map(Blocks::CORAL_BLOCK(), fn(CoralBlock $block) => BlockStateData::current(match($block->getCoralType()){
			CoralType::BRAIN => $block->isDead() ? Ids::DEAD_BRAIN_CORAL_BLOCK : Ids::BRAIN_CORAL_BLOCK,
			CoralType::BUBBLE => $block->isDead() ? Ids::DEAD_BUBBLE_CORAL_BLOCK : Ids::BUBBLE_CORAL_BLOCK,
			CoralType::FIRE => $block->isDead() ? Ids::DEAD_FIRE_CORAL_BLOCK : Ids::FIRE_CORAL_BLOCK,
			CoralType::HORN => $block->isDead() ? Ids::DEAD_HORN_CORAL_BLOCK : Ids::HORN_CORAL_BLOCK,
			CoralType::TUBE => $block->isDead() ? Ids::DEAD_TUBE_CORAL_BLOCK : Ids::TUBE_CORAL_BLOCK,
		}, []));

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
		$this->map(Blocks::CAULDRON(), Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, 0));
		$this->map(Blocks::LAVA_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_LAVA, $b->getFillLevel()));
		//potion cauldrons store their real information in the block actor data
		$this->map(Blocks::POTION_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, $b->getFillLevel()));
		$this->map(Blocks::WATER_CAULDRON(), fn(FillableCauldron $b) => Helper::encodeCauldron(StringValues::CAULDRON_LIQUID_WATER, $b->getFillLevel()));
	}

	private function registerCopperSerializers() : void{
		$this->map(Blocks::COPPER(), function(Copper $block) : BlockStateData{
			$oxidation = $block->getOxidation();
			return BlockStateData::current(
				$block->isWaxed() ?
					Helper::selectCopperId($oxidation, Ids::WAXED_COPPER, Ids::WAXED_EXPOSED_COPPER, Ids::WAXED_WEATHERED_COPPER, Ids::WAXED_OXIDIZED_COPPER) :
					Helper::selectCopperId($oxidation, Ids::COPPER_BLOCK, Ids::EXPOSED_COPPER, Ids::WEATHERED_COPPER, Ids::OXIDIZED_COPPER),
				[]
			);
		});
		$this->map(Blocks::CHISELED_COPPER(), function(Copper $block) : BlockStateData{
			$oxidation = $block->getOxidation();
			return BlockStateData::current(
				$block->isWaxed() ?
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
					),
				[]
			);
		});
		$this->map(Blocks::COPPER_GRATE(), function(CopperGrate $block) : BlockStateData{
			$oxidation = $block->getOxidation();
			return BlockStateData::current(
				$block->isWaxed() ?
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
					),
				[]
			);
		});
		$this->map(Blocks::CUT_COPPER(), function(Copper $block) : BlockStateData{
			$oxidation = $block->getOxidation();
			return BlockStateData::current(
				$block->isWaxed() ?
					Helper::selectCopperId($oxidation, Ids::WAXED_CUT_COPPER, Ids::WAXED_EXPOSED_CUT_COPPER, Ids::WAXED_WEATHERED_CUT_COPPER, Ids::WAXED_OXIDIZED_CUT_COPPER) :
					Helper::selectCopperId($oxidation, Ids::CUT_COPPER, Ids::EXPOSED_CUT_COPPER, Ids::WEATHERED_CUT_COPPER, Ids::OXIDIZED_CUT_COPPER),
				[]
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
	}

	private function registerSerializers() : void{
		$this->map(Blocks::ACTIVATOR_RAIL(), function(ActivatorRail $block) : Writer{
			return Writer::create(Ids::ACTIVATOR_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isPowered())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(Blocks::ALL_SIDED_MUSHROOM_STEM(), Writer::create(Ids::MUSHROOM_STEM)
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
		$this->map(Blocks::COCOA_POD(), function(CocoaBlock $block) : Writer{
			return Writer::create(Ids::COCOA)
				->writeInt(StateNames::AGE, $block->getAge())
				->writeLegacyHorizontalFacing(Facing::opposite($block->getFacing()));
		});
		$this->map(Blocks::COMPOUND_CREATOR(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::COMPOUND_CREATOR)));
		$this->map(Blocks::DAYLIGHT_SENSOR(), function(DaylightSensor $block) : Writer{
			return Writer::create($block->isInverted() ? Ids::DAYLIGHT_DETECTOR_INVERTED : Ids::DAYLIGHT_DETECTOR)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::DEEPSLATE(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::DEEPSLATE)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::DEEPSLATE_REDSTONE_ORE(), fn(RedstoneOre $block) => new Writer($block->isLit() ? Ids::LIT_DEEPSLATE_REDSTONE_ORE : Ids::DEEPSLATE_REDSTONE_ORE));
		$this->map(Blocks::DETECTOR_RAIL(), function(DetectorRail $block) : Writer{
			return Writer::create(Ids::DETECTOR_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isActivated())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(Blocks::DIRT(), fn(Dirt $block) => BlockStateData::current(match($block->getDirtType()){
			DirtType::NORMAL => Ids::DIRT,
			DirtType::COARSE => Ids::COARSE_DIRT,
			DirtType::ROOTED => Ids::DIRT_WITH_ROOTS,
		}, []));
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
		$this->map(Blocks::FARMLAND(), function(Farmland $block) : Writer{
			return Writer::create(Ids::FARMLAND)
				->writeInt(StateNames::MOISTURIZED_AMOUNT, $block->getWetness());
		});
		$this->map(Blocks::FIRE(), function(Fire $block) : Writer{
			return Writer::create(Ids::FIRE)
				->writeInt(StateNames::AGE, $block->getAge());
		});
		$this->map(Blocks::FLOWER_POT(), Writer::create(Ids::FLOWER_POT)
				->writeBool(StateNames::UPDATE_BIT, false) //to keep MCPE happy
		);
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
		$this->map(Blocks::LIGHT(), fn(Light $block) => BlockStateData::current(match($block->getLightLevel()){
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
		}, []));
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
		$this->map(Blocks::MUDDY_MANGROVE_ROOTS(), fn(SimplePillar $block) => Writer::create(Ids::MUDDY_MANGROVE_ROOTS)
				->writePillarAxis($block->getAxis()));
		$this->map(Blocks::MUSHROOM_STEM(), Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_STEM));
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
		$this->map(Blocks::POLISHED_BASALT(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::POLISHED_BASALT)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::POLISHED_BLACKSTONE_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::POLISHED_BLACKSTONE_BUTTON)));
		$this->map(Blocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE)));
		$this->map(Blocks::POTATOES(), fn(Potato $block) => Helper::encodeCrops($block, new Writer(Ids::POTATOES)));
		$this->map(Blocks::POWERED_RAIL(), function(PoweredRail $block) : Writer{
			return Writer::create(Ids::GOLDEN_RAIL)
				->writeBool(StateNames::RAIL_DATA_BIT, $block->isPowered())
				->writeInt(StateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(Blocks::PUMPKIN(), Writer::create(Ids::PUMPKIN)
				->writeCardinalHorizontalFacing(Facing::SOUTH) //no longer used
		);
		$this->map(Blocks::PUMPKIN_STEM(), fn(PumpkinStem $block) => Helper::encodeStem($block, new Writer(Ids::PUMPKIN_STEM)));
		$this->map(Blocks::PURPUR(), Writer::create(Ids::PURPUR_BLOCK)->writePillarAxis(Axis::Y));
		$this->map(Blocks::PURPLE_TORCH(), fn(Torch $block) => Helper::encodeTorch($block, Writer::create(Ids::COLORED_TORCH_PURPLE)));
		$this->map(Blocks::PURPUR_PILLAR(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::PURPUR_PILLAR)
				->writePillarAxis($block->getAxis());
		});
		$this->map(Blocks::QUARTZ(), Helper::encodeQuartz(Axis::Y, Writer::create(Ids::QUARTZ_BLOCK)));
		$this->map(Blocks::QUARTZ_PILLAR(), fn(SimplePillar $block) => Helper::encodeQuartz($block->getAxis(), Writer::create(Ids::QUARTZ_PILLAR)));
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
		$this->map(Blocks::RED_TORCH(), fn(Torch $block) => Helper::encodeTorch($block, Writer::create(Ids::COLORED_TORCH_RED)));
		$this->map(Blocks::RESIN_CLUMP(), function(ResinClump $block) : Writer{
			return Writer::create(Ids::RESIN_CLUMP)
				->writeFacingFlags($block->getFaces());
		});
		$this->map(Blocks::RESPAWN_ANCHOR(), function(RespawnAnchor $block) : Writer{
			return Writer::create(Ids::RESPAWN_ANCHOR)
				->writeInt(StateNames::RESPAWN_ANCHOR_CHARGE, $block->getCharges());
		});
		$this->map(Blocks::ROSE_BUSH(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::ROSE_BUSH)));
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
		$this->map(Blocks::SMOOTH_QUARTZ(), Helper::encodeQuartz(Axis::Y, Writer::create(Ids::SMOOTH_QUARTZ)));
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
		$this->map(Blocks::SOUL_FIRE(), Writer::create(Ids::SOUL_FIRE)
				->writeInt(StateNames::AGE, 0) //useless for soul fire, we don't track it
		);
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
		$this->map(Blocks::STONE_BUTTON(), fn(StoneButton $block) => Helper::encodeButton($block, new Writer(Ids::STONE_BUTTON)));
		$this->map(Blocks::STONE_PRESSURE_PLATE(), fn(StonePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::STONE_PRESSURE_PLATE)));
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
