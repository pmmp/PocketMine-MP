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

use pocketmine\block\Block;
use pocketmine\block\CaveVines;
use pocketmine\block\DoublePitcherCrop;
use pocketmine\block\PitcherCrop;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\utils\ChiseledBookshelfSlot;
use pocketmine\block\utils\Colored;
use pocketmine\block\utils\CopperMaterial;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\block\Wood;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateDeserializer;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateDeserializerHelper as Helper;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use pocketmine\math\Facing;
use pocketmine\utils\Utils;
use function array_key_exists;
use function count;
use function min;

final class BlockStateToObjectDeserializer implements BlockStateDeserializer{

	/**
	 * @var \Closure[]
	 * @phpstan-var array<string, \Closure(Reader $in) : Block>
	 */
	private array $deserializeFuncs = [];

	/**
	 * @var int[]
	 * @phpstan-var array<string, int>
	 */
	private array $simpleCache = [];

	public function __construct(){
		$this->registerCauldronDeserializers();
		$this->registerCopperDeserializers();
		$this->registerDeserializers();
		new BlockSerializerDeserializerRegistrar($this, null);
	}

	public function deserialize(BlockStateData $stateData) : int{
		if(count($stateData->getStates()) === 0){
			//if a block has zero properties, we can keep a map of string ID -> internal blockstate ID
			return $this->simpleCache[$stateData->getName()] ??= $this->deserializeToStateId($stateData);
		}

		//we can't cache blocks that have properties - go ahead and deserialize the slow way
		return $this->deserializeToStateId($stateData);
	}

	private function deserializeToStateId(BlockStateData $stateData) : int{
		$stateId = $this->deserializeBlock($stateData)->getStateId();
		//plugin devs seem to keep missing this and causing core crashes, so we need to verify this at the earliest
		//available opportunity
		if(!RuntimeBlockStateRegistry::getInstance()->hasStateId($stateId)){
			throw new \LogicException("State ID $stateId returned by deserializer for " . $stateData->getName() . " is not registered in RuntimeBlockStateRegistry");
		}
		return $stateId;
	}

	/** @phpstan-param \Closure(Reader) : Block $c */
	public function map(string $id, \Closure $c) : void{
		$this->deserializeFuncs[$id] = $c;
		$this->simpleCache = [];
	}

	/**
	 * Returns the existing data deserializer for the given ID, or null if none exists.
	 * This may be useful if you need to override a deserializer, but still want to be able to fall back to the original.
	 *
	 * @phpstan-return ?\Closure(Reader) : Block
	 */
	public function getDeserializerForId(string $id) : ?\Closure{
		return $this->deserializeFuncs[$id] ?? null;
	}

	/** @phpstan-param \Closure() : Block $getBlock */
	public function mapSimple(string $id, \Closure $getBlock) : void{
		$this->map($id, $getBlock);
	}

	/**
	 * @phpstan-param \Closure(Reader) : Slab $getBlock
	 */
	public function mapSlab(string $singleId, string $doubleId, \Closure $getBlock) : void{
		$this->map($singleId, fn(Reader $in) => Helper::decodeSingleSlab($getBlock($in), $in));
		$this->map($doubleId, fn(Reader $in) => Helper::decodeDoubleSlab($getBlock($in), $in));
	}

	/**
	 * @phpstan-param \Closure() : Stair $getBlock
	 */
	public function mapStairs(string $id, \Closure $getBlock) : void{
		$this->map($id, fn(Reader $in) : Stair => Helper::decodeStairs($getBlock(), $in));
	}

	/** @phpstan-param \Closure() : Wood $getBlock */
	public function mapLog(string $unstrippedId, string $strippedId, \Closure $getBlock) : void{
		$this->map($unstrippedId, fn(Reader $in) => Helper::decodeLog($getBlock(), false, $in));
		$this->map($strippedId, fn(Reader $in) => Helper::decodeLog($getBlock(), true, $in));
	}

