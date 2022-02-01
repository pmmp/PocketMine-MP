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

namespace pocketmine\data\bedrock\blockstate;

use pocketmine\block\ActivatorRail;
use pocketmine\block\Anvil;
use pocketmine\block\Bamboo;
use pocketmine\block\BambooSapling;
use pocketmine\block\Barrel;
use pocketmine\block\Bed;
use pocketmine\block\Beetroot;
use pocketmine\block\Bell;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\BoneBlock;
use pocketmine\block\BrewingStand;
use pocketmine\block\BrownMushroomBlock;
use pocketmine\block\Cactus;
use pocketmine\block\Cake;
use pocketmine\block\Carpet;
use pocketmine\block\Carrot;
use pocketmine\block\CarvedPumpkin;
use pocketmine\block\ChemistryTable;
use pocketmine\block\Chest;
use pocketmine\block\CocoaBlock;
use pocketmine\block\Concrete;
use pocketmine\block\ConcretePowder;
use pocketmine\block\Coral;
use pocketmine\block\CoralBlock;
use pocketmine\block\DaylightSensor;
use pocketmine\block\DetectorRail;
use pocketmine\block\Dirt;
use pocketmine\block\Door;
use pocketmine\block\DoublePlant;
use pocketmine\block\DoubleTallGrass;
use pocketmine\block\DyedShulkerBox;
use pocketmine\block\EnderChest;
use pocketmine\block\EndPortalFrame;
use pocketmine\block\EndRod;
use pocketmine\block\Farmland;
use pocketmine\block\FenceGate;
use pocketmine\block\Fire;
use pocketmine\block\FloorBanner;
use pocketmine\block\FloorCoralFan;
use pocketmine\block\FloorSign;
use pocketmine\block\FrostedIce;
use pocketmine\block\Furnace;
use pocketmine\block\GlazedTerracotta;
use pocketmine\block\HayBale;
use pocketmine\block\Hopper;
use pocketmine\block\ItemFrame;
use pocketmine\block\Ladder;
use pocketmine\block\Lantern;
use pocketmine\block\Lava;
use pocketmine\block\Leaves;
use pocketmine\block\Lectern;
use pocketmine\block\Lever;
use pocketmine\block\LitPumpkin;
use pocketmine\block\Log;
use pocketmine\block\Loom;
use pocketmine\block\MelonStem;
use pocketmine\block\NetherPortal;
use pocketmine\block\NetherWartPlant;
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
use pocketmine\block\Sapling;
use pocketmine\block\SeaPickle;
use pocketmine\block\SimplePillar;
use pocketmine\block\Skull;
use pocketmine\block\Slab;
use pocketmine\block\SnowLayer;
use pocketmine\block\Sponge;
use pocketmine\block\StainedGlass;
use pocketmine\block\StainedGlassPane;
use pocketmine\block\StainedHardenedClay;
use pocketmine\block\StainedHardenedGlass;
use pocketmine\block\StainedHardenedGlassPane;
use pocketmine\block\Stair;
use pocketmine\block\StoneButton;
use pocketmine\block\StonePressurePlate;
use pocketmine\block\Sugarcane;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\TNT;
use pocketmine\block\Torch;
use pocketmine\block\Trapdoor;
use pocketmine\block\TrappedChest;
use pocketmine\block\Tripwire;
use pocketmine\block\TripwireHook;
use pocketmine\block\UnderwaterTorch;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\VanillaBlocks;
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
use pocketmine\data\bedrock\blockstate\BlockStateSerializerHelper as Helper;
use pocketmine\data\bedrock\blockstate\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\blockstate\BlockStateWriter as Writer;
use pocketmine\data\bedrock\blockstate\BlockTypeNames as Ids;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;
use function class_parents;
use function get_class;

final class BlockStateSerializer{
	/**
	 * These callables actually accept Block, but for the sake of type completeness, it has to be never, since we can't
	 * describe the bottom type of a type hierarchy only containing Block.
	 *
	 * @var \Closure[][]
	 * @phpstan-var array<int, array<class-string, \Closure(never) : Writer>>
	 */
	private array $serializers = [];

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $block
	 * @phpstan-param \Closure(TBlockType) : Writer $serializer
	 */
	public function map(Block $block, \Closure $serializer) : void{
		if(isset($this->serializers[$block->getTypeId()])){
			//TODO: REMOVE ME
			throw new AssumptionFailedError("Registering the same block twice!");
		}
		$this->serializers[$block->getTypeId()][get_class($block)] = $serializer;
	}

	/**
	 * @phpstan-template TBlockType of Block
	 * @phpstan-param TBlockType $blockState
	 */
	public function serialize(Block $blockState) : BlockStateData{
		$typeId = $blockState->getTypeId();

		$locatedSerializer = $this->serializers[$typeId][get_class($blockState)] ?? null;
		if($locatedSerializer === null){
			$parents = class_parents($blockState);
			if($parents === false){
				throw new AssumptionFailedError("A block class should always have at least one parent");
			}
			foreach($parents as $parent){
				if(isset($this->serializers[$typeId][$parent])){
					$locatedSerializer = $this->serializers[$typeId][$parent];
					break;
				}
			}
		}

		if($locatedSerializer === null){
			throw new BlockStateSerializeException("No serializer registered for " . get_class($blockState) . " with type ID $typeId");
		}

		/**
		 * @var \Closure $serializer
		 * @phpstan-var \Closure(TBlockType) : Writer $serializer
		 */
		$serializer = $locatedSerializer;

		/** @var Writer $writer */
		$writer = $serializer($blockState);
		return $writer->getBlockStateData();
	}

