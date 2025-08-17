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

use pocketmine\block\AmethystCluster;
use pocketmine\block\Anvil;
use pocketmine\block\Bamboo;
use pocketmine\block\Block;
use pocketmine\block\CakeWithDyedCandle;
use pocketmine\block\CaveVines;
use pocketmine\block\DoublePitcherCrop;
use pocketmine\block\Opaque;
use pocketmine\block\PinkPetals;
use pocketmine\block\PitcherCrop;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\ChiseledBookshelfSlot;
use pocketmine\block\utils\Colored;
use pocketmine\block\utils\CopperMaterial;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DripleafState;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\LeverFacing;
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
use pocketmine\math\Axis;
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
		$this->registerCandleDeserializers();
		$this->registerFlatColorBlockDeserializers();
		$this->registerFlatCoralDeserializers();
		$this->registerCauldronDeserializers();
		$this->registerLightDeserializers();
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
	 * @phpstan-param StringEnumMap<TEnum> $mapProperty
	 * @phpstan-param \Closure(TEnum) : TBlock $getBlock
	 * @phpstan-param ?\Closure(TBlock, Reader) : TBlock $extra
	 */
	public function mapFlattenedEnum(
		StringEnumMap $mapProperty,
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
			ValueMappings::getInstance()->getEnumMap(DyeColor::class),
			$prefix,
			$suffix,
			fn(DyeColor $color) => $getBlock()->setColor($color),
			$extra
		);
	}

	private function registerCandleDeserializers() : void{
		$this->map(Ids::CANDLE, fn(Reader $in) => Helper::decodeCandle(Blocks::CANDLE(), $in));
		$this->mapColored(
			"minecraft:",
			"_candle",
			fn() => Blocks::DYED_CANDLE(),
			Helper::decodeCandle(...)
		);

		$this->map(Ids::CANDLE_CAKE, fn(Reader $in) => Blocks::CAKE_WITH_CANDLE()->setLit($in->readBool(StateNames::LIT)));

		$this->mapColored(
			"minecraft:",
			"_candle_cake",
			fn() => Blocks::CAKE_WITH_DYED_CANDLE(),
			fn(CakeWithDyedCandle $block, Reader $in) => $block->setLit($in->readBool(StateNames::LIT))
		);
	}

	private function registerFlatColorBlockDeserializers() : void{
		foreach([
			Ids::BLACK_GLAZED_TERRACOTTA => DyeColor::BLACK,
			Ids::BLUE_GLAZED_TERRACOTTA => DyeColor::BLUE,
			Ids::BROWN_GLAZED_TERRACOTTA => DyeColor::BROWN,
			Ids::CYAN_GLAZED_TERRACOTTA => DyeColor::CYAN,
			Ids::GRAY_GLAZED_TERRACOTTA => DyeColor::GRAY,
			Ids::GREEN_GLAZED_TERRACOTTA => DyeColor::GREEN,
			Ids::LIGHT_BLUE_GLAZED_TERRACOTTA => DyeColor::LIGHT_BLUE,
			Ids::SILVER_GLAZED_TERRACOTTA => DyeColor::LIGHT_GRAY, //minecraft sadness
			Ids::LIME_GLAZED_TERRACOTTA => DyeColor::LIME,
			Ids::MAGENTA_GLAZED_TERRACOTTA => DyeColor::MAGENTA,
			Ids::ORANGE_GLAZED_TERRACOTTA => DyeColor::ORANGE,
			Ids::PINK_GLAZED_TERRACOTTA => DyeColor::PINK,
			Ids::PURPLE_GLAZED_TERRACOTTA => DyeColor::PURPLE,
			Ids::RED_GLAZED_TERRACOTTA => DyeColor::RED,
			Ids::WHITE_GLAZED_TERRACOTTA => DyeColor::WHITE,
			Ids::YELLOW_GLAZED_TERRACOTTA => DyeColor::YELLOW,
		] as $id => $color){
			$this->map($id, fn(Reader $in) => Blocks::GLAZED_TERRACOTTA()
				->setColor($color)
				->setFacing($in->readHorizontalFacing())
			);
		}
	}

	private function registerFlatCoralDeserializers() : void{
		foreach([
			Ids::BRAIN_CORAL => CoralType::BRAIN,
			Ids::BUBBLE_CORAL => CoralType::BUBBLE,
			Ids::FIRE_CORAL => CoralType::FIRE,
			Ids::HORN_CORAL => CoralType::HORN,
			Ids::TUBE_CORAL => CoralType::TUBE,
		] as $id => $coralType){
			$this->mapSimple($id, fn() => Blocks::CORAL()->setCoralType($coralType)->setDead(false));
		}
		foreach([
			Ids::DEAD_BRAIN_CORAL => CoralType::BRAIN,
			Ids::DEAD_BUBBLE_CORAL => CoralType::BUBBLE,
			Ids::DEAD_FIRE_CORAL => CoralType::FIRE,
			Ids::DEAD_HORN_CORAL => CoralType::HORN,
			Ids::DEAD_TUBE_CORAL => CoralType::TUBE,
		] as $id => $coralType){
			$this->mapSimple($id, fn() => Blocks::CORAL()->setCoralType($coralType)->setDead(true));
		}

		foreach([
			[CoralType::BRAIN, Ids::BRAIN_CORAL_FAN, Ids::DEAD_BRAIN_CORAL_FAN],
			[CoralType::BUBBLE, Ids::BUBBLE_CORAL_FAN, Ids::DEAD_BUBBLE_CORAL_FAN],
			[CoralType::FIRE, Ids::FIRE_CORAL_FAN, Ids::DEAD_FIRE_CORAL_FAN],
			[CoralType::HORN, Ids::HORN_CORAL_FAN, Ids::DEAD_HORN_CORAL_FAN],
			[CoralType::TUBE, Ids::TUBE_CORAL_FAN, Ids::DEAD_TUBE_CORAL_FAN],
		] as [$coralType, $aliveId, $deadId]){
			$this->map($aliveId, fn(Reader $in) => Helper::decodeFloorCoralFan(Blocks::CORAL_FAN()->setCoralType($coralType)->setDead(false), $in));
			$this->map($deadId, fn(Reader $in) => Helper::decodeFloorCoralFan(Blocks::CORAL_FAN()->setCoralType($coralType)->setDead(true), $in));
		}

		foreach([
			[CoralType::BRAIN, Ids::BRAIN_CORAL_BLOCK, Ids::DEAD_BRAIN_CORAL_BLOCK],
			[CoralType::BUBBLE, Ids::BUBBLE_CORAL_BLOCK, Ids::DEAD_BUBBLE_CORAL_BLOCK],
			[CoralType::FIRE, Ids::FIRE_CORAL_BLOCK, Ids::DEAD_FIRE_CORAL_BLOCK],
			[CoralType::HORN, Ids::HORN_CORAL_BLOCK, Ids::DEAD_HORN_CORAL_BLOCK],
			[CoralType::TUBE, Ids::TUBE_CORAL_BLOCK, Ids::DEAD_TUBE_CORAL_BLOCK],
		] as [$coralType, $aliveId, $deadId]){
			$this->map($aliveId, fn(Reader $in) => Blocks::CORAL_BLOCK()->setCoralType($coralType)->setDead(false));
			$this->map($deadId, fn(Reader $in) => Blocks::CORAL_BLOCK()->setCoralType($coralType)->setDead(true));
		}

		foreach([
			[CoralType::BRAIN, Ids::BRAIN_CORAL_WALL_FAN, Ids::DEAD_BRAIN_CORAL_WALL_FAN],
			[CoralType::BUBBLE, Ids::BUBBLE_CORAL_WALL_FAN, Ids::DEAD_BUBBLE_CORAL_WALL_FAN],
			[CoralType::FIRE, Ids::FIRE_CORAL_WALL_FAN, Ids::DEAD_FIRE_CORAL_WALL_FAN],
			[CoralType::HORN, Ids::HORN_CORAL_WALL_FAN, Ids::DEAD_HORN_CORAL_WALL_FAN],
			[CoralType::TUBE, Ids::TUBE_CORAL_WALL_FAN, Ids::DEAD_TUBE_CORAL_WALL_FAN],
		] as [$coralType, $aliveId, $deadId]){
			$this->map($aliveId, fn(Reader $in) => Blocks::WALL_CORAL_FAN()->setFacing($in->readCoralFacing())->setCoralType($coralType)->setDead(false));
			$this->map($deadId, fn(Reader $in) => Blocks::WALL_CORAL_FAN()->setFacing($in->readCoralFacing())->setCoralType($coralType)->setDead(true));
		}
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

	private function registerLightDeserializers() : void{
		foreach([
			Ids::LIGHT_BLOCK_0 => 0,
			Ids::LIGHT_BLOCK_1 => 1,
			Ids::LIGHT_BLOCK_2 => 2,
			Ids::LIGHT_BLOCK_3 => 3,
			Ids::LIGHT_BLOCK_4 => 4,
			Ids::LIGHT_BLOCK_5 => 5,
			Ids::LIGHT_BLOCK_6 => 6,
			Ids::LIGHT_BLOCK_7 => 7,
			Ids::LIGHT_BLOCK_8 => 8,
			Ids::LIGHT_BLOCK_9 => 9,
			Ids::LIGHT_BLOCK_10 => 10,
			Ids::LIGHT_BLOCK_11 => 11,
			Ids::LIGHT_BLOCK_12 => 12,
			Ids::LIGHT_BLOCK_13 => 13,
			Ids::LIGHT_BLOCK_14 => 14,
			Ids::LIGHT_BLOCK_15 => 15,
		] as $id => $level){
			$this->mapSimple($id, fn() => Blocks::LIGHT()->setLightLevel($level));
		}
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

		$this->mapCopper(
			Ids::COPPER_BULB,
			Ids::WAXED_COPPER_BULB,
			Ids::EXPOSED_COPPER_BULB,
			Ids::WAXED_EXPOSED_COPPER_BULB,
			Ids::WEATHERED_COPPER_BULB,
			Ids::WAXED_WEATHERED_COPPER_BULB,
			Ids::OXIDIZED_COPPER_BULB,
			Ids::WAXED_OXIDIZED_COPPER_BULB,
			fn(Reader $in) => Blocks::COPPER_BULB()
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT))
		);
		$this->mapCopper(
			Ids::COPPER_DOOR,
			Ids::WAXED_COPPER_DOOR,
			Ids::EXPOSED_COPPER_DOOR,
			Ids::WAXED_EXPOSED_COPPER_DOOR,
			Ids::WEATHERED_COPPER_DOOR,
			Ids::WAXED_WEATHERED_COPPER_DOOR,
			Ids::OXIDIZED_COPPER_DOOR,
			Ids::WAXED_OXIDIZED_COPPER_DOOR,
			fn(Reader $in) => Helper::decodeDoor(Blocks::COPPER_DOOR(), $in)
		);
		$this->mapCopper(
			Ids::COPPER_TRAPDOOR,
			Ids::WAXED_COPPER_TRAPDOOR,
			Ids::EXPOSED_COPPER_TRAPDOOR,
			Ids::WAXED_EXPOSED_COPPER_TRAPDOOR,
			Ids::WEATHERED_COPPER_TRAPDOOR,
			Ids::WAXED_WEATHERED_COPPER_TRAPDOOR,
			Ids::OXIDIZED_COPPER_TRAPDOOR,
			Ids::WAXED_OXIDIZED_COPPER_TRAPDOOR,
			fn(Reader $in) => Helper::decodeTrapdoor(Blocks::COPPER_TRAPDOOR(), $in)
		);
		$this->mapCopper(
			Ids::COPPER_BLOCK,
			Ids::WAXED_COPPER,
			Ids::EXPOSED_COPPER,
			Ids::WAXED_EXPOSED_COPPER,
			Ids::WEATHERED_COPPER,
			Ids::WAXED_WEATHERED_COPPER,
			Ids::OXIDIZED_COPPER,
			Ids::WAXED_OXIDIZED_COPPER,
			fn(Reader $in) => Blocks::COPPER()
		);
		$this->mapCopper(
			Ids::CHISELED_COPPER,
			Ids::WAXED_CHISELED_COPPER,
			Ids::EXPOSED_CHISELED_COPPER,
			Ids::WAXED_EXPOSED_CHISELED_COPPER,
			Ids::WEATHERED_CHISELED_COPPER,
			Ids::WAXED_WEATHERED_CHISELED_COPPER,
			Ids::OXIDIZED_CHISELED_COPPER,
			Ids::WAXED_OXIDIZED_CHISELED_COPPER,
			fn(Reader $in) => Blocks::CHISELED_COPPER()
		);
		$this->mapCopper(
			Ids::COPPER_GRATE,
			Ids::WAXED_COPPER_GRATE,
			Ids::EXPOSED_COPPER_GRATE,
			Ids::WAXED_EXPOSED_COPPER_GRATE,
			Ids::WEATHERED_COPPER_GRATE,
			Ids::WAXED_WEATHERED_COPPER_GRATE,
			Ids::OXIDIZED_COPPER_GRATE,
			Ids::WAXED_OXIDIZED_COPPER_GRATE,
			fn(Reader $in) => Blocks::COPPER_GRATE()
		);
		$this->mapCopper(
			Ids::CUT_COPPER,
			Ids::WAXED_CUT_COPPER,
			Ids::EXPOSED_CUT_COPPER,
			Ids::WAXED_EXPOSED_CUT_COPPER,
			Ids::WEATHERED_CUT_COPPER,
			Ids::WAXED_WEATHERED_CUT_COPPER,
			Ids::OXIDIZED_CUT_COPPER,
			Ids::WAXED_OXIDIZED_CUT_COPPER,
			fn(Reader $in) => Blocks::CUT_COPPER()
		);
		$this->mapCopper(
			Ids::CUT_COPPER_STAIRS,
			Ids::WAXED_CUT_COPPER_STAIRS,
			Ids::EXPOSED_CUT_COPPER_STAIRS,
			Ids::WAXED_EXPOSED_CUT_COPPER_STAIRS,
			Ids::WEATHERED_CUT_COPPER_STAIRS,
			Ids::WAXED_WEATHERED_CUT_COPPER_STAIRS,
			Ids::OXIDIZED_CUT_COPPER_STAIRS,
			Ids::WAXED_OXIDIZED_CUT_COPPER_STAIRS,
			fn(Reader $in) => Helper::decodeStairs(Blocks::CUT_COPPER_STAIRS(), $in)
		);
	}

	private function registerDeserializers() : void{
		$this->map(Ids::AMETHYST_CLUSTER, function(Reader $in) : Block{
			return Blocks::AMETHYST_CLUSTER()
				->setStage(AmethystCluster::STAGE_CLUSTER)
				->setFacing($in->readBlockFace());
		});
		$this->map(Ids::ANVIL, function(Reader $in) : Block{
			return Blocks::ANVIL()
				->setDamage(Anvil::UNDAMAGED)
				->setFacing($in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::CHIPPED_ANVIL, function(Reader $in) : Block{
			return Blocks::ANVIL()
				->setDamage(Anvil::SLIGHTLY_DAMAGED)
				->setFacing($in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::DAMAGED_ANVIL, function(Reader $in) : Block{
			return Blocks::ANVIL()
				->setDamage(Anvil::VERY_DAMAGED)
				->setFacing($in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::BAMBOO, function(Reader $in) : Block{
			return Blocks::BAMBOO()
				->setLeafSize(match($value = $in->readString(StateNames::BAMBOO_LEAF_SIZE)){
					StringValues::BAMBOO_LEAF_SIZE_NO_LEAVES => Bamboo::NO_LEAVES,
					StringValues::BAMBOO_LEAF_SIZE_SMALL_LEAVES => Bamboo::SMALL_LEAVES,
					StringValues::BAMBOO_LEAF_SIZE_LARGE_LEAVES => Bamboo::LARGE_LEAVES,
					default => throw $in->badValueException(StateNames::BAMBOO_LEAF_SIZE, $value),
				})
				->setReady($in->readBool(StateNames::AGE_BIT))
				->setThick(match($value = $in->readString(StateNames::BAMBOO_STALK_THICKNESS)){
					StringValues::BAMBOO_STALK_THICKNESS_THIN => false,
					StringValues::BAMBOO_STALK_THICKNESS_THICK => true,
					default => throw $in->badValueException(StateNames::BAMBOO_STALK_THICKNESS, $value),
				});
		});
		$this->map(Ids::BARREL, function(Reader $in) : Block{
			return Blocks::BARREL()
				->setFacing($in->readFacingDirection())
				->setOpen($in->readBool(StateNames::OPEN_BIT));
		});
		$this->map(Ids::BED, function(Reader $in) : Block{
			return Blocks::BED()
				->setFacing($in->readLegacyHorizontalFacing())
				->setHead($in->readBool(StateNames::HEAD_PIECE_BIT))
				->setOccupied($in->readBool(StateNames::OCCUPIED_BIT));
		});
		$this->map(Ids::BEETROOT, fn(Reader $in) => Helper::decodeCrops(Blocks::BEETROOTS(), $in));
		$this->map(Ids::BELL, function(Reader $in) : Block{
			$in->ignored(StateNames::TOGGLE_BIT); //only useful at runtime
			return Blocks::BELL()
				->setFacing($in->readLegacyHorizontalFacing())
				->setAttachmentType($in->readUnitEnum(StateNames::ATTACHMENT, BellAttachmentType::class));
		});
		$this->map(Ids::BIG_DRIPLEAF, function(Reader $in) : Block{
			if($in->readBool(StateNames::BIG_DRIPLEAF_HEAD)){
				return Blocks::BIG_DRIPLEAF_HEAD()
					->setFacing($in->readCardinalHorizontalFacing())
					->setLeafState($in->readUnitEnum(StateNames::BIG_DRIPLEAF_TILT, DripleafState::class));
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
		$this->map(Ids::BONE_BLOCK, function(Reader $in) : Block{
			$in->ignored(StateNames::DEPRECATED);
			return Blocks::BONE_BLOCK()->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::BREWING_STAND, function(Reader $in) : Block{
			return Blocks::BREWING_STAND()
				->setSlot(BrewingStandSlot::EAST, $in->readBool(StateNames::BREWING_STAND_SLOT_A_BIT))
				->setSlot(BrewingStandSlot::SOUTHWEST, $in->readBool(StateNames::BREWING_STAND_SLOT_B_BIT))
				->setSlot(BrewingStandSlot::NORTHWEST, $in->readBool(StateNames::BREWING_STAND_SLOT_C_BIT));
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
		$this->map(Ids::CHISELED_COPPER, fn() => Helper::decodeCopper(Blocks::CHISELED_COPPER(), CopperOxidation::NONE));
		$this->map(Ids::COARSE_DIRT, fn() => Blocks::DIRT()->setDirtType(DirtType::COARSE));
		$this->map(Ids::COCOA, function(Reader $in) : Block{
			return Blocks::COCOA_POD()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 2))
				->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()));
		});
		$this->map(Ids::COLORED_TORCH_BLUE, fn(Reader $in) => Blocks::BLUE_TORCH()->setFacing($in->readTorchFacing()));
		$this->map(Ids::COLORED_TORCH_GREEN, fn(Reader $in) => Blocks::GREEN_TORCH()->setFacing($in->readTorchFacing()));
		$this->map(Ids::COLORED_TORCH_PURPLE, fn(Reader $in) => Blocks::PURPLE_TORCH()->setFacing($in->readTorchFacing()));
		$this->map(Ids::COLORED_TORCH_RED, fn(Reader $in) => Blocks::RED_TORCH()->setFacing($in->readTorchFacing()));
		$this->map(Ids::COMPOUND_CREATOR, fn(Reader $in) => Blocks::COMPOUND_CREATOR()
			->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()))
		);
		$this->map(Ids::DAYLIGHT_DETECTOR, fn(Reader $in) => Helper::decodeDaylightSensor(Blocks::DAYLIGHT_SENSOR(), $in)
				->setInverted(false));
		$this->map(Ids::DAYLIGHT_DETECTOR_INVERTED, fn(Reader $in) => Helper::decodeDaylightSensor(Blocks::DAYLIGHT_SENSOR(), $in)
				->setInverted(true));
		$this->map(Ids::DEEPSLATE_REDSTONE_ORE, fn() => Blocks::DEEPSLATE_REDSTONE_ORE()->setLit(false));
		$this->map(Ids::DIRT, fn() => Blocks::DIRT()->setDirtType(DirtType::NORMAL));
		$this->map(Ids::DIRT_WITH_ROOTS, fn() => Blocks::DIRT()->setDirtType(DirtType::ROOTED));
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
		$this->map(Ids::FLOWER_POT, function(Reader $in) : Block{
			$in->ignored(StateNames::UPDATE_BIT);
			return Blocks::FLOWER_POT();
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
		$this->map(Ids::HAY_BLOCK, function(Reader $in) : Block{
			$in->ignored(StateNames::DEPRECATED);
			return Blocks::HAY_BALE()->setAxis($in->readPillarAxis());
		});
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
		$this->map(Ids::LADDER, function(Reader $in) : Block{
			return Blocks::LADDER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::LARGE_AMETHYST_BUD, function(Reader $in) : Block{
			return Blocks::AMETHYST_CLUSTER()
				->setStage(AmethystCluster::STAGE_LARGE_BUD)
				->setFacing($in->readBlockFace());
		});
		$this->map(Ids::LAVA, fn(Reader $in) => Helper::decodeStillLiquid(Blocks::LAVA(), $in));
		$this->map(Ids::LEVER, function(Reader $in) : Block{
			return Blocks::LEVER()
				->setActivated($in->readBool(StateNames::OPEN_BIT))
				->setFacing($in->readUnitEnum(StateNames::LEVER_DIRECTION, LeverFacing::class));
		});
		$this->map(Ids::LIGHTNING_ROD, function(Reader $in) : Block{
			return Blocks::LIGHTNING_ROD()
				->setFacing($in->readFacingDirection());
		});
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
		$this->map(Ids::LOOM, function(Reader $in) : Block{
			return Blocks::LOOM()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::MATERIAL_REDUCER, fn(Reader $in) => Blocks::MATERIAL_REDUCER()
			->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()))
		);
		$this->map(Ids::MEDIUM_AMETHYST_BUD, function(Reader $in) : Block{
			return Blocks::AMETHYST_CLUSTER()
				->setStage(AmethystCluster::STAGE_MEDIUM_BUD)
				->setFacing($in->readBlockFace());
		});
		$this->map(Ids::MELON_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::MELON_STEM(), $in));
		$this->map(Ids::PINK_PETALS, function(Reader $in) : Block{
			//Pink petals only uses 0-3, but GROWTH state can go up to 7
			$growth = $in->readBoundedInt(StateNames::GROWTH, 0, 7);
			return Blocks::PINK_PETALS()
				->setFacing($in->readCardinalHorizontalFacing())
				->setCount(min($growth + 1, PinkPetals::MAX_COUNT));
		});
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
		$this->map(Ids::PORTAL, function(Reader $in) : Block{
			return Blocks::NETHER_PORTAL()
				->setAxis(match($value = $in->readString(StateNames::PORTAL_AXIS)){
					StringValues::PORTAL_AXIS_UNKNOWN => Axis::X,
					StringValues::PORTAL_AXIS_X => Axis::X,
					StringValues::PORTAL_AXIS_Z => Axis::Z,
					default => throw $in->badValueException(StateNames::PORTAL_AXIS, $value),
				});
		});
		$this->map(Ids::POTATOES, fn(Reader $in) => Helper::decodeCrops(Blocks::POTATOES(), $in));
		$this->map(Ids::POWERED_COMPARATOR, fn(Reader $in) => Helper::decodeComparator(Blocks::REDSTONE_COMPARATOR(), $in));
		$this->map(Ids::POWERED_REPEATER, fn(Reader $in) => Helper::decodeRepeater(Blocks::REDSTONE_REPEATER(), $in)
				->setPowered(true));
		$this->map(Ids::PUMPKIN, function(Reader $in) : Block{
			$in->ignored(StateNames::MC_CARDINAL_DIRECTION); //obsolete
			return Blocks::PUMPKIN();
		});
		$this->map(Ids::PUMPKIN_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::PUMPKIN_STEM(), $in));
		$this->map(Ids::PURPUR_BLOCK, function(Reader $in) : Block{
			$in->ignored(StateNames::PILLAR_AXIS); //???
			return Blocks::PURPUR();
		});
		$this->map(Ids::QUARTZ_BLOCK, function(Reader $in) : Opaque{
			$in->ignored(StateNames::PILLAR_AXIS);
			return Blocks::QUARTZ();
		});
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
		$this->map(Ids::SMALL_AMETHYST_BUD, function(Reader $in) : Block{
			return Blocks::AMETHYST_CLUSTER()
				->setStage(AmethystCluster::STAGE_SMALL_BUD)
				->setFacing($in->readBlockFace());
		});
		$this->map(Ids::SMOOTH_QUARTZ, function(Reader $in) : Block{
			$in->ignored(StateNames::PILLAR_AXIS);
			return Blocks::SMOOTH_QUARTZ();
		});
		$this->map(Ids::SNOW_LAYER, function(Reader $in) : Block{
			$in->ignored(StateNames::COVERED_BIT); //seems to be useless
			return Blocks::SNOW_LAYER()->setLayers($in->readBoundedInt(StateNames::HEIGHT, 0, 7) + 1);
		});
		$this->map(Ids::SOUL_CAMPFIRE, function(Reader $in) : Block{
			return Blocks::SOUL_CAMPFIRE()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(!$in->readBool(StateNames::EXTINGUISHED));
		});
		$this->map(Ids::SOUL_FIRE, function(Reader $in) : Block{
			$in->ignored(StateNames::AGE); //this is useless for soul fire, since it doesn't have the logic associated
			return Blocks::SOUL_FIRE();
		});
		$this->map(Ids::SOUL_TORCH, function(Reader $in) : Block{
			return Blocks::SOUL_TORCH()
				->setFacing($in->readTorchFacing());
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
		$this->map(Ids::TORCH, function(Reader $in) : Block{
			return Blocks::TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::TORCHFLOWER_CROP, function(Reader $in) : Block{
			return Blocks::TORCHFLOWER_CROP()
				//this property can have values 0-7, but only 0-1 are valid
				->setReady($in->readBoundedInt(StateNames::GROWTH, 0, 7) !== 0);
		});
		$this->map(Ids::TRIPWIRE_HOOK, function(Reader $in) : Block{
			return Blocks::TRIPWIRE_HOOK()
				->setConnected($in->readBool(StateNames::ATTACHED_BIT))
				->setFacing($in->readLegacyHorizontalFacing())
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::UNDERWATER_TNT, function(Reader $in) : Block{
			return Blocks::TNT()
				->setUnstable($in->readBool(StateNames::EXPLODE_BIT))
				->setWorksUnderwater(true);
		});
		$this->map(Ids::UNDERWATER_TORCH, function(Reader $in) : Block{
			return Blocks::UNDERWATER_TORCH()
				->setFacing($in->readTorchFacing());
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
		$this->map(Ids::WALL_BANNER, function(Reader $in) : Block{
			return Blocks::WALL_BANNER()
				->setFacing($in->readHorizontalFacing());
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