	/**
	 * @phpstan-template TBlock of Block
	 * @phpstan-template TEnum of \UnitEnum
	 *
	 * @phpstan-param EnumFromStringStateMap<TEnum> $mapProperty
	 * @phpstan-param \Closure(TEnum) : TBlock      $getBlock
	 * @phpstan-param ?\Closure(TBlock, Reader) : TBlock $extra
	 */
	public function mapFlattenedEnum(
		EnumFromStringStateMap $mapProperty,
		string $prefix,
		string $suffix,
		\Closure $getBlock,
		?\Closure $extra = null
	) : void{
		foreach(Utils::stringifyKeys($mapProperty->getValueToEnum()) as $infix => $enumCase){
			$id = $prefix . $infix . $suffix;
			if($extra === null){
				$this->map($id, fn() => $getBlock($enumCase));
			}else{
				$this->map($id, function(Reader $in) use ($enumCase, $getBlock, $extra) : Block{
					$block = $getBlock($enumCase);
					$extra($block, $in);
					return $block;
				});
			}
		}
	}

	/**
	 * @phpstan-template TBlock of Block&Colored
	 * @phpstan-param \Closure() : TBlock $getBlock
	 * @phpstan-param ?\Closure(TBlock, Reader) : TBlock $extra
	 */
	public function mapColored(string $prefix, string $suffix, \Closure $getBlock, ?\Closure $extra = null) : void{
		$this->mapFlattenedEnum(
			ValueMappings::getInstance()->dyeColor,
			$prefix,
			$suffix,
			fn(DyeColor $color) => $getBlock()->setColor($color),
			$extra
		);
	}

	private function registerCauldronDeserializers() : void{
		$deserializer = function(Reader $in) : Block{
			$level = $in->readBoundedInt(StateNames::FILL_LEVEL, 0, 6);
			if($level === 0){
				$in->ignored(StateNames::CAULDRON_LIQUID);
				return Blocks::CAULDRON();
			}

			return (match($liquid = $in->readString(StateNames::CAULDRON_LIQUID)){
				StringValues::CAULDRON_LIQUID_WATER => Blocks::WATER_CAULDRON(),
				StringValues::CAULDRON_LIQUID_LAVA => Blocks::LAVA_CAULDRON(),
				StringValues::CAULDRON_LIQUID_POWDER_SNOW => throw new UnsupportedBlockStateException("Powder snow is not supported yet"),
				default => throw $in->badValueException(StateNames::CAULDRON_LIQUID, $liquid)
			})->setFillLevel($level);
		};
		$this->map(Ids::CAULDRON, $deserializer);
	}

	/**
	 * @phpstan-param \Closure(Reader) : (CopperMaterial&Block) $deserializer
	 */
	private function mapCopper(
		string $normalId,
		string $waxedNormalId,
		string $exposedId,
		string $waxedExposedId,
		string $weatheredId,
		string $waxedWeatheredId,
		string $oxidizedId,
		string $waxedOxidizedId,
		\Closure $deserializer
	) : void{
		foreach(Utils::stringifyKeys([
			$normalId => [CopperOxidation::NONE, false],
			$waxedNormalId => [CopperOxidation::NONE, true],
			$exposedId => [CopperOxidation::EXPOSED, false],
			$waxedExposedId => [CopperOxidation::EXPOSED, true],
			$weatheredId => [CopperOxidation::WEATHERED, false],
			$waxedWeatheredId => [CopperOxidation::WEATHERED, true],
			$oxidizedId => [CopperOxidation::OXIDIZED, false],
			$waxedOxidizedId => [CopperOxidation::OXIDIZED, true],
		]) as $id => [$oxidation, $waxed]){
			$this->map($id, fn(Reader $in) => $deserializer($in)->setOxidation($oxidation)->setWaxed($waxed));
		}
	}