	public function __construct(){
		$this->map(VanillaBlocks::ACACIA_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::ACACIA_BUTTON)));
		$this->map(VanillaBlocks::ACACIA_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::ACACIA_DOOR)));
		$this->map(VanillaBlocks::ACACIA_FENCE(), fn() => Writer::create(Ids::FENCE)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_ACACIA));
		$this->map(VanillaBlocks::ACACIA_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::ACACIA_FENCE_GATE)));
		$this->map(VanillaBlocks::ACACIA_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves2($block, StringValues::NEW_LEAF_TYPE_ACACIA));
		$this->map(VanillaBlocks::ACACIA_LOG(), fn(Log $block) => Helper::encodeLog2($block, StringValues::NEW_LOG_TYPE_ACACIA));
		$this->map(VanillaBlocks::ACACIA_PLANKS(), fn() => Writer::create(Ids::PLANKS)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_ACACIA));
		$this->map(VanillaBlocks::ACACIA_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::ACACIA_PRESSURE_PLATE)));
		$this->map(VanillaBlocks::ACACIA_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_ACACIA));
		$this->map(VanillaBlocks::ACACIA_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::ACACIA_STANDING_SIGN)));
		$this->map(VanillaBlocks::ACACIA_SLAB(), fn(Slab $block) => Helper::encodeWoodenSlab($block, StringValues::WOOD_TYPE_ACACIA));
		$this->map(VanillaBlocks::ACACIA_STAIRS(), fn(WoodenStairs $block) => Helper::encodeStairs($block, new Writer(Ids::ACACIA_STAIRS)));
		$this->map(VanillaBlocks::ACACIA_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::ACACIA_TRAPDOOR)));
		$this->map(VanillaBlocks::ACACIA_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::ACACIA_WALL_SIGN)));
		$this->map(VanillaBlocks::ACACIA_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::ACTIVATOR_RAIL(), function(ActivatorRail $block) : Writer{
			return Writer::create(Ids::ACTIVATOR_RAIL)
				->writeBool(BlockStateNames::RAIL_DATA_BIT, $block->isPowered())
				->writeInt(BlockStateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(VanillaBlocks::AIR(), fn() => new Writer(Ids::AIR));
		$this->map(VanillaBlocks::ALLIUM(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_ALLIUM));
		$this->map(VanillaBlocks::ALL_SIDED_MUSHROOM_STEM(), fn() => Writer::create(Ids::BROWN_MUSHROOM_BLOCK)
				->writeInt(BlockStateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM));
		$this->map(VanillaBlocks::ANDESITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_ANDESITE));
		$this->map(VanillaBlocks::ANDESITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_ANDESITE));
		$this->map(VanillaBlocks::ANDESITE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::ANDESITE_STAIRS)));
		$this->map(VanillaBlocks::ANDESITE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_ANDESITE));
		$this->map(VanillaBlocks::ANVIL(), function(Anvil $block) : Writer{
			return Writer::create(Ids::ANVIL)
				->writeLegacyHorizontalFacing($block->getFacing())
				->writeString(BlockStateNames::DAMAGE, match($damage = $block->getDamage()){
					0 => StringValues::DAMAGE_UNDAMAGED,
					1 => StringValues::DAMAGE_SLIGHTLY_DAMAGED,
					2 => StringValues::DAMAGE_VERY_DAMAGED,
					default => throw new BlockStateSerializeException("Invalid Anvil damage {$damage}"),
				});
		});
		$this->map(VanillaBlocks::AZURE_BLUET(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_HOUSTONIA));
		$this->map(VanillaBlocks::BAMBOO(), function(Bamboo $block) : Writer{
			return Writer::create(Ids::BAMBOO)
				->writeBool(BlockStateNames::AGE_BIT, $block->isReady())
				->writeString(BlockStateNames::BAMBOO_LEAF_SIZE, match($block->getLeafSize()){
					Bamboo::NO_LEAVES => StringValues::BAMBOO_LEAF_SIZE_NO_LEAVES,
					Bamboo::SMALL_LEAVES => StringValues::BAMBOO_LEAF_SIZE_SMALL_LEAVES,
					Bamboo::LARGE_LEAVES => StringValues::BAMBOO_LEAF_SIZE_LARGE_LEAVES,
					default => throw new BlockStateSerializeException("Invalid Bamboo leaf thickness " . $block->getLeafSize()),
				})
				->writeString(BlockStateNames::BAMBOO_STALK_THICKNESS, $block->isThick() ? StringValues::BAMBOO_STALK_THICKNESS_THICK : StringValues::BAMBOO_STALK_THICKNESS_THIN);
		});
		$this->map(VanillaBlocks::BAMBOO_SAPLING(), function(BambooSapling $block) : Writer{
			return Writer::create(Ids::BAMBOO_SAPLING)
				->writeBool(BlockStateNames::AGE_BIT, $block->isReady())

				//TODO: bug in MCPE
				->writeString(BlockStateNames::SAPLING_TYPE, StringValues::SAPLING_TYPE_OAK);
		});
		$this->map(VanillaBlocks::BANNER(), function(FloorBanner $block) : Writer{
			return Writer::create(Ids::STANDING_BANNER)
				->writeInt(BlockStateNames::GROUND_SIGN_DIRECTION, $block->getRotation());
		});
		$this->map(VanillaBlocks::BARREL(), function(Barrel $block) : Writer{
			return Writer::create(Ids::BARREL)
				->writeBool(BlockStateNames::OPEN_BIT, $block->isOpen())
				->writeFacingDirection($block->getFacing());
		});
		$this->map(VanillaBlocks::BARRIER(), fn() => new Writer(Ids::BARRIER));
		$this->map(VanillaBlocks::BEACON(), fn() => new Writer(Ids::BEACON));
		$this->map(VanillaBlocks::BED(), function(Bed $block) : Writer{
			return Writer::create(Ids::BED)
				->writeBool(BlockStateNames::HEAD_PIECE_BIT, $block->isHeadPart())
				->writeBool(BlockStateNames::OCCUPIED_BIT, $block->isOccupied())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::BEDROCK(), function(Block $block) : Writer{
			return Writer::create(Ids::BEDROCK)
				->writeBool(BlockStateNames::INFINIBURN_BIT, $block->burnsForever());
		});
		$this->map(VanillaBlocks::BEETROOTS(), fn(Beetroot $block) => Helper::encodeCrops($block, new Writer(Ids::BEETROOT)));
		$this->map(VanillaBlocks::BELL(), function(Bell $block) : Writer{
			return Writer::create(Ids::BELL)
				->writeBellAttachmentType($block->getAttachmentType())
				->writeBool(BlockStateNames::TOGGLE_BIT, false) //we don't care about this; it's just to keep MCPE happy
				->writeLegacyHorizontalFacing($block->getFacing());

		});
		$this->map(VanillaBlocks::BIRCH_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::BIRCH_BUTTON)));
		$this->map(VanillaBlocks::BIRCH_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::BIRCH_DOOR)));
		$this->map(VanillaBlocks::BIRCH_FENCE(), fn() => Writer::create(Ids::FENCE)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_BIRCH));
		$this->map(VanillaBlocks::BIRCH_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::BIRCH_FENCE_GATE)));
		$this->map(VanillaBlocks::BIRCH_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves1($block, StringValues::OLD_LEAF_TYPE_BIRCH));
		$this->map(VanillaBlocks::BIRCH_LOG(), fn(Log $block) => Helper::encodeLog1($block, StringValues::OLD_LOG_TYPE_BIRCH));
		$this->map(VanillaBlocks::BIRCH_PLANKS(), fn() => Writer::create(Ids::PLANKS)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_BIRCH));
		$this->map(VanillaBlocks::BIRCH_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::BIRCH_PRESSURE_PLATE)));
		$this->map(VanillaBlocks::BIRCH_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_BIRCH));
		$this->map(VanillaBlocks::BIRCH_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::BIRCH_STANDING_SIGN)));
		$this->map(VanillaBlocks::BIRCH_SLAB(), fn(Slab $block) => Helper::encodeWoodenSlab($block, StringValues::WOOD_TYPE_BIRCH));
		$this->map(VanillaBlocks::BIRCH_STAIRS(), fn(WoodenStairs $block) => Helper::encodeStairs($block, new Writer(Ids::BIRCH_STAIRS)));
		$this->map(VanillaBlocks::BIRCH_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::BIRCH_TRAPDOOR)));
		$this->map(VanillaBlocks::BIRCH_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::BIRCH_WALL_SIGN)));
		$this->map(VanillaBlocks::BIRCH_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::BLACK_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::BLACK_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::BLAST_FURNACE(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::BLAST_FURNACE, Ids::LIT_BLAST_FURNACE));
		$this->map(VanillaBlocks::BLUE_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::BLUE_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::BLUE_ICE(), fn() => new Writer(Ids::BLUE_ICE));
		$this->map(VanillaBlocks::BLUE_ORCHID(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_ORCHID));
		$this->map(VanillaBlocks::BLUE_TORCH(), fn(Torch $block) => Helper::encodeColoredTorch($block, false, Writer::create(Ids::COLORED_TORCH_BP)));
		$this->map(VanillaBlocks::BONE_BLOCK(), function(BoneBlock $block) : Writer{
			return Writer::create(Ids::BONE_BLOCK)
				->writeInt(BlockStateNames::DEPRECATED, 0)
				->writePillarAxis($block->getAxis());
		});
		$this->map(VanillaBlocks::BOOKSHELF(), fn() => new Writer(Ids::BOOKSHELF));
		$this->map(VanillaBlocks::BREWING_STAND(), function(BrewingStand $block) : Writer{
			return Writer::create(Ids::BREWING_STAND)
				->writeBool(BlockStateNames::BREWING_STAND_SLOT_A_BIT, $block->hasSlot(BrewingStandSlot::EAST()))
				->writeBool(BlockStateNames::BREWING_STAND_SLOT_B_BIT, $block->hasSlot(BrewingStandSlot::SOUTHWEST()))
				->writeBool(BlockStateNames::BREWING_STAND_SLOT_C_BIT, $block->hasSlot(BrewingStandSlot::NORTHWEST()));
		});
		$this->map(VanillaBlocks::BRICKS(), fn() => new Writer(Ids::BRICK_BLOCK));
		$this->map(VanillaBlocks::BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_BRICK));
		$this->map(VanillaBlocks::BRICK_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::BRICK_STAIRS)));
		$this->map(VanillaBlocks::BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_BRICK));
		$this->map(VanillaBlocks::BROWN_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::BROWN_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::BROWN_MUSHROOM(), fn() => new Writer(Ids::BROWN_MUSHROOM));
		$this->map(VanillaBlocks::BROWN_MUSHROOM_BLOCK(), fn(BrownMushroomBlock $block) => Helper::encodeMushroomBlock($block, new Writer(Ids::BROWN_MUSHROOM_BLOCK)));
		$this->map(VanillaBlocks::CACTUS(), function(Cactus $block) : Writer{
			return Writer::create(Ids::CACTUS)
				->writeInt(BlockStateNames::AGE, $block->getAge());
		});
		$this->map(VanillaBlocks::CAKE(), function(Cake $block) : Writer{
			return Writer::create(Ids::CAKE)
				->writeInt(BlockStateNames::BITE_COUNTER, $block->getBites());
		});
		$this->map(VanillaBlocks::CARPET(), function(Carpet $block) : Writer{
			return Writer::create(Ids::CARPET)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::CARROTS(), fn(Carrot $block) => Helper::encodeCrops($block, new Writer(Ids::CARROTS)));
		$this->map(VanillaBlocks::CARVED_PUMPKIN(), function(CarvedPumpkin $block) : Writer{
			return Writer::create(Ids::CARVED_PUMPKIN)
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::CHEMICAL_HEAT(), fn() => new Writer(Ids::CHEMICAL_HEAT));
		$this->map(VanillaBlocks::CHEST(), function(Chest $block) : Writer{
			return Writer::create(Ids::CHEST)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::CHISELED_QUARTZ(), fn(SimplePillar $block) => Helper::encodeQuartz(StringValues::CHISEL_TYPE_CHISELED, $block->getAxis()));
		$this->map(VanillaBlocks::CHISELED_RED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::RED_SANDSTONE, StringValues::SAND_STONE_TYPE_HEIROGLYPHS));
		$this->map(VanillaBlocks::CHISELED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::SANDSTONE, StringValues::SAND_STONE_TYPE_HEIROGLYPHS));
		$this->map(VanillaBlocks::CHISELED_STONE_BRICKS(), fn() => Helper::encodeStoneBricks(StringValues::STONE_BRICK_TYPE_CHISELED));
		$this->map(VanillaBlocks::CLAY(), fn() => new Writer(Ids::CLAY));
		$this->map(VanillaBlocks::COAL(), fn() => new Writer(Ids::COAL_BLOCK));
		$this->map(VanillaBlocks::COAL_ORE(), fn() => new Writer(Ids::COAL_ORE));
		$this->map(VanillaBlocks::COBBLESTONE(), fn() => new Writer(Ids::COBBLESTONE));
		$this->map(VanillaBlocks::COBBLESTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_COBBLESTONE));
		$this->map(VanillaBlocks::COBBLESTONE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::STONE_STAIRS)));
		$this->map(VanillaBlocks::COBBLESTONE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_COBBLESTONE));
		$this->map(VanillaBlocks::COBWEB(), fn() => new Writer(Ids::WEB));
		$this->map(VanillaBlocks::COCOA_POD(), function(CocoaBlock $block) : Writer{
			return Writer::create(Ids::COCOA)
				->writeInt(BlockStateNames::AGE, $block->getAge())
				->writeLegacyHorizontalFacing(Facing::opposite($block->getFacing()));
		});
		$this->map(VanillaBlocks::COMPOUND_CREATOR(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, StringValues::CHEMISTRY_TABLE_TYPE_COMPOUND_CREATOR, new Writer(Ids::CHEMISTRY_TABLE)));
		$this->map(VanillaBlocks::CONCRETE(), function(Concrete $block) : Writer{
			return Writer::create(Ids::CONCRETE)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::CONCRETE_POWDER(), function(ConcretePowder $block) : Writer{
			return Writer::create(Ids::CONCRETEPOWDER)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::CORAL(), function(Coral $block) : Writer{
			return Writer::create(Ids::CORAL)
				->writeBool(BlockStateNames::DEAD_BIT, $block->isDead())
				->writeCoralType($block->getCoralType());
		});
		$this->map(VanillaBlocks::CORAL_BLOCK(), function(CoralBlock $block) : Writer{
			return Writer::create(Ids::CORAL_BLOCK)
				->writeBool(BlockStateNames::DEAD_BIT, $block->isDead())
				->writeCoralType($block->getCoralType());
		});
		$this->map(VanillaBlocks::CORAL_FAN(), function(FloorCoralFan $block) : Writer{
			return Writer::create($block->isDead() ? Ids::CORAL_FAN_DEAD : Ids::CORAL_FAN)
				->writeCoralType($block->getCoralType())
				->writeInt(BlockStateNames::CORAL_FAN_DIRECTION, match($axis = $block->getAxis()){
					Axis::X => 0,
					Axis::Z => 1,
					default => throw new BlockStateSerializeException("Invalid axis {$axis}"),
				});
		});
		$this->map(VanillaBlocks::CORNFLOWER(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_CORNFLOWER));
		$this->map(VanillaBlocks::CRACKED_STONE_BRICKS(), fn() => Helper::encodeStoneBricks(StringValues::STONE_BRICK_TYPE_CRACKED));
		$this->map(VanillaBlocks::CRAFTING_TABLE(), fn() => new Writer(Ids::CRAFTING_TABLE));
		$this->map(VanillaBlocks::CUT_RED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::RED_SANDSTONE, StringValues::SAND_STONE_TYPE_CUT));
		$this->map(VanillaBlocks::CUT_RED_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_CUT_RED_SANDSTONE));
		$this->map(VanillaBlocks::CUT_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::SANDSTONE, StringValues::SAND_STONE_TYPE_CUT));
		$this->map(VanillaBlocks::CUT_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_CUT_SANDSTONE));
		$this->map(VanillaBlocks::CYAN_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::CYAN_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::DANDELION(), fn() => new Writer(Ids::YELLOW_FLOWER));
		$this->map(VanillaBlocks::DARK_OAK_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::DARK_OAK_BUTTON)));
		$this->map(VanillaBlocks::DARK_OAK_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::DARK_OAK_DOOR)));
		$this->map(VanillaBlocks::DARK_OAK_FENCE(), fn() => Writer::create(Ids::FENCE)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_DARK_OAK));
		$this->map(VanillaBlocks::DARK_OAK_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::DARK_OAK_FENCE_GATE)));
		$this->map(VanillaBlocks::DARK_OAK_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves2($block, StringValues::NEW_LEAF_TYPE_DARK_OAK));
		$this->map(VanillaBlocks::DARK_OAK_LOG(), fn(Log $block) => Helper::encodeLog2($block, StringValues::NEW_LOG_TYPE_DARK_OAK));
		$this->map(VanillaBlocks::DARK_OAK_PLANKS(), fn() => Writer::create(Ids::PLANKS)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_DARK_OAK));
		$this->map(VanillaBlocks::DARK_OAK_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::DARK_OAK_PRESSURE_PLATE)));
		$this->map(VanillaBlocks::DARK_OAK_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_DARK_OAK));
		$this->map(VanillaBlocks::DARK_OAK_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::DARKOAK_STANDING_SIGN)));
		$this->map(VanillaBlocks::DARK_OAK_SLAB(), fn(Slab $block) => Helper::encodeWoodenSlab($block, StringValues::WOOD_TYPE_DARK_OAK));
		$this->map(VanillaBlocks::DARK_OAK_STAIRS(), fn(WoodenStairs $block) => Helper::encodeStairs($block, new Writer(Ids::DARK_OAK_STAIRS)));
		$this->map(VanillaBlocks::DARK_OAK_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::DARK_OAK_TRAPDOOR)));
		$this->map(VanillaBlocks::DARK_OAK_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::DARKOAK_WALL_SIGN)));
		$this->map(VanillaBlocks::DARK_OAK_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::DARK_PRISMARINE(), fn() => Writer::create(Ids::PRISMARINE)
				->writeString(BlockStateNames::PRISMARINE_BLOCK_TYPE, StringValues::PRISMARINE_BLOCK_TYPE_DARK));
		$this->map(VanillaBlocks::DARK_PRISMARINE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_PRISMARINE_DARK));
		$this->map(VanillaBlocks::DARK_PRISMARINE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::DARK_PRISMARINE_STAIRS)));
		$this->map(VanillaBlocks::DAYLIGHT_SENSOR(), function(DaylightSensor $block) : Writer{
			return Writer::create($block->isInverted() ? Ids::DAYLIGHT_DETECTOR_INVERTED : Ids::DAYLIGHT_DETECTOR)
				->writeInt(BlockStateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(VanillaBlocks::DEAD_BUSH(), fn() => new Writer(Ids::DEADBUSH));
		$this->map(VanillaBlocks::DETECTOR_RAIL(), function(DetectorRail $block) : Writer{
			return Writer::create(Ids::DETECTOR_RAIL)
				->writeBool(BlockStateNames::RAIL_DATA_BIT, $block->isActivated())
				->writeInt(BlockStateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(VanillaBlocks::DIAMOND(), fn() => new Writer(Ids::DIAMOND_BLOCK));
		$this->map(VanillaBlocks::DIAMOND_ORE(), fn() => new Writer(Ids::DIAMOND_ORE));
		$this->map(VanillaBlocks::DIORITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_DIORITE));
		$this->map(VanillaBlocks::DIORITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_DIORITE));
		$this->map(VanillaBlocks::DIORITE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::DIORITE_STAIRS)));
		$this->map(VanillaBlocks::DIORITE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_DIORITE));
		$this->map(VanillaBlocks::DIRT(), function(Dirt $block) : Writer{
			return Writer::create(Ids::DIRT)
				->writeString(BlockStateNames::DIRT_TYPE, $block->isCoarse() ? StringValues::DIRT_TYPE_COARSE : StringValues::DIRT_TYPE_NORMAL);
		});
		$this->map(VanillaBlocks::DOUBLE_TALLGRASS(), fn(DoubleTallGrass $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_GRASS, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(VanillaBlocks::DRAGON_EGG(), fn() => new Writer(Ids::DRAGON_EGG));
		$this->map(VanillaBlocks::DRIED_KELP(), fn() => new Writer(Ids::DRIED_KELP_BLOCK));
		$this->map(VanillaBlocks::DYED_SHULKER_BOX(), function(DyedShulkerBox $block) : Writer{
			return Writer::create(Ids::SHULKER_BOX)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::ELEMENT_ACTINIUM(), fn() => new Writer(Ids::ELEMENT_89));
		$this->map(VanillaBlocks::ELEMENT_ALUMINUM(), fn() => new Writer(Ids::ELEMENT_13));
		$this->map(VanillaBlocks::ELEMENT_AMERICIUM(), fn() => new Writer(Ids::ELEMENT_95));
		$this->map(VanillaBlocks::ELEMENT_ANTIMONY(), fn() => new Writer(Ids::ELEMENT_51));
		$this->map(VanillaBlocks::ELEMENT_ARGON(), fn() => new Writer(Ids::ELEMENT_18));
		$this->map(VanillaBlocks::ELEMENT_ARSENIC(), fn() => new Writer(Ids::ELEMENT_33));
		$this->map(VanillaBlocks::ELEMENT_ASTATINE(), fn() => new Writer(Ids::ELEMENT_85));
		$this->map(VanillaBlocks::ELEMENT_BARIUM(), fn() => new Writer(Ids::ELEMENT_56));
		$this->map(VanillaBlocks::ELEMENT_BERKELIUM(), fn() => new Writer(Ids::ELEMENT_97));
		$this->map(VanillaBlocks::ELEMENT_BERYLLIUM(), fn() => new Writer(Ids::ELEMENT_4));
		$this->map(VanillaBlocks::ELEMENT_BISMUTH(), fn() => new Writer(Ids::ELEMENT_83));
		$this->map(VanillaBlocks::ELEMENT_BOHRIUM(), fn() => new Writer(Ids::ELEMENT_107));
		$this->map(VanillaBlocks::ELEMENT_BORON(), fn() => new Writer(Ids::ELEMENT_5));
		$this->map(VanillaBlocks::ELEMENT_BROMINE(), fn() => new Writer(Ids::ELEMENT_35));
		$this->map(VanillaBlocks::ELEMENT_CADMIUM(), fn() => new Writer(Ids::ELEMENT_48));
		$this->map(VanillaBlocks::ELEMENT_CALCIUM(), fn() => new Writer(Ids::ELEMENT_20));
		$this->map(VanillaBlocks::ELEMENT_CALIFORNIUM(), fn() => new Writer(Ids::ELEMENT_98));
		$this->map(VanillaBlocks::ELEMENT_CARBON(), fn() => new Writer(Ids::ELEMENT_6));
		$this->map(VanillaBlocks::ELEMENT_CERIUM(), fn() => new Writer(Ids::ELEMENT_58));
		$this->map(VanillaBlocks::ELEMENT_CESIUM(), fn() => new Writer(Ids::ELEMENT_55));
		$this->map(VanillaBlocks::ELEMENT_CHLORINE(), fn() => new Writer(Ids::ELEMENT_17));
		$this->map(VanillaBlocks::ELEMENT_CHROMIUM(), fn() => new Writer(Ids::ELEMENT_24));
		$this->map(VanillaBlocks::ELEMENT_COBALT(), fn() => new Writer(Ids::ELEMENT_27));
		$this->map(VanillaBlocks::ELEMENT_CONSTRUCTOR(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, StringValues::CHEMISTRY_TABLE_TYPE_ELEMENT_CONSTRUCTOR, new Writer(Ids::CHEMISTRY_TABLE)));
		$this->map(VanillaBlocks::ELEMENT_COPERNICIUM(), fn() => new Writer(Ids::ELEMENT_112));
		$this->map(VanillaBlocks::ELEMENT_COPPER(), fn() => new Writer(Ids::ELEMENT_29));
		$this->map(VanillaBlocks::ELEMENT_CURIUM(), fn() => new Writer(Ids::ELEMENT_96));
		$this->map(VanillaBlocks::ELEMENT_DARMSTADTIUM(), fn() => new Writer(Ids::ELEMENT_110));
		$this->map(VanillaBlocks::ELEMENT_DUBNIUM(), fn() => new Writer(Ids::ELEMENT_105));
		$this->map(VanillaBlocks::ELEMENT_DYSPROSIUM(), fn() => new Writer(Ids::ELEMENT_66));
		$this->map(VanillaBlocks::ELEMENT_EINSTEINIUM(), fn() => new Writer(Ids::ELEMENT_99));
		$this->map(VanillaBlocks::ELEMENT_ERBIUM(), fn() => new Writer(Ids::ELEMENT_68));
		$this->map(VanillaBlocks::ELEMENT_EUROPIUM(), fn() => new Writer(Ids::ELEMENT_63));
		$this->map(VanillaBlocks::ELEMENT_FERMIUM(), fn() => new Writer(Ids::ELEMENT_100));
		$this->map(VanillaBlocks::ELEMENT_FLEROVIUM(), fn() => new Writer(Ids::ELEMENT_114));
		$this->map(VanillaBlocks::ELEMENT_FLUORINE(), fn() => new Writer(Ids::ELEMENT_9));
		$this->map(VanillaBlocks::ELEMENT_FRANCIUM(), fn() => new Writer(Ids::ELEMENT_87));
		$this->map(VanillaBlocks::ELEMENT_GADOLINIUM(), fn() => new Writer(Ids::ELEMENT_64));
		$this->map(VanillaBlocks::ELEMENT_GALLIUM(), fn() => new Writer(Ids::ELEMENT_31));
		$this->map(VanillaBlocks::ELEMENT_GERMANIUM(), fn() => new Writer(Ids::ELEMENT_32));
		$this->map(VanillaBlocks::ELEMENT_GOLD(), fn() => new Writer(Ids::ELEMENT_79));
		$this->map(VanillaBlocks::ELEMENT_HAFNIUM(), fn() => new Writer(Ids::ELEMENT_72));
		$this->map(VanillaBlocks::ELEMENT_HASSIUM(), fn() => new Writer(Ids::ELEMENT_108));
		$this->map(VanillaBlocks::ELEMENT_HELIUM(), fn() => new Writer(Ids::ELEMENT_2));
		$this->map(VanillaBlocks::ELEMENT_HOLMIUM(), fn() => new Writer(Ids::ELEMENT_67));
		$this->map(VanillaBlocks::ELEMENT_HYDROGEN(), fn() => new Writer(Ids::ELEMENT_1));
		$this->map(VanillaBlocks::ELEMENT_INDIUM(), fn() => new Writer(Ids::ELEMENT_49));
		$this->map(VanillaBlocks::ELEMENT_IODINE(), fn() => new Writer(Ids::ELEMENT_53));
		$this->map(VanillaBlocks::ELEMENT_IRIDIUM(), fn() => new Writer(Ids::ELEMENT_77));
		$this->map(VanillaBlocks::ELEMENT_IRON(), fn() => new Writer(Ids::ELEMENT_26));
		$this->map(VanillaBlocks::ELEMENT_KRYPTON(), fn() => new Writer(Ids::ELEMENT_36));
		$this->map(VanillaBlocks::ELEMENT_LANTHANUM(), fn() => new Writer(Ids::ELEMENT_57));
		$this->map(VanillaBlocks::ELEMENT_LAWRENCIUM(), fn() => new Writer(Ids::ELEMENT_103));
		$this->map(VanillaBlocks::ELEMENT_LEAD(), fn() => new Writer(Ids::ELEMENT_82));
		$this->map(VanillaBlocks::ELEMENT_LITHIUM(), fn() => new Writer(Ids::ELEMENT_3));
		$this->map(VanillaBlocks::ELEMENT_LIVERMORIUM(), fn() => new Writer(Ids::ELEMENT_116));
		$this->map(VanillaBlocks::ELEMENT_LUTETIUM(), fn() => new Writer(Ids::ELEMENT_71));
		$this->map(VanillaBlocks::ELEMENT_MAGNESIUM(), fn() => new Writer(Ids::ELEMENT_12));
		$this->map(VanillaBlocks::ELEMENT_MANGANESE(), fn() => new Writer(Ids::ELEMENT_25));
		$this->map(VanillaBlocks::ELEMENT_MEITNERIUM(), fn() => new Writer(Ids::ELEMENT_109));
		$this->map(VanillaBlocks::ELEMENT_MENDELEVIUM(), fn() => new Writer(Ids::ELEMENT_101));
		$this->map(VanillaBlocks::ELEMENT_MERCURY(), fn() => new Writer(Ids::ELEMENT_80));
		$this->map(VanillaBlocks::ELEMENT_MOLYBDENUM(), fn() => new Writer(Ids::ELEMENT_42));
		$this->map(VanillaBlocks::ELEMENT_MOSCOVIUM(), fn() => new Writer(Ids::ELEMENT_115));
		$this->map(VanillaBlocks::ELEMENT_NEODYMIUM(), fn() => new Writer(Ids::ELEMENT_60));
		$this->map(VanillaBlocks::ELEMENT_NEON(), fn() => new Writer(Ids::ELEMENT_10));
		$this->map(VanillaBlocks::ELEMENT_NEPTUNIUM(), fn() => new Writer(Ids::ELEMENT_93));
		$this->map(VanillaBlocks::ELEMENT_NICKEL(), fn() => new Writer(Ids::ELEMENT_28));
		$this->map(VanillaBlocks::ELEMENT_NIHONIUM(), fn() => new Writer(Ids::ELEMENT_113));
		$this->map(VanillaBlocks::ELEMENT_NIOBIUM(), fn() => new Writer(Ids::ELEMENT_41));
		$this->map(VanillaBlocks::ELEMENT_NITROGEN(), fn() => new Writer(Ids::ELEMENT_7));
		$this->map(VanillaBlocks::ELEMENT_NOBELIUM(), fn() => new Writer(Ids::ELEMENT_102));
		$this->map(VanillaBlocks::ELEMENT_OGANESSON(), fn() => new Writer(Ids::ELEMENT_118));
		$this->map(VanillaBlocks::ELEMENT_OSMIUM(), fn() => new Writer(Ids::ELEMENT_76));
		$this->map(VanillaBlocks::ELEMENT_OXYGEN(), fn() => new Writer(Ids::ELEMENT_8));
		$this->map(VanillaBlocks::ELEMENT_PALLADIUM(), fn() => new Writer(Ids::ELEMENT_46));
		$this->map(VanillaBlocks::ELEMENT_PHOSPHORUS(), fn() => new Writer(Ids::ELEMENT_15));
		$this->map(VanillaBlocks::ELEMENT_PLATINUM(), fn() => new Writer(Ids::ELEMENT_78));
		$this->map(VanillaBlocks::ELEMENT_PLUTONIUM(), fn() => new Writer(Ids::ELEMENT_94));
		$this->map(VanillaBlocks::ELEMENT_POLONIUM(), fn() => new Writer(Ids::ELEMENT_84));
		$this->map(VanillaBlocks::ELEMENT_POTASSIUM(), fn() => new Writer(Ids::ELEMENT_19));
		$this->map(VanillaBlocks::ELEMENT_PRASEODYMIUM(), fn() => new Writer(Ids::ELEMENT_59));
		$this->map(VanillaBlocks::ELEMENT_PROMETHIUM(), fn() => new Writer(Ids::ELEMENT_61));
		$this->map(VanillaBlocks::ELEMENT_PROTACTINIUM(), fn() => new Writer(Ids::ELEMENT_91));
		$this->map(VanillaBlocks::ELEMENT_RADIUM(), fn() => new Writer(Ids::ELEMENT_88));
		$this->map(VanillaBlocks::ELEMENT_RADON(), fn() => new Writer(Ids::ELEMENT_86));
		$this->map(VanillaBlocks::ELEMENT_RHENIUM(), fn() => new Writer(Ids::ELEMENT_75));
		$this->map(VanillaBlocks::ELEMENT_RHODIUM(), fn() => new Writer(Ids::ELEMENT_45));
		$this->map(VanillaBlocks::ELEMENT_ROENTGENIUM(), fn() => new Writer(Ids::ELEMENT_111));
		$this->map(VanillaBlocks::ELEMENT_RUBIDIUM(), fn() => new Writer(Ids::ELEMENT_37));
		$this->map(VanillaBlocks::ELEMENT_RUTHENIUM(), fn() => new Writer(Ids::ELEMENT_44));
		$this->map(VanillaBlocks::ELEMENT_RUTHERFORDIUM(), fn() => new Writer(Ids::ELEMENT_104));
		$this->map(VanillaBlocks::ELEMENT_SAMARIUM(), fn() => new Writer(Ids::ELEMENT_62));
		$this->map(VanillaBlocks::ELEMENT_SCANDIUM(), fn() => new Writer(Ids::ELEMENT_21));
		$this->map(VanillaBlocks::ELEMENT_SEABORGIUM(), fn() => new Writer(Ids::ELEMENT_106));
		$this->map(VanillaBlocks::ELEMENT_SELENIUM(), fn() => new Writer(Ids::ELEMENT_34));
		$this->map(VanillaBlocks::ELEMENT_SILICON(), fn() => new Writer(Ids::ELEMENT_14));
		$this->map(VanillaBlocks::ELEMENT_SILVER(), fn() => new Writer(Ids::ELEMENT_47));
		$this->map(VanillaBlocks::ELEMENT_SODIUM(), fn() => new Writer(Ids::ELEMENT_11));
		$this->map(VanillaBlocks::ELEMENT_STRONTIUM(), fn() => new Writer(Ids::ELEMENT_38));
		$this->map(VanillaBlocks::ELEMENT_SULFUR(), fn() => new Writer(Ids::ELEMENT_16));
		$this->map(VanillaBlocks::ELEMENT_TANTALUM(), fn() => new Writer(Ids::ELEMENT_73));
		$this->map(VanillaBlocks::ELEMENT_TECHNETIUM(), fn() => new Writer(Ids::ELEMENT_43));
		$this->map(VanillaBlocks::ELEMENT_TELLURIUM(), fn() => new Writer(Ids::ELEMENT_52));
		$this->map(VanillaBlocks::ELEMENT_TENNESSINE(), fn() => new Writer(Ids::ELEMENT_117));
		$this->map(VanillaBlocks::ELEMENT_TERBIUM(), fn() => new Writer(Ids::ELEMENT_65));
		$this->map(VanillaBlocks::ELEMENT_THALLIUM(), fn() => new Writer(Ids::ELEMENT_81));
		$this->map(VanillaBlocks::ELEMENT_THORIUM(), fn() => new Writer(Ids::ELEMENT_90));
		$this->map(VanillaBlocks::ELEMENT_THULIUM(), fn() => new Writer(Ids::ELEMENT_69));
		$this->map(VanillaBlocks::ELEMENT_TIN(), fn() => new Writer(Ids::ELEMENT_50));
		$this->map(VanillaBlocks::ELEMENT_TITANIUM(), fn() => new Writer(Ids::ELEMENT_22));
		$this->map(VanillaBlocks::ELEMENT_TUNGSTEN(), fn() => new Writer(Ids::ELEMENT_74));
		$this->map(VanillaBlocks::ELEMENT_URANIUM(), fn() => new Writer(Ids::ELEMENT_92));
		$this->map(VanillaBlocks::ELEMENT_VANADIUM(), fn() => new Writer(Ids::ELEMENT_23));
		$this->map(VanillaBlocks::ELEMENT_XENON(), fn() => new Writer(Ids::ELEMENT_54));
		$this->map(VanillaBlocks::ELEMENT_YTTERBIUM(), fn() => new Writer(Ids::ELEMENT_70));
		$this->map(VanillaBlocks::ELEMENT_YTTRIUM(), fn() => new Writer(Ids::ELEMENT_39));
		$this->map(VanillaBlocks::ELEMENT_ZERO(), fn() => new Writer(Ids::ELEMENT_0));
		$this->map(VanillaBlocks::ELEMENT_ZINC(), fn() => new Writer(Ids::ELEMENT_30));
		$this->map(VanillaBlocks::ELEMENT_ZIRCONIUM(), fn() => new Writer(Ids::ELEMENT_40));
		$this->map(VanillaBlocks::EMERALD(), fn() => new Writer(Ids::EMERALD_BLOCK));
		$this->map(VanillaBlocks::EMERALD_ORE(), fn() => new Writer(Ids::EMERALD_ORE));
		$this->map(VanillaBlocks::ENCHANTING_TABLE(), fn() => new Writer(Ids::ENCHANTING_TABLE));
		$this->map(VanillaBlocks::ENDER_CHEST(), function(EnderChest $block) : Writer{
			return Writer::create(Ids::ENDER_CHEST)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::END_PORTAL_FRAME(), function(EndPortalFrame $block) : Writer{
			return Writer::create(Ids::END_PORTAL_FRAME)
				->writeBool(BlockStateNames::END_PORTAL_EYE_BIT, $block->hasEye())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::END_ROD(), function(EndRod $block) : Writer{
			return Writer::create(Ids::END_ROD)
				->writeEndRodFacingDirection($block->getFacing());
		});
		$this->map(VanillaBlocks::END_STONE(), fn() => new Writer(Ids::END_STONE));
		$this->map(VanillaBlocks::END_STONE_BRICKS(), fn() => new Writer(Ids::END_BRICKS));
		$this->map(VanillaBlocks::END_STONE_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_END_STONE_BRICK));
		$this->map(VanillaBlocks::END_STONE_BRICK_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::END_BRICK_STAIRS)));
		$this->map(VanillaBlocks::END_STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_END_BRICK));
		$this->map(VanillaBlocks::FAKE_WOODEN_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_WOOD));
		$this->map(VanillaBlocks::FARMLAND(), function(Farmland $block) : Writer{
			return Writer::create(Ids::FARMLAND)
				->writeInt(BlockStateNames::MOISTURIZED_AMOUNT, $block->getWetness());
		});
		$this->map(VanillaBlocks::FERN(), fn() => Writer::create(Ids::TALLGRASS)
				->writeString(BlockStateNames::TALL_GRASS_TYPE, StringValues::TALL_GRASS_TYPE_FERN));
		$this->map(VanillaBlocks::FIRE(), function(Fire $block) : Writer{
			return Writer::create(Ids::FIRE)
				->writeInt(BlockStateNames::AGE, $block->getAge());
		});
		$this->map(VanillaBlocks::FLETCHING_TABLE(), fn() => new Writer(Ids::FLETCHING_TABLE));
		$this->map(VanillaBlocks::FLOWER_POT(), function() : Writer{
			return Writer::create(Ids::FLOWER_POT)
				->writeBool(BlockStateNames::UPDATE_BIT, true); //to keep MCPE happy
		});
		$this->map(VanillaBlocks::FROSTED_ICE(), function(FrostedIce $block) : Writer{
			return Writer::create(Ids::FROSTED_ICE)
				->writeInt(BlockStateNames::AGE, $block->getAge());
		});
		$this->map(VanillaBlocks::FURNACE(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::FURNACE, Ids::LIT_FURNACE));
		$this->map(VanillaBlocks::GLASS(), fn() => new Writer(Ids::GLASS));
		$this->map(VanillaBlocks::GLASS_PANE(), fn() => new Writer(Ids::GLASS_PANE));
		$this->map(VanillaBlocks::GLOWING_OBSIDIAN(), fn() => new Writer(Ids::GLOWINGOBSIDIAN));
		$this->map(VanillaBlocks::GLOWSTONE(), fn() => new Writer(Ids::GLOWSTONE));
		$this->map(VanillaBlocks::GOLD(), fn() => new Writer(Ids::GOLD_BLOCK));
		$this->map(VanillaBlocks::GOLD_ORE(), fn() => new Writer(Ids::GOLD_ORE));
		$this->map(VanillaBlocks::GRANITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_GRANITE));
		$this->map(VanillaBlocks::GRANITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_GRANITE));
		$this->map(VanillaBlocks::GRANITE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::GRANITE_STAIRS)));
		$this->map(VanillaBlocks::GRANITE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_GRANITE));
		$this->map(VanillaBlocks::GRASS(), fn() => new Writer(Ids::GRASS));
		$this->map(VanillaBlocks::GRASS_PATH(), fn() => new Writer(Ids::GRASS_PATH));
		$this->map(VanillaBlocks::GRAVEL(), fn() => new Writer(Ids::GRAVEL));
		$this->map(VanillaBlocks::GRAY_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::GRAY_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::GREEN_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::GREEN_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::GREEN_TORCH(), fn(Torch $block) => Helper::encodeColoredTorch($block, true, Writer::create(Ids::COLORED_TORCH_RG)));
		$this->map(VanillaBlocks::HARDENED_CLAY(), fn() => new Writer(Ids::HARDENED_CLAY));
		$this->map(VanillaBlocks::HARDENED_GLASS(), fn() => new Writer(Ids::HARD_GLASS));
		$this->map(VanillaBlocks::HARDENED_GLASS_PANE(), fn() => new Writer(Ids::HARD_GLASS_PANE));
		$this->map(VanillaBlocks::HAY_BALE(), function(HayBale $block) : Writer{
			return Writer::create(Ids::HAY_BLOCK)
				->writeInt(BlockStateNames::DEPRECATED, 0)
				->writePillarAxis($block->getAxis());
		});
		$this->map(VanillaBlocks::HOPPER(), function(Hopper $block) : Writer{
			return Writer::create(Ids::HOPPER)
				->writeBool(BlockStateNames::TOGGLE_BIT, $block->isPowered())
				->writeFacingWithoutUp($block->getFacing());
		});
		$this->map(VanillaBlocks::ICE(), fn() => new Writer(Ids::ICE));
		$this->map(VanillaBlocks::INFESTED_CHISELED_STONE_BRICK(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(BlockStateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_CHISELED_STONE_BRICK));
		$this->map(VanillaBlocks::INFESTED_COBBLESTONE(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(BlockStateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_COBBLESTONE));
		$this->map(VanillaBlocks::INFESTED_CRACKED_STONE_BRICK(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(BlockStateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_CRACKED_STONE_BRICK));
		$this->map(VanillaBlocks::INFESTED_MOSSY_STONE_BRICK(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(BlockStateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_MOSSY_STONE_BRICK));
		$this->map(VanillaBlocks::INFESTED_STONE(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(BlockStateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_STONE));
		$this->map(VanillaBlocks::INFESTED_STONE_BRICK(), fn() => Writer::create(Ids::MONSTER_EGG)
				->writeString(BlockStateNames::MONSTER_EGG_STONE_TYPE, StringValues::MONSTER_EGG_STONE_TYPE_STONE_BRICK));
		$this->map(VanillaBlocks::INFO_UPDATE(), fn() => new Writer(Ids::INFO_UPDATE));
		$this->map(VanillaBlocks::INFO_UPDATE2(), fn() => new Writer(Ids::INFO_UPDATE2));
		$this->map(VanillaBlocks::INVISIBLE_BEDROCK(), fn() => new Writer(Ids::INVISIBLEBEDROCK));
		$this->map(VanillaBlocks::IRON(), fn() => new Writer(Ids::IRON_BLOCK));
		$this->map(VanillaBlocks::IRON_BARS(), fn() => new Writer(Ids::IRON_BARS));
		$this->map(VanillaBlocks::IRON_DOOR(), fn(Door $block) => Helper::encodeDoor($block, new Writer(Ids::IRON_DOOR)));
		$this->map(VanillaBlocks::IRON_ORE(), fn() => new Writer(Ids::IRON_ORE));
		$this->map(VanillaBlocks::IRON_TRAPDOOR(), fn(Trapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::IRON_TRAPDOOR)));
		$this->map(VanillaBlocks::ITEM_FRAME(), function(ItemFrame $block) : Writer{
			return Writer::create(Ids::FRAME)
				->writeBool(BlockStateNames::ITEM_FRAME_MAP_BIT, $block->hasMap())
				->writeBool(BlockStateNames::ITEM_FRAME_PHOTO_BIT, false)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::JUKEBOX(), fn() => new Writer(Ids::JUKEBOX));
		$this->map(VanillaBlocks::JUNGLE_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::JUNGLE_BUTTON)));
		$this->map(VanillaBlocks::JUNGLE_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::JUNGLE_DOOR)));
		$this->map(VanillaBlocks::JUNGLE_FENCE(), fn() => Writer::create(Ids::FENCE)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_JUNGLE));
		$this->map(VanillaBlocks::JUNGLE_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::JUNGLE_FENCE_GATE)));
		$this->map(VanillaBlocks::JUNGLE_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves1($block, StringValues::OLD_LEAF_TYPE_JUNGLE));
		$this->map(VanillaBlocks::JUNGLE_LOG(), fn(Log $block) => Helper::encodeLog1($block, StringValues::OLD_LOG_TYPE_JUNGLE));
		$this->map(VanillaBlocks::JUNGLE_PLANKS(), fn() => Writer::create(Ids::PLANKS)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_JUNGLE));
		$this->map(VanillaBlocks::JUNGLE_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::JUNGLE_PRESSURE_PLATE)));
		$this->map(VanillaBlocks::JUNGLE_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_JUNGLE));
		$this->map(VanillaBlocks::JUNGLE_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::JUNGLE_STANDING_SIGN)));
		$this->map(VanillaBlocks::JUNGLE_SLAB(), fn(Slab $block) => Helper::encodeWoodenSlab($block, StringValues::WOOD_TYPE_JUNGLE));
		$this->map(VanillaBlocks::JUNGLE_STAIRS(), fn(WoodenStairs $block) => Helper::encodeStairs($block, new Writer(Ids::JUNGLE_STAIRS)));
		$this->map(VanillaBlocks::JUNGLE_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::JUNGLE_TRAPDOOR)));
		$this->map(VanillaBlocks::JUNGLE_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::JUNGLE_WALL_SIGN)));
		$this->map(VanillaBlocks::JUNGLE_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::LAB_TABLE(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, StringValues::CHEMISTRY_TABLE_TYPE_LAB_TABLE, new Writer(Ids::CHEMISTRY_TABLE)));
		$this->map(VanillaBlocks::LADDER(), function(Ladder $block) : Writer{
			return Writer::create(Ids::LADDER)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::LANTERN(), function(Lantern $block) : Writer{
			return Writer::create(Ids::LANTERN)
				->writeBool(BlockStateNames::HANGING, $block->isHanging());
		});
		$this->map(VanillaBlocks::LAPIS_LAZULI(), fn() => new Writer(Ids::LAPIS_BLOCK));
		$this->map(VanillaBlocks::LAPIS_LAZULI_ORE(), fn() => new Writer(Ids::LAPIS_ORE));
		$this->map(VanillaBlocks::LARGE_FERN(), fn(DoubleTallGrass $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_FERN, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(VanillaBlocks::LAVA(), fn(Lava $block) => Helper::encodeLiquid($block, Ids::LAVA, Ids::FLOWING_LAVA));
		$this->map(VanillaBlocks::LECTERN(), function(Lectern $block) : Writer{
			return Writer::create(Ids::LECTERN)
				->writeBool(BlockStateNames::POWERED_BIT, $block->isProducingSignal())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::LEGACY_STONECUTTER(), fn() => new Writer(Ids::STONECUTTER));
		$this->map(VanillaBlocks::LEVER(), function(Lever $block) : Writer{
			return Writer::create(Ids::LEVER)
				->writeBool(BlockStateNames::OPEN_BIT, $block->isActivated())
				->writeString(BlockStateNames::LEVER_DIRECTION, match($block->getFacing()->id()){
					LeverFacing::DOWN_AXIS_Z()->id() => StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH,
					LeverFacing::DOWN_AXIS_X()->id() => StringValues::LEVER_DIRECTION_DOWN_EAST_WEST,
					LeverFacing::UP_AXIS_Z()->id() => StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH,
					LeverFacing::UP_AXIS_X()->id() => StringValues::LEVER_DIRECTION_UP_EAST_WEST,
					LeverFacing::NORTH()->id() => StringValues::LEVER_DIRECTION_NORTH,
					LeverFacing::SOUTH()->id() => StringValues::LEVER_DIRECTION_SOUTH,
					LeverFacing::WEST()->id() => StringValues::LEVER_DIRECTION_WEST,
					LeverFacing::EAST()->id() => StringValues::LEVER_DIRECTION_EAST,
					default => throw new BlockStateSerializeException("Invalid Lever facing " . $block->getFacing()->name()),
				});
		});
		$this->map(VanillaBlocks::LIGHT_BLUE_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::LIGHT_BLUE_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::LIGHT_GRAY_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::SILVER_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::LILAC(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_SYRINGA, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(VanillaBlocks::LILY_OF_THE_VALLEY(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_LILY_OF_THE_VALLEY));
		$this->map(VanillaBlocks::LILY_PAD(), fn() => new Writer(Ids::WATERLILY));
		$this->map(VanillaBlocks::LIME_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::LIME_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::LIT_PUMPKIN(), function(LitPumpkin $block) : Writer{
			return Writer::create(Ids::LIT_PUMPKIN)
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::LOOM(), function(Loom $block) : Writer{
			return Writer::create(Ids::LOOM)
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::MAGENTA_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::MAGENTA_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::MAGMA(), fn() => new Writer(Ids::MAGMA));
		$this->map(VanillaBlocks::MATERIAL_REDUCER(), fn(ChemistryTable $block) => Helper::encodeChemistryTable($block, StringValues::CHEMISTRY_TABLE_TYPE_MATERIAL_REDUCER, new Writer(Ids::CHEMISTRY_TABLE)));
		$this->map(VanillaBlocks::MELON(), fn() => new Writer(Ids::MELON_BLOCK));
		$this->map(VanillaBlocks::MELON_STEM(), fn(MelonStem $block) => Helper::encodeStem($block, new Writer(Ids::MELON_STEM)));
		$this->map(VanillaBlocks::MOB_HEAD(), function(Skull $block) : Writer{
			return Writer::create(Ids::SKULL)
				->writeBool(BlockStateNames::NO_DROP_BIT, $block->isNoDrops())
				->writeFacingWithoutDown($block->getFacing());
		});
		$this->map(VanillaBlocks::MONSTER_SPAWNER(), fn() => new Writer(Ids::MOB_SPAWNER));
		$this->map(VanillaBlocks::MOSSY_COBBLESTONE(), fn() => new Writer(Ids::MOSSY_COBBLESTONE));
		$this->map(VanillaBlocks::MOSSY_COBBLESTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_MOSSY_COBBLESTONE));
		$this->map(VanillaBlocks::MOSSY_COBBLESTONE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::MOSSY_COBBLESTONE_STAIRS)));
		$this->map(VanillaBlocks::MOSSY_COBBLESTONE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_MOSSY_COBBLESTONE));
		$this->map(VanillaBlocks::MOSSY_STONE_BRICKS(), fn() => Helper::encodeStoneBricks(StringValues::STONE_BRICK_TYPE_MOSSY));
		$this->map(VanillaBlocks::MOSSY_STONE_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_MOSSY_STONE_BRICK));
		$this->map(VanillaBlocks::MOSSY_STONE_BRICK_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::MOSSY_STONE_BRICK_STAIRS)));
		$this->map(VanillaBlocks::MOSSY_STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_MOSSY_STONE_BRICK));
		$this->map(VanillaBlocks::MUSHROOM_STEM(), fn() => Writer::create(Ids::BROWN_MUSHROOM_BLOCK)
				->writeInt(BlockStateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_STEM));
		$this->map(VanillaBlocks::MYCELIUM(), fn() => new Writer(Ids::MYCELIUM));
		$this->map(VanillaBlocks::NETHERRACK(), fn() => new Writer(Ids::NETHERRACK));
		$this->map(VanillaBlocks::NETHER_BRICKS(), fn() => new Writer(Ids::NETHER_BRICK));
		$this->map(VanillaBlocks::NETHER_BRICK_FENCE(), fn() => new Writer(Ids::NETHER_BRICK_FENCE));
		$this->map(VanillaBlocks::NETHER_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_NETHER_BRICK));
		$this->map(VanillaBlocks::NETHER_BRICK_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::NETHER_BRICK_STAIRS)));
		$this->map(VanillaBlocks::NETHER_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_NETHER_BRICK));
		$this->map(VanillaBlocks::NETHER_PORTAL(), function(NetherPortal $block) : Writer{
			return Writer::create(Ids::PORTAL)
				->writeString(BlockStateNames::PORTAL_AXIS, match($block->getAxis()){
					Axis::X => StringValues::PORTAL_AXIS_X,
					Axis::Z => StringValues::PORTAL_AXIS_Z,
					default => throw new BlockStateSerializeException("Invalid Nether Portal axis " . $block->getAxis()),
				});
		});
		$this->map(VanillaBlocks::NETHER_QUARTZ_ORE(), fn() => new Writer(Ids::QUARTZ_ORE));
		$this->map(VanillaBlocks::NETHER_REACTOR_CORE(), fn() => new Writer(Ids::NETHERREACTOR));
		$this->map(VanillaBlocks::NETHER_WART(), function(NetherWartPlant $block) : Writer{
			return Writer::create(Ids::NETHER_WART)
				->writeInt(BlockStateNames::AGE, $block->getAge());
		});
		$this->map(VanillaBlocks::NETHER_WART_BLOCK(), fn() => new Writer(Ids::NETHER_WART_BLOCK));
		$this->map(VanillaBlocks::NOTE_BLOCK(), fn() => new Writer(Ids::NOTEBLOCK));
		$this->map(VanillaBlocks::OAK_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::WOODEN_BUTTON)));
		$this->map(VanillaBlocks::OAK_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::WOODEN_DOOR)));
		$this->map(VanillaBlocks::OAK_FENCE(), fn() => Writer::create(Ids::FENCE)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_OAK));
		$this->map(VanillaBlocks::OAK_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::FENCE_GATE)));
		$this->map(VanillaBlocks::OAK_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves1($block, StringValues::OLD_LEAF_TYPE_OAK));
		$this->map(VanillaBlocks::OAK_LOG(), fn(Log $block) => Helper::encodeLog1($block, StringValues::OLD_LOG_TYPE_OAK));
		$this->map(VanillaBlocks::OAK_PLANKS(), fn() => Writer::create(Ids::PLANKS)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_OAK));
		$this->map(VanillaBlocks::OAK_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::WOODEN_PRESSURE_PLATE)));
		$this->map(VanillaBlocks::OAK_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_OAK));
		$this->map(VanillaBlocks::OAK_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::STANDING_SIGN)));
		$this->map(VanillaBlocks::OAK_SLAB(), fn(Slab $block) => Helper::encodeWoodenSlab($block, StringValues::WOOD_TYPE_OAK));
		$this->map(VanillaBlocks::OAK_STAIRS(), fn(WoodenStairs $block) => Helper::encodeStairs($block, new Writer(Ids::OAK_STAIRS)));
		$this->map(VanillaBlocks::OAK_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::TRAPDOOR)));
		$this->map(VanillaBlocks::OAK_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::WALL_SIGN)));
		$this->map(VanillaBlocks::OAK_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::OBSIDIAN(), fn() => new Writer(Ids::OBSIDIAN));
		$this->map(VanillaBlocks::ORANGE_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::ORANGE_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::ORANGE_TULIP(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_TULIP_ORANGE));
		$this->map(VanillaBlocks::OXEYE_DAISY(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_OXEYE));
		$this->map(VanillaBlocks::PACKED_ICE(), fn() => new Writer(Ids::PACKED_ICE));
		$this->map(VanillaBlocks::PEONY(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_PAEONIA, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(VanillaBlocks::PINK_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::PINK_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::PINK_TULIP(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_TULIP_PINK));
		$this->map(VanillaBlocks::PODZOL(), fn() => new Writer(Ids::PODZOL));
		$this->map(VanillaBlocks::POLISHED_ANDESITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_ANDESITE_SMOOTH));
		$this->map(VanillaBlocks::POLISHED_ANDESITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_POLISHED_ANDESITE));
		$this->map(VanillaBlocks::POLISHED_ANDESITE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::POLISHED_ANDESITE_STAIRS)));
		$this->map(VanillaBlocks::POLISHED_DIORITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_DIORITE_SMOOTH));
		$this->map(VanillaBlocks::POLISHED_DIORITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_POLISHED_DIORITE));
		$this->map(VanillaBlocks::POLISHED_DIORITE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::POLISHED_DIORITE_STAIRS)));
		$this->map(VanillaBlocks::POLISHED_GRANITE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_GRANITE_SMOOTH));
		$this->map(VanillaBlocks::POLISHED_GRANITE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_POLISHED_GRANITE));
		$this->map(VanillaBlocks::POLISHED_GRANITE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::POLISHED_GRANITE_STAIRS)));
		$this->map(VanillaBlocks::POPPY(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_POPPY));
		$this->map(VanillaBlocks::POTATOES(), fn(Potato $block) => Helper::encodeCrops($block, new Writer(Ids::POTATOES)));
		$this->map(VanillaBlocks::POWERED_RAIL(), function(PoweredRail $block) : Writer{
			return Writer::create(Ids::GOLDEN_RAIL)
				->writeBool(BlockStateNames::RAIL_DATA_BIT, $block->isPowered())
				->writeInt(BlockStateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(VanillaBlocks::PRISMARINE(), fn() => Writer::create(Ids::PRISMARINE)
				->writeString(BlockStateNames::PRISMARINE_BLOCK_TYPE, StringValues::PRISMARINE_BLOCK_TYPE_DEFAULT));
		$this->map(VanillaBlocks::PRISMARINE_BRICKS(), fn() => Writer::create(Ids::PRISMARINE)
				->writeString(BlockStateNames::PRISMARINE_BLOCK_TYPE, StringValues::PRISMARINE_BLOCK_TYPE_BRICKS));
		$this->map(VanillaBlocks::PRISMARINE_BRICKS_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_PRISMARINE_BRICK));
		$this->map(VanillaBlocks::PRISMARINE_BRICKS_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::PRISMARINE_BRICKS_STAIRS)));
		$this->map(VanillaBlocks::PRISMARINE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_PRISMARINE_ROUGH));
		$this->map(VanillaBlocks::PRISMARINE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::PRISMARINE_STAIRS)));
		$this->map(VanillaBlocks::PRISMARINE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_PRISMARINE));
		$this->map(VanillaBlocks::PUMPKIN(), function() : Writer{
			return Writer::create(Ids::PUMPKIN)
				->writeLegacyHorizontalFacing(Facing::SOUTH); //no longer used
		});
		$this->map(VanillaBlocks::PUMPKIN_STEM(), fn(PumpkinStem $block) => Helper::encodeStem($block, new Writer(Ids::PUMPKIN_STEM)));
		$this->map(VanillaBlocks::PURPLE_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::PURPLE_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::PURPLE_TORCH(), fn(Torch $block) => Helper::encodeColoredTorch($block, true, Writer::create(Ids::COLORED_TORCH_BP)));
		$this->map(VanillaBlocks::PURPUR(), function() : Writer{
			return Writer::create(Ids::PURPUR_BLOCK)
				->writeString(BlockStateNames::CHISEL_TYPE, StringValues::CHISEL_TYPE_DEFAULT)
				->writePillarAxis(Axis::Y); //useless, but MCPE wants it
		});
		$this->map(VanillaBlocks::PURPUR_PILLAR(), function(SimplePillar $block) : Writer{
			return Writer::create(Ids::PURPUR_BLOCK)
				->writeString(BlockStateNames::CHISEL_TYPE, StringValues::CHISEL_TYPE_LINES)
				->writePillarAxis($block->getAxis());
		});
		$this->map(VanillaBlocks::PURPUR_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_PURPUR));
		$this->map(VanillaBlocks::PURPUR_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::PURPUR_STAIRS)));
		$this->map(VanillaBlocks::QUARTZ(), fn() => Helper::encodeQuartz(StringValues::CHISEL_TYPE_DEFAULT, Axis::Y));
		$this->map(VanillaBlocks::QUARTZ_PILLAR(), fn(SimplePillar $block) => Helper::encodeQuartz(StringValues::CHISEL_TYPE_LINES, $block->getAxis()));
		$this->map(VanillaBlocks::QUARTZ_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_QUARTZ));
		$this->map(VanillaBlocks::QUARTZ_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::QUARTZ_STAIRS)));
		$this->map(VanillaBlocks::RAIL(), function(Rail $block) : Writer{
			return Writer::create(Ids::RAIL)
				->writeInt(BlockStateNames::RAIL_DIRECTION, $block->getShape());
		});
		$this->map(VanillaBlocks::REDSTONE(), fn() => new Writer(Ids::REDSTONE_BLOCK));
		$this->map(VanillaBlocks::REDSTONE_COMPARATOR(), function(RedstoneComparator $block) : BlockStateWriter{
			return BlockStateWriter::create($block->isPowered() ? Ids::POWERED_COMPARATOR : Ids::UNPOWERED_COMPARATOR)
				->writeBool(BlockStateNames::OUTPUT_LIT_BIT, $block->isPowered())
				->writeBool(BlockStateNames::OUTPUT_SUBTRACT_BIT, $block->isSubtractMode())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::REDSTONE_LAMP(), fn(RedstoneLamp $block) => new Writer($block->isPowered() ? Ids::LIT_REDSTONE_LAMP : Ids::REDSTONE_LAMP));
		$this->map(VanillaBlocks::REDSTONE_ORE(), fn(RedstoneOre $block) => new Writer($block->isLit() ? Ids::LIT_REDSTONE_ORE : Ids::REDSTONE_ORE));
		$this->map(VanillaBlocks::REDSTONE_REPEATER(), function(RedstoneRepeater $block) : BlockStateWriter{
			return Writer::create($block->isPowered() ? Ids::POWERED_REPEATER : Ids::UNPOWERED_REPEATER)
				->writeLegacyHorizontalFacing($block->getFacing())
				->writeInt(BlockStateNames::REPEATER_DELAY, $block->getDelay() - 1);
		});
		$this->map(VanillaBlocks::REDSTONE_TORCH(), function(RedstoneTorch $block) : Writer{
			return Writer::create($block->isLit() ? Ids::REDSTONE_TORCH : Ids::UNLIT_REDSTONE_TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::REDSTONE_WIRE(), function(RedstoneWire $block) : Writer{
			return Writer::create(Ids::REDSTONE_WIRE)
				->writeInt(BlockStateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(VanillaBlocks::RED_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::RED_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::RED_MUSHROOM(), fn() => new Writer(Ids::RED_MUSHROOM));
		$this->map(VanillaBlocks::RED_MUSHROOM_BLOCK(), fn(RedMushroomBlock $block) => Helper::encodeMushroomBlock($block, new Writer(Ids::RED_MUSHROOM_BLOCK)));
		$this->map(VanillaBlocks::RED_NETHER_BRICKS(), fn() => new Writer(Ids::RED_NETHER_BRICK));
		$this->map(VanillaBlocks::RED_NETHER_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_RED_NETHER_BRICK));
		$this->map(VanillaBlocks::RED_NETHER_BRICK_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::RED_NETHER_BRICK_STAIRS)));
		$this->map(VanillaBlocks::RED_NETHER_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_RED_NETHER_BRICK));
		$this->map(VanillaBlocks::RED_SAND(), fn() => Writer::create(Ids::SAND)
				->writeString(BlockStateNames::SAND_TYPE, StringValues::SAND_TYPE_RED));
		$this->map(VanillaBlocks::RED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::RED_SANDSTONE, StringValues::SAND_STONE_TYPE_DEFAULT));
		$this->map(VanillaBlocks::RED_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_RED_SANDSTONE));
		$this->map(VanillaBlocks::RED_SANDSTONE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::RED_SANDSTONE_STAIRS)));
		$this->map(VanillaBlocks::RED_SANDSTONE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_RED_SANDSTONE));
		$this->map(VanillaBlocks::RED_TORCH(), fn(Torch $block) => Helper::encodeColoredTorch($block, false, Writer::create(Ids::COLORED_TORCH_RG)));
		$this->map(VanillaBlocks::RED_TULIP(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_TULIP_RED));
		$this->map(VanillaBlocks::RESERVED6(), fn() => new Writer(Ids::RESERVED6));
		$this->map(VanillaBlocks::ROSE_BUSH(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_ROSE, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(VanillaBlocks::SAND(), fn() => Writer::create(Ids::SAND)
				->writeString(BlockStateNames::SAND_TYPE, StringValues::SAND_TYPE_NORMAL));
		$this->map(VanillaBlocks::SANDSTONE(), fn() => Helper::encodeSandstone(Ids::SANDSTONE, StringValues::SAND_STONE_TYPE_DEFAULT));
		$this->map(VanillaBlocks::SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_SANDSTONE));
		$this->map(VanillaBlocks::SANDSTONE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::SANDSTONE_STAIRS)));
		$this->map(VanillaBlocks::SANDSTONE_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_SANDSTONE));
		$this->map(VanillaBlocks::SEA_LANTERN(), fn() => new Writer(Ids::SEALANTERN));
		$this->map(VanillaBlocks::SEA_PICKLE(), function(SeaPickle $block) : Writer{
			return Writer::create(Ids::SEA_PICKLE)
				->writeBool(BlockStateNames::DEAD_BIT, !$block->isUnderwater())
				->writeInt(BlockStateNames::CLUSTER_COUNT, $block->getCount() - 1);
		});
		$this->map(VanillaBlocks::SHULKER_BOX(), fn() => new Writer(Ids::UNDYED_SHULKER_BOX));
		$this->map(VanillaBlocks::SLIME(), fn() => new Writer(Ids::SLIME));
		$this->map(VanillaBlocks::SMOKER(), fn(Furnace $block) => Helper::encodeFurnace($block, Ids::SMOKER, Ids::LIT_SMOKER));
		$this->map(VanillaBlocks::SMOOTH_QUARTZ(), fn() => Helper::encodeQuartz(StringValues::CHISEL_TYPE_SMOOTH, Axis::Y));
		$this->map(VanillaBlocks::SMOOTH_QUARTZ_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_SMOOTH_QUARTZ));
		$this->map(VanillaBlocks::SMOOTH_QUARTZ_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::SMOOTH_QUARTZ_STAIRS)));
		$this->map(VanillaBlocks::SMOOTH_RED_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::RED_SANDSTONE, StringValues::SAND_STONE_TYPE_SMOOTH));
		$this->map(VanillaBlocks::SMOOTH_RED_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab3($block, StringValues::STONE_SLAB_TYPE_3_SMOOTH_RED_SANDSTONE));
		$this->map(VanillaBlocks::SMOOTH_RED_SANDSTONE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::SMOOTH_RED_SANDSTONE_STAIRS)));
		$this->map(VanillaBlocks::SMOOTH_SANDSTONE(), fn() => Helper::encodeSandstone(Ids::SANDSTONE, StringValues::SAND_STONE_TYPE_SMOOTH));
		$this->map(VanillaBlocks::SMOOTH_SANDSTONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab2($block, StringValues::STONE_SLAB_TYPE_2_SMOOTH_SANDSTONE));
		$this->map(VanillaBlocks::SMOOTH_SANDSTONE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::SMOOTH_SANDSTONE_STAIRS)));
		$this->map(VanillaBlocks::SMOOTH_STONE(), fn() => new Writer(Ids::SMOOTH_STONE));
		$this->map(VanillaBlocks::SMOOTH_STONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_SMOOTH_STONE));
		$this->map(VanillaBlocks::SNOW(), fn() => new Writer(Ids::SNOW));
		$this->map(VanillaBlocks::SNOW_LAYER(), function(SnowLayer $block) : Writer{
			return Writer::create(Ids::SNOW_LAYER)
				->writeBool(BlockStateNames::COVERED_BIT, false)
				->writeInt(BlockStateNames::HEIGHT, $block->getLayers() - 1);
		});
		$this->map(VanillaBlocks::SOUL_SAND(), fn() => new Writer(Ids::SOUL_SAND));
		$this->map(VanillaBlocks::SPONGE(), function(Sponge $block) : Writer{
			return Writer::create(Ids::SPONGE)
				->writeString(BlockStateNames::SPONGE_TYPE, $block->isWet() ? StringValues::SPONGE_TYPE_WET : StringValues::SPONGE_TYPE_DRY);
		});
		$this->map(VanillaBlocks::SPRUCE_BUTTON(), fn(WoodenButton $block) => Helper::encodeButton($block, new Writer(Ids::SPRUCE_BUTTON)));
		$this->map(VanillaBlocks::SPRUCE_DOOR(), fn(WoodenDoor $block) => Helper::encodeDoor($block, new Writer(Ids::SPRUCE_DOOR)));
		$this->map(VanillaBlocks::SPRUCE_FENCE(), fn() => Writer::create(Ids::FENCE)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_SPRUCE));
		$this->map(VanillaBlocks::SPRUCE_FENCE_GATE(), fn(FenceGate $block) => Helper::encodeFenceGate($block, new Writer(Ids::SPRUCE_FENCE_GATE)));
		$this->map(VanillaBlocks::SPRUCE_LEAVES(), fn(Leaves $block) => Helper::encodeLeaves1($block, StringValues::OLD_LEAF_TYPE_SPRUCE));
		$this->map(VanillaBlocks::SPRUCE_LOG(), fn(Log $block) => Helper::encodeLog1($block, StringValues::OLD_LOG_TYPE_SPRUCE));
		$this->map(VanillaBlocks::SPRUCE_PLANKS(), fn() => Writer::create(Ids::PLANKS)
				->writeString(BlockStateNames::WOOD_TYPE, StringValues::WOOD_TYPE_SPRUCE));
		$this->map(VanillaBlocks::SPRUCE_PRESSURE_PLATE(), fn(WoodenPressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::SPRUCE_PRESSURE_PLATE)));
		$this->map(VanillaBlocks::SPRUCE_SAPLING(), fn(Sapling $block) => Helper::encodeSapling($block, StringValues::SAPLING_TYPE_SPRUCE));
		$this->map(VanillaBlocks::SPRUCE_SIGN(), fn(FloorSign $block) => Helper::encodeFloorSign($block, new Writer(Ids::SPRUCE_STANDING_SIGN)));
		$this->map(VanillaBlocks::SPRUCE_SLAB(), fn(Slab $block) => Helper::encodeWoodenSlab($block, StringValues::WOOD_TYPE_SPRUCE));
		$this->map(VanillaBlocks::SPRUCE_STAIRS(), fn(WoodenStairs $block) => Helper::encodeStairs($block, new Writer(Ids::SPRUCE_STAIRS)));
		$this->map(VanillaBlocks::SPRUCE_TRAPDOOR(), fn(WoodenTrapdoor $block) => Helper::encodeTrapdoor($block, new Writer(Ids::SPRUCE_TRAPDOOR)));
		$this->map(VanillaBlocks::SPRUCE_WALL_SIGN(), fn(WallSign $block) => Helper::encodeWallSign($block, new Writer(Ids::SPRUCE_WALL_SIGN)));
		$this->map(VanillaBlocks::SPRUCE_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::STAINED_CLAY(), function(StainedHardenedClay $block) : Writer{
			return Writer::create(Ids::STAINED_HARDENED_CLAY)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::STAINED_GLASS(), function(StainedGlass $block) : Writer{
			return Writer::create(Ids::STAINED_GLASS)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::STAINED_GLASS_PANE(), function(StainedGlassPane $block) : Writer{
			return Writer::create(Ids::STAINED_GLASS_PANE)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::STAINED_HARDENED_GLASS(), function(StainedHardenedGlass $block) : Writer{
			return Writer::create(Ids::HARD_STAINED_GLASS)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::STAINED_HARDENED_GLASS_PANE(), function(StainedHardenedGlassPane $block) : Writer{
			return Writer::create(Ids::HARD_STAINED_GLASS_PANE)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::STONE(), fn() => Helper::encodeStone(StringValues::STONE_TYPE_STONE));
		$this->map(VanillaBlocks::STONE_BRICKS(), fn() => Helper::encodeStoneBricks(StringValues::STONE_BRICK_TYPE_DEFAULT));
		$this->map(VanillaBlocks::STONE_BRICK_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab1($block, StringValues::STONE_SLAB_TYPE_STONE_BRICK));
		$this->map(VanillaBlocks::STONE_BRICK_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::STONE_BRICK_STAIRS)));
		$this->map(VanillaBlocks::STONE_BRICK_WALL(), fn(Wall $block) => Helper::encodeLegacyWall($block, StringValues::WALL_BLOCK_TYPE_STONE_BRICK));
		$this->map(VanillaBlocks::STONE_BUTTON(), fn(StoneButton $block) => Helper::encodeButton($block, new Writer(Ids::STONE_BUTTON)));
		$this->map(VanillaBlocks::STONE_PRESSURE_PLATE(), fn(StonePressurePlate $block) => Helper::encodeSimplePressurePlate($block, new Writer(Ids::STONE_PRESSURE_PLATE)));
		$this->map(VanillaBlocks::STONE_SLAB(), fn(Slab $block) => Helper::encodeStoneSlab4($block, StringValues::STONE_SLAB_TYPE_4_STONE));
		$this->map(VanillaBlocks::STONE_STAIRS(), fn(Stair $block) => Helper::encodeStairs($block, new Writer(Ids::NORMAL_STONE_STAIRS)));
		$this->map(VanillaBlocks::STRIPPED_ACACIA_LOG(), fn(Log $block) => Writer::create(Ids::STRIPPED_ACACIA_LOG)
				->writePillarAxis($block->getAxis()));
		$this->map(VanillaBlocks::STRIPPED_ACACIA_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::STRIPPED_BIRCH_LOG(), fn(Log $block) => Writer::create(Ids::STRIPPED_BIRCH_LOG)
				->writePillarAxis($block->getAxis()));
		$this->map(VanillaBlocks::STRIPPED_BIRCH_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::STRIPPED_DARK_OAK_LOG(), fn(Log $block) => Writer::create(Ids::STRIPPED_DARK_OAK_LOG)
				->writePillarAxis($block->getAxis()));
		$this->map(VanillaBlocks::STRIPPED_DARK_OAK_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::STRIPPED_JUNGLE_LOG(), fn(Log $block) => Writer::create(Ids::STRIPPED_JUNGLE_LOG)
				->writePillarAxis($block->getAxis()));
		$this->map(VanillaBlocks::STRIPPED_JUNGLE_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::STRIPPED_OAK_LOG(), fn(Log $block) => Writer::create(Ids::STRIPPED_OAK_LOG)
				->writePillarAxis($block->getAxis()));
		$this->map(VanillaBlocks::STRIPPED_OAK_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::STRIPPED_SPRUCE_LOG(), fn(Log $block) => Writer::create(Ids::STRIPPED_SPRUCE_LOG)
				->writePillarAxis($block->getAxis()));
		$this->map(VanillaBlocks::STRIPPED_SPRUCE_WOOD(), fn(Wood $block) => Helper::encodeAllSidedLog($block));
		$this->map(VanillaBlocks::SUGARCANE(), function(Sugarcane $block) : Writer{
			return Writer::create(Ids::REEDS)
				->writeInt(BlockStateNames::AGE, $block->getAge());
		});
		$this->map(VanillaBlocks::SUNFLOWER(), fn(DoublePlant $block) => Helper::encodeDoublePlant($block, StringValues::DOUBLE_PLANT_TYPE_SUNFLOWER, Writer::create(Ids::DOUBLE_PLANT)));
		$this->map(VanillaBlocks::SWEET_BERRY_BUSH(), function(SweetBerryBush $block) : Writer{
			return Writer::create(Ids::SWEET_BERRY_BUSH)
				->writeInt(BlockStateNames::GROWTH, $block->getAge());
		});
		$this->map(VanillaBlocks::TALL_GRASS(), fn() => Writer::create(Ids::TALLGRASS)
				->writeString(BlockStateNames::TALL_GRASS_TYPE, StringValues::TALL_GRASS_TYPE_TALL));
		$this->map(VanillaBlocks::TNT(), function(TNT $block) : Writer{
			return Writer::create(Ids::TNT)
				->writeBool(BlockStateNames::ALLOW_UNDERWATER_BIT, $block->worksUnderwater())
				->writeBool(BlockStateNames::EXPLODE_BIT, $block->isUnstable());
		});
		$this->map(VanillaBlocks::TORCH(), function(Torch $block) : Writer{
			return Writer::create(Ids::TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::TRAPPED_CHEST(), function(TrappedChest $block) : Writer{
			return Writer::create(Ids::TRAPPED_CHEST)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::TRIPWIRE(), function(Tripwire $block) : Writer{
			return Writer::create(Ids::TRIPWIRE)
				->writeBool(BlockStateNames::ATTACHED_BIT, $block->isConnected())
				->writeBool(BlockStateNames::DISARMED_BIT, $block->isDisarmed())
				->writeBool(BlockStateNames::POWERED_BIT, $block->isTriggered())
				->writeBool(BlockStateNames::SUSPENDED_BIT, $block->isSuspended());
		});
		$this->map(VanillaBlocks::TRIPWIRE_HOOK(), function(TripwireHook $block) : Writer{
			return Writer::create(Ids::TRIPWIRE_HOOK)
				->writeBool(BlockStateNames::ATTACHED_BIT, $block->isConnected())
				->writeBool(BlockStateNames::POWERED_BIT, $block->isPowered())
				->writeLegacyHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::UNDERWATER_TORCH(), function(UnderwaterTorch $block) : Writer{
			return Writer::create(Ids::UNDERWATER_TORCH)
				->writeTorchFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::VINES(), function(Vine $block) : Writer{
			return Writer::create(Ids::VINE)
				->writeInt(BlockStateNames::VINE_DIRECTION_BITS, ($block->hasFace(Facing::NORTH) ? BlockLegacyMetadata::VINE_FLAG_NORTH : 0) | ($block->hasFace(Facing::SOUTH) ? BlockLegacyMetadata::VINE_FLAG_SOUTH : 0) | ($block->hasFace(Facing::WEST) ? BlockLegacyMetadata::VINE_FLAG_WEST : 0) | ($block->hasFace(Facing::EAST) ? BlockLegacyMetadata::VINE_FLAG_EAST : 0));
		});
		$this->map(VanillaBlocks::WALL_BANNER(), function(WallBanner $block) : Writer{
			return Writer::create(Ids::WALL_BANNER)
				->writeHorizontalFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::WALL_CORAL_FAN(), function(WallCoralFan $block) : Writer{
			$coralType = $block->getCoralType();
			return Writer::create(match($coralType->id()){
				CoralType::TUBE()->id(), CoralType::BRAIN()->id() => Ids::CORAL_FAN_HANG,
				CoralType::BUBBLE()->id(), CoralType::FIRE()->id() => Ids::CORAL_FAN_HANG2,
				CoralType::HORN()->id() => Ids::CORAL_FAN_HANG3,
				default => throw new BlockStateSerializeException("Invalid Coral type " . $coralType->name()),
			})
				->writeBool(BlockStateNames::CORAL_HANG_TYPE_BIT, $coralType->equals(CoralType::BRAIN()) || $coralType->equals(CoralType::FIRE()))
				->writeBool(BlockStateNames::DEAD_BIT, $block->isDead())
				->writeCoralFacing($block->getFacing());
		});
		$this->map(VanillaBlocks::WATER(), fn(Water $block) => Helper::encodeLiquid($block, Ids::WATER, Ids::FLOWING_WATER));
		$this->map(VanillaBlocks::WEIGHTED_PRESSURE_PLATE_HEAVY(), function(WeightedPressurePlateHeavy $block) : Writer{
			return Writer::create(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE)
				->writeInt(BlockStateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(VanillaBlocks::WEIGHTED_PRESSURE_PLATE_LIGHT(), function(WeightedPressurePlateLight $block) : Writer{
			return Writer::create(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE)
				->writeInt(BlockStateNames::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
		});
		$this->map(VanillaBlocks::WHEAT(), fn(Wheat $block) => Helper::encodeCrops($block, new Writer(Ids::WHEAT)));
		$this->map(VanillaBlocks::WHITE_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::WHITE_GLAZED_TERRACOTTA)));
		$this->map(VanillaBlocks::WHITE_TULIP(), fn() => Helper::encodeRedFlower(StringValues::FLOWER_TYPE_TULIP_WHITE));
		$this->map(VanillaBlocks::WOOL(), function(Wool $block) : Writer{
			return Writer::create(Ids::WOOL)
				->writeColor($block->getColor());
		});
		$this->map(VanillaBlocks::YELLOW_GLAZED_TERRACOTTA(), fn(GlazedTerracotta $block) => Helper::encodeGlazedTerracotta($block, new Writer(Ids::YELLOW_GLAZED_TERRACOTTA)));
	}
}
