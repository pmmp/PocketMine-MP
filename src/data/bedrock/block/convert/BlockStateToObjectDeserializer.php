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
use pocketmine\block\Block;
use pocketmine\block\CaveVines;
use pocketmine\block\ChorusFlower;
use pocketmine\block\Light;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\SlabType;
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
		$this->registerWoodBlockDeserializers();
		$this->registerSimpleDeserializers();
		$this->registerDeserializers();
	}

	public function deserialize(BlockStateData $stateData) : int{
		if(count($stateData->getStates()) === 0){
			//if a block has zero properties, we can keep a map of string ID -> internal blockstate ID
			return $this->simpleCache[$stateData->getName()] ??= $this->deserializeBlock($stateData)->getStateId();
		}

		//we can't cache blocks that have properties - go ahead and deserialize the slow way
		return $this->deserializeBlock($stateData)->getStateId();
	}

	/** @phpstan-param \Closure(Reader) : Block $c */
	public function map(string $id, \Closure $c) : void{
		if(array_key_exists($id, $this->deserializeFuncs)){
			throw new \InvalidArgumentException("Deserializer is already assigned for \"$id\"");
		}
		$this->deserializeFuncs[$id] = $c;
	}

	/** @phpstan-param \Closure() : Block $getBlock */
	public function mapSimple(string $id, \Closure $getBlock) : void{
		$this->map($id, $getBlock);
	}

	/**
	 * @phpstan-param \Closure(Reader) : Slab $getBlock
	 */
	public function mapSlab(string $singleId, string $doubleId, \Closure $getBlock) : void{
		$this->map($singleId, fn(Reader $in) : Slab => $getBlock($in)->setSlabType($in->readSlabPosition()));
		$this->map($doubleId, function(Reader $in) use ($getBlock) : Slab{
			$in->ignored(StateNames::TOP_SLOT_BIT);
			return $getBlock($in)->setSlabType(SlabType::DOUBLE());
		});
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

	private function registerCandleDeserializers() : void{
		$this->map(Ids::CANDLE, fn(Reader $in) => Helper::decodeCandle(Blocks::CANDLE(), $in));
		foreach([
			Ids::BLACK_CANDLE => DyeColor::BLACK(),
			Ids::BLUE_CANDLE => DyeColor::BLUE(),
			Ids::BROWN_CANDLE => DyeColor::BROWN(),
			Ids::CYAN_CANDLE => DyeColor::CYAN(),
			Ids::GRAY_CANDLE => DyeColor::GRAY(),
			Ids::GREEN_CANDLE => DyeColor::GREEN(),
			Ids::LIGHT_BLUE_CANDLE => DyeColor::LIGHT_BLUE(),
			Ids::LIGHT_GRAY_CANDLE => DyeColor::LIGHT_GRAY(),
			Ids::LIME_CANDLE => DyeColor::LIME(),
			Ids::MAGENTA_CANDLE => DyeColor::MAGENTA(),
			Ids::ORANGE_CANDLE => DyeColor::ORANGE(),
			Ids::PINK_CANDLE => DyeColor::PINK(),
			Ids::PURPLE_CANDLE => DyeColor::PURPLE(),
			Ids::RED_CANDLE => DyeColor::RED(),
			Ids::WHITE_CANDLE => DyeColor::WHITE(),
			Ids::YELLOW_CANDLE => DyeColor::YELLOW(),
		] as $id => $color){
			$this->map($id, fn(Reader $in) => Helper::decodeCandle(Blocks::DYED_CANDLE()->setColor($color), $in));
		}

		$this->map(Ids::CANDLE_CAKE, fn(Reader $in) => Blocks::CAKE_WITH_CANDLE()->setLit($in->readBool(StateNames::LIT)));
		foreach([
			Ids::BLACK_CANDLE_CAKE => DyeColor::BLACK(),
			Ids::BLUE_CANDLE_CAKE => DyeColor::BLUE(),
			Ids::BROWN_CANDLE_CAKE => DyeColor::BROWN(),
			Ids::CYAN_CANDLE_CAKE => DyeColor::CYAN(),
			Ids::GRAY_CANDLE_CAKE => DyeColor::GRAY(),
			Ids::GREEN_CANDLE_CAKE => DyeColor::GREEN(),
			Ids::LIGHT_BLUE_CANDLE_CAKE => DyeColor::LIGHT_BLUE(),
			Ids::LIGHT_GRAY_CANDLE_CAKE => DyeColor::LIGHT_GRAY(),
			Ids::LIME_CANDLE_CAKE => DyeColor::LIME(),
			Ids::MAGENTA_CANDLE_CAKE => DyeColor::MAGENTA(),
			Ids::ORANGE_CANDLE_CAKE => DyeColor::ORANGE(),
			Ids::PINK_CANDLE_CAKE => DyeColor::PINK(),
			Ids::PURPLE_CANDLE_CAKE => DyeColor::PURPLE(),
			Ids::RED_CANDLE_CAKE => DyeColor::RED(),
			Ids::WHITE_CANDLE_CAKE => DyeColor::WHITE(),
			Ids::YELLOW_CANDLE_CAKE => DyeColor::YELLOW(),
		] as $id => $color){
			$this->map($id, fn(Reader $in) => Blocks::CAKE_WITH_DYED_CANDLE()
				->setColor($color)
				->setLit($in->readBool(StateNames::LIT))
			);
		}
	}

	private function registerFlatColorBlockDeserializers() : void{
		foreach([
			Ids::BLACK_GLAZED_TERRACOTTA => DyeColor::BLACK(),
			Ids::BLUE_GLAZED_TERRACOTTA => DyeColor::BLUE(),
			Ids::BROWN_GLAZED_TERRACOTTA => DyeColor::BROWN(),
			Ids::CYAN_GLAZED_TERRACOTTA => DyeColor::CYAN(),
			Ids::GRAY_GLAZED_TERRACOTTA => DyeColor::GRAY(),
			Ids::GREEN_GLAZED_TERRACOTTA => DyeColor::GREEN(),
			Ids::LIGHT_BLUE_GLAZED_TERRACOTTA => DyeColor::LIGHT_BLUE(),
			Ids::SILVER_GLAZED_TERRACOTTA => DyeColor::LIGHT_GRAY(),
			Ids::LIME_GLAZED_TERRACOTTA => DyeColor::LIME(),
			Ids::MAGENTA_GLAZED_TERRACOTTA => DyeColor::MAGENTA(),
			Ids::ORANGE_GLAZED_TERRACOTTA => DyeColor::ORANGE(),
			Ids::PINK_GLAZED_TERRACOTTA => DyeColor::PINK(),
			Ids::PURPLE_GLAZED_TERRACOTTA => DyeColor::PURPLE(),
			Ids::RED_GLAZED_TERRACOTTA => DyeColor::RED(),
			Ids::WHITE_GLAZED_TERRACOTTA => DyeColor::WHITE(),
			Ids::YELLOW_GLAZED_TERRACOTTA => DyeColor::YELLOW(),
		] as $id => $color){
			$this->map($id, fn(Reader $in) => Blocks::GLAZED_TERRACOTTA()
				->setColor($color)
				->setFacing($in->readHorizontalFacing())
			);
		}

		foreach([
			Ids::BLACK_WOOL => DyeColor::BLACK(),
			Ids::BLUE_WOOL => DyeColor::BLUE(),
			Ids::BROWN_WOOL => DyeColor::BROWN(),
			Ids::CYAN_WOOL => DyeColor::CYAN(),
			Ids::GRAY_WOOL => DyeColor::GRAY(),
			Ids::GREEN_WOOL => DyeColor::GREEN(),
			Ids::LIGHT_BLUE_WOOL => DyeColor::LIGHT_BLUE(),
			Ids::LIGHT_GRAY_WOOL => DyeColor::LIGHT_GRAY(),
			Ids::LIME_WOOL => DyeColor::LIME(),
			Ids::MAGENTA_WOOL => DyeColor::MAGENTA(),
			Ids::ORANGE_WOOL => DyeColor::ORANGE(),
			Ids::PINK_WOOL => DyeColor::PINK(),
			Ids::PURPLE_WOOL => DyeColor::PURPLE(),
			Ids::RED_WOOL => DyeColor::RED(),
			Ids::WHITE_WOOL => DyeColor::WHITE(),
			Ids::YELLOW_WOOL => DyeColor::YELLOW(),
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::WOOL()->setColor($color));
		}

		foreach([
			Ids::BLACK_CARPET => DyeColor::BLACK(),
			Ids::BLUE_CARPET => DyeColor::BLUE(),
			Ids::BROWN_CARPET => DyeColor::BROWN(),
			Ids::CYAN_CARPET => DyeColor::CYAN(),
			Ids::GRAY_CARPET => DyeColor::GRAY(),
			Ids::GREEN_CARPET => DyeColor::GREEN(),
			Ids::LIGHT_BLUE_CARPET => DyeColor::LIGHT_BLUE(),
			Ids::LIGHT_GRAY_CARPET => DyeColor::LIGHT_GRAY(),
			Ids::LIME_CARPET => DyeColor::LIME(),
			Ids::MAGENTA_CARPET => DyeColor::MAGENTA(),
			Ids::ORANGE_CARPET => DyeColor::ORANGE(),
			Ids::PINK_CARPET => DyeColor::PINK(),
			Ids::PURPLE_CARPET => DyeColor::PURPLE(),
			Ids::RED_CARPET => DyeColor::RED(),
			Ids::WHITE_CARPET => DyeColor::WHITE(),
			Ids::YELLOW_CARPET => DyeColor::YELLOW(),
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::CARPET()->setColor($color));
		}
	}

	private function registerFlatCoralDeserializers() : void{
		foreach([
			Ids::BRAIN_CORAL => CoralType::BRAIN(),
			Ids::BUBBLE_CORAL => CoralType::BUBBLE(),
			Ids::FIRE_CORAL => CoralType::FIRE(),
			Ids::HORN_CORAL => CoralType::HORN(),
			Ids::TUBE_CORAL => CoralType::TUBE(),
		] as $id => $coralType){
			$this->mapSimple($id, fn() => Blocks::CORAL()->setCoralType($coralType)->setDead(false));
		}
		foreach([
			Ids::DEAD_BRAIN_CORAL => CoralType::BRAIN(),
			Ids::DEAD_BUBBLE_CORAL => CoralType::BUBBLE(),
			Ids::DEAD_FIRE_CORAL => CoralType::FIRE(),
			Ids::DEAD_HORN_CORAL => CoralType::HORN(),
			Ids::DEAD_TUBE_CORAL => CoralType::TUBE(),
		] as $id => $coralType){
			$this->mapSimple($id, fn() => Blocks::CORAL()->setCoralType($coralType)->setDead(true));
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

	private function registerWoodBlockDeserializers() : void{
		$this->mapSimple(Ids::ACACIA_FENCE, fn() => Blocks::ACACIA_FENCE());
		$this->mapSimple(Ids::BIRCH_FENCE, fn() => Blocks::BIRCH_FENCE());
		$this->mapSimple(Ids::DARK_OAK_FENCE, fn() => Blocks::DARK_OAK_FENCE());
		$this->mapSimple(Ids::JUNGLE_FENCE, fn() => Blocks::JUNGLE_FENCE());
		$this->mapSimple(Ids::OAK_FENCE, fn() => Blocks::OAK_FENCE());
		$this->mapSimple(Ids::SPRUCE_FENCE, fn() => Blocks::SPRUCE_FENCE());

		$this->mapLog(Ids::ACACIA_LOG, Ids::STRIPPED_ACACIA_LOG, fn() => Blocks::ACACIA_LOG());
		$this->mapLog(Ids::BIRCH_LOG, Ids::STRIPPED_BIRCH_LOG, fn() => Blocks::BIRCH_LOG());
		$this->mapLog(Ids::DARK_OAK_LOG, Ids::STRIPPED_DARK_OAK_LOG, fn() => Blocks::DARK_OAK_LOG());
		$this->mapLog(Ids::JUNGLE_LOG, Ids::STRIPPED_JUNGLE_LOG, fn() => Blocks::JUNGLE_LOG());
		$this->mapLog(Ids::OAK_LOG, Ids::STRIPPED_OAK_LOG, fn() => Blocks::OAK_LOG());
		$this->mapLog(Ids::SPRUCE_LOG, Ids::STRIPPED_SPRUCE_LOG, fn() => Blocks::SPRUCE_LOG());
	}

	private function registerSimpleDeserializers() : void{
		$this->mapSimple(Ids::AIR, fn() => Blocks::AIR());
		$this->mapSimple(Ids::AMETHYST_BLOCK, fn() => Blocks::AMETHYST());
		$this->mapSimple(Ids::ANCIENT_DEBRIS, fn() => Blocks::ANCIENT_DEBRIS());
		$this->mapSimple(Ids::BARRIER, fn() => Blocks::BARRIER());
		$this->mapSimple(Ids::BEACON, fn() => Blocks::BEACON());
		$this->mapSimple(Ids::BLACKSTONE, fn() => Blocks::BLACKSTONE());
		$this->mapSimple(Ids::BLUE_ICE, fn() => Blocks::BLUE_ICE());
		$this->mapSimple(Ids::BOOKSHELF, fn() => Blocks::BOOKSHELF());
		$this->mapSimple(Ids::BRICK_BLOCK, fn() => Blocks::BRICKS());
		$this->mapSimple(Ids::BROWN_MUSHROOM, fn() => Blocks::BROWN_MUSHROOM());
		$this->mapSimple(Ids::CALCITE, fn() => Blocks::CALCITE());
		$this->mapSimple(Ids::CARTOGRAPHY_TABLE, fn() => Blocks::CARTOGRAPHY_TABLE());
		$this->mapSimple(Ids::CHEMICAL_HEAT, fn() => Blocks::CHEMICAL_HEAT());
		$this->mapSimple(Ids::CHISELED_DEEPSLATE, fn() => Blocks::CHISELED_DEEPSLATE());
		$this->mapSimple(Ids::CHISELED_NETHER_BRICKS, fn() => Blocks::CHISELED_NETHER_BRICKS());
		$this->mapSimple(Ids::CHISELED_POLISHED_BLACKSTONE, fn() => Blocks::CHISELED_POLISHED_BLACKSTONE());
		$this->mapSimple(Ids::CHORUS_PLANT, fn() => Blocks::CHORUS_PLANT());
		$this->mapSimple(Ids::CLAY, fn() => Blocks::CLAY());
		$this->mapSimple(Ids::COAL_BLOCK, fn() => Blocks::COAL());
		$this->mapSimple(Ids::COAL_ORE, fn() => Blocks::COAL_ORE());
		$this->mapSimple(Ids::COBBLED_DEEPSLATE, fn() => Blocks::COBBLED_DEEPSLATE());
		$this->mapSimple(Ids::COBBLESTONE, fn() => Blocks::COBBLESTONE());
		$this->mapSimple(Ids::COPPER_ORE, fn() => Blocks::COPPER_ORE());
		$this->mapSimple(Ids::CRACKED_DEEPSLATE_BRICKS, fn() => Blocks::CRACKED_DEEPSLATE_BRICKS());
		$this->mapSimple(Ids::CRACKED_DEEPSLATE_TILES, fn() => Blocks::CRACKED_DEEPSLATE_TILES());
		$this->mapSimple(Ids::CRACKED_NETHER_BRICKS, fn() => Blocks::CRACKED_NETHER_BRICKS());
		$this->mapSimple(Ids::CRACKED_POLISHED_BLACKSTONE_BRICKS, fn() => Blocks::CRACKED_POLISHED_BLACKSTONE_BRICKS());
		$this->mapSimple(Ids::CRAFTING_TABLE, fn() => Blocks::CRAFTING_TABLE());
		$this->mapSimple(Ids::CRIMSON_FENCE, fn() => Blocks::CRIMSON_FENCE());
		$this->mapSimple(Ids::CRIMSON_PLANKS, fn() => Blocks::CRIMSON_PLANKS());
		$this->mapSimple(Ids::CRYING_OBSIDIAN, fn() => Blocks::CRYING_OBSIDIAN());
		$this->mapSimple(Ids::DEADBUSH, fn() => Blocks::DEAD_BUSH());
		$this->mapSimple(Ids::DEEPSLATE_BRICKS, fn() => Blocks::DEEPSLATE_BRICKS());
		$this->mapSimple(Ids::DEEPSLATE_COAL_ORE, fn() => Blocks::DEEPSLATE_COAL_ORE());
		$this->mapSimple(Ids::DEEPSLATE_COPPER_ORE, fn() => Blocks::DEEPSLATE_COPPER_ORE());
		$this->mapSimple(Ids::DEEPSLATE_DIAMOND_ORE, fn() => Blocks::DEEPSLATE_DIAMOND_ORE());
		$this->mapSimple(Ids::DEEPSLATE_EMERALD_ORE, fn() => Blocks::DEEPSLATE_EMERALD_ORE());
		$this->mapSimple(Ids::DEEPSLATE_GOLD_ORE, fn() => Blocks::DEEPSLATE_GOLD_ORE());
		$this->mapSimple(Ids::DEEPSLATE_IRON_ORE, fn() => Blocks::DEEPSLATE_IRON_ORE());
		$this->mapSimple(Ids::DEEPSLATE_LAPIS_ORE, fn() => Blocks::DEEPSLATE_LAPIS_LAZULI_ORE());
		$this->mapSimple(Ids::DEEPSLATE_TILES, fn() => Blocks::DEEPSLATE_TILES());
		$this->mapSimple(Ids::DIAMOND_BLOCK, fn() => Blocks::DIAMOND());
		$this->mapSimple(Ids::DIAMOND_ORE, fn() => Blocks::DIAMOND_ORE());
		$this->mapSimple(Ids::DRAGON_EGG, fn() => Blocks::DRAGON_EGG());
		$this->mapSimple(Ids::DRIED_KELP_BLOCK, fn() => Blocks::DRIED_KELP());
		$this->mapSimple(Ids::ELEMENT_0, fn() => Blocks::ELEMENT_ZERO());
		$this->mapSimple(Ids::ELEMENT_1, fn() => Blocks::ELEMENT_HYDROGEN());
		$this->mapSimple(Ids::ELEMENT_10, fn() => Blocks::ELEMENT_NEON());
		$this->mapSimple(Ids::ELEMENT_100, fn() => Blocks::ELEMENT_FERMIUM());
		$this->mapSimple(Ids::ELEMENT_101, fn() => Blocks::ELEMENT_MENDELEVIUM());
		$this->mapSimple(Ids::ELEMENT_102, fn() => Blocks::ELEMENT_NOBELIUM());
		$this->mapSimple(Ids::ELEMENT_103, fn() => Blocks::ELEMENT_LAWRENCIUM());
		$this->mapSimple(Ids::ELEMENT_104, fn() => Blocks::ELEMENT_RUTHERFORDIUM());
		$this->mapSimple(Ids::ELEMENT_105, fn() => Blocks::ELEMENT_DUBNIUM());
		$this->mapSimple(Ids::ELEMENT_106, fn() => Blocks::ELEMENT_SEABORGIUM());
		$this->mapSimple(Ids::ELEMENT_107, fn() => Blocks::ELEMENT_BOHRIUM());
		$this->mapSimple(Ids::ELEMENT_108, fn() => Blocks::ELEMENT_HASSIUM());
		$this->mapSimple(Ids::ELEMENT_109, fn() => Blocks::ELEMENT_MEITNERIUM());
		$this->mapSimple(Ids::ELEMENT_11, fn() => Blocks::ELEMENT_SODIUM());
		$this->mapSimple(Ids::ELEMENT_110, fn() => Blocks::ELEMENT_DARMSTADTIUM());
		$this->mapSimple(Ids::ELEMENT_111, fn() => Blocks::ELEMENT_ROENTGENIUM());
		$this->mapSimple(Ids::ELEMENT_112, fn() => Blocks::ELEMENT_COPERNICIUM());
		$this->mapSimple(Ids::ELEMENT_113, fn() => Blocks::ELEMENT_NIHONIUM());
		$this->mapSimple(Ids::ELEMENT_114, fn() => Blocks::ELEMENT_FLEROVIUM());
		$this->mapSimple(Ids::ELEMENT_115, fn() => Blocks::ELEMENT_MOSCOVIUM());
		$this->mapSimple(Ids::ELEMENT_116, fn() => Blocks::ELEMENT_LIVERMORIUM());
		$this->mapSimple(Ids::ELEMENT_117, fn() => Blocks::ELEMENT_TENNESSINE());
		$this->mapSimple(Ids::ELEMENT_118, fn() => Blocks::ELEMENT_OGANESSON());
		$this->mapSimple(Ids::ELEMENT_12, fn() => Blocks::ELEMENT_MAGNESIUM());
		$this->mapSimple(Ids::ELEMENT_13, fn() => Blocks::ELEMENT_ALUMINUM());
		$this->mapSimple(Ids::ELEMENT_14, fn() => Blocks::ELEMENT_SILICON());
		$this->mapSimple(Ids::ELEMENT_15, fn() => Blocks::ELEMENT_PHOSPHORUS());
		$this->mapSimple(Ids::ELEMENT_16, fn() => Blocks::ELEMENT_SULFUR());
		$this->mapSimple(Ids::ELEMENT_17, fn() => Blocks::ELEMENT_CHLORINE());
		$this->mapSimple(Ids::ELEMENT_18, fn() => Blocks::ELEMENT_ARGON());
		$this->mapSimple(Ids::ELEMENT_19, fn() => Blocks::ELEMENT_POTASSIUM());
		$this->mapSimple(Ids::ELEMENT_2, fn() => Blocks::ELEMENT_HELIUM());
		$this->mapSimple(Ids::ELEMENT_20, fn() => Blocks::ELEMENT_CALCIUM());
		$this->mapSimple(Ids::ELEMENT_21, fn() => Blocks::ELEMENT_SCANDIUM());
		$this->mapSimple(Ids::ELEMENT_22, fn() => Blocks::ELEMENT_TITANIUM());
		$this->mapSimple(Ids::ELEMENT_23, fn() => Blocks::ELEMENT_VANADIUM());
		$this->mapSimple(Ids::ELEMENT_24, fn() => Blocks::ELEMENT_CHROMIUM());
		$this->mapSimple(Ids::ELEMENT_25, fn() => Blocks::ELEMENT_MANGANESE());
		$this->mapSimple(Ids::ELEMENT_26, fn() => Blocks::ELEMENT_IRON());
		$this->mapSimple(Ids::ELEMENT_27, fn() => Blocks::ELEMENT_COBALT());
		$this->mapSimple(Ids::ELEMENT_28, fn() => Blocks::ELEMENT_NICKEL());
		$this->mapSimple(Ids::ELEMENT_29, fn() => Blocks::ELEMENT_COPPER());
		$this->mapSimple(Ids::ELEMENT_3, fn() => Blocks::ELEMENT_LITHIUM());
		$this->mapSimple(Ids::ELEMENT_30, fn() => Blocks::ELEMENT_ZINC());
		$this->mapSimple(Ids::ELEMENT_31, fn() => Blocks::ELEMENT_GALLIUM());
		$this->mapSimple(Ids::ELEMENT_32, fn() => Blocks::ELEMENT_GERMANIUM());
		$this->mapSimple(Ids::ELEMENT_33, fn() => Blocks::ELEMENT_ARSENIC());
		$this->mapSimple(Ids::ELEMENT_34, fn() => Blocks::ELEMENT_SELENIUM());
		$this->mapSimple(Ids::ELEMENT_35, fn() => Blocks::ELEMENT_BROMINE());
		$this->mapSimple(Ids::ELEMENT_36, fn() => Blocks::ELEMENT_KRYPTON());
		$this->mapSimple(Ids::ELEMENT_37, fn() => Blocks::ELEMENT_RUBIDIUM());
		$this->mapSimple(Ids::ELEMENT_38, fn() => Blocks::ELEMENT_STRONTIUM());
		$this->mapSimple(Ids::ELEMENT_39, fn() => Blocks::ELEMENT_YTTRIUM());
		$this->mapSimple(Ids::ELEMENT_4, fn() => Blocks::ELEMENT_BERYLLIUM());
		$this->mapSimple(Ids::ELEMENT_40, fn() => Blocks::ELEMENT_ZIRCONIUM());
		$this->mapSimple(Ids::ELEMENT_41, fn() => Blocks::ELEMENT_NIOBIUM());
		$this->mapSimple(Ids::ELEMENT_42, fn() => Blocks::ELEMENT_MOLYBDENUM());
		$this->mapSimple(Ids::ELEMENT_43, fn() => Blocks::ELEMENT_TECHNETIUM());
		$this->mapSimple(Ids::ELEMENT_44, fn() => Blocks::ELEMENT_RUTHENIUM());
		$this->mapSimple(Ids::ELEMENT_45, fn() => Blocks::ELEMENT_RHODIUM());
		$this->mapSimple(Ids::ELEMENT_46, fn() => Blocks::ELEMENT_PALLADIUM());
		$this->mapSimple(Ids::ELEMENT_47, fn() => Blocks::ELEMENT_SILVER());
		$this->mapSimple(Ids::ELEMENT_48, fn() => Blocks::ELEMENT_CADMIUM());
		$this->mapSimple(Ids::ELEMENT_49, fn() => Blocks::ELEMENT_INDIUM());
		$this->mapSimple(Ids::ELEMENT_5, fn() => Blocks::ELEMENT_BORON());
		$this->mapSimple(Ids::ELEMENT_50, fn() => Blocks::ELEMENT_TIN());
		$this->mapSimple(Ids::ELEMENT_51, fn() => Blocks::ELEMENT_ANTIMONY());
		$this->mapSimple(Ids::ELEMENT_52, fn() => Blocks::ELEMENT_TELLURIUM());
		$this->mapSimple(Ids::ELEMENT_53, fn() => Blocks::ELEMENT_IODINE());
		$this->mapSimple(Ids::ELEMENT_54, fn() => Blocks::ELEMENT_XENON());
		$this->mapSimple(Ids::ELEMENT_55, fn() => Blocks::ELEMENT_CESIUM());
		$this->mapSimple(Ids::ELEMENT_56, fn() => Blocks::ELEMENT_BARIUM());
		$this->mapSimple(Ids::ELEMENT_57, fn() => Blocks::ELEMENT_LANTHANUM());
		$this->mapSimple(Ids::ELEMENT_58, fn() => Blocks::ELEMENT_CERIUM());
		$this->mapSimple(Ids::ELEMENT_59, fn() => Blocks::ELEMENT_PRASEODYMIUM());
		$this->mapSimple(Ids::ELEMENT_6, fn() => Blocks::ELEMENT_CARBON());
		$this->mapSimple(Ids::ELEMENT_60, fn() => Blocks::ELEMENT_NEODYMIUM());
		$this->mapSimple(Ids::ELEMENT_61, fn() => Blocks::ELEMENT_PROMETHIUM());
		$this->mapSimple(Ids::ELEMENT_62, fn() => Blocks::ELEMENT_SAMARIUM());
		$this->mapSimple(Ids::ELEMENT_63, fn() => Blocks::ELEMENT_EUROPIUM());
		$this->mapSimple(Ids::ELEMENT_64, fn() => Blocks::ELEMENT_GADOLINIUM());
		$this->mapSimple(Ids::ELEMENT_65, fn() => Blocks::ELEMENT_TERBIUM());
		$this->mapSimple(Ids::ELEMENT_66, fn() => Blocks::ELEMENT_DYSPROSIUM());
		$this->mapSimple(Ids::ELEMENT_67, fn() => Blocks::ELEMENT_HOLMIUM());
		$this->mapSimple(Ids::ELEMENT_68, fn() => Blocks::ELEMENT_ERBIUM());
		$this->mapSimple(Ids::ELEMENT_69, fn() => Blocks::ELEMENT_THULIUM());
		$this->mapSimple(Ids::ELEMENT_7, fn() => Blocks::ELEMENT_NITROGEN());
		$this->mapSimple(Ids::ELEMENT_70, fn() => Blocks::ELEMENT_YTTERBIUM());
		$this->mapSimple(Ids::ELEMENT_71, fn() => Blocks::ELEMENT_LUTETIUM());
		$this->mapSimple(Ids::ELEMENT_72, fn() => Blocks::ELEMENT_HAFNIUM());
		$this->mapSimple(Ids::ELEMENT_73, fn() => Blocks::ELEMENT_TANTALUM());
		$this->mapSimple(Ids::ELEMENT_74, fn() => Blocks::ELEMENT_TUNGSTEN());
		$this->mapSimple(Ids::ELEMENT_75, fn() => Blocks::ELEMENT_RHENIUM());
		$this->mapSimple(Ids::ELEMENT_76, fn() => Blocks::ELEMENT_OSMIUM());
		$this->mapSimple(Ids::ELEMENT_77, fn() => Blocks::ELEMENT_IRIDIUM());
		$this->mapSimple(Ids::ELEMENT_78, fn() => Blocks::ELEMENT_PLATINUM());
		$this->mapSimple(Ids::ELEMENT_79, fn() => Blocks::ELEMENT_GOLD());
		$this->mapSimple(Ids::ELEMENT_8, fn() => Blocks::ELEMENT_OXYGEN());
		$this->mapSimple(Ids::ELEMENT_80, fn() => Blocks::ELEMENT_MERCURY());
		$this->mapSimple(Ids::ELEMENT_81, fn() => Blocks::ELEMENT_THALLIUM());
		$this->mapSimple(Ids::ELEMENT_82, fn() => Blocks::ELEMENT_LEAD());
		$this->mapSimple(Ids::ELEMENT_83, fn() => Blocks::ELEMENT_BISMUTH());
		$this->mapSimple(Ids::ELEMENT_84, fn() => Blocks::ELEMENT_POLONIUM());
		$this->mapSimple(Ids::ELEMENT_85, fn() => Blocks::ELEMENT_ASTATINE());
		$this->mapSimple(Ids::ELEMENT_86, fn() => Blocks::ELEMENT_RADON());
		$this->mapSimple(Ids::ELEMENT_87, fn() => Blocks::ELEMENT_FRANCIUM());
		$this->mapSimple(Ids::ELEMENT_88, fn() => Blocks::ELEMENT_RADIUM());
		$this->mapSimple(Ids::ELEMENT_89, fn() => Blocks::ELEMENT_ACTINIUM());
		$this->mapSimple(Ids::ELEMENT_9, fn() => Blocks::ELEMENT_FLUORINE());
		$this->mapSimple(Ids::ELEMENT_90, fn() => Blocks::ELEMENT_THORIUM());
		$this->mapSimple(Ids::ELEMENT_91, fn() => Blocks::ELEMENT_PROTACTINIUM());
		$this->mapSimple(Ids::ELEMENT_92, fn() => Blocks::ELEMENT_URANIUM());
		$this->mapSimple(Ids::ELEMENT_93, fn() => Blocks::ELEMENT_NEPTUNIUM());
		$this->mapSimple(Ids::ELEMENT_94, fn() => Blocks::ELEMENT_PLUTONIUM());
		$this->mapSimple(Ids::ELEMENT_95, fn() => Blocks::ELEMENT_AMERICIUM());
		$this->mapSimple(Ids::ELEMENT_96, fn() => Blocks::ELEMENT_CURIUM());
		$this->mapSimple(Ids::ELEMENT_97, fn() => Blocks::ELEMENT_BERKELIUM());
		$this->mapSimple(Ids::ELEMENT_98, fn() => Blocks::ELEMENT_CALIFORNIUM());
		$this->mapSimple(Ids::ELEMENT_99, fn() => Blocks::ELEMENT_EINSTEINIUM());
		$this->mapSimple(Ids::EMERALD_BLOCK, fn() => Blocks::EMERALD());
		$this->mapSimple(Ids::EMERALD_ORE, fn() => Blocks::EMERALD_ORE());
		$this->mapSimple(Ids::ENCHANTING_TABLE, fn() => Blocks::ENCHANTING_TABLE());
		$this->mapSimple(Ids::END_BRICKS, fn() => Blocks::END_STONE_BRICKS());
		$this->mapSimple(Ids::END_STONE, fn() => Blocks::END_STONE());
		$this->mapSimple(Ids::FLETCHING_TABLE, fn() => Blocks::FLETCHING_TABLE());
		$this->mapSimple(Ids::GILDED_BLACKSTONE, fn() => Blocks::GILDED_BLACKSTONE());
		$this->mapSimple(Ids::GLASS, fn() => Blocks::GLASS());
		$this->mapSimple(Ids::GLASS_PANE, fn() => Blocks::GLASS_PANE());
		$this->mapSimple(Ids::GLOWINGOBSIDIAN, fn() => Blocks::GLOWING_OBSIDIAN());
		$this->mapSimple(Ids::GLOWSTONE, fn() => Blocks::GLOWSTONE());
		$this->mapSimple(Ids::GOLD_BLOCK, fn() => Blocks::GOLD());
		$this->mapSimple(Ids::GOLD_ORE, fn() => Blocks::GOLD_ORE());
		$this->mapSimple(Ids::GRASS, fn() => Blocks::GRASS());
		$this->mapSimple(Ids::GRASS_PATH, fn() => Blocks::GRASS_PATH());
		$this->mapSimple(Ids::GRAVEL, fn() => Blocks::GRAVEL());
		$this->mapSimple(Ids::HANGING_ROOTS, fn() => Blocks::HANGING_ROOTS());
		$this->mapSimple(Ids::HARD_GLASS, fn() => Blocks::HARDENED_GLASS());
		$this->mapSimple(Ids::HARD_GLASS_PANE, fn() => Blocks::HARDENED_GLASS_PANE());
		$this->mapSimple(Ids::HARDENED_CLAY, fn() => Blocks::HARDENED_CLAY());
		$this->mapSimple(Ids::HONEYCOMB_BLOCK, fn() => Blocks::HONEYCOMB());
		$this->mapSimple(Ids::ICE, fn() => Blocks::ICE());
		$this->mapSimple(Ids::INFO_UPDATE, fn() => Blocks::INFO_UPDATE());
		$this->mapSimple(Ids::INFO_UPDATE2, fn() => Blocks::INFO_UPDATE2());
		$this->mapSimple(Ids::INVISIBLE_BEDROCK, fn() => Blocks::INVISIBLE_BEDROCK());
		$this->mapSimple(Ids::IRON_BARS, fn() => Blocks::IRON_BARS());
		$this->mapSimple(Ids::IRON_BLOCK, fn() => Blocks::IRON());
		$this->mapSimple(Ids::IRON_ORE, fn() => Blocks::IRON_ORE());
		$this->mapSimple(Ids::JUKEBOX, fn() => Blocks::JUKEBOX());
		$this->mapSimple(Ids::LAPIS_BLOCK, fn() => Blocks::LAPIS_LAZULI());
		$this->mapSimple(Ids::LAPIS_ORE, fn() => Blocks::LAPIS_LAZULI_ORE());
		$this->mapSimple(Ids::MAGMA, fn() => Blocks::MAGMA());
		$this->mapSimple(Ids::MANGROVE_FENCE, fn() => Blocks::MANGROVE_FENCE());
		$this->mapSimple(Ids::MANGROVE_PLANKS, fn() => Blocks::MANGROVE_PLANKS());
		$this->mapSimple(Ids::MANGROVE_ROOTS, fn() => Blocks::MANGROVE_ROOTS());
		$this->mapSimple(Ids::MELON_BLOCK, fn() => Blocks::MELON());
		$this->mapSimple(Ids::MOB_SPAWNER, fn() => Blocks::MONSTER_SPAWNER());
		$this->mapSimple(Ids::MOSSY_COBBLESTONE, fn() => Blocks::MOSSY_COBBLESTONE());
		$this->mapSimple(Ids::MUD, fn() => Blocks::MUD());
		$this->mapSimple(Ids::MUD_BRICKS, fn() => Blocks::MUD_BRICKS());
		$this->mapSimple(Ids::MYCELIUM, fn() => Blocks::MYCELIUM());
		$this->mapSimple(Ids::NETHER_BRICK, fn() => Blocks::NETHER_BRICKS());
		$this->mapSimple(Ids::NETHER_BRICK_FENCE, fn() => Blocks::NETHER_BRICK_FENCE());
		$this->mapSimple(Ids::NETHER_GOLD_ORE, fn() => Blocks::NETHER_GOLD_ORE());
		$this->mapSimple(Ids::NETHER_WART_BLOCK, fn() => Blocks::NETHER_WART_BLOCK());
		$this->mapSimple(Ids::NETHERITE_BLOCK, fn() => Blocks::NETHERITE());
		$this->mapSimple(Ids::NETHERRACK, fn() => Blocks::NETHERRACK());
		$this->mapSimple(Ids::NETHERREACTOR, fn() => Blocks::NETHER_REACTOR_CORE());
		$this->mapSimple(Ids::NOTEBLOCK, fn() => Blocks::NOTE_BLOCK());
		$this->mapSimple(Ids::OBSIDIAN, fn() => Blocks::OBSIDIAN());
		$this->mapSimple(Ids::PACKED_ICE, fn() => Blocks::PACKED_ICE());
		$this->mapSimple(Ids::PACKED_MUD, fn() => Blocks::PACKED_MUD());
		$this->mapSimple(Ids::PODZOL, fn() => Blocks::PODZOL());
		$this->mapSimple(Ids::POLISHED_BLACKSTONE, fn() => Blocks::POLISHED_BLACKSTONE());
		$this->mapSimple(Ids::POLISHED_BLACKSTONE_BRICKS, fn() => Blocks::POLISHED_BLACKSTONE_BRICKS());
		$this->mapSimple(Ids::POLISHED_DEEPSLATE, fn() => Blocks::POLISHED_DEEPSLATE());
		$this->mapSimple(Ids::QUARTZ_BRICKS, fn() => Blocks::QUARTZ_BRICKS());
		$this->mapSimple(Ids::QUARTZ_ORE, fn() => Blocks::NETHER_QUARTZ_ORE());
		$this->mapSimple(Ids::RAW_COPPER_BLOCK, fn() => Blocks::RAW_COPPER());
		$this->mapSimple(Ids::RAW_GOLD_BLOCK, fn() => Blocks::RAW_GOLD());
		$this->mapSimple(Ids::RAW_IRON_BLOCK, fn() => Blocks::RAW_IRON());
		$this->mapSimple(Ids::RED_MUSHROOM, fn() => Blocks::RED_MUSHROOM());
		$this->mapSimple(Ids::RED_NETHER_BRICK, fn() => Blocks::RED_NETHER_BRICKS());
		$this->mapSimple(Ids::REDSTONE_BLOCK, fn() => Blocks::REDSTONE());
		$this->mapSimple(Ids::REINFORCED_DEEPSLATE, fn() => Blocks::REINFORCED_DEEPSLATE());
		$this->mapSimple(Ids::RESERVED6, fn() => Blocks::RESERVED6());
		$this->mapSimple(Ids::SCULK, fn() => Blocks::SCULK());
		$this->mapSimple(Ids::SEA_LANTERN, fn() => Blocks::SEA_LANTERN());
		$this->mapSimple(Ids::SHROOMLIGHT, fn() => Blocks::SHROOMLIGHT());
		$this->mapSimple(Ids::SLIME, fn() => Blocks::SLIME());
		$this->mapSimple(Ids::SMITHING_TABLE, fn() => Blocks::SMITHING_TABLE());
		$this->mapSimple(Ids::SMOOTH_BASALT, fn() => Blocks::SMOOTH_BASALT());
		$this->mapSimple(Ids::SMOOTH_STONE, fn() => Blocks::SMOOTH_STONE());
		$this->mapSimple(Ids::SNOW, fn() => Blocks::SNOW());
		$this->mapSimple(Ids::SOUL_SAND, fn() => Blocks::SOUL_SAND());
		$this->mapSimple(Ids::SOUL_SOIL, fn() => Blocks::SOUL_SOIL());
		$this->mapSimple(Ids::SPORE_BLOSSOM, fn() => Blocks::SPORE_BLOSSOM());
		$this->mapSimple(Ids::STONECUTTER, fn() => Blocks::LEGACY_STONECUTTER());
		$this->mapSimple(Ids::TINTED_GLASS, fn() => Blocks::TINTED_GLASS());
		$this->mapSimple(Ids::TUFF, fn() => Blocks::TUFF());
		$this->mapSimple(Ids::UNDYED_SHULKER_BOX, fn() => Blocks::SHULKER_BOX());
		$this->mapSimple(Ids::WARPED_FENCE, fn() => Blocks::WARPED_FENCE());
		$this->mapSimple(Ids::WARPED_PLANKS, fn() => Blocks::WARPED_PLANKS());
		$this->mapSimple(Ids::WARPED_WART_BLOCK, fn() => Blocks::WARPED_WART_BLOCK());
		$this->mapSimple(Ids::WATERLILY, fn() => Blocks::LILY_PAD());
		$this->mapSimple(Ids::WEB, fn() => Blocks::COBWEB());
		$this->mapSimple(Ids::WITHER_ROSE, fn() => Blocks::WITHER_ROSE());
		$this->mapSimple(Ids::YELLOW_FLOWER, fn() => Blocks::DANDELION());
	}

	private function registerDeserializers() : void{
		$this->map(Ids::ACACIA_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::ACACIA_BUTTON(), $in));
		$this->map(Ids::ACACIA_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::ACACIA_DOOR(), $in));
		$this->map(Ids::ACACIA_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::ACACIA_FENCE_GATE(), $in));
		$this->map(Ids::ACACIA_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::ACACIA_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::ACACIA_STAIRS, fn() => Blocks::ACACIA_STAIRS());
		$this->map(Ids::ACACIA_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::ACACIA_SIGN(), $in));
		$this->map(Ids::ACACIA_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::ACACIA_TRAPDOOR(), $in));
		$this->map(Ids::ACACIA_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::ACACIA_WALL_SIGN(), $in));
		$this->map(Ids::ACTIVATOR_RAIL, function(Reader $in) : Block{
			return Blocks::ACTIVATOR_RAIL()
				->setPowered($in->readBool(StateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->mapStairs(Ids::ANDESITE_STAIRS, fn() => Blocks::ANDESITE_STAIRS());
		$this->map(Ids::ANVIL, function(Reader $in) : Block{
			return Blocks::ANVIL()
				->setDamage(match($value = $in->readString(StateNames::DAMAGE)){
					StringValues::DAMAGE_UNDAMAGED => 0,
					StringValues::DAMAGE_SLIGHTLY_DAMAGED => 1,
					StringValues::DAMAGE_VERY_DAMAGED => 2,
					StringValues::DAMAGE_BROKEN => 0,
					default => throw $in->badValueException(StateNames::DAMAGE, $value),
				})
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::AZALEA_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::AZALEA_LEAVES(), $in));
		$this->map(Ids::AZALEA_LEAVES_FLOWERED, fn(Reader $in) => Helper::decodeLeaves(Blocks::FLOWERING_AZALEA_LEAVES(), $in));
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
		$this->map(Ids::BAMBOO_SAPLING, function(Reader $in) : Block{
			$in->ignored(StateNames::SAPLING_TYPE); //bug in MCPE
			return Blocks::BAMBOO_SAPLING()->setReady($in->readBool(StateNames::AGE_BIT));
		});
		$this->map(Ids::BARREL, function(Reader $in) : Block{
			return Blocks::BARREL()
				->setFacing($in->readFacingDirection())
				->setOpen($in->readBool(StateNames::OPEN_BIT));
		});
		$this->map(Ids::BASALT, function(Reader $in){
			return Blocks::BASALT()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::BED, function(Reader $in) : Block{
			return Blocks::BED()
				->setFacing($in->readLegacyHorizontalFacing())
				->setHead($in->readBool(StateNames::HEAD_PIECE_BIT))
				->setOccupied($in->readBool(StateNames::OCCUPIED_BIT));
		});
		$this->map(Ids::BEDROCK, function(Reader $in) : Block{
			return Blocks::BEDROCK()
				->setBurnsForever($in->readBool(StateNames::INFINIBURN_BIT));
		});
		$this->map(Ids::BEETROOT, fn(Reader $in) => Helper::decodeCrops(Blocks::BEETROOTS(), $in));
		$this->map(Ids::BELL, function(Reader $in) : Block{
			$in->ignored(StateNames::TOGGLE_BIT); //only useful at runtime
			return Blocks::BELL()
				->setFacing($in->readLegacyHorizontalFacing())
				->setAttachmentType($in->readBellAttachmentType());
		});
		$this->map(Ids::BIRCH_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::BIRCH_BUTTON(), $in));
		$this->map(Ids::BIRCH_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::BIRCH_DOOR(), $in));
		$this->map(Ids::BIRCH_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::BIRCH_FENCE_GATE(), $in));
		$this->map(Ids::BIRCH_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::BIRCH_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::BIRCH_STAIRS, fn() => Blocks::BIRCH_STAIRS());
		$this->map(Ids::BIRCH_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::BIRCH_SIGN(), $in));
		$this->map(Ids::BIRCH_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::BIRCH_TRAPDOOR(), $in));
		$this->map(Ids::BIRCH_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::BIRCH_WALL_SIGN(), $in));
		$this->mapSlab(Ids::BLACKSTONE_SLAB, Ids::BLACKSTONE_DOUBLE_SLAB, fn() => Blocks::BLACKSTONE_SLAB());
		$this->mapStairs(Ids::BLACKSTONE_STAIRS, fn() => Blocks::BLACKSTONE_STAIRS());
		$this->map(Ids::BLACKSTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::BLACKSTONE_WALL(), $in));
		$this->map(Ids::BLAST_FURNACE, function(Reader $in) : Block{
			return Blocks::BLAST_FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::BONE_BLOCK, function(Reader $in) : Block{
			$in->ignored(StateNames::DEPRECATED);
			return Blocks::BONE_BLOCK()->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::BREWING_STAND, function(Reader $in) : Block{
			return Blocks::BREWING_STAND()
				->setSlot(BrewingStandSlot::EAST(), $in->readBool(StateNames::BREWING_STAND_SLOT_A_BIT))
				->setSlot(BrewingStandSlot::SOUTHWEST(), $in->readBool(StateNames::BREWING_STAND_SLOT_B_BIT))
				->setSlot(BrewingStandSlot::NORTHWEST(), $in->readBool(StateNames::BREWING_STAND_SLOT_C_BIT));
		});
		$this->mapStairs(Ids::BRICK_STAIRS, fn() => Blocks::BRICK_STAIRS());
		$this->map(Ids::BROWN_MUSHROOM_BLOCK, fn(Reader $in) => Helper::decodeMushroomBlock(Blocks::BROWN_MUSHROOM_BLOCK(), $in));
		$this->map(Ids::CACTUS, function(Reader $in) : Block{
			return Blocks::CACTUS()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::CAKE, function(Reader $in) : Block{
			return Blocks::CAKE()
				->setBites($in->readBoundedInt(StateNames::BITE_COUNTER, 0, 6));
		});
		$this->map(Ids::CARROTS, fn(Reader $in) => Helper::decodeCrops(Blocks::CARROTS(), $in));
		$this->map(Ids::CARVED_PUMPKIN, function(Reader $in) : Block{
			return Blocks::CARVED_PUMPKIN()
				->setFacing($in->readCardinalHorizontalFacing());
		});
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
		$this->map(Ids::CHAIN, function(Reader $in) : Block{
			return Blocks::CHAIN()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::CHEMISTRY_TABLE, function(Reader $in) : Block{
			return (match($type = $in->readString(StateNames::CHEMISTRY_TABLE_TYPE)){
				StringValues::CHEMISTRY_TABLE_TYPE_COMPOUND_CREATOR => Blocks::COMPOUND_CREATOR(),
				StringValues::CHEMISTRY_TABLE_TYPE_ELEMENT_CONSTRUCTOR => Blocks::ELEMENT_CONSTRUCTOR(),
				StringValues::CHEMISTRY_TABLE_TYPE_LAB_TABLE => Blocks::LAB_TABLE(),
				StringValues::CHEMISTRY_TABLE_TYPE_MATERIAL_REDUCER => Blocks::MATERIAL_REDUCER(),
				default => throw $in->badValueException(StateNames::CHEMISTRY_TABLE_TYPE, $type),
			})->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()));
		});
		$this->map(Ids::CHEST, function(Reader $in) : Block{
			return Blocks::CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::CHORUS_FLOWER, function(Reader $in) : Block{
			return Blocks::CHORUS_FLOWER()
				->setAge($in->readBoundedInt(StateNames::AGE, ChorusFlower::MIN_AGE, ChorusFlower::MAX_AGE));
		});
		$this->mapSlab(Ids::COBBLED_DEEPSLATE_SLAB, Ids::COBBLED_DEEPSLATE_DOUBLE_SLAB, fn() => Blocks::COBBLED_DEEPSLATE_SLAB());
		$this->mapStairs(Ids::COBBLED_DEEPSLATE_STAIRS, fn() => Blocks::COBBLED_DEEPSLATE_STAIRS());
		$this->map(Ids::COBBLED_DEEPSLATE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::COBBLED_DEEPSLATE_WALL(), $in));
		$this->map(Ids::COBBLESTONE_WALL, fn(Reader $in) => Helper::mapLegacyWallType($in));
		$this->map(Ids::COCOA, function(Reader $in) : Block{
			return Blocks::COCOA_POD()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 2))
				->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()));
		});
		$this->map(Ids::COLORED_TORCH_BP, function(Reader $in) : Block{
			return $in->readBool(StateNames::COLOR_BIT) ?
				Blocks::PURPLE_TORCH()->setFacing($in->readTorchFacing()) :
				Blocks::BLUE_TORCH()->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::COLORED_TORCH_RG, function(Reader $in) : Block{
			return $in->readBool(StateNames::COLOR_BIT) ?
				Blocks::GREEN_TORCH()->setFacing($in->readTorchFacing()) :
				Blocks::RED_TORCH()->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::CONCRETE, function(Reader $in) : Block{
			return Blocks::CONCRETE()
				->setColor($in->readColor());
		});
		$this->map(Ids::CONCRETE_POWDER, function(Reader $in) : Block{
			return Blocks::CONCRETE_POWDER()
				->setColor($in->readColor());
		});
		$this->map(Ids::COPPER_BLOCK, fn() => Helper::decodeCopper(Blocks::COPPER(), CopperOxidation::NONE()));
		$this->map(Ids::CUT_COPPER, fn() => Helper::decodeCopper(Blocks::CUT_COPPER(), CopperOxidation::NONE()));
		$this->mapSlab(Ids::CUT_COPPER_SLAB, Ids::DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::NONE()));
		$this->mapStairs(Ids::CUT_COPPER_STAIRS, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::NONE()));
		$this->map(Ids::CORAL_BLOCK, function(Reader $in) : Block{
			return Blocks::CORAL_BLOCK()
				->setCoralType($in->readCoralType())
				->setDead($in->readBool(StateNames::DEAD_BIT));
		});
		$this->map(Ids::CORAL_FAN, fn(Reader $in) => Helper::decodeFloorCoralFan(Blocks::CORAL_FAN(), $in)
				->setDead(false));
		$this->map(Ids::CORAL_FAN_DEAD, fn(Reader $in) => Helper::decodeFloorCoralFan(Blocks::CORAL_FAN(), $in)
				->setDead(true));
		$this->map(Ids::CORAL_FAN_HANG, fn(Reader $in) => Helper::decodeWallCoralFan(Blocks::WALL_CORAL_FAN(), $in)
				->setCoralType($in->readBool(StateNames::CORAL_HANG_TYPE_BIT) ? CoralType::BRAIN() : CoralType::TUBE()));
		$this->map(Ids::CORAL_FAN_HANG2, fn(Reader $in) => Helper::decodeWallCoralFan(Blocks::WALL_CORAL_FAN(), $in)
				->setCoralType($in->readBool(StateNames::CORAL_HANG_TYPE_BIT) ? CoralType::FIRE() : CoralType::BUBBLE()));
		$this->map(Ids::CORAL_FAN_HANG3, function(Reader $in) : Block{
			$in->ignored(StateNames::CORAL_HANG_TYPE_BIT); //the game always writes this, even though it's not used
			return Helper::decodeWallCoralFan(Blocks::WALL_CORAL_FAN(), $in)
				->setCoralType(CoralType::HORN());
		});
		$this->map(Ids::CRIMSON_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::CRIMSON_BUTTON(), $in));
		$this->map(Ids::CRIMSON_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::CRIMSON_DOOR(), $in));
		$this->mapSlab(Ids::CRIMSON_SLAB, Ids::CRIMSON_DOUBLE_SLAB, fn() => Blocks::CRIMSON_SLAB());
		$this->map(Ids::CRIMSON_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::CRIMSON_FENCE_GATE(), $in));
		$this->map(Ids::CRIMSON_HYPHAE, fn(Reader $in) => Helper::decodeLog(Blocks::CRIMSON_HYPHAE(), false, $in));
		$this->map(Ids::CRIMSON_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::CRIMSON_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::CRIMSON_STAIRS, fn() => Blocks::CRIMSON_STAIRS());
		$this->map(Ids::CRIMSON_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::CRIMSON_SIGN(), $in));
		$this->map(Ids::CRIMSON_STEM, fn(Reader $in) => Helper::decodeLog(Blocks::CRIMSON_STEM(), false, $in));
		$this->map(Ids::CRIMSON_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::CRIMSON_TRAPDOOR(), $in));
		$this->map(Ids::CRIMSON_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::CRIMSON_WALL_SIGN(), $in));
		$this->map(Ids::DARK_OAK_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::DARK_OAK_BUTTON(), $in));
		$this->map(Ids::DARK_OAK_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::DARK_OAK_DOOR(), $in));
		$this->map(Ids::DARK_OAK_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::DARK_OAK_FENCE_GATE(), $in));
		$this->map(Ids::DARK_OAK_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::DARK_OAK_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::DARK_OAK_STAIRS, fn() => Blocks::DARK_OAK_STAIRS());
		$this->map(Ids::DARK_OAK_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::DARK_OAK_TRAPDOOR(), $in));
		$this->mapStairs(Ids::DARK_PRISMARINE_STAIRS, fn() => Blocks::DARK_PRISMARINE_STAIRS());
		$this->map(Ids::DARKOAK_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::DARK_OAK_SIGN(), $in));
		$this->map(Ids::DARKOAK_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::DARK_OAK_WALL_SIGN(), $in));
		$this->map(Ids::DAYLIGHT_DETECTOR, fn(Reader $in) => Helper::decodeDaylightSensor(Blocks::DAYLIGHT_SENSOR(), $in)
				->setInverted(false));
		$this->map(Ids::DAYLIGHT_DETECTOR_INVERTED, fn(Reader $in) => Helper::decodeDaylightSensor(Blocks::DAYLIGHT_SENSOR(), $in)
				->setInverted(true));
		$this->map(Ids::DEEPSLATE, function(Reader $in) : Block{
			return Blocks::DEEPSLATE()
				->setAxis($in->readPillarAxis());
		});
		$this->mapSlab(Ids::DEEPSLATE_BRICK_SLAB, Ids::DEEPSLATE_BRICK_DOUBLE_SLAB, fn() => Blocks::DEEPSLATE_BRICK_SLAB());
		$this->mapStairs(Ids::DEEPSLATE_BRICK_STAIRS, fn() => Blocks::DEEPSLATE_BRICK_STAIRS());
		$this->map(Ids::DEEPSLATE_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::DEEPSLATE_BRICK_WALL(), $in));
		$this->map(Ids::DEEPSLATE_REDSTONE_ORE, fn() => Blocks::DEEPSLATE_REDSTONE_ORE()->setLit(false));
		$this->mapSlab(Ids::DEEPSLATE_TILE_SLAB, Ids::DEEPSLATE_TILE_DOUBLE_SLAB, fn() => Blocks::DEEPSLATE_TILE_SLAB());
		$this->mapStairs(Ids::DEEPSLATE_TILE_STAIRS, fn() => Blocks::DEEPSLATE_TILE_STAIRS());
		$this->map(Ids::DEEPSLATE_TILE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::DEEPSLATE_TILE_WALL(), $in));
		$this->map(Ids::DETECTOR_RAIL, function(Reader $in) : Block{
			return Blocks::DETECTOR_RAIL()
				->setActivated($in->readBool(StateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->mapStairs(Ids::DIORITE_STAIRS, fn() => Blocks::DIORITE_STAIRS());
		$this->map(Ids::DIRT, function(Reader $in) : Block{
			return Blocks::DIRT()
				->setDirtType(match($value = $in->readString(StateNames::DIRT_TYPE)){
					StringValues::DIRT_TYPE_NORMAL => DirtType::NORMAL(),
					StringValues::DIRT_TYPE_COARSE => DirtType::COARSE(),
					default => throw $in->badValueException(StateNames::DIRT_TYPE, $value),
				});
		});
		$this->map(Ids::DIRT_WITH_ROOTS, fn() => Blocks::DIRT()->setDirtType(DirtType::ROOTED()));
		$this->map(Ids::DOUBLE_PLANT, function(Reader $in) : Block{
			return (match($type = $in->readString(StateNames::DOUBLE_PLANT_TYPE)){
				StringValues::DOUBLE_PLANT_TYPE_FERN => Blocks::LARGE_FERN(),
				StringValues::DOUBLE_PLANT_TYPE_GRASS => Blocks::DOUBLE_TALLGRASS(),
				StringValues::DOUBLE_PLANT_TYPE_PAEONIA => Blocks::PEONY(),
				StringValues::DOUBLE_PLANT_TYPE_ROSE => Blocks::ROSE_BUSH(),
				StringValues::DOUBLE_PLANT_TYPE_SUNFLOWER => Blocks::SUNFLOWER(),
				StringValues::DOUBLE_PLANT_TYPE_SYRINGA => Blocks::LILAC(),
				default => throw $in->badValueException(StateNames::DOUBLE_PLANT_TYPE, $type),
			})->setTop($in->readBool(StateNames::UPPER_BLOCK_BIT));
		});
		$this->mapStairs(Ids::END_BRICK_STAIRS, fn() => Blocks::END_STONE_BRICK_STAIRS());
		$this->map(Ids::END_PORTAL_FRAME, function(Reader $in) : Block{
			return Blocks::END_PORTAL_FRAME()
				->setEye($in->readBool(StateNames::END_PORTAL_EYE_BIT))
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::END_ROD, function(Reader $in) : Block{
			return Blocks::END_ROD()
				->setFacing($in->readEndRodFacingDirection());
		});
		$this->map(Ids::ENDER_CHEST, function(Reader $in) : Block{
			return Blocks::ENDER_CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::EXPOSED_COPPER, fn() => Helper::decodeCopper(Blocks::COPPER(), CopperOxidation::EXPOSED()));
		$this->map(Ids::EXPOSED_CUT_COPPER, fn() => Helper::decodeCopper(Blocks::CUT_COPPER(), CopperOxidation::EXPOSED()));
		$this->mapSlab(Ids::EXPOSED_CUT_COPPER_SLAB, Ids::EXPOSED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::EXPOSED()));
		$this->mapStairs(Ids::EXPOSED_CUT_COPPER_STAIRS, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::EXPOSED()));
		$this->map(Ids::FARMLAND, function(Reader $in) : Block{
			return Blocks::FARMLAND()
				->setWetness($in->readBoundedInt(StateNames::MOISTURIZED_AMOUNT, 0, 7));
		});
		$this->map(Ids::FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::OAK_FENCE_GATE(), $in));
		$this->map(Ids::FIRE, function(Reader $in) : Block{
			return Blocks::FIRE()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::FLOWER_POT, function(Reader $in) : Block{
			$in->ignored(StateNames::UPDATE_BIT);
			return Blocks::FLOWER_POT();
		});
		$this->map(Ids::FLOWING_LAVA, fn(Reader $in) => Helper::decodeFlowingLiquid(Blocks::LAVA(), $in));
		$this->map(Ids::FLOWING_WATER, fn(Reader $in) => Helper::decodeFlowingLiquid(Blocks::WATER(), $in));
		$this->map(Ids::FRAME, fn(Reader $in) => Helper::decodeItemFrame(Blocks::ITEM_FRAME(), $in));
		$this->map(Ids::FROSTED_ICE, function(Reader $in) : Block{
			return Blocks::FROSTED_ICE()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 3));
		});
		$this->map(Ids::FURNACE, function(Reader $in) : Block{
			return Blocks::FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::GLOW_FRAME, fn(Reader $in) => Helper::decodeItemFrame(Blocks::GLOWING_ITEM_FRAME(), $in));
		$this->map(Ids::GOLDEN_RAIL, function(Reader $in) : Block{
			return Blocks::POWERED_RAIL()
				->setPowered($in->readBool(StateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->mapStairs(Ids::GRANITE_STAIRS, fn() => Blocks::GRANITE_STAIRS());
		$this->map(Ids::HARD_STAINED_GLASS, function(Reader $in) : Block{
			return Blocks::STAINED_HARDENED_GLASS()
				->setColor($in->readColor());
		});
		$this->map(Ids::HARD_STAINED_GLASS_PANE, function(Reader $in) : Block{
			return Blocks::STAINED_HARDENED_GLASS_PANE()
				->setColor($in->readColor());
		});
		$this->map(Ids::HAY_BLOCK, function(Reader $in) : Block{
			$in->ignored(StateNames::DEPRECATED);
			return Blocks::HAY_BALE()->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeWeightedPressurePlate(Blocks::WEIGHTED_PRESSURE_PLATE_HEAVY(), $in));
		$this->map(Ids::HOPPER, function(Reader $in) : Block{
			return Blocks::HOPPER()
				->setFacing($in->readFacingWithoutUp())
				->setPowered($in->readBool(StateNames::TOGGLE_BIT));
		});
		$this->map(Ids::IRON_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::IRON_DOOR(), $in));
		$this->map(Ids::IRON_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::IRON_TRAPDOOR(), $in));
		$this->map(Ids::JUNGLE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::JUNGLE_BUTTON(), $in));
		$this->map(Ids::JUNGLE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::JUNGLE_DOOR(), $in));
		$this->map(Ids::JUNGLE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::JUNGLE_FENCE_GATE(), $in));
		$this->map(Ids::JUNGLE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::JUNGLE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::JUNGLE_STAIRS, fn() => Blocks::JUNGLE_STAIRS());
		$this->map(Ids::JUNGLE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::JUNGLE_SIGN(), $in));
		$this->map(Ids::JUNGLE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::JUNGLE_TRAPDOOR(), $in));
		$this->map(Ids::JUNGLE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::JUNGLE_WALL_SIGN(), $in));
		$this->map(Ids::LADDER, function(Reader $in) : Block{
			return Blocks::LADDER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::LANTERN, function(Reader $in) : Block{
			return Blocks::LANTERN()
				->setHanging($in->readBool(StateNames::HANGING));
		});
		$this->map(Ids::LAVA, fn(Reader $in) => Helper::decodeStillLiquid(Blocks::LAVA(), $in));
		$this->map(Ids::LEAVES, fn(Reader $in) => Helper::decodeLeaves(match($type = $in->readString(StateNames::OLD_LEAF_TYPE)){
			StringValues::OLD_LEAF_TYPE_BIRCH => Blocks::BIRCH_LEAVES(),
			StringValues::OLD_LEAF_TYPE_JUNGLE => Blocks::JUNGLE_LEAVES(),
			StringValues::OLD_LEAF_TYPE_OAK => Blocks::OAK_LEAVES(),
			StringValues::OLD_LEAF_TYPE_SPRUCE => Blocks::SPRUCE_LEAVES(),
			default => throw $in->badValueException(StateNames::OLD_LEAF_TYPE, $type),
		}, $in));
		$this->map(Ids::LEAVES2, fn(Reader $in) => Helper::decodeLeaves(match($type = $in->readString(StateNames::NEW_LEAF_TYPE)){
			StringValues::NEW_LEAF_TYPE_ACACIA => Blocks::ACACIA_LEAVES(),
			StringValues::NEW_LEAF_TYPE_DARK_OAK => Blocks::DARK_OAK_LEAVES(),
			default => throw $in->badValueException(StateNames::NEW_LEAF_TYPE, $type),
		}, $in));
		$this->map(Ids::LECTERN, function(Reader $in) : Block{
			return Blocks::LECTERN()
				->setFacing($in->readLegacyHorizontalFacing())
				->setProducingSignal($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::LEVER, function(Reader $in) : Block{
			return Blocks::LEVER()
				->setActivated($in->readBool(StateNames::OPEN_BIT))
				->setFacing(match($value = $in->readString(StateNames::LEVER_DIRECTION)){
					StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH => LeverFacing::DOWN_AXIS_Z(),
					StringValues::LEVER_DIRECTION_DOWN_EAST_WEST => LeverFacing::DOWN_AXIS_X(),
					StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH => LeverFacing::UP_AXIS_Z(),
					StringValues::LEVER_DIRECTION_UP_EAST_WEST => LeverFacing::UP_AXIS_X(),
					StringValues::LEVER_DIRECTION_NORTH => LeverFacing::NORTH(),
					StringValues::LEVER_DIRECTION_SOUTH => LeverFacing::SOUTH(),
					StringValues::LEVER_DIRECTION_WEST => LeverFacing::WEST(),
					StringValues::LEVER_DIRECTION_EAST => LeverFacing::EAST(),
					default => throw $in->badValueException(StateNames::LEVER_DIRECTION, $value),
				});
		});
		$this->map(Ids::LIGHT_BLOCK, function(Reader $in) : Block{
			return Blocks::LIGHT()
				->setLightLevel($in->readBoundedInt(StateNames::BLOCK_LIGHT_LEVEL, Light::MIN_LIGHT_LEVEL, Light::MAX_LIGHT_LEVEL));
		});
		$this->map(Ids::LIGHTNING_ROD, function(Reader $in) : Block{
			return Blocks::LIGHTNING_ROD()
				->setFacing($in->readFacingDirection());
		});
		$this->map(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeWeightedPressurePlate(Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT(), $in));
		$this->map(Ids::LIT_BLAST_FURNACE, function(Reader $in) : Block{
			return Blocks::BLAST_FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LIT_DEEPSLATE_REDSTONE_ORE, fn() => Blocks::DEEPSLATE_REDSTONE_ORE()->setLit(true));
		$this->map(Ids::LIT_FURNACE, function(Reader $in) : Block{
			return Blocks::FURNACE()
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LIT_PUMPKIN, function(Reader $in) : Block{
			return Blocks::LIT_PUMPKIN()
				->setFacing($in->readCardinalHorizontalFacing());
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
				->setFacing($in->readHorizontalFacing())
				->setLit(true);
		});
		$this->map(Ids::LOOM, function(Reader $in) : Block{
			return Blocks::LOOM()
				->setFacing($in->readLegacyHorizontalFacing());
		});
		$this->map(Ids::MANGROVE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::MANGROVE_BUTTON(), $in));
		$this->map(Ids::MANGROVE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::MANGROVE_DOOR(), $in));
		$this->mapSlab(Ids::MANGROVE_SLAB, Ids::MANGROVE_DOUBLE_SLAB, fn() => Blocks::MANGROVE_SLAB());
		$this->map(Ids::MANGROVE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::MANGROVE_FENCE_GATE(), $in));
		$this->map(Ids::MANGROVE_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::MANGROVE_LEAVES(), $in));
		$this->map(Ids::MANGROVE_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::MANGROVE_LOG(), false, $in));
		$this->map(Ids::MANGROVE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::MANGROVE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::MANGROVE_STAIRS, fn() => Blocks::MANGROVE_STAIRS());
		$this->map(Ids::MANGROVE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::MANGROVE_SIGN(), $in));
		$this->map(Ids::MANGROVE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::MANGROVE_TRAPDOOR(), $in));
		$this->map(Ids::MANGROVE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::MANGROVE_WALL_SIGN(), $in));
		$this->map(Ids::MANGROVE_WOOD, function(Reader $in){
			$in->ignored(StateNames::STRIPPED_BIT); //this is also ignored by vanilla
			return Helper::decodeLog(Blocks::MANGROVE_WOOD(), false, $in);
		});
		$this->map(Ids::MELON_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::MELON_STEM(), $in));
		$this->map(Ids::MONSTER_EGG, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::MONSTER_EGG_STONE_TYPE)){
				StringValues::MONSTER_EGG_STONE_TYPE_CHISELED_STONE_BRICK => Blocks::INFESTED_CHISELED_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_COBBLESTONE => Blocks::INFESTED_COBBLESTONE(),
				StringValues::MONSTER_EGG_STONE_TYPE_CRACKED_STONE_BRICK => Blocks::INFESTED_CRACKED_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_MOSSY_STONE_BRICK => Blocks::INFESTED_MOSSY_STONE_BRICK(),
				StringValues::MONSTER_EGG_STONE_TYPE_STONE => Blocks::INFESTED_STONE(),
				StringValues::MONSTER_EGG_STONE_TYPE_STONE_BRICK => Blocks::INFESTED_STONE_BRICK(),
				default => throw $in->badValueException(StateNames::MONSTER_EGG_STONE_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::MOSSY_COBBLESTONE_STAIRS, fn() => Blocks::MOSSY_COBBLESTONE_STAIRS());
		$this->mapStairs(Ids::MOSSY_STONE_BRICK_STAIRS, fn() => Blocks::MOSSY_STONE_BRICK_STAIRS());
		$this->mapSlab(Ids::MUD_BRICK_SLAB, Ids::MUD_BRICK_DOUBLE_SLAB, fn() => Blocks::MUD_BRICK_SLAB());
		$this->mapStairs(Ids::MUD_BRICK_STAIRS, fn() => Blocks::MUD_BRICK_STAIRS());
		$this->map(Ids::MUD_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::MUD_BRICK_WALL(), $in));
		$this->map(Ids::MUDDY_MANGROVE_ROOTS, function(Reader $in) : Block{
			return Blocks::MUDDY_MANGROVE_ROOTS()
				->setAxis($in->readPillarAxis());
		});
		$this->mapStairs(Ids::NETHER_BRICK_STAIRS, fn() => Blocks::NETHER_BRICK_STAIRS());
		$this->map(Ids::NETHER_WART, function(Reader $in) : Block{
			return Blocks::NETHER_WART()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 3));
		});
		$this->mapStairs(Ids::NORMAL_STONE_STAIRS, fn() => Blocks::STONE_STAIRS());
		$this->mapStairs(Ids::OAK_STAIRS, fn() => Blocks::OAK_STAIRS());
		$this->map(Ids::OCHRE_FROGLIGHT, fn(Reader $in) => Blocks::FROGLIGHT()->setFroglightType(FroglightType::OCHRE())->setAxis($in->readPillarAxis()));
		$this->map(Ids::OXIDIZED_COPPER, fn() => Helper::decodeCopper(Blocks::COPPER(), CopperOxidation::OXIDIZED()));
		$this->map(Ids::OXIDIZED_CUT_COPPER, fn() => Helper::decodeCopper(Blocks::CUT_COPPER(), CopperOxidation::OXIDIZED()));
		$this->mapSlab(Ids::OXIDIZED_CUT_COPPER_SLAB, Ids::OXIDIZED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::OXIDIZED()));
		$this->mapStairs(Ids::OXIDIZED_CUT_COPPER_STAIRS, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::OXIDIZED()));
		$this->map(Ids::PEARLESCENT_FROGLIGHT, fn(Reader $in) => Blocks::FROGLIGHT()->setFroglightType(FroglightType::PEARLESCENT())->setAxis($in->readPillarAxis()));
		$this->map(Ids::PLANKS, function(Reader $in) : Block{
			return match($woodName = $in->readString(StateNames::WOOD_TYPE)){
				StringValues::WOOD_TYPE_OAK => Blocks::OAK_PLANKS(),
				StringValues::WOOD_TYPE_SPRUCE => Blocks::SPRUCE_PLANKS(),
				StringValues::WOOD_TYPE_BIRCH => Blocks::BIRCH_PLANKS(),
				StringValues::WOOD_TYPE_JUNGLE => Blocks::JUNGLE_PLANKS(),
				StringValues::WOOD_TYPE_ACACIA => Blocks::ACACIA_PLANKS(),
				StringValues::WOOD_TYPE_DARK_OAK => Blocks::DARK_OAK_PLANKS(),
				default => throw $in->badValueException(StateNames::WOOD_TYPE, $woodName),
			};
		});
		$this->mapStairs(Ids::POLISHED_ANDESITE_STAIRS, fn() => Blocks::POLISHED_ANDESITE_STAIRS());
		$this->map(Ids::POLISHED_BASALT, function(Reader $in) : Block{
			return Blocks::POLISHED_BASALT()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::POLISHED_BLACKSTONE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::POLISHED_BLACKSTONE_BUTTON(), $in));
		$this->mapSlab(Ids::POLISHED_BLACKSTONE_SLAB, Ids::POLISHED_BLACKSTONE_DOUBLE_SLAB, fn() => Blocks::POLISHED_BLACKSTONE_SLAB());
		$this->map(Ids::POLISHED_BLACKSTONE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::POLISHED_BLACKSTONE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::POLISHED_BLACKSTONE_STAIRS, fn() => Blocks::POLISHED_BLACKSTONE_STAIRS());
		$this->map(Ids::POLISHED_BLACKSTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::POLISHED_BLACKSTONE_WALL(), $in));
		$this->mapSlab(Ids::POLISHED_BLACKSTONE_BRICK_SLAB, Ids::POLISHED_BLACKSTONE_BRICK_DOUBLE_SLAB, fn() => Blocks::POLISHED_BLACKSTONE_BRICK_SLAB());
		$this->mapStairs(Ids::POLISHED_BLACKSTONE_BRICK_STAIRS, fn() => Blocks::POLISHED_BLACKSTONE_BRICK_STAIRS());
		$this->map(Ids::POLISHED_BLACKSTONE_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::POLISHED_BLACKSTONE_BRICK_WALL(), $in));
		$this->mapSlab(Ids::POLISHED_DEEPSLATE_SLAB, Ids::POLISHED_DEEPSLATE_DOUBLE_SLAB, fn() => Blocks::POLISHED_DEEPSLATE_SLAB());
		$this->mapStairs(Ids::POLISHED_DEEPSLATE_STAIRS, fn() => Blocks::POLISHED_DEEPSLATE_STAIRS());
		$this->map(Ids::POLISHED_DEEPSLATE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::POLISHED_DEEPSLATE_WALL(), $in));
		$this->mapStairs(Ids::POLISHED_DIORITE_STAIRS, fn() => Blocks::POLISHED_DIORITE_STAIRS());
		$this->mapStairs(Ids::POLISHED_GRANITE_STAIRS, fn() => Blocks::POLISHED_GRANITE_STAIRS());
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
		$this->map(Ids::PRISMARINE, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::PRISMARINE_BLOCK_TYPE)){
				StringValues::PRISMARINE_BLOCK_TYPE_BRICKS => Blocks::PRISMARINE_BRICKS(),
				StringValues::PRISMARINE_BLOCK_TYPE_DARK => Blocks::DARK_PRISMARINE(),
				StringValues::PRISMARINE_BLOCK_TYPE_DEFAULT => Blocks::PRISMARINE(),
				default => throw $in->badValueException(StateNames::PRISMARINE_BLOCK_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::PRISMARINE_BRICKS_STAIRS, fn() => Blocks::PRISMARINE_BRICKS_STAIRS());
		$this->mapStairs(Ids::PRISMARINE_STAIRS, fn() => Blocks::PRISMARINE_STAIRS());
		$this->map(Ids::PUMPKIN, function(Reader $in) : Block{
			$in->ignored(StateNames::CARDINAL_DIRECTION); //obsolete
			return Blocks::PUMPKIN();
		});
		$this->map(Ids::PUMPKIN_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::PUMPKIN_STEM(), $in));
		$this->map(Ids::PURPUR_BLOCK, function(Reader $in) : Block{
			$type = $in->readString(StateNames::CHISEL_TYPE);
			if($type === StringValues::CHISEL_TYPE_LINES){
				return Blocks::PURPUR_PILLAR()->setAxis($in->readPillarAxis());
			}else{
				$in->ignored(StateNames::PILLAR_AXIS); //axis only applies to pillars
				return match($type){
					StringValues::CHISEL_TYPE_CHISELED, //TODO: bug in MCPE
					StringValues::CHISEL_TYPE_SMOOTH, //TODO: bug in MCPE
					StringValues::CHISEL_TYPE_DEFAULT => Blocks::PURPUR(),
					default => throw $in->badValueException(StateNames::CHISEL_TYPE, $type),
				};
			}
		});
		$this->mapStairs(Ids::PURPUR_STAIRS, fn() => Blocks::PURPUR_STAIRS());
		$this->map(Ids::QUARTZ_BLOCK, function(Reader $in) : Block{
			switch($type = $in->readString(StateNames::CHISEL_TYPE)){
				case StringValues::CHISEL_TYPE_CHISELED:
					return Blocks::CHISELED_QUARTZ()->setAxis($in->readPillarAxis());
				case StringValues::CHISEL_TYPE_DEFAULT:
					$in->ignored(StateNames::PILLAR_AXIS);
					return Blocks::QUARTZ();
				case StringValues::CHISEL_TYPE_LINES:
					return Blocks::QUARTZ_PILLAR()->setAxis($in->readPillarAxis());
				case StringValues::CHISEL_TYPE_SMOOTH:
					$in->ignored(StateNames::PILLAR_AXIS);
					return Blocks::SMOOTH_QUARTZ();
				default:
					return throw $in->badValueException(StateNames::CHISEL_TYPE, $type);
			}
		});
		$this->mapStairs(Ids::QUARTZ_STAIRS, fn() => Blocks::QUARTZ_STAIRS());
		$this->map(Ids::RAIL, function(Reader $in) : Block{
			return Blocks::RAIL()
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 9));
		});
		$this->map(Ids::RED_FLOWER, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::FLOWER_TYPE)){
				StringValues::FLOWER_TYPE_ALLIUM => Blocks::ALLIUM(),
				StringValues::FLOWER_TYPE_CORNFLOWER => Blocks::CORNFLOWER(),
				StringValues::FLOWER_TYPE_HOUSTONIA => Blocks::AZURE_BLUET(), //wtf ???
				StringValues::FLOWER_TYPE_LILY_OF_THE_VALLEY => Blocks::LILY_OF_THE_VALLEY(),
				StringValues::FLOWER_TYPE_ORCHID => Blocks::BLUE_ORCHID(),
				StringValues::FLOWER_TYPE_OXEYE => Blocks::OXEYE_DAISY(),
				StringValues::FLOWER_TYPE_POPPY => Blocks::POPPY(),
				StringValues::FLOWER_TYPE_TULIP_ORANGE => Blocks::ORANGE_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_PINK => Blocks::PINK_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_RED => Blocks::RED_TULIP(),
				StringValues::FLOWER_TYPE_TULIP_WHITE => Blocks::WHITE_TULIP(),
				default => throw $in->badValueException(StateNames::FLOWER_TYPE, $type),
			};
		});
		$this->map(Ids::RED_MUSHROOM_BLOCK, fn(Reader $in) => Helper::decodeMushroomBlock(Blocks::RED_MUSHROOM_BLOCK(), $in));
		$this->mapStairs(Ids::RED_NETHER_BRICK_STAIRS, fn() => Blocks::RED_NETHER_BRICK_STAIRS());
		$this->map(Ids::RED_SANDSTONE, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::SAND_STONE_TYPE)){
				StringValues::SAND_STONE_TYPE_CUT => Blocks::CUT_RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_DEFAULT => Blocks::RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_HEIROGLYPHS => Blocks::CHISELED_RED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_SMOOTH => Blocks::SMOOTH_RED_SANDSTONE(),
				default => throw $in->badValueException(StateNames::SAND_STONE_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::RED_SANDSTONE_STAIRS, fn() => Blocks::RED_SANDSTONE_STAIRS());
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
		$this->map(Ids::REDSTONE_WIRE, function(Reader $in) : Block{
			return Blocks::REDSTONE_WIRE()
				->setOutputSignalStrength($in->readBoundedInt(StateNames::REDSTONE_SIGNAL, 0, 15));
		});
		$this->map(Ids::REEDS, function(Reader $in) : Block{
			return Blocks::SUGARCANE()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::SAND, function(Reader $in) : Block{
			return match($value = $in->readString(StateNames::SAND_TYPE)){
				StringValues::SAND_TYPE_NORMAL => Blocks::SAND(),
				StringValues::SAND_TYPE_RED => Blocks::RED_SAND(),
				default => throw $in->badValueException(StateNames::SAND_TYPE, $value),
			};
		});
		$this->map(Ids::SANDSTONE, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::SAND_STONE_TYPE)){
				StringValues::SAND_STONE_TYPE_CUT => Blocks::CUT_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_DEFAULT => Blocks::SANDSTONE(),
				StringValues::SAND_STONE_TYPE_HEIROGLYPHS => Blocks::CHISELED_SANDSTONE(),
				StringValues::SAND_STONE_TYPE_SMOOTH => Blocks::SMOOTH_SANDSTONE(),
				default => throw $in->badValueException(StateNames::SAND_STONE_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::SANDSTONE_STAIRS, fn() => Blocks::SANDSTONE_STAIRS());
		$this->map(Ids::SAPLING, function(Reader $in) : Block{
			return (match($type = $in->readString(StateNames::SAPLING_TYPE)){
					StringValues::SAPLING_TYPE_ACACIA => Blocks::ACACIA_SAPLING(),
					StringValues::SAPLING_TYPE_BIRCH => Blocks::BIRCH_SAPLING(),
					StringValues::SAPLING_TYPE_DARK_OAK => Blocks::DARK_OAK_SAPLING(),
					StringValues::SAPLING_TYPE_JUNGLE => Blocks::JUNGLE_SAPLING(),
					StringValues::SAPLING_TYPE_OAK => Blocks::OAK_SAPLING(),
					StringValues::SAPLING_TYPE_SPRUCE => Blocks::SPRUCE_SAPLING(),
					default => throw $in->badValueException(StateNames::SAPLING_TYPE, $type),
				})
				->setReady($in->readBool(StateNames::AGE_BIT));
		});
		$this->map(Ids::SEA_PICKLE, function(Reader $in) : Block{
			return Blocks::SEA_PICKLE()
				->setCount($in->readBoundedInt(StateNames::CLUSTER_COUNT, 0, 3) + 1)
				->setUnderwater(!$in->readBool(StateNames::DEAD_BIT));
		});
		$this->map(Ids::SHULKER_BOX, function(Reader $in) : Block{
			return Blocks::DYED_SHULKER_BOX()
				->setColor($in->readColor());
		});
		$this->map(Ids::SKULL, function(Reader $in) : Block{
			return Blocks::MOB_HEAD()
				->setFacing($in->readFacingWithoutDown());
		});
		$this->map(Ids::SMOKER, function(Reader $in) : Block{
			return Blocks::SMOKER()
				->setFacing($in->readHorizontalFacing())
				->setLit(false);
		});
		$this->mapStairs(Ids::SMOOTH_QUARTZ_STAIRS, fn() => Blocks::SMOOTH_QUARTZ_STAIRS());
		$this->mapStairs(Ids::SMOOTH_RED_SANDSTONE_STAIRS, fn() => Blocks::SMOOTH_RED_SANDSTONE_STAIRS());
		$this->mapStairs(Ids::SMOOTH_SANDSTONE_STAIRS, fn() => Blocks::SMOOTH_SANDSTONE_STAIRS());
		$this->map(Ids::SNOW_LAYER, function(Reader $in) : Block{
			$in->ignored(StateNames::COVERED_BIT); //seems to be useless
			return Blocks::SNOW_LAYER()->setLayers($in->readBoundedInt(StateNames::HEIGHT, 0, 7) + 1);
		});
		$this->map(Ids::SOUL_FIRE, function(Reader $in) : Block{
			$in->ignored(StateNames::AGE); //this is useless for soul fire, since it doesn't have the logic associated
			return Blocks::SOUL_FIRE();
		});
		$this->map(Ids::SOUL_LANTERN, function(Reader $in) : Block{
			return Blocks::SOUL_LANTERN()
				->setHanging($in->readBool(StateNames::HANGING));
		});
		$this->map(Ids::SOUL_TORCH, function(Reader $in) : Block{
			return Blocks::SOUL_TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::SPONGE, function(Reader $in) : Block{
			return Blocks::SPONGE()->setWet(match($type = $in->readString(StateNames::SPONGE_TYPE)){
				StringValues::SPONGE_TYPE_DRY => false,
				StringValues::SPONGE_TYPE_WET => true,
				default => throw $in->badValueException(StateNames::SPONGE_TYPE, $type),
			});
		});
		$this->map(Ids::SPRUCE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::SPRUCE_BUTTON(), $in));
		$this->map(Ids::SPRUCE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::SPRUCE_DOOR(), $in));
		$this->map(Ids::SPRUCE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::SPRUCE_FENCE_GATE(), $in));
		$this->map(Ids::SPRUCE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::SPRUCE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::SPRUCE_STAIRS, fn() => Blocks::SPRUCE_STAIRS());
		$this->map(Ids::SPRUCE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::SPRUCE_SIGN(), $in));
		$this->map(Ids::SPRUCE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::SPRUCE_TRAPDOOR(), $in));
		$this->map(Ids::SPRUCE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::SPRUCE_WALL_SIGN(), $in));
		$this->map(Ids::STAINED_GLASS, function(Reader $in) : Block{
			return Blocks::STAINED_GLASS()
				->setColor($in->readColor());
		});
		$this->map(Ids::STAINED_GLASS_PANE, function(Reader $in) : Block{
			return Blocks::STAINED_GLASS_PANE()
				->setColor($in->readColor());
		});
		$this->map(Ids::STAINED_HARDENED_CLAY, function(Reader $in) : Block{
			return Blocks::STAINED_CLAY()
				->setColor($in->readColor());
		});
		$this->map(Ids::STANDING_BANNER, function(Reader $in) : Block{
			return Blocks::BANNER()
				->setRotation($in->readBoundedInt(StateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->map(Ids::STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::OAK_SIGN(), $in));
		$this->map(Ids::STONE, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::STONE_TYPE)){
				StringValues::STONE_TYPE_ANDESITE => Blocks::ANDESITE(),
				StringValues::STONE_TYPE_ANDESITE_SMOOTH => Blocks::POLISHED_ANDESITE(),
				StringValues::STONE_TYPE_DIORITE => Blocks::DIORITE(),
				StringValues::STONE_TYPE_DIORITE_SMOOTH => Blocks::POLISHED_DIORITE(),
				StringValues::STONE_TYPE_GRANITE => Blocks::GRANITE(),
				StringValues::STONE_TYPE_GRANITE_SMOOTH => Blocks::POLISHED_GRANITE(),
				StringValues::STONE_TYPE_STONE => Blocks::STONE(),
				default => throw $in->badValueException(StateNames::STONE_TYPE, $type),
			};
		});
		$this->mapStairs(Ids::STONE_BRICK_STAIRS, fn() => Blocks::STONE_BRICK_STAIRS());
		$this->map(Ids::STONE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::STONE_BUTTON(), $in));
		$this->map(Ids::STONE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::STONE_PRESSURE_PLATE(), $in));
		$this->mapSlab(Ids::STONE_BLOCK_SLAB, Ids::DOUBLE_STONE_BLOCK_SLAB, fn(Reader $in) => Helper::mapStoneSlab1Type($in));
		$this->mapSlab(Ids::STONE_BLOCK_SLAB2, Ids::DOUBLE_STONE_BLOCK_SLAB2, fn(Reader $in) => Helper::mapStoneSlab2Type($in));
		$this->mapSlab(Ids::STONE_BLOCK_SLAB3, Ids::DOUBLE_STONE_BLOCK_SLAB3, fn(Reader $in) => Helper::mapStoneSlab3Type($in));
		$this->mapSlab(Ids::STONE_BLOCK_SLAB4, Ids::DOUBLE_STONE_BLOCK_SLAB4, fn(Reader $in) => Helper::mapStoneSlab4Type($in));
		$this->mapStairs(Ids::STONE_STAIRS, fn() => Blocks::COBBLESTONE_STAIRS());
		$this->map(Ids::STONEBRICK, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::STONE_BRICK_TYPE)){
				StringValues::STONE_BRICK_TYPE_SMOOTH, //TODO: bug in vanilla
				StringValues::STONE_BRICK_TYPE_DEFAULT => Blocks::STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_CHISELED => Blocks::CHISELED_STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_CRACKED => Blocks::CRACKED_STONE_BRICKS(),
				StringValues::STONE_BRICK_TYPE_MOSSY => Blocks::MOSSY_STONE_BRICKS(),
				default => throw $in->badValueException(StateNames::STONE_BRICK_TYPE, $type),
			};
		});
		$this->map(Ids::STONECUTTER_BLOCK, function(Reader $in) : Block{
			return Blocks::STONECUTTER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::STRIPPED_CRIMSON_HYPHAE, fn(Reader $in) => Helper::decodeLog(Blocks::CRIMSON_HYPHAE(), true, $in));
		$this->map(Ids::STRIPPED_CRIMSON_STEM, fn(Reader $in) => Helper::decodeLog(Blocks::CRIMSON_STEM(), true, $in));
		$this->map(Ids::STRIPPED_MANGROVE_LOG, fn(Reader $in) => Helper::decodeLog(Blocks::MANGROVE_LOG(), true, $in));
		$this->map(Ids::STRIPPED_MANGROVE_WOOD, fn(Reader $in) => Helper::decodeLog(Blocks::MANGROVE_WOOD(), true, $in));
		$this->map(Ids::STRIPPED_WARPED_HYPHAE, fn(Reader $in) => Helper::decodeLog(Blocks::WARPED_HYPHAE(), true, $in));
		$this->map(Ids::STRIPPED_WARPED_STEM, fn(Reader $in) => Helper::decodeLog(Blocks::WARPED_STEM(), true, $in));
		$this->map(Ids::SWEET_BERRY_BUSH, function(Reader $in) : Block{
			//berry bush only wants 0-3, but it can be bigger in MCPE due to misuse of GROWTH state which goes up to 7
			$growth = $in->readBoundedInt(StateNames::GROWTH, 0, 7);
			return Blocks::SWEET_BERRY_BUSH()
				->setAge(min($growth, SweetBerryBush::STAGE_MATURE));
		});
		$this->map(Ids::TALLGRASS, function(Reader $in) : Block{
			return match($type = $in->readString(StateNames::TALL_GRASS_TYPE)){
				StringValues::TALL_GRASS_TYPE_DEFAULT, StringValues::TALL_GRASS_TYPE_SNOW, StringValues::TALL_GRASS_TYPE_TALL => Blocks::TALL_GRASS(),
				StringValues::TALL_GRASS_TYPE_FERN => Blocks::FERN(),
				default => throw $in->badValueException(StateNames::TALL_GRASS_TYPE, $type),
			};
		});
		$this->map(Ids::TNT, function(Reader $in) : Block{
			return Blocks::TNT()
				->setUnstable($in->readBool(StateNames::EXPLODE_BIT))
				->setWorksUnderwater($in->readBool(StateNames::ALLOW_UNDERWATER_BIT));
		});
		$this->map(Ids::TORCH, function(Reader $in) : Block{
			return Blocks::TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::OAK_TRAPDOOR(), $in));
		$this->map(Ids::TRAPPED_CHEST, function(Reader $in) : Block{
			return Blocks::TRAPPED_CHEST()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::TRIP_WIRE, function(Reader $in) : Block{
			return Blocks::TRIPWIRE()
				->setConnected($in->readBool(StateNames::ATTACHED_BIT))
				->setDisarmed($in->readBool(StateNames::DISARMED_BIT))
				->setSuspended($in->readBool(StateNames::SUSPENDED_BIT))
				->setTriggered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::TRIPWIRE_HOOK, function(Reader $in) : Block{
			return Blocks::TRIPWIRE_HOOK()
				->setConnected($in->readBool(StateNames::ATTACHED_BIT))
				->setFacing($in->readLegacyHorizontalFacing())
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::TWISTING_VINES, function(Reader $in) : Block{
			return Blocks::TWISTING_VINES()
				->setAge($in->readBoundedInt(StateNames::TWISTING_VINES_AGE, 0, 25));
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
		$this->map(Ids::VERDANT_FROGLIGHT, fn(Reader $in) => Blocks::FROGLIGHT()->setFroglightType(FroglightType::VERDANT())->setAxis($in->readPillarAxis()));
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
		$this->map(Ids::WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::OAK_WALL_SIGN(), $in));
		$this->map(Ids::WARPED_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::WARPED_BUTTON(), $in));
		$this->map(Ids::WARPED_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::WARPED_DOOR(), $in));
		$this->mapSlab(Ids::WARPED_SLAB, Ids::WARPED_DOUBLE_SLAB, fn() => Blocks::WARPED_SLAB());
		$this->map(Ids::WARPED_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::WARPED_FENCE_GATE(), $in));
		$this->map(Ids::WARPED_HYPHAE, fn(Reader $in) => Helper::decodeLog(Blocks::WARPED_HYPHAE(), false, $in));
		$this->map(Ids::WARPED_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::WARPED_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::WARPED_STAIRS, fn() => Blocks::WARPED_STAIRS());
		$this->map(Ids::WARPED_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::WARPED_SIGN(), $in));
		$this->map(Ids::WARPED_STEM, fn(Reader $in) => Helper::decodeLog(Blocks::WARPED_STEM(), false, $in));
		$this->map(Ids::WARPED_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::WARPED_TRAPDOOR(), $in));
		$this->map(Ids::WARPED_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::WARPED_WALL_SIGN(), $in));
		$this->map(Ids::WATER, fn(Reader $in) => Helper::decodeStillLiquid(Blocks::WATER(), $in));
		$this->map(Ids::WAXED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::COPPER(), CopperOxidation::NONE()));
		$this->map(Ids::WAXED_CUT_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER(), CopperOxidation::NONE()));
		$this->mapSlab(Ids::WAXED_CUT_COPPER_SLAB, Ids::WAXED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::NONE()));
		$this->mapStairs(Ids::WAXED_CUT_COPPER_STAIRS, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::NONE()));
		$this->map(Ids::WAXED_EXPOSED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::COPPER(), CopperOxidation::EXPOSED()));
		$this->map(Ids::WAXED_EXPOSED_CUT_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER(), CopperOxidation::EXPOSED()));
		$this->mapSlab(Ids::WAXED_EXPOSED_CUT_COPPER_SLAB, Ids::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::EXPOSED()));
		$this->mapStairs(Ids::WAXED_EXPOSED_CUT_COPPER_STAIRS, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::EXPOSED()));
		$this->map(Ids::WAXED_OXIDIZED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::COPPER(), CopperOxidation::OXIDIZED()));
		$this->map(Ids::WAXED_OXIDIZED_CUT_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER(), CopperOxidation::OXIDIZED()));
		$this->mapSlab(Ids::WAXED_OXIDIZED_CUT_COPPER_SLAB, Ids::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::OXIDIZED()));
		$this->mapStairs(Ids::WAXED_OXIDIZED_CUT_COPPER_STAIRS, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::OXIDIZED()));
		$this->map(Ids::WAXED_WEATHERED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::COPPER(), CopperOxidation::WEATHERED()));
		$this->map(Ids::WAXED_WEATHERED_CUT_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER(), CopperOxidation::WEATHERED()));
		$this->mapSlab(Ids::WAXED_WEATHERED_CUT_COPPER_SLAB, Ids::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::WEATHERED()));
		$this->mapStairs(Ids::WAXED_WEATHERED_CUT_COPPER_STAIRS, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::WEATHERED()));
		$this->map(Ids::WEATHERED_COPPER, fn() => Helper::decodeCopper(Blocks::COPPER(), CopperOxidation::WEATHERED()));
		$this->map(Ids::WEATHERED_CUT_COPPER, fn() => Helper::decodeCopper(Blocks::CUT_COPPER(), CopperOxidation::WEATHERED()));
		$this->mapSlab(Ids::WEATHERED_CUT_COPPER_SLAB, Ids::WEATHERED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::WEATHERED()));
		$this->mapStairs(Ids::WEATHERED_CUT_COPPER_STAIRS, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::WEATHERED()));
		$this->map(Ids::WEEPING_VINES, function(Reader $in) : Block{
			return Blocks::WEEPING_VINES()
				->setAge($in->readBoundedInt(StateNames::WEEPING_VINES_AGE, 0, 25));
		});
		$this->map(Ids::WHEAT, fn(Reader $in) => Helper::decodeCrops(Blocks::WHEAT(), $in));
		$this->map(Ids::WOOD, fn(Reader $in) : Block => Helper::decodeLog(match($woodType = $in->readString(StateNames::WOOD_TYPE)){
			StringValues::WOOD_TYPE_ACACIA => Blocks::ACACIA_WOOD(),
			StringValues::WOOD_TYPE_BIRCH => Blocks::BIRCH_WOOD(),
			StringValues::WOOD_TYPE_DARK_OAK => Blocks::DARK_OAK_WOOD(),
			StringValues::WOOD_TYPE_JUNGLE => Blocks::JUNGLE_WOOD(),
			StringValues::WOOD_TYPE_OAK => Blocks::OAK_WOOD(),
			StringValues::WOOD_TYPE_SPRUCE => Blocks::SPRUCE_WOOD(),
			default => throw $in->badValueException(StateNames::WOOD_TYPE, $woodType),
		}, $in->readBool(StateNames::STRIPPED_BIT), $in));
		$this->map(Ids::WOODEN_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::OAK_BUTTON(), $in));
		$this->map(Ids::WOODEN_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::OAK_DOOR(), $in));
		$this->map(Ids::WOODEN_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::OAK_PRESSURE_PLATE(), $in));
		$this->mapSlab(Ids::WOODEN_SLAB, Ids::DOUBLE_WOODEN_SLAB, fn(Reader $in) => Helper::mapWoodenSlabType($in));
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