	private function registerCopperDeserializers() : void{
		$this->mapCopper(
			Ids::CUT_COPPER_SLAB,
			Ids::WAXED_CUT_COPPER_SLAB,
			Ids::EXPOSED_CUT_COPPER_SLAB,
			Ids::WAXED_EXPOSED_CUT_COPPER_SLAB,
			Ids::WEATHERED_CUT_COPPER_SLAB,
			Ids::WAXED_WEATHERED_CUT_COPPER_SLAB,
			Ids::OXIDIZED_CUT_COPPER_SLAB,
			Ids::WAXED_OXIDIZED_CUT_COPPER_SLAB,
			fn(Reader $in) => Helper::decodeSingleSlab(Blocks::CUT_COPPER_SLAB(), $in)
		);
		$this->mapCopper(
			Ids::DOUBLE_CUT_COPPER_SLAB,
			Ids::WAXED_DOUBLE_CUT_COPPER_SLAB,
			Ids::EXPOSED_DOUBLE_CUT_COPPER_SLAB,
			Ids::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB,
			Ids::WEATHERED_DOUBLE_CUT_COPPER_SLAB,
			Ids::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB,
			Ids::OXIDIZED_DOUBLE_CUT_COPPER_SLAB,
			Ids::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB,
			fn(Reader $in) => Helper::decodeDoubleSlab(Blocks::CUT_COPPER_SLAB(), $in)
		);
	}

