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

use pocketmine\block\Bamboo;
use pocketmine\block\Beetroot;
use pocketmine\block\BigDripleafHead;
use pocketmine\block\BigDripleafStem;
use pocketmine\block\Block;
use pocketmine\block\BrownMushroomBlock;
use pocketmine\block\Button;
use pocketmine\block\Campfire;
use pocketmine\block\Carrot;
use pocketmine\block\CaveVines;
use pocketmine\block\ChemistryTable;
use pocketmine\block\ChiseledBookshelf;
use pocketmine\block\CopperSlab;
use pocketmine\block\DaylightSensor;
use pocketmine\block\Door;
use pocketmine\block\DoublePitcherCrop;
use pocketmine\block\DoublePlant;
use pocketmine\block\DoubleTallGrass;
use pocketmine\block\EndRod;
use pocketmine\block\FillableCauldron;
use pocketmine\block\Furnace;
use pocketmine\block\GlowLichen;
use pocketmine\block\Hopper;
use pocketmine\block\ItemFrame;
use pocketmine\block\Lava;
use pocketmine\block\MelonStem;
use pocketmine\block\PitcherCrop;
use pocketmine\block\Potato;
use pocketmine\block\PumpkinStem;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\RedstoneComparator;
use pocketmine\block\RedstoneLamp;
use pocketmine\block\RedstoneOre;
use pocketmine\block\RedstoneRepeater;
use pocketmine\block\RedstoneTorch;
use pocketmine\block\ResinClump;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\SeaPickle;
use pocketmine\block\SimplePressurePlate;
use pocketmine\block\Slab;
use pocketmine\block\SoulCampfire;
use pocketmine\block\Sponge;
use pocketmine\block\Stair;
use pocketmine\block\StoneButton;
use pocketmine\block\StonePressurePlate;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\TNT;
use pocketmine\block\TorchflowerCrop;
use pocketmine\block\Trapdoor;
use pocketmine\block\utils\Colored;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\block\Vine;
use pocketmine\block\Water;
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
			throw new \InvalidArgumentException("Block type ID " . $block->getTypeId() . " (" . $block->getName() . ") already has a serializer registered");
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
	 * @phpstan-param TBlock                        $block
	 * @phpstan-param EnumFromStringStateMap<TEnum> $mapProperty
	 * @phpstan-param \Closure(TBlock) : TEnum      $getProperty
	 * @phpstan-param ?\Closure(TBlock, Writer) : Writer $extra
	 */
	public function mapFlattenedEnum(
		Block $block,
		EnumFromStringStateMap $mapProperty,
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
			ValueMappings::getInstance()->dyeColor,
			$prefix,
			$suffix,
			fn(Colored $block) => $block->getColor(),
			$extra
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
	}

	private function registerSerializers() : void{
		$this->map(Blocks::ALL_SIDED_MUSHROOM_STEM(), Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM));
		$this->map(Blocks::BAMBOO(), function(Bamboo $block) : Writer{
			return Writer::create(Ids::BAMBOO)
				->writeBool(StateNames::AGE_BIT, $block->isReady())
				->mapIntToString(StateNames::BAMBOO_LEAF_SIZE, ValueMappings::getInstance()->bambooLeafSize, $block->getLeafSize())
				->writeString(StateNames::BAMBOO_STALK_THICKNESS, $block->isThick() ? StringValues::BAMBOO_STALK_THICKNESS_THICK : StringValues::BAMBOO_STALK_THICKNESS_THIN);
		});
		$this->map(Blocks::BEETROOTS(), fn(Beetroot $block) => Helper::encodeCrops($block, new Writer(Ids::BEETROOT)));
		$this->map(Blocks::BIG_DRIPLEAF_HEAD(), function(BigDripleafHead $block) : Writer{
			return Writer::create(Ids::BIG_DRIPLEAF)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeUnitEnum(StateNames::BIG_DRIPLEAF_TILT, ValueMappings::getInstance()->dripleafState, $block->getLeafState())
				->writeBool(StateNames::BIG_DRIPLEAF_HEAD, true);
		});
		$this->map(Blocks::BIG_DRIPLEAF_STEM(), function(BigDripleafStem $block) : Writer{
			return Writer::create(Ids::BIG_DRIPLEAF)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeString(StateNames::BIG_DRIPLEAF_TILT, StringValues::BIG_DRIPLEAF_TILT_NONE)
				->writeBool(StateNames::BIG_DRIPLEAF_HEAD, false);
		});
		$this->map(Blocks::BLAST_FURNACE(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::BLAST_FURNACE, Ids::LIT_BLAST_FURNACE));
		$this->map(Blocks::BROWN_MUSHROOM_BLOCK(), fn(BrownMushroomBlock $block) => Helper::encodeMushroomBlock($block, new Writer(Ids::BROWN_MUSHROOM_BLOCK)));
		$this->map(Blocks::CAMPFIRE(), function(Campfire $block) : Writer{
			return Writer::create(Ids::CAMPFIRE)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeBool(StateNames::EXTINGUISHED, !$block->isLit());
		});
		$this->map(Blocks::CARROTS(), fn(Carrot $block) => Helper::encodeCrops($block, new Writer(Ids::CARROTS)));
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
		$this->map(Blocks::CHISELED_BOOKSHELF(), function(ChiseledBookshelf $block) : Writer{
			$flags = 0;
			foreach($block->getSlots() as $slot){
				$flags |= 1 << $slot->value;
			}
			return Writer::create(Ids::CHISELED_BOOKSHELF)
				->writeLegacyHorizontalFacing($block->getFacing())
				->writeInt(StateNames::BOOKS_STORED, $flags);
		});
		$this->map(Blocks::COMPOUND_CREATOR(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::COMPOUND_CREATOR)));
		$this->map(Blocks::DAYLIGHT_SENSOR(), function(DaylightSensor $block) : Writer{
			return Writer::create($block->isInverted() ? Ids::DAYLIGHT_DETECTOR_INVERTED : Ids::DAYLIGHT_DETECTOR)
				->writeInt(StateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(Blocks::DEEPSLATE_REDSTONE_ORE(), fn(RedstoneOre $block) => new Writer($block->isLit() ? Ids::LIT_DEEPSLATE_REDSTONE_ORE : Ids::DEEPSLATE_REDSTONE_ORE));
		$this->map(Blocks::DOUBLE_TALLGRASS(), fn(DoubleTallGrass $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::TALL_GRASS)));
		$this->map(Blocks::ELEMENT_CONSTRUCTOR(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::ELEMENT_CONSTRUCTOR)));
		$this->map(Blocks::END_ROD(), function(EndRod $block) : Writer{
			return Writer::create(Ids::END_ROD)
				->writeEndRodFacingDirection($block->getFacing());
		});
		$this->map(Blocks::FURNACE(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::FURNACE, Ids::LIT_FURNACE));
		$this->map(Blocks::GLOW_LICHEN(), function(GlowLichen $block) : Writer{
			return Writer::create(Ids::GLOW_LICHEN)
				->writeFacingFlags($block->getFaces());
		});
		$this->map(Blocks::GLOWING_ITEM_FRAME(), fn(ItemFrame $block) => Helper::encodeItemFrame($block, Ids::GLOW_FRAME));
		$this->map(Blocks::HOPPER(), function(Hopper $block) : Writer{
			return Writer::create(Ids::HOPPER)
				->writeBool(StateNames::TOGGLE_BIT, $block->isPowered())
				->writeFacingWithoutUp($block->getFacing());
		});
		$this->map(Blocks::IRON_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::IRON_DOOR)));
		$this->map(Blocks::IRON_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::IRON_TRAPDOOR)));
		$this->map(Blocks::ITEM_FRAME(), fn(ItemFrame $block) => Helper::encodeItemFrame($block, Ids::FRAME));
		$this->map(Blocks::LAB_TABLE(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::LAB_TABLE)));
		$this->map(Blocks::LARGE_FERN(), fn(DoubleTallGrass $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::LARGE_FERN)));
		$this->map(Blocks::LAVA(), fn(Lava $block) => Helper::encodeLiquid($block, Ids::LAVA, Ids::FLOWING_LAVA));
		$this->map(Blocks::LILAC(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::LILAC)));
		$this->map(Blocks::MATERIAL_REDUCER(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, Writer::create(Ids::MATERIAL_REDUCER)));
		$this->map(Blocks::MELON_STEM(), fn(MelonStem $block) => Helper::encodeStem($block, new Writer(Ids::MELON_STEM)));
		$this->map(Blocks::MUSHROOM_STEM(), Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_STEM));
		$this->map(Blocks::PEONY(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::PEONY)));
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
		$this->map(Blocks::POLISHED_BLACKSTONE_BUTTON(), fn(Button $block) => Helper::encodeButton($block, new Writer(Ids::POLISHED_BLACKSTONE_BUTTON)));
		$this->map(Blocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), fn(SimplePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE)));
		$this->map(Blocks::POTATOES(), fn(Potato $block) => Helper::encodeCrops($block, new Writer(Ids::POTATOES)));
		$this->map(Blocks::PUMPKIN_STEM(), fn(PumpkinStem $block) => Helper::encodeStem($block, new Writer(Ids::PUMPKIN_STEM)));
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
		$this->map(Blocks::RED_MUSHROOM_BLOCK(), fn(RedMushroomBlock $block) => Helper::encodeMushroomBlock($block, new Writer(Ids::RED_MUSHROOM_BLOCK)));
		$this->map(Blocks::RESIN_CLUMP(), function(ResinClump $block) : Writer{
			return Writer::create(Ids::RESIN_CLUMP)
				->writeFacingFlags($block->getFaces());
		});
		$this->map(Blocks::ROSE_BUSH(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::ROSE_BUSH)));
		$this->map(Blocks::SEA_PICKLE(), function(SeaPickle $block) : Writer{
			return Writer::create(Ids::SEA_PICKLE)
				->writeBool(StateNames::DEAD_BIT, !$block->isUnderwater())
				->writeInt(StateNames::CLUSTER_COUNT, $block->getCount() - 1);
		});
		$this->map(Blocks::SMOKER(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::SMOKER, Ids::LIT_SMOKER));
		$this->map(Blocks::SOUL_CAMPFIRE(), function(SoulCampfire $block) : Writer{
			return Writer::create(Ids::SOUL_CAMPFIRE)
				->writeCardinalHorizontalFacing($block->getFacing())
				->writeBool(StateNames::EXTINGUISHED, !$block->isLit());
		});
		$this->map(Blocks::SPONGE(), fn(Sponge $block) => Writer::create($block->isWet() ? Ids::WET_SPONGE : Ids::SPONGE));
		$this->map(Blocks::STONE_BUTTON(), fn(StoneButton $block) => Helper::encodeButton($block, new Writer(Ids::STONE_BUTTON)));
		$this->map(Blocks::STONE_PRESSURE_PLATE(), fn(StonePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::STONE_PRESSURE_PLATE)));
		$this->map(Blocks::SUNFLOWER(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, Writer::create(Ids::SUNFLOWER)));
		$this->map(Blocks::SWEET_BERRY_BUSH(), function(SweetBerryBush $block) : Writer{
			return Writer::create(Ids::SWEET_BERRY_BUSH)
				->writeInt(StateNames::GROWTH, $block->getAge());
		});
		$this->map(Blocks::TNT(), fn(TNT $block) => Writer::create($block->worksUnderwater() ? Ids::UNDERWATER_TNT : Ids::TNT)
				->writeBool(StateNames::EXPLODE_BIT, $block->isUnstable())
		);
		$this->map(Blocks::TORCHFLOWER_CROP(), function(TorchflowerCrop $block){
			return Writer::create(Ids::TORCHFLOWER_CROP)
				->writeInt(StateNames::GROWTH, $block->isReady() ? 1 : 0);
		});
		$this->map(Blocks::VINES(), function(Vine $block) : Writer{
			return Writer::create(Ids::VINE)
				->writeInt(StateNames::VINE_DIRECTION_BITS, ($block->hasFace(Facing::NORTH) ? BlockLegacyMetadata::VINE_FLAG_NORTH : 0) | ($block->hasFace(Facing::SOUTH) ? BlockLegacyMetadata::VINE_FLAG_SOUTH : 0) | ($block->hasFace(Facing::WEST) ? BlockLegacyMetadata::VINE_FLAG_WEST : 0) | ($block->hasFace(Facing::EAST) ? BlockLegacyMetadata::VINE_FLAG_EAST : 0));
		});
		$this->map(Blocks::WATER(), fn(Water $block) => Helper::encodeLiquid($block, Ids::WATER, Ids::FLOWING_WATER));
		$this->map(Blocks::WHEAT(), fn(Wheat $block) => Helper::encodeCrops($block, new Writer(Ids::WHEAT)));
	}
}