	private function registerDeserializers() : void{
		$this->map(Ids::BAMBOO, function(Reader $in) : Block{
			return Blocks::BAMBOO()
				->setLeafSize($in->mapIntFromString(StateNames::BAMBOO_LEAF_SIZE, ValueMappings::getInstance()->bambooLeafSize))
				->setReady($in->readBool(StateNames::AGE_BIT))
				->setThick(match($value = $in->readString(StateNames::BAMBOO_STALK_THICKNESS)){
					StringValues::BAMBOO_STALK_THICKNESS_THIN => false,
					StringValues::BAMBOO_STALK_THICKNESS_THICK => true,
					default => throw $in->badValueException(StateNames::BAMBOO_STALK_THICKNESS, $value),
				});
		});
		$this->map(Ids::BEETROOT, fn(Reader $in) => Helper::decodeCrops(Blocks::BEETROOTS(), $in));
		$this->map(Ids::BIG_DRIPLEAF, function(Reader $in) : Block{
			if($in->readBool(StateNames::BIG_DRIPLEAF_HEAD)){
				return Blocks::BIG_DRIPLEAF_HEAD()
					->setFacing($in->readCardinalHorizontalFacing())
					->setLeafState($in->readUnitEnum(StateNames::BIG_DRIPLEAF_TILT, ValueMappings::getInstance()->dripleafState));
			}else{
				$in->ignored(StateNames::BIG_DRIPLEAF_TILT);
				return Blocks::BIG_DRIPLEAF_STEM()->setFacing($in->readCardinalHorizontalFacing());
			}
		});
		$this->map(Ids::BLAST_FURNACE, function(Reader $in) : Block{
			return Blocks::BLAST_FURNACE()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::MUSHROOM_STEM, fn(Reader $in) => match($in->readBoundedInt(StateNames::HUGE_MUSHROOM_BITS, 0, 15)){
			BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM => Blocks::ALL_SIDED_MUSHROOM_STEM(),
			BlockLegacyMetadata::MUSHROOM_BLOCK_STEM => Blocks::MUSHROOM_STEM(),
			default => throw new BlockStateDeserializeException("This state does not exist"),
		});
		$this->map(Ids::BROWN_MUSHROOM_BLOCK, fn(Reader $in) => Helper::decodeMushroomBlock(Blocks::BROWN_MUSHROOM_BLOCK(), $in));
		$this->map(Ids::CAMPFIRE, function(Reader $in) : Block{
			return Blocks::CAMPFIRE()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(!$in->readBool(StateNames::EXTINGUISHED));
		});
		$this->map(Ids::CARROTS, fn(Reader $in) => Helper::decodeCrops(Blocks::CARROTS(), $in));
		$this->map(Ids::CAVE_VINES, function(Reader $in) : CaveVines{
			return Blocks::CAVE_VINES()
				->setBerries(false)
				->setHead(false)
				->setAge($in->readBoundedInt(StateNames::GROWING_PLANT_AGE, 0, 25));
		});
		$this->map(Ids::CAVE_VINES_BODY_WITH_BERRIES, function(Reader $in) : CaveVines{
			return Blocks::CAVE_VINES()
				->setBerries(true)
				->setHead(false)
				->setAge($in->readBoundedInt(StateNames::GROWING_PLANT_AGE, 0, 25));
		});
		$this->map(Ids::CAVE_VINES_HEAD_WITH_BERRIES, function(Reader $in) : CaveVines{
			return Blocks::CAVE_VINES()
				->setBerries(true)
				->setHead(true)
				->setAge($in->readBoundedInt(StateNames::GROWING_PLANT_AGE, 0, 25));
		});
		$this->map(Ids::CHISELED_BOOKSHELF, function(Reader $in) : Block{
			$block = Blocks::CHISELED_BOOKSHELF()
				->setFacing($in->readLegacyHorizontalFacing());

			//we don't use API constant for bounds here as the data bounds might be different to what we support internally
			$flags = $in->readBoundedInt(StateNames::BOOKS_STORED, 0, (1 << 6) - 1);
			foreach(ChiseledBookshelfSlot::cases() as $slot){
				$block->setSlot($slot, ($flags & (1 << $slot->value)) !== 0);
			}

			return $block;
		});
		$this->map(Ids::COMPOUND_CREATOR, fn(Reader $in) => Blocks::COMPOUND_CREATOR()
			->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()))
		);
		$this->map(Ids::DAYLIGHT_DETECTOR, fn(Reader $in) => Helper::decodeDaylightSensor(Blocks::DAYLIGHT_SENSOR(), $in)
				->setInverted(false));
		$this->map(Ids::DAYLIGHT_DETECTOR_INVERTED, fn(Reader $in) => Helper::decodeDaylightSensor(Blocks::DAYLIGHT_SENSOR(), $in)
				->setInverted(true));
		$this->map(Ids::DEEPSLATE_REDSTONE_ORE, fn() => Blocks::DEEPSLATE_REDSTONE_ORE()->setLit(false));
		$this->map(Ids::LARGE_FERN, fn(Reader $in) => Helper::decodeDoublePlant(Blocks::LARGE_FERN(), $in));
		$this->map(Ids::TALL_GRASS, fn(Reader $in) => Helper::decodeDoublePlant(Blocks::DOUBLE_TALLGRASS(), $in));
		$this->map(Ids::PEONY, fn(Reader $in) => Helper::decodeDoublePlant(Blocks::PEONY(), $in));
		$this->map(Ids::ROSE_BUSH, fn(Reader $in) => Helper::decodeDoublePlant(Blocks::ROSE_BUSH(), $in));
		$this->map(Ids::SUNFLOWER, fn(Reader $in) => Helper::decodeDoublePlant(Blocks::SUNFLOWER(), $in));
		$this->map(Ids::LILAC, fn(Reader $in) => Helper::decodeDoublePlant(Blocks::LILAC(), $in));
		$this->map(Ids::ELEMENT_CONSTRUCTOR, fn(Reader $in) => Blocks::ELEMENT_CONSTRUCTOR()
			->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()))
		);
		$this->map(Ids::END_ROD, function(Reader $in) : Block{
			return Blocks::END_ROD()
				->setFacing($in->readEndRodFacingDirection());
		});
		$this->map(Ids::FLOWING_LAVA, fn(Reader $in) => Helper::decodeFlowingLiquid(Blocks::LAVA(), $in));
		$this->map(Ids::FLOWING_WATER, fn(Reader $in) => Helper::decodeFlowingLiquid(Blocks::WATER(), $in));
		$this->map(Ids::FRAME, fn(Reader $in) => Helper::decodeItemFrame(Blocks::ITEM_FRAME(), $in));
		$this->map(Ids::FURNACE, function(Reader $in) : Block{
			return Blocks::FURNACE()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::GLOW_LICHEN, fn(Reader $in) => Blocks::GLOW_LICHEN()->setFaces($in->readFacingFlags()));
		$this->map(Ids::GLOW_FRAME, fn(Reader $in) => Helper::decodeItemFrame(Blocks::GLOWING_ITEM_FRAME(), $in));
		$this->map(Ids::HOPPER, function(Reader $in) : Block{
			return Blocks::HOPPER()
				->setFacing($in->readFacingWithoutUp())
				->setPowered($in->readBool(StateNames::TOGGLE_BIT));
		});
		$this->map(Ids::IRON_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::IRON_DOOR(), $in));
		$this->map(Ids::IRON_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::IRON_TRAPDOOR(), $in));
		$this->map(Ids::LAB_TABLE, fn(Reader $in) => Blocks::LAB_TABLE()
			->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()))
		);
		$this->map(Ids::LAVA, fn(Reader $in) => Helper::decodeStillLiquid(Blocks::LAVA(), $in));
		$this->map(Ids::LIT_BLAST_FURNACE, function(Reader $in) : Block{
			return Blocks::BLAST_FURNACE()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LIT_DEEPSLATE_REDSTONE_ORE, fn() => Blocks::DEEPSLATE_REDSTONE_ORE()->setLit(true));
		$this->map(Ids::LIT_FURNACE, function(Reader $in) : Block{
			return Blocks::FURNACE()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LIT_REDSTONE_LAMP, function() : Block{
			return Blocks::REDSTONE_LAMP()
				->setPowered(true);
		});
		$this->map(Ids::LIT_REDSTONE_ORE, function() : Block{
			return Blocks::REDSTONE_ORE()
				->setLit(true);
		});
		$this->map(Ids::LIT_SMOKER, function(Reader $in) : Block{
			return Blocks::SMOKER()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::MATERIAL_REDUCER, fn(Reader $in) => Blocks::MATERIAL_REDUCER()
			->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()))
		);
		$this->map(Ids::MELON_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::MELON_STEM(), $in));
		$this->map(Ids::PITCHER_CROP, function(Reader $in) : Block{
			$growth = $in->readBoundedInt(StateNames::GROWTH, 0, 7);
			$top = $in->readBool(StateNames::UPPER_BLOCK_BIT);
			if($growth <= PitcherCrop::MAX_AGE){
				//top pitcher crop with age 0-2 is an invalid state
				//only the bottom half should exist in this case
				return $top ? Blocks::AIR() : Blocks::PITCHER_CROP()->setAge($growth);
			}
			return Blocks::DOUBLE_PITCHER_CROP()
				->setAge(min($growth - PitcherCrop::MAX_AGE - 1, DoublePitcherCrop::MAX_AGE))
				->setTop($top);
		});
		$this->map(Ids::POLISHED_BLACKSTONE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::POLISHED_BLACKSTONE_BUTTON(), $in));
		$this->map(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), $in));
		$this->map(Ids::POTATOES, fn(Reader $in) => Helper::decodeCrops(Blocks::POTATOES(), $in));
		$this->map(Ids::POWERED_COMPARATOR, fn(Reader $in) => Helper::decodeComparator(Blocks::REDSTONE_COMPARATOR(), $in));
		$this->map(Ids::POWERED_REPEATER, fn(Reader $in) => Helper::decodeRepeater(Blocks::REDSTONE_REPEATER(), $in)
				->setPowered(true));
		$this->map(Ids::PUMPKIN_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::PUMPKIN_STEM(), $in));
		$this->map(Ids::RED_MUSHROOM_BLOCK, fn(Reader $in) => Helper::decodeMushroomBlock(Blocks::RED_MUSHROOM_BLOCK(), $in));
		$this->map(Ids::REDSTONE_LAMP, function() : Block{
			return Blocks::REDSTONE_LAMP()
				->setPowered(false);
		});
		$this->map(Ids::REDSTONE_ORE, function() : Block{
			return Blocks::REDSTONE_ORE()
				->setLit(false);
		});
		$this->map(Ids::REDSTONE_TORCH, function(Reader $in) : Block{
			return Blocks::REDSTONE_TORCH()
				->setFacing($in->readTorchFacing())
				->setLit(true);
		});
		$this->map(Ids::RESIN_CLUMP, fn(Reader $in) => Blocks::RESIN_CLUMP()->setFaces($in->readFacingFlags()));
		$this->map(Ids::SEA_PICKLE, function(Reader $in) : Block{
			return Blocks::SEA_PICKLE()
				->setCount($in->readBoundedInt(StateNames::CLUSTER_COUNT, 0, 3) + 1)
				->setUnderwater(!$in->readBool(StateNames::DEAD_BIT));
		});
		$this->map(Ids::SMOKER, function(Reader $in) : Block{
			return Blocks::SMOKER()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::SOUL_CAMPFIRE, function(Reader $in) : Block{
			return Blocks::SOUL_CAMPFIRE()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(!$in->readBool(StateNames::EXTINGUISHED));
		});
		$this->mapSimple(Ids::SPONGE, fn() => Blocks::SPONGE());
		$this->map(Ids::STONE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::STONE_BUTTON(), $in));
		$this->map(Ids::STONE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::STONE_PRESSURE_PLATE(), $in));
		$this->map(Ids::SWEET_BERRY_BUSH, function(Reader $in) : Block{
			//berry bush only wants 0-3, but it can be bigger in MCPE due to misuse of GROWTH state which goes up to 7
			$growth = $in->readBoundedInt(StateNames::GROWTH, 0, 7);
			return Blocks::SWEET_BERRY_BUSH()
				->setAge(min($growth, SweetBerryBush::STAGE_MATURE));
		});
		$this->map(Ids::TNT, function(Reader $in) : Block{
			return Blocks::TNT()
				->setUnstable($in->readBool(StateNames::EXPLODE_BIT))
				->setWorksUnderwater(false);
		});
		$this->map(Ids::TORCHFLOWER_CROP, function(Reader $in) : Block{
			return Blocks::TORCHFLOWER_CROP()
				//this property can have values 0-7, but only 0-1 are valid
				->setReady($in->readBoundedInt(StateNames::GROWTH, 0, 7) !== 0);
		});
		$this->map(Ids::UNDERWATER_TNT, function(Reader $in) : Block{
			return Blocks::TNT()
				->setUnstable($in->readBool(StateNames::EXPLODE_BIT))
				->setWorksUnderwater(true);
		});
		$this->map(Ids::UNLIT_REDSTONE_TORCH, function(Reader $in) : Block{
			return Blocks::REDSTONE_TORCH()
				->setFacing($in->readTorchFacing())
				->setLit(false);
		});
		$this->map(Ids::UNPOWERED_COMPARATOR, fn(Reader $in) => Helper::decodeComparator(Blocks::REDSTONE_COMPARATOR(), $in));
		$this->map(Ids::UNPOWERED_REPEATER, fn(Reader $in) => Helper::decodeRepeater(Blocks::REDSTONE_REPEATER(), $in)
				->setPowered(false));
		$this->map(Ids::VINE, function(Reader $in) : Block{
			$vineDirectionFlags = $in->readBoundedInt(StateNames::VINE_DIRECTION_BITS, 0, 15);
			return Blocks::VINES()
				->setFace(Facing::NORTH, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_NORTH) !== 0)
				->setFace(Facing::SOUTH, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_SOUTH) !== 0)
				->setFace(Facing::WEST, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_WEST) !== 0)
				->setFace(Facing::EAST, ($vineDirectionFlags & BlockLegacyMetadata::VINE_FLAG_EAST) !== 0);
		});
		$this->map(Ids::WATER, fn(Reader $in) => Helper::decodeStillLiquid(Blocks::WATER(), $in));
		$this->mapSimple(Ids::WET_SPONGE, fn() => Blocks::SPONGE()->setWet(true));
		$this->map(Ids::WHEAT, fn(Reader $in) => Helper::decodeCrops(Blocks::WHEAT(), $in));
	}

	/** @throws BlockStateDeserializeException */
	public function deserializeBlock(BlockStateData $blockStateData) : Block{
		$id = $blockStateData->getName();
		if(!array_key_exists($id, $this->deserializeFuncs)){
			throw new UnsupportedBlockStateException("Unknown block ID \"$id\"");
		}
		$reader = new Reader($blockStateData);
		$block = $this->deserializeFuncs[$id]($reader);
		$reader->checkUnreadProperties();
		return $block;
	}
}
