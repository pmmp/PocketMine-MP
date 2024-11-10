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
use pocketmine\block\CaveVines;
use pocketmine\block\ChorusFlower;
use pocketmine\block\DoublePitcherCrop;
use pocketmine\block\Opaque;
use pocketmine\block\PinkPetals;
use pocketmine\block\PitcherCrop;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\SweetBerryBush;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\ChiseledBookshelfSlot;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DripleafState;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\MobHeadType;
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
		$this->registerFlatWoodBlockDeserializers();
		$this->registerLeavesDeserializers();
		$this->registerSaplingDeserializers();
		$this->registerLightDeserializers();
		$this->registerMobHeadDeserializers();
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

	private function registerCandleDeserializers() : void{
		$this->map(Ids::CANDLE, fn(Reader $in) => Helper::decodeCandle(Blocks::CANDLE(), $in));
		foreach([
			Ids::BLACK_CANDLE => DyeColor::BLACK,
			Ids::BLUE_CANDLE => DyeColor::BLUE,
			Ids::BROWN_CANDLE => DyeColor::BROWN,
			Ids::CYAN_CANDLE => DyeColor::CYAN,
			Ids::GRAY_CANDLE => DyeColor::GRAY,
			Ids::GREEN_CANDLE => DyeColor::GREEN,
			Ids::LIGHT_BLUE_CANDLE => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_CANDLE => DyeColor::LIGHT_GRAY,
			Ids::LIME_CANDLE => DyeColor::LIME,
			Ids::MAGENTA_CANDLE => DyeColor::MAGENTA,
			Ids::ORANGE_CANDLE => DyeColor::ORANGE,
			Ids::PINK_CANDLE => DyeColor::PINK,
			Ids::PURPLE_CANDLE => DyeColor::PURPLE,
			Ids::RED_CANDLE => DyeColor::RED,
			Ids::WHITE_CANDLE => DyeColor::WHITE,
			Ids::YELLOW_CANDLE => DyeColor::YELLOW,
		] as $id => $color){
			$this->map($id, fn(Reader $in) => Helper::decodeCandle(Blocks::DYED_CANDLE()->setColor($color), $in));
		}

		$this->map(Ids::CANDLE_CAKE, fn(Reader $in) => Blocks::CAKE_WITH_CANDLE()->setLit($in->readBool(StateNames::LIT)));
		foreach([
			Ids::BLACK_CANDLE_CAKE => DyeColor::BLACK,
			Ids::BLUE_CANDLE_CAKE => DyeColor::BLUE,
			Ids::BROWN_CANDLE_CAKE => DyeColor::BROWN,
			Ids::CYAN_CANDLE_CAKE => DyeColor::CYAN,
			Ids::GRAY_CANDLE_CAKE => DyeColor::GRAY,
			Ids::GREEN_CANDLE_CAKE => DyeColor::GREEN,
			Ids::LIGHT_BLUE_CANDLE_CAKE => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_CANDLE_CAKE => DyeColor::LIGHT_GRAY,
			Ids::LIME_CANDLE_CAKE => DyeColor::LIME,
			Ids::MAGENTA_CANDLE_CAKE => DyeColor::MAGENTA,
			Ids::ORANGE_CANDLE_CAKE => DyeColor::ORANGE,
			Ids::PINK_CANDLE_CAKE => DyeColor::PINK,
			Ids::PURPLE_CANDLE_CAKE => DyeColor::PURPLE,
			Ids::RED_CANDLE_CAKE => DyeColor::RED,
			Ids::WHITE_CANDLE_CAKE => DyeColor::WHITE,
			Ids::YELLOW_CANDLE_CAKE => DyeColor::YELLOW,
		] as $id => $color){
			$this->map($id, fn(Reader $in) => Blocks::CAKE_WITH_DYED_CANDLE()
				->setColor($color)
				->setLit($in->readBool(StateNames::LIT))
			);
		}
	}

	private function registerFlatColorBlockDeserializers() : void{
		foreach([
			Ids::HARD_BLACK_STAINED_GLASS => DyeColor::BLACK,
			Ids::HARD_BLUE_STAINED_GLASS => DyeColor::BLUE,
			Ids::HARD_BROWN_STAINED_GLASS => DyeColor::BROWN,
			Ids::HARD_CYAN_STAINED_GLASS => DyeColor::CYAN,
			Ids::HARD_GRAY_STAINED_GLASS => DyeColor::GRAY,
			Ids::HARD_GREEN_STAINED_GLASS => DyeColor::GREEN,
			Ids::HARD_LIGHT_BLUE_STAINED_GLASS => DyeColor::LIGHT_BLUE,
			Ids::HARD_LIGHT_GRAY_STAINED_GLASS => DyeColor::LIGHT_GRAY,
			Ids::HARD_LIME_STAINED_GLASS => DyeColor::LIME,
			Ids::HARD_MAGENTA_STAINED_GLASS => DyeColor::MAGENTA,
			Ids::HARD_ORANGE_STAINED_GLASS => DyeColor::ORANGE,
			Ids::HARD_PINK_STAINED_GLASS => DyeColor::PINK,
			Ids::HARD_PURPLE_STAINED_GLASS => DyeColor::PURPLE,
			Ids::HARD_RED_STAINED_GLASS => DyeColor::RED,
			Ids::HARD_WHITE_STAINED_GLASS => DyeColor::WHITE,
			Ids::HARD_YELLOW_STAINED_GLASS => DyeColor::YELLOW,
		] as $id => $color){
			$this->map($id, fn(Reader $in) => Blocks::STAINED_HARDENED_GLASS()->setColor($color));
		}

		foreach([
			Ids::HARD_BLACK_STAINED_GLASS_PANE => DyeColor::BLACK,
			Ids::HARD_BLUE_STAINED_GLASS_PANE => DyeColor::BLUE,
			Ids::HARD_BROWN_STAINED_GLASS_PANE => DyeColor::BROWN,
			Ids::HARD_CYAN_STAINED_GLASS_PANE => DyeColor::CYAN,
			Ids::HARD_GRAY_STAINED_GLASS_PANE => DyeColor::GRAY,
			Ids::HARD_GREEN_STAINED_GLASS_PANE => DyeColor::GREEN,
			Ids::HARD_LIGHT_BLUE_STAINED_GLASS_PANE => DyeColor::LIGHT_BLUE,
			Ids::HARD_LIGHT_GRAY_STAINED_GLASS_PANE => DyeColor::LIGHT_GRAY,
			Ids::HARD_LIME_STAINED_GLASS_PANE => DyeColor::LIME,
			Ids::HARD_MAGENTA_STAINED_GLASS_PANE => DyeColor::MAGENTA,
			Ids::HARD_ORANGE_STAINED_GLASS_PANE => DyeColor::ORANGE,
			Ids::HARD_PINK_STAINED_GLASS_PANE => DyeColor::PINK,
			Ids::HARD_PURPLE_STAINED_GLASS_PANE => DyeColor::PURPLE,
			Ids::HARD_RED_STAINED_GLASS_PANE => DyeColor::RED,
			Ids::HARD_WHITE_STAINED_GLASS_PANE => DyeColor::WHITE,
			Ids::HARD_YELLOW_STAINED_GLASS_PANE => DyeColor::YELLOW,
		] as $id => $color){
			$this->map($id, fn(Reader $in) => Blocks::STAINED_HARDENED_GLASS_PANE()->setColor($color));
		}

		foreach([
			Ids::BLACK_GLAZED_TERRACOTTA => DyeColor::BLACK,
			Ids::BLUE_GLAZED_TERRACOTTA => DyeColor::BLUE,
			Ids::BROWN_GLAZED_TERRACOTTA => DyeColor::BROWN,
			Ids::CYAN_GLAZED_TERRACOTTA => DyeColor::CYAN,
			Ids::GRAY_GLAZED_TERRACOTTA => DyeColor::GRAY,
			Ids::GREEN_GLAZED_TERRACOTTA => DyeColor::GREEN,
			Ids::LIGHT_BLUE_GLAZED_TERRACOTTA => DyeColor::LIGHT_BLUE,
			Ids::SILVER_GLAZED_TERRACOTTA => DyeColor::LIGHT_GRAY,
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

		foreach([
			Ids::BLACK_WOOL => DyeColor::BLACK,
			Ids::BLUE_WOOL => DyeColor::BLUE,
			Ids::BROWN_WOOL => DyeColor::BROWN,
			Ids::CYAN_WOOL => DyeColor::CYAN,
			Ids::GRAY_WOOL => DyeColor::GRAY,
			Ids::GREEN_WOOL => DyeColor::GREEN,
			Ids::LIGHT_BLUE_WOOL => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_WOOL => DyeColor::LIGHT_GRAY,
			Ids::LIME_WOOL => DyeColor::LIME,
			Ids::MAGENTA_WOOL => DyeColor::MAGENTA,
			Ids::ORANGE_WOOL => DyeColor::ORANGE,
			Ids::PINK_WOOL => DyeColor::PINK,
			Ids::PURPLE_WOOL => DyeColor::PURPLE,
			Ids::RED_WOOL => DyeColor::RED,
			Ids::WHITE_WOOL => DyeColor::WHITE,
			Ids::YELLOW_WOOL => DyeColor::YELLOW,
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::WOOL()->setColor($color));
		}

		foreach([
			Ids::BLACK_CARPET => DyeColor::BLACK,
			Ids::BLUE_CARPET => DyeColor::BLUE,
			Ids::BROWN_CARPET => DyeColor::BROWN,
			Ids::CYAN_CARPET => DyeColor::CYAN,
			Ids::GRAY_CARPET => DyeColor::GRAY,
			Ids::GREEN_CARPET => DyeColor::GREEN,
			Ids::LIGHT_BLUE_CARPET => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_CARPET => DyeColor::LIGHT_GRAY,
			Ids::LIME_CARPET => DyeColor::LIME,
			Ids::MAGENTA_CARPET => DyeColor::MAGENTA,
			Ids::ORANGE_CARPET => DyeColor::ORANGE,
			Ids::PINK_CARPET => DyeColor::PINK,
			Ids::PURPLE_CARPET => DyeColor::PURPLE,
			Ids::RED_CARPET => DyeColor::RED,
			Ids::WHITE_CARPET => DyeColor::WHITE,
			Ids::YELLOW_CARPET => DyeColor::YELLOW,
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::CARPET()->setColor($color));
		}

		foreach([
			Ids::BLACK_SHULKER_BOX => DyeColor::BLACK,
			Ids::BLUE_SHULKER_BOX => DyeColor::BLUE,
			Ids::BROWN_SHULKER_BOX => DyeColor::BROWN,
			Ids::CYAN_SHULKER_BOX => DyeColor::CYAN,
			Ids::GRAY_SHULKER_BOX => DyeColor::GRAY,
			Ids::GREEN_SHULKER_BOX => DyeColor::GREEN,
			Ids::LIGHT_BLUE_SHULKER_BOX => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_SHULKER_BOX => DyeColor::LIGHT_GRAY,
			Ids::LIME_SHULKER_BOX => DyeColor::LIME,
			Ids::MAGENTA_SHULKER_BOX => DyeColor::MAGENTA,
			Ids::ORANGE_SHULKER_BOX => DyeColor::ORANGE,
			Ids::PINK_SHULKER_BOX => DyeColor::PINK,
			Ids::PURPLE_SHULKER_BOX => DyeColor::PURPLE,
			Ids::RED_SHULKER_BOX => DyeColor::RED,
			Ids::WHITE_SHULKER_BOX => DyeColor::WHITE,
			Ids::YELLOW_SHULKER_BOX => DyeColor::YELLOW,
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::DYED_SHULKER_BOX()->setColor($color));
		}

		foreach([
			Ids::BLACK_CONCRETE => DyeColor::BLACK,
			Ids::BLUE_CONCRETE => DyeColor::BLUE,
			Ids::BROWN_CONCRETE => DyeColor::BROWN,
			Ids::CYAN_CONCRETE => DyeColor::CYAN,
			Ids::GRAY_CONCRETE => DyeColor::GRAY,
			Ids::GREEN_CONCRETE => DyeColor::GREEN,
			Ids::LIGHT_BLUE_CONCRETE => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_CONCRETE => DyeColor::LIGHT_GRAY,
			Ids::LIME_CONCRETE => DyeColor::LIME,
			Ids::MAGENTA_CONCRETE => DyeColor::MAGENTA,
			Ids::ORANGE_CONCRETE => DyeColor::ORANGE,
			Ids::PINK_CONCRETE => DyeColor::PINK,
			Ids::PURPLE_CONCRETE => DyeColor::PURPLE,
			Ids::RED_CONCRETE => DyeColor::RED,
			Ids::WHITE_CONCRETE => DyeColor::WHITE,
			Ids::YELLOW_CONCRETE => DyeColor::YELLOW,
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::CONCRETE()->setColor($color));
		}

		foreach([
			Ids::BLACK_CONCRETE_POWDER => DyeColor::BLACK,
			Ids::BLUE_CONCRETE_POWDER => DyeColor::BLUE,
			Ids::BROWN_CONCRETE_POWDER => DyeColor::BROWN,
			Ids::CYAN_CONCRETE_POWDER => DyeColor::CYAN,
			Ids::GRAY_CONCRETE_POWDER => DyeColor::GRAY,
			Ids::GREEN_CONCRETE_POWDER => DyeColor::GREEN,
			Ids::LIGHT_BLUE_CONCRETE_POWDER => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_CONCRETE_POWDER => DyeColor::LIGHT_GRAY,
			Ids::LIME_CONCRETE_POWDER => DyeColor::LIME,
			Ids::MAGENTA_CONCRETE_POWDER => DyeColor::MAGENTA,
			Ids::ORANGE_CONCRETE_POWDER => DyeColor::ORANGE,
			Ids::PINK_CONCRETE_POWDER => DyeColor::PINK,
			Ids::PURPLE_CONCRETE_POWDER => DyeColor::PURPLE,
			Ids::RED_CONCRETE_POWDER => DyeColor::RED,
			Ids::WHITE_CONCRETE_POWDER => DyeColor::WHITE,
			Ids::YELLOW_CONCRETE_POWDER => DyeColor::YELLOW,
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::CONCRETE_POWDER()->setColor($color));
		}

		foreach([
			Ids::BLACK_TERRACOTTA => DyeColor::BLACK,
			Ids::BLUE_TERRACOTTA => DyeColor::BLUE,
			Ids::BROWN_TERRACOTTA => DyeColor::BROWN,
			Ids::CYAN_TERRACOTTA => DyeColor::CYAN,
			Ids::GRAY_TERRACOTTA => DyeColor::GRAY,
			Ids::GREEN_TERRACOTTA => DyeColor::GREEN,
			Ids::LIGHT_BLUE_TERRACOTTA => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_TERRACOTTA => DyeColor::LIGHT_GRAY,
			Ids::LIME_TERRACOTTA => DyeColor::LIME,
			Ids::MAGENTA_TERRACOTTA => DyeColor::MAGENTA,
			Ids::ORANGE_TERRACOTTA => DyeColor::ORANGE,
			Ids::PINK_TERRACOTTA => DyeColor::PINK,
			Ids::PURPLE_TERRACOTTA => DyeColor::PURPLE,
			Ids::RED_TERRACOTTA => DyeColor::RED,
			Ids::WHITE_TERRACOTTA => DyeColor::WHITE,
			Ids::YELLOW_TERRACOTTA => DyeColor::YELLOW,
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::STAINED_CLAY()->setColor($color));
		}

		foreach([
			Ids::BLACK_STAINED_GLASS => DyeColor::BLACK,
			Ids::BLUE_STAINED_GLASS => DyeColor::BLUE,
			Ids::BROWN_STAINED_GLASS => DyeColor::BROWN,
			Ids::CYAN_STAINED_GLASS => DyeColor::CYAN,
			Ids::GRAY_STAINED_GLASS => DyeColor::GRAY,
			Ids::GREEN_STAINED_GLASS => DyeColor::GREEN,
			Ids::LIGHT_BLUE_STAINED_GLASS => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_STAINED_GLASS => DyeColor::LIGHT_GRAY,
			Ids::LIME_STAINED_GLASS => DyeColor::LIME,
			Ids::MAGENTA_STAINED_GLASS => DyeColor::MAGENTA,
			Ids::ORANGE_STAINED_GLASS => DyeColor::ORANGE,
			Ids::PINK_STAINED_GLASS => DyeColor::PINK,
			Ids::PURPLE_STAINED_GLASS => DyeColor::PURPLE,
			Ids::RED_STAINED_GLASS => DyeColor::RED,
			Ids::WHITE_STAINED_GLASS => DyeColor::WHITE,
			Ids::YELLOW_STAINED_GLASS => DyeColor::YELLOW,
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::STAINED_GLASS()->setColor($color));
		}

		foreach([
			Ids::BLACK_STAINED_GLASS_PANE => DyeColor::BLACK,
			Ids::BLUE_STAINED_GLASS_PANE => DyeColor::BLUE,
			Ids::BROWN_STAINED_GLASS_PANE => DyeColor::BROWN,
			Ids::CYAN_STAINED_GLASS_PANE => DyeColor::CYAN,
			Ids::GRAY_STAINED_GLASS_PANE => DyeColor::GRAY,
			Ids::GREEN_STAINED_GLASS_PANE => DyeColor::GREEN,
			Ids::LIGHT_BLUE_STAINED_GLASS_PANE => DyeColor::LIGHT_BLUE,
			Ids::LIGHT_GRAY_STAINED_GLASS_PANE => DyeColor::LIGHT_GRAY,
			Ids::LIME_STAINED_GLASS_PANE => DyeColor::LIME,
			Ids::MAGENTA_STAINED_GLASS_PANE => DyeColor::MAGENTA,
			Ids::ORANGE_STAINED_GLASS_PANE => DyeColor::ORANGE,
			Ids::PINK_STAINED_GLASS_PANE => DyeColor::PINK,
			Ids::PURPLE_STAINED_GLASS_PANE => DyeColor::PURPLE,
			Ids::RED_STAINED_GLASS_PANE => DyeColor::RED,
			Ids::WHITE_STAINED_GLASS_PANE => DyeColor::WHITE,
			Ids::YELLOW_STAINED_GLASS_PANE => DyeColor::YELLOW,
		] as $id => $color){
			$this->mapSimple($id, fn() => Blocks::STAINED_GLASS_PANE()->setColor($color));
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

	private function registerFlatWoodBlockDeserializers() : void{
		$this->map(Ids::ACACIA_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::ACACIA_BUTTON(), $in));
		$this->map(Ids::ACACIA_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::ACACIA_DOOR(), $in));
		$this->map(Ids::ACACIA_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::ACACIA_FENCE_GATE(), $in));
		$this->map(Ids::ACACIA_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::ACACIA_PRESSURE_PLATE(), $in));
		$this->map(Ids::ACACIA_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::ACACIA_SIGN(), $in));
		$this->map(Ids::ACACIA_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::ACACIA_TRAPDOOR(), $in));
		$this->map(Ids::ACACIA_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::ACACIA_WALL_SIGN(), $in));
		$this->mapLog(Ids::ACACIA_LOG, Ids::STRIPPED_ACACIA_LOG, fn() => Blocks::ACACIA_LOG());
		$this->mapLog(Ids::ACACIA_WOOD, Ids::STRIPPED_ACACIA_WOOD, fn() => Blocks::ACACIA_WOOD());
		$this->mapSimple(Ids::ACACIA_FENCE, fn() => Blocks::ACACIA_FENCE());
		$this->mapSimple(Ids::ACACIA_PLANKS, fn() => Blocks::ACACIA_PLANKS());
		$this->mapSlab(Ids::ACACIA_SLAB, Ids::ACACIA_DOUBLE_SLAB, fn() => Blocks::ACACIA_SLAB());
		$this->mapStairs(Ids::ACACIA_STAIRS, fn() => Blocks::ACACIA_STAIRS());

		$this->map(Ids::BIRCH_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::BIRCH_BUTTON(), $in));
		$this->map(Ids::BIRCH_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::BIRCH_DOOR(), $in));
		$this->map(Ids::BIRCH_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::BIRCH_FENCE_GATE(), $in));
		$this->map(Ids::BIRCH_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::BIRCH_PRESSURE_PLATE(), $in));
		$this->map(Ids::BIRCH_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::BIRCH_SIGN(), $in));
		$this->map(Ids::BIRCH_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::BIRCH_TRAPDOOR(), $in));
		$this->map(Ids::BIRCH_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::BIRCH_WALL_SIGN(), $in));
		$this->mapLog(Ids::BIRCH_LOG, Ids::STRIPPED_BIRCH_LOG, fn() => Blocks::BIRCH_LOG());
		$this->mapLog(Ids::BIRCH_WOOD, Ids::STRIPPED_BIRCH_WOOD, fn() => Blocks::BIRCH_WOOD());
		$this->mapSimple(Ids::BIRCH_FENCE, fn() => Blocks::BIRCH_FENCE());
		$this->mapSimple(Ids::BIRCH_PLANKS, fn() => Blocks::BIRCH_PLANKS());
		$this->mapSlab(Ids::BIRCH_SLAB, Ids::BIRCH_DOUBLE_SLAB, fn() => Blocks::BIRCH_SLAB());
		$this->mapStairs(Ids::BIRCH_STAIRS, fn() => Blocks::BIRCH_STAIRS());

		$this->map(Ids::CHERRY_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::CHERRY_BUTTON(), $in));
		$this->map(Ids::CHERRY_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::CHERRY_DOOR(), $in));
		$this->map(Ids::CHERRY_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::CHERRY_FENCE_GATE(), $in));
		$this->map(Ids::CHERRY_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::CHERRY_PRESSURE_PLATE(), $in));
		$this->map(Ids::CHERRY_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::CHERRY_SIGN(), $in));
		$this->map(Ids::CHERRY_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::CHERRY_TRAPDOOR(), $in));
		$this->map(Ids::CHERRY_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::CHERRY_WALL_SIGN(), $in));
		$this->mapLog(Ids::CHERRY_LOG, Ids::STRIPPED_CHERRY_LOG, fn() => Blocks::CHERRY_LOG());
		$this->mapSimple(Ids::CHERRY_FENCE, fn() => Blocks::CHERRY_FENCE());
		$this->mapSimple(Ids::CHERRY_PLANKS, fn() => Blocks::CHERRY_PLANKS());
		$this->mapSlab(Ids::CHERRY_SLAB, Ids::CHERRY_DOUBLE_SLAB, fn() => Blocks::CHERRY_SLAB());
		$this->mapStairs(Ids::CHERRY_STAIRS, fn() => Blocks::CHERRY_STAIRS());
		$this->map(Ids::CHERRY_WOOD, fn(Reader $in) => Helper::decodeLog(Blocks::CHERRY_WOOD(), false, $in));
		$this->map(Ids::STRIPPED_CHERRY_WOOD, fn(Reader $in) => Helper::decodeLog(Blocks::CHERRY_WOOD(), true, $in));

		$this->map(Ids::CRIMSON_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::CRIMSON_BUTTON(), $in));
		$this->map(Ids::CRIMSON_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::CRIMSON_DOOR(), $in));
		$this->map(Ids::CRIMSON_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::CRIMSON_FENCE_GATE(), $in));
		$this->map(Ids::CRIMSON_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::CRIMSON_PRESSURE_PLATE(), $in));
		$this->map(Ids::CRIMSON_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::CRIMSON_SIGN(), $in));
		$this->map(Ids::CRIMSON_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::CRIMSON_TRAPDOOR(), $in));
		$this->map(Ids::CRIMSON_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::CRIMSON_WALL_SIGN(), $in));
		$this->mapLog(Ids::CRIMSON_HYPHAE, Ids::STRIPPED_CRIMSON_HYPHAE, fn() => Blocks::CRIMSON_HYPHAE());
		$this->mapLog(Ids::CRIMSON_STEM, Ids::STRIPPED_CRIMSON_STEM, fn() => Blocks::CRIMSON_STEM());
		$this->mapSimple(Ids::CRIMSON_FENCE, fn() => Blocks::CRIMSON_FENCE());
		$this->mapSimple(Ids::CRIMSON_PLANKS, fn() => Blocks::CRIMSON_PLANKS());
		$this->mapSlab(Ids::CRIMSON_SLAB, Ids::CRIMSON_DOUBLE_SLAB, fn() => Blocks::CRIMSON_SLAB());
		$this->mapStairs(Ids::CRIMSON_STAIRS, fn() => Blocks::CRIMSON_STAIRS());

		$this->map(Ids::DARKOAK_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::DARK_OAK_SIGN(), $in));
		$this->map(Ids::DARKOAK_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::DARK_OAK_WALL_SIGN(), $in));
		$this->map(Ids::DARK_OAK_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::DARK_OAK_BUTTON(), $in));
		$this->map(Ids::DARK_OAK_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::DARK_OAK_DOOR(), $in));
		$this->map(Ids::DARK_OAK_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::DARK_OAK_FENCE_GATE(), $in));
		$this->map(Ids::DARK_OAK_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::DARK_OAK_PRESSURE_PLATE(), $in));
		$this->map(Ids::DARK_OAK_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::DARK_OAK_TRAPDOOR(), $in));
		$this->mapLog(Ids::DARK_OAK_LOG, Ids::STRIPPED_DARK_OAK_LOG, fn() => Blocks::DARK_OAK_LOG());
		$this->mapLog(Ids::DARK_OAK_WOOD, Ids::STRIPPED_DARK_OAK_WOOD, fn() => Blocks::DARK_OAK_WOOD());
		$this->mapSimple(Ids::DARK_OAK_FENCE, fn() => Blocks::DARK_OAK_FENCE());
		$this->mapSimple(Ids::DARK_OAK_PLANKS, fn() => Blocks::DARK_OAK_PLANKS());
		$this->mapSlab(Ids::DARK_OAK_SLAB, Ids::DARK_OAK_DOUBLE_SLAB, fn() => Blocks::DARK_OAK_SLAB());
		$this->mapStairs(Ids::DARK_OAK_STAIRS, fn() => Blocks::DARK_OAK_STAIRS());

		$this->map(Ids::JUNGLE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::JUNGLE_BUTTON(), $in));
		$this->map(Ids::JUNGLE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::JUNGLE_DOOR(), $in));
		$this->map(Ids::JUNGLE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::JUNGLE_FENCE_GATE(), $in));
		$this->map(Ids::JUNGLE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::JUNGLE_PRESSURE_PLATE(), $in));
		$this->map(Ids::JUNGLE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::JUNGLE_SIGN(), $in));
		$this->map(Ids::JUNGLE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::JUNGLE_TRAPDOOR(), $in));
		$this->map(Ids::JUNGLE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::JUNGLE_WALL_SIGN(), $in));
		$this->mapLog(Ids::JUNGLE_LOG, Ids::STRIPPED_JUNGLE_LOG, fn() => Blocks::JUNGLE_LOG());
		$this->mapLog(Ids::JUNGLE_WOOD, Ids::STRIPPED_JUNGLE_WOOD, fn() => Blocks::JUNGLE_WOOD());
		$this->mapSimple(Ids::JUNGLE_FENCE, fn() => Blocks::JUNGLE_FENCE());
		$this->mapSimple(Ids::JUNGLE_PLANKS, fn() => Blocks::JUNGLE_PLANKS());
		$this->mapSlab(Ids::JUNGLE_SLAB, Ids::JUNGLE_DOUBLE_SLAB, fn() => Blocks::JUNGLE_SLAB());
		$this->mapStairs(Ids::JUNGLE_STAIRS, fn() => Blocks::JUNGLE_STAIRS());

		$this->map(Ids::MANGROVE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::MANGROVE_BUTTON(), $in));
		$this->map(Ids::MANGROVE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::MANGROVE_DOOR(), $in));
		$this->map(Ids::MANGROVE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::MANGROVE_FENCE_GATE(), $in));
		$this->map(Ids::MANGROVE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::MANGROVE_PRESSURE_PLATE(), $in));
		$this->map(Ids::MANGROVE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::MANGROVE_SIGN(), $in));
		$this->map(Ids::MANGROVE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::MANGROVE_TRAPDOOR(), $in));
		$this->map(Ids::MANGROVE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::MANGROVE_WALL_SIGN(), $in));
		$this->mapLog(Ids::MANGROVE_LOG, Ids::STRIPPED_MANGROVE_LOG, fn() => Blocks::MANGROVE_LOG());
		$this->mapSimple(Ids::MANGROVE_FENCE, fn() => Blocks::MANGROVE_FENCE());
		$this->mapSimple(Ids::MANGROVE_PLANKS, fn() => Blocks::MANGROVE_PLANKS());
		$this->mapSlab(Ids::MANGROVE_SLAB, Ids::MANGROVE_DOUBLE_SLAB, fn() => Blocks::MANGROVE_SLAB());
		$this->mapStairs(Ids::MANGROVE_STAIRS, fn() => Blocks::MANGROVE_STAIRS());
		$this->map(Ids::MANGROVE_WOOD, fn(Reader $in) => Helper::decodeLog(Blocks::MANGROVE_WOOD(), false, $in));
		$this->map(Ids::STRIPPED_MANGROVE_WOOD, fn(Reader $in) => Helper::decodeLog(Blocks::MANGROVE_WOOD(), true, $in));

		//oak - due to age, many of these don't specify "oak", making for confusing reading
		$this->map(Ids::WOODEN_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::OAK_BUTTON(), $in));
		$this->map(Ids::WOODEN_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::OAK_DOOR(), $in));
		$this->map(Ids::FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::OAK_FENCE_GATE(), $in));
		$this->map(Ids::WOODEN_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::OAK_PRESSURE_PLATE(), $in));
		$this->map(Ids::STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::OAK_SIGN(), $in));
		$this->map(Ids::TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::OAK_TRAPDOOR(), $in));
		$this->map(Ids::WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::OAK_WALL_SIGN(), $in));
		$this->mapLog(Ids::OAK_LOG, Ids::STRIPPED_OAK_LOG, fn() => Blocks::OAK_LOG());
		$this->mapLog(Ids::OAK_WOOD, Ids::STRIPPED_OAK_WOOD, fn() => Blocks::OAK_WOOD());
		$this->mapSimple(Ids::OAK_FENCE, fn() => Blocks::OAK_FENCE());
		$this->mapSimple(Ids::OAK_PLANKS, fn() => Blocks::OAK_PLANKS());
		$this->mapSlab(Ids::OAK_SLAB, Ids::OAK_DOUBLE_SLAB, fn() => Blocks::OAK_SLAB());
		$this->mapStairs(Ids::OAK_STAIRS, fn() => Blocks::OAK_STAIRS());

		$this->map(Ids::SPRUCE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::SPRUCE_BUTTON(), $in));
		$this->map(Ids::SPRUCE_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::SPRUCE_DOOR(), $in));
		$this->map(Ids::SPRUCE_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::SPRUCE_FENCE_GATE(), $in));
		$this->map(Ids::SPRUCE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::SPRUCE_PRESSURE_PLATE(), $in));
		$this->map(Ids::SPRUCE_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::SPRUCE_SIGN(), $in));
		$this->map(Ids::SPRUCE_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::SPRUCE_TRAPDOOR(), $in));
		$this->map(Ids::SPRUCE_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::SPRUCE_WALL_SIGN(), $in));
		$this->mapLog(Ids::SPRUCE_LOG, Ids::STRIPPED_SPRUCE_LOG, fn() => Blocks::SPRUCE_LOG());
		$this->mapLog(Ids::SPRUCE_WOOD, Ids::STRIPPED_SPRUCE_WOOD, fn() => Blocks::SPRUCE_WOOD());
		$this->mapSimple(Ids::SPRUCE_FENCE, fn() => Blocks::SPRUCE_FENCE());
		$this->mapSimple(Ids::SPRUCE_PLANKS, fn() => Blocks::SPRUCE_PLANKS());
		$this->mapSlab(Ids::SPRUCE_SLAB, Ids::SPRUCE_DOUBLE_SLAB, fn() => Blocks::SPRUCE_SLAB());
		$this->mapStairs(Ids::SPRUCE_STAIRS, fn() => Blocks::SPRUCE_STAIRS());

		$this->map(Ids::WARPED_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::WARPED_BUTTON(), $in));
		$this->map(Ids::WARPED_DOOR, fn(Reader $in) => Helper::decodeDoor(Blocks::WARPED_DOOR(), $in));
		$this->map(Ids::WARPED_FENCE_GATE, fn(Reader $in) => Helper::decodeFenceGate(Blocks::WARPED_FENCE_GATE(), $in));
		$this->map(Ids::WARPED_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::WARPED_PRESSURE_PLATE(), $in));
		$this->map(Ids::WARPED_STANDING_SIGN, fn(Reader $in) => Helper::decodeFloorSign(Blocks::WARPED_SIGN(), $in));
		$this->map(Ids::WARPED_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Blocks::WARPED_TRAPDOOR(), $in));
		$this->map(Ids::WARPED_WALL_SIGN, fn(Reader $in) => Helper::decodeWallSign(Blocks::WARPED_WALL_SIGN(), $in));
		$this->mapLog(Ids::WARPED_HYPHAE, Ids::STRIPPED_WARPED_HYPHAE, fn() => Blocks::WARPED_HYPHAE());
		$this->mapLog(Ids::WARPED_STEM, Ids::STRIPPED_WARPED_STEM, fn() => Blocks::WARPED_STEM());
		$this->mapSimple(Ids::WARPED_FENCE, fn() => Blocks::WARPED_FENCE());
		$this->mapSimple(Ids::WARPED_PLANKS, fn() => Blocks::WARPED_PLANKS());
		$this->mapSlab(Ids::WARPED_SLAB, Ids::WARPED_DOUBLE_SLAB, fn() => Blocks::WARPED_SLAB());
		$this->mapStairs(Ids::WARPED_STAIRS, fn() => Blocks::WARPED_STAIRS());
	}

	private function registerLeavesDeserializers() : void{
		$this->map(Ids::ACACIA_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::ACACIA_LEAVES(), $in));
		$this->map(Ids::AZALEA_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::AZALEA_LEAVES(), $in));
		$this->map(Ids::AZALEA_LEAVES_FLOWERED, fn(Reader $in) => Helper::decodeLeaves(Blocks::FLOWERING_AZALEA_LEAVES(), $in));
		$this->map(Ids::BIRCH_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::BIRCH_LEAVES(), $in));
		$this->map(Ids::CHERRY_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::CHERRY_LEAVES(), $in));
		$this->map(Ids::DARK_OAK_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::DARK_OAK_LEAVES(), $in));
		$this->map(Ids::JUNGLE_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::JUNGLE_LEAVES(), $in));
		$this->map(Ids::MANGROVE_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::MANGROVE_LEAVES(), $in));
		$this->map(Ids::OAK_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::OAK_LEAVES(), $in));
		$this->map(Ids::SPRUCE_LEAVES, fn(Reader $in) => Helper::decodeLeaves(Blocks::SPRUCE_LEAVES(), $in));
	}

	private function registerSaplingDeserializers() : void{
		foreach([
			Ids::ACACIA_SAPLING => fn() => Blocks::ACACIA_SAPLING(),
			Ids::BIRCH_SAPLING => fn() => Blocks::BIRCH_SAPLING(),
			Ids::DARK_OAK_SAPLING => fn() => Blocks::DARK_OAK_SAPLING(),
			Ids::JUNGLE_SAPLING => fn() => Blocks::JUNGLE_SAPLING(),
			Ids::OAK_SAPLING => fn() => Blocks::OAK_SAPLING(),
			Ids::SPRUCE_SAPLING => fn() => Blocks::SPRUCE_SAPLING(),
		] as $id => $getBlock){
			$this->map($id, fn(Reader $in) => Helper::decodeSapling($getBlock(), $in));
		}
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

	private function registerMobHeadDeserializers() : void{
		foreach([
			Ids::CREEPER_HEAD => MobHeadType::CREEPER,
			Ids::DRAGON_HEAD => MobHeadType::DRAGON,
			Ids::PIGLIN_HEAD => MobHeadType::PIGLIN,
			Ids::PLAYER_HEAD => MobHeadType::PLAYER,
			Ids::SKELETON_SKULL => MobHeadType::SKELETON,
			Ids::WITHER_SKELETON_SKULL => MobHeadType::WITHER_SKELETON,
			Ids::ZOMBIE_HEAD => MobHeadType::ZOMBIE
		] as $id => $mobHeadType){
			$this->map($id, fn(Reader $in) => Blocks::MOB_HEAD()->setMobHeadType($mobHeadType)->setFacing($in->readFacingWithoutDown()));
		}
	}

	private function registerSimpleDeserializers() : void{
		$this->mapSimple(Ids::AIR, fn() => Blocks::AIR());
		$this->mapSimple(Ids::AMETHYST_BLOCK, fn() => Blocks::AMETHYST());
		$this->mapSimple(Ids::ANCIENT_DEBRIS, fn() => Blocks::ANCIENT_DEBRIS());
		$this->mapSimple(Ids::ANDESITE, fn() => Blocks::ANDESITE());
		$this->mapSimple(Ids::BARRIER, fn() => Blocks::BARRIER());
		$this->mapSimple(Ids::BEACON, fn() => Blocks::BEACON());
		$this->mapSimple(Ids::BLACKSTONE, fn() => Blocks::BLACKSTONE());
		$this->mapSimple(Ids::BLUE_ICE, fn() => Blocks::BLUE_ICE());
		$this->mapSimple(Ids::BOOKSHELF, fn() => Blocks::BOOKSHELF());
		$this->mapSimple(Ids::BRICK_BLOCK, fn() => Blocks::BRICKS());
		$this->mapSimple(Ids::BROWN_MUSHROOM, fn() => Blocks::BROWN_MUSHROOM());
		$this->mapSimple(Ids::BUDDING_AMETHYST, fn() => Blocks::BUDDING_AMETHYST());
		$this->mapSimple(Ids::CALCITE, fn() => Blocks::CALCITE());
		$this->mapSimple(Ids::CARTOGRAPHY_TABLE, fn() => Blocks::CARTOGRAPHY_TABLE());
		$this->mapSimple(Ids::CHEMICAL_HEAT, fn() => Blocks::CHEMICAL_HEAT());
		$this->mapSimple(Ids::CHISELED_DEEPSLATE, fn() => Blocks::CHISELED_DEEPSLATE());
		$this->mapSimple(Ids::CHISELED_NETHER_BRICKS, fn() => Blocks::CHISELED_NETHER_BRICKS());
		$this->mapSimple(Ids::CHISELED_POLISHED_BLACKSTONE, fn() => Blocks::CHISELED_POLISHED_BLACKSTONE());
		$this->mapSimple(Ids::CHISELED_RED_SANDSTONE, fn() => Blocks::CHISELED_RED_SANDSTONE());
		$this->mapSimple(Ids::CHISELED_SANDSTONE, fn() => Blocks::CHISELED_SANDSTONE());
		$this->mapSimple(Ids::CHISELED_STONE_BRICKS, fn() => Blocks::CHISELED_STONE_BRICKS());
		$this->mapSimple(Ids::CHISELED_TUFF, fn() => Blocks::CHISELED_TUFF());
		$this->mapSimple(Ids::CHISELED_TUFF_BRICKS, fn() => Blocks::CHISELED_TUFF_BRICKS());
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
		$this->mapSimple(Ids::CRACKED_STONE_BRICKS, fn() => Blocks::CRACKED_STONE_BRICKS());
		$this->mapSimple(Ids::CRAFTING_TABLE, fn() => Blocks::CRAFTING_TABLE());
		$this->mapSimple(Ids::CRIMSON_ROOTS, fn() => Blocks::CRIMSON_ROOTS());
		$this->mapSimple(Ids::CRYING_OBSIDIAN, fn() => Blocks::CRYING_OBSIDIAN());
		$this->mapSimple(Ids::CUT_RED_SANDSTONE, fn() => Blocks::CUT_RED_SANDSTONE());
		$this->mapSimple(Ids::CUT_SANDSTONE, fn() => Blocks::CUT_SANDSTONE());
		$this->mapSimple(Ids::DARK_PRISMARINE, fn() => Blocks::DARK_PRISMARINE());
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
		$this->mapSimple(Ids::DIORITE, fn() => Blocks::DIORITE());
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
		$this->mapSimple(Ids::FERN, fn() => Blocks::FERN());
		$this->mapSimple(Ids::FLETCHING_TABLE, fn() => Blocks::FLETCHING_TABLE());
		$this->mapSimple(Ids::GILDED_BLACKSTONE, fn() => Blocks::GILDED_BLACKSTONE());
		$this->mapSimple(Ids::GLASS, fn() => Blocks::GLASS());
		$this->mapSimple(Ids::GLASS_PANE, fn() => Blocks::GLASS_PANE());
		$this->mapSimple(Ids::GLOWINGOBSIDIAN, fn() => Blocks::GLOWING_OBSIDIAN());
		$this->mapSimple(Ids::GLOWSTONE, fn() => Blocks::GLOWSTONE());
		$this->mapSimple(Ids::GOLD_BLOCK, fn() => Blocks::GOLD());
		$this->mapSimple(Ids::GOLD_ORE, fn() => Blocks::GOLD_ORE());
		$this->mapSimple(Ids::GRANITE, fn() => Blocks::GRANITE());
		$this->mapSimple(Ids::GRASS_BLOCK, fn() => Blocks::GRASS());
		$this->mapSimple(Ids::GRASS_PATH, fn() => Blocks::GRASS_PATH());
		$this->mapSimple(Ids::GRAVEL, fn() => Blocks::GRAVEL());
		$this->mapSimple(Ids::HANGING_ROOTS, fn() => Blocks::HANGING_ROOTS());
		$this->mapSimple(Ids::HARD_GLASS, fn() => Blocks::HARDENED_GLASS());
		$this->mapSimple(Ids::HARD_GLASS_PANE, fn() => Blocks::HARDENED_GLASS_PANE());
		$this->mapSimple(Ids::HARDENED_CLAY, fn() => Blocks::HARDENED_CLAY());
		$this->mapSimple(Ids::HONEYCOMB_BLOCK, fn() => Blocks::HONEYCOMB());
		$this->mapSimple(Ids::ICE, fn() => Blocks::ICE());
		$this->mapSimple(Ids::INFESTED_CHISELED_STONE_BRICKS, fn() => Blocks::INFESTED_CHISELED_STONE_BRICK());
		$this->mapSimple(Ids::INFESTED_COBBLESTONE, fn() => Blocks::INFESTED_COBBLESTONE());
		$this->mapSimple(Ids::INFESTED_CRACKED_STONE_BRICKS, fn() => Blocks::INFESTED_CRACKED_STONE_BRICK());
		$this->mapSimple(Ids::INFESTED_MOSSY_STONE_BRICKS, fn() => Blocks::INFESTED_MOSSY_STONE_BRICK());
		$this->mapSimple(Ids::INFESTED_STONE, fn() => Blocks::INFESTED_STONE());
		$this->mapSimple(Ids::INFESTED_STONE_BRICKS, fn() => Blocks::INFESTED_STONE_BRICK());
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
		$this->mapSimple(Ids::MANGROVE_ROOTS, fn() => Blocks::MANGROVE_ROOTS());
		$this->mapSimple(Ids::MELON_BLOCK, fn() => Blocks::MELON());
		$this->mapSimple(Ids::MOB_SPAWNER, fn() => Blocks::MONSTER_SPAWNER());
		$this->mapSimple(Ids::MOSSY_COBBLESTONE, fn() => Blocks::MOSSY_COBBLESTONE());
		$this->mapSimple(Ids::MOSSY_STONE_BRICKS, fn() => Blocks::MOSSY_STONE_BRICKS());
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
		$this->mapSimple(Ids::POLISHED_ANDESITE, fn() => Blocks::POLISHED_ANDESITE());
		$this->mapSimple(Ids::POLISHED_BLACKSTONE, fn() => Blocks::POLISHED_BLACKSTONE());
		$this->mapSimple(Ids::POLISHED_BLACKSTONE_BRICKS, fn() => Blocks::POLISHED_BLACKSTONE_BRICKS());
		$this->mapSimple(Ids::POLISHED_DEEPSLATE, fn() => Blocks::POLISHED_DEEPSLATE());
		$this->mapSimple(Ids::POLISHED_DIORITE, fn() => Blocks::POLISHED_DIORITE());
		$this->mapSimple(Ids::POLISHED_GRANITE, fn() => Blocks::POLISHED_GRANITE());
		$this->mapSimple(Ids::POLISHED_TUFF, fn() => Blocks::POLISHED_TUFF());
		$this->mapSimple(Ids::PRISMARINE, fn() => Blocks::PRISMARINE());
		$this->mapSimple(Ids::PRISMARINE_BRICKS, fn() => Blocks::PRISMARINE_BRICKS());
		$this->mapSimple(Ids::QUARTZ_BRICKS, fn() => Blocks::QUARTZ_BRICKS());
		$this->mapSimple(Ids::QUARTZ_ORE, fn() => Blocks::NETHER_QUARTZ_ORE());
		$this->mapSimple(Ids::RAW_COPPER_BLOCK, fn() => Blocks::RAW_COPPER());
		$this->mapSimple(Ids::RAW_GOLD_BLOCK, fn() => Blocks::RAW_GOLD());
		$this->mapSimple(Ids::RAW_IRON_BLOCK, fn() => Blocks::RAW_IRON());
		$this->mapSimple(Ids::RED_MUSHROOM, fn() => Blocks::RED_MUSHROOM());
		$this->mapSimple(Ids::RED_NETHER_BRICK, fn() => Blocks::RED_NETHER_BRICKS());
		$this->mapSimple(Ids::RED_SAND, fn() => Blocks::RED_SAND());
		$this->mapSimple(Ids::RED_SANDSTONE, fn() => Blocks::RED_SANDSTONE());
		$this->mapSimple(Ids::REDSTONE_BLOCK, fn() => Blocks::REDSTONE());
		$this->mapSimple(Ids::REINFORCED_DEEPSLATE, fn() => Blocks::REINFORCED_DEEPSLATE());
		$this->mapSimple(Ids::RESERVED6, fn() => Blocks::RESERVED6());
		$this->mapSimple(Ids::SAND, fn() => Blocks::SAND());
		$this->mapSimple(Ids::SANDSTONE, fn() => Blocks::SANDSTONE());
		$this->mapSimple(Ids::SCULK, fn() => Blocks::SCULK());
		$this->mapSimple(Ids::SEA_LANTERN, fn() => Blocks::SEA_LANTERN());
		$this->mapSimple(Ids::SHORT_GRASS, fn() => Blocks::TALL_GRASS()); //no, this is not a typo - tall_grass is now the double block, just to be confusing :(
		$this->mapSimple(Ids::SHROOMLIGHT, fn() => Blocks::SHROOMLIGHT());
		$this->mapSimple(Ids::SLIME, fn() => Blocks::SLIME());
		$this->mapSimple(Ids::SMITHING_TABLE, fn() => Blocks::SMITHING_TABLE());
		$this->mapSimple(Ids::SMOOTH_BASALT, fn() => Blocks::SMOOTH_BASALT());
		$this->mapSimple(Ids::SMOOTH_RED_SANDSTONE, fn() => Blocks::SMOOTH_RED_SANDSTONE());
		$this->mapSimple(Ids::SMOOTH_SANDSTONE, fn() => Blocks::SMOOTH_SANDSTONE());
		$this->mapSimple(Ids::SMOOTH_STONE, fn() => Blocks::SMOOTH_STONE());
		$this->mapSimple(Ids::SNOW, fn() => Blocks::SNOW());
		$this->mapSimple(Ids::SOUL_SAND, fn() => Blocks::SOUL_SAND());
		$this->mapSimple(Ids::SOUL_SOIL, fn() => Blocks::SOUL_SOIL());
		$this->mapSimple(Ids::SPORE_BLOSSOM, fn() => Blocks::SPORE_BLOSSOM());
		$this->mapSimple(Ids::SPONGE, fn() => Blocks::SPONGE());
		$this->mapSimple(Ids::STONE, fn() => Blocks::STONE());
		$this->mapSimple(Ids::STONECUTTER, fn() => Blocks::LEGACY_STONECUTTER());
		$this->mapSimple(Ids::STONE_BRICKS, fn() => Blocks::STONE_BRICKS());
		$this->mapSimple(Ids::TINTED_GLASS, fn() => Blocks::TINTED_GLASS());
		$this->mapSimple(Ids::TORCHFLOWER, fn() => Blocks::TORCHFLOWER());
		$this->mapSimple(Ids::TUFF, fn() => Blocks::TUFF());
		$this->mapSimple(Ids::TUFF_BRICKS, fn() => Blocks::TUFF_BRICKS());
		$this->mapSimple(Ids::UNDYED_SHULKER_BOX, fn() => Blocks::SHULKER_BOX());
		$this->mapSimple(Ids::WARPED_WART_BLOCK, fn() => Blocks::WARPED_WART_BLOCK());
		$this->mapSimple(Ids::WARPED_ROOTS, fn() => Blocks::WARPED_ROOTS());
		$this->mapSimple(Ids::WATERLILY, fn() => Blocks::LILY_PAD());
		$this->mapSimple(Ids::WEB, fn() => Blocks::COBWEB());
		$this->mapSimple(Ids::WET_SPONGE, fn() => Blocks::SPONGE()->setWet(true));
		$this->mapSimple(Ids::WITHER_ROSE, fn() => Blocks::WITHER_ROSE());
		$this->mapSimple(Ids::DANDELION, fn() => Blocks::DANDELION());

		$this->mapSimple(Ids::ALLIUM, fn() => Blocks::ALLIUM());
		$this->mapSimple(Ids::CORNFLOWER, fn() => Blocks::CORNFLOWER());
		$this->mapSimple(Ids::AZURE_BLUET, fn() => Blocks::AZURE_BLUET());
		$this->mapSimple(Ids::LILY_OF_THE_VALLEY, fn() => Blocks::LILY_OF_THE_VALLEY());
		$this->mapSimple(Ids::BLUE_ORCHID, fn() => Blocks::BLUE_ORCHID());
		$this->mapSimple(Ids::OXEYE_DAISY, fn() => Blocks::OXEYE_DAISY());
		$this->mapSimple(Ids::POPPY, fn() => Blocks::POPPY());
		$this->mapSimple(Ids::ORANGE_TULIP, fn() => Blocks::ORANGE_TULIP());
		$this->mapSimple(Ids::PINK_TULIP, fn() => Blocks::PINK_TULIP());
		$this->mapSimple(Ids::RED_TULIP, fn() => Blocks::RED_TULIP());
		$this->mapSimple(Ids::WHITE_TULIP, fn() => Blocks::WHITE_TULIP());
	}

	private function registerDeserializers() : void{
		$this->map(Ids::ACTIVATOR_RAIL, function(Reader $in) : Block{
			return Blocks::ACTIVATOR_RAIL()
				->setPowered($in->readBool(StateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->map(Ids::AMETHYST_CLUSTER, function(Reader $in) : Block{
			return Blocks::AMETHYST_CLUSTER()
				->setStage(AmethystCluster::STAGE_CLUSTER)
				->setFacing($in->readBlockFace());
		});
		$this->mapSlab(Ids::ANDESITE_SLAB, Ids::ANDESITE_DOUBLE_SLAB, fn() => Blocks::ANDESITE_SLAB());
		$this->mapStairs(Ids::ANDESITE_STAIRS, fn() => Blocks::ANDESITE_STAIRS());
		$this->map(Ids::ANDESITE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::ANDESITE_WALL(), $in));
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
		$this->map(Ids::BAMBOO_SAPLING, function(Reader $in) : Block{
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
		$this->map(Ids::BIG_DRIPLEAF, function(Reader $in) : Block{
			if($in->readBool(StateNames::BIG_DRIPLEAF_HEAD)){
				return Blocks::BIG_DRIPLEAF_HEAD()
					->setFacing($in->readCardinalHorizontalFacing())
					->setLeafState(match($type = $in->readString(StateNames::BIG_DRIPLEAF_TILT)){
						StringValues::BIG_DRIPLEAF_TILT_NONE => DripleafState::STABLE,
						StringValues::BIG_DRIPLEAF_TILT_UNSTABLE => DripleafState::UNSTABLE,
						StringValues::BIG_DRIPLEAF_TILT_PARTIAL_TILT => DripleafState::PARTIAL_TILT,
						StringValues::BIG_DRIPLEAF_TILT_FULL_TILT => DripleafState::FULL_TILT,
						default => throw $in->badValueException(StateNames::BIG_DRIPLEAF_TILT, $type),
					});
			}else{
				$in->ignored(StateNames::BIG_DRIPLEAF_TILT);
				return Blocks::BIG_DRIPLEAF_STEM()->setFacing($in->readCardinalHorizontalFacing());
			}
		});
		$this->mapSlab(Ids::BLACKSTONE_SLAB, Ids::BLACKSTONE_DOUBLE_SLAB, fn() => Blocks::BLACKSTONE_SLAB());
		$this->mapStairs(Ids::BLACKSTONE_STAIRS, fn() => Blocks::BLACKSTONE_STAIRS());
		$this->map(Ids::BLACKSTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::BLACKSTONE_WALL(), $in));
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
		$this->mapSlab(Ids::BRICK_SLAB, Ids::BRICK_DOUBLE_SLAB, fn() => Blocks::BRICK_SLAB());
		$this->mapStairs(Ids::BRICK_STAIRS, fn() => Blocks::BRICK_STAIRS());
		$this->map(Ids::BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::BRICK_WALL(), $in));
		$this->map(Ids::MUSHROOM_STEM, fn(Reader $in) => match($in->readBoundedInt(StateNames::HUGE_MUSHROOM_BITS, 0, 15)){
			BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM => Blocks::ALL_SIDED_MUSHROOM_STEM(),
			BlockLegacyMetadata::MUSHROOM_BLOCK_STEM => Blocks::MUSHROOM_STEM(),
			default => throw new BlockStateDeserializeException("This state does not exist"),
		});
		$this->map(Ids::BROWN_MUSHROOM_BLOCK, fn(Reader $in) => Helper::decodeMushroomBlock(Blocks::BROWN_MUSHROOM_BLOCK(), $in));
		$this->map(Ids::CACTUS, function(Reader $in) : Block{
			return Blocks::CACTUS()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 15));
		});
		$this->map(Ids::CAKE, function(Reader $in) : Block{
			return Blocks::CAKE()
				->setBites($in->readBoundedInt(StateNames::BITE_COUNTER, 0, 6));
		});
		$this->map(Ids::CAMPFIRE, function(Reader $in) : Block{
			return Blocks::CAMPFIRE()
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(!$in->readBool(StateNames::EXTINGUISHED));
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
		$this->map(Ids::CHISELED_QUARTZ_BLOCK, function(Reader $in) : Block{
			return Blocks::CHISELED_QUARTZ()
				->setAxis($in->readPillarAxis());
		});
		$this->map(Ids::CHEST, function(Reader $in) : Block{
			return Blocks::CHEST()
				->setFacing($in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::CHORUS_FLOWER, function(Reader $in) : Block{
			return Blocks::CHORUS_FLOWER()
				->setAge($in->readBoundedInt(StateNames::AGE, ChorusFlower::MIN_AGE, ChorusFlower::MAX_AGE));
		});
		$this->map(Ids::COARSE_DIRT, fn() => Blocks::DIRT()->setDirtType(DirtType::COARSE));
		$this->mapSlab(Ids::COBBLED_DEEPSLATE_SLAB, Ids::COBBLED_DEEPSLATE_DOUBLE_SLAB, fn() => Blocks::COBBLED_DEEPSLATE_SLAB());
		$this->mapStairs(Ids::COBBLED_DEEPSLATE_STAIRS, fn() => Blocks::COBBLED_DEEPSLATE_STAIRS());
		$this->map(Ids::COBBLED_DEEPSLATE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::COBBLED_DEEPSLATE_WALL(), $in));
		$this->mapSlab(Ids::COBBLESTONE_SLAB, Ids::COBBLESTONE_DOUBLE_SLAB, fn() => Blocks::COBBLESTONE_SLAB());
		$this->map(Ids::COBBLESTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::COBBLESTONE_WALL(), $in));
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
		$this->map(Ids::COPPER_BLOCK, fn() => Helper::decodeCopper(Blocks::COPPER(), CopperOxidation::NONE));
		$this->map(Ids::COPPER_BULB, function(Reader $in) : Block{
			return Helper::decodeCopper(Blocks::COPPER_BULB(), CopperOxidation::NONE)
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::COPPER_DOOR, fn(Reader $in) => Helper::decodeDoor(Helper::decodeCopper(Blocks::COPPER_DOOR(), CopperOxidation::NONE), $in));
		$this->map(Ids::COPPER_GRATE, fn() => Helper::decodeCopper(Blocks::COPPER_GRATE(), CopperOxidation::NONE));
		$this->map(Ids::COPPER_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Helper::decodeCopper(Blocks::COPPER_TRAPDOOR(), CopperOxidation::NONE), $in));
		$this->map(Ids::CUT_COPPER, fn() => Helper::decodeCopper(Blocks::CUT_COPPER(), CopperOxidation::NONE));
		$this->mapSlab(Ids::CUT_COPPER_SLAB, Ids::DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::NONE));
		$this->mapStairs(Ids::CUT_COPPER_STAIRS, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::NONE));
		$this->mapSlab(Ids::CUT_RED_SANDSTONE_SLAB, Ids::CUT_RED_SANDSTONE_DOUBLE_SLAB, fn() => Blocks::CUT_RED_SANDSTONE_SLAB());
		$this->mapSlab(Ids::CUT_SANDSTONE_SLAB, Ids::CUT_SANDSTONE_DOUBLE_SLAB, fn() => Blocks::CUT_SANDSTONE_SLAB());
		$this->mapSlab(Ids::DARK_PRISMARINE_SLAB, Ids::DARK_PRISMARINE_DOUBLE_SLAB, fn() => Blocks::DARK_PRISMARINE_SLAB());
		$this->mapStairs(Ids::DARK_PRISMARINE_STAIRS, fn() => Blocks::DARK_PRISMARINE_STAIRS());
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
		$this->mapSlab(Ids::DIORITE_SLAB, Ids::DIORITE_DOUBLE_SLAB, fn() => Blocks::DIORITE_SLAB());
		$this->mapStairs(Ids::DIORITE_STAIRS, fn() => Blocks::DIORITE_STAIRS());
		$this->map(Ids::DIORITE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::DIORITE_WALL(), $in));
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
		$this->mapStairs(Ids::END_BRICK_STAIRS, fn() => Blocks::END_STONE_BRICK_STAIRS());
		$this->map(Ids::END_STONE_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::END_STONE_BRICK_WALL(), $in));
		$this->map(Ids::END_PORTAL_FRAME, function(Reader $in) : Block{
			return Blocks::END_PORTAL_FRAME()
				->setEye($in->readBool(StateNames::END_PORTAL_EYE_BIT))
				->setFacing($in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::END_ROD, function(Reader $in) : Block{
			return Blocks::END_ROD()
				->setFacing($in->readEndRodFacingDirection());
		});
		$this->mapSlab(Ids::END_STONE_BRICK_SLAB, Ids::END_STONE_BRICK_DOUBLE_SLAB, fn() => Blocks::END_STONE_BRICK_SLAB());
		$this->map(Ids::ENDER_CHEST, function(Reader $in) : Block{
			return Blocks::ENDER_CHEST()
				->setFacing($in->readCardinalHorizontalFacing());
		});
		$this->map(Ids::EXPOSED_COPPER, fn() => Helper::decodeCopper(Blocks::COPPER(), CopperOxidation::EXPOSED));
		$this->map(Ids::EXPOSED_CHISELED_COPPER, fn() => Helper::decodeCopper(Blocks::CHISELED_COPPER(), CopperOxidation::EXPOSED));
		$this->map(Ids::EXPOSED_COPPER_GRATE, fn() => Helper::decodeCopper(Blocks::COPPER_GRATE(), CopperOxidation::EXPOSED));
		$this->map(Ids::EXPOSED_CUT_COPPER, fn() => Helper::decodeCopper(Blocks::CUT_COPPER(), CopperOxidation::EXPOSED));
		$this->mapSlab(Ids::EXPOSED_CUT_COPPER_SLAB, Ids::EXPOSED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::EXPOSED));
		$this->mapStairs(Ids::EXPOSED_CUT_COPPER_STAIRS, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::EXPOSED));
		$this->map(Ids::EXPOSED_COPPER_BULB, function(Reader $in) : Block{
			return Helper::decodeCopper(Blocks::COPPER_BULB(), CopperOxidation::EXPOSED)
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::EXPOSED_COPPER_DOOR, fn(Reader $in) => Helper::decodeDoor(Helper::decodeCopper(Blocks::COPPER_DOOR(), CopperOxidation::EXPOSED), $in));
		$this->map(Ids::EXPOSED_COPPER_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Helper::decodeCopper(Blocks::COPPER_TRAPDOOR(), CopperOxidation::EXPOSED), $in));
		$this->map(Ids::FARMLAND, function(Reader $in) : Block{
			return Blocks::FARMLAND()
				->setWetness($in->readBoundedInt(StateNames::MOISTURIZED_AMOUNT, 0, 7));
		});
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
				->setFacing($in->readCardinalHorizontalFacing())
				->setLit(false);
		});
		$this->map(Ids::GLOW_LICHEN, fn(Reader $in) => Blocks::GLOW_LICHEN()->setFaces($in->readFacingFlags()));
		$this->map(Ids::GLOW_FRAME, fn(Reader $in) => Helper::decodeItemFrame(Blocks::GLOWING_ITEM_FRAME(), $in));
		$this->map(Ids::GOLDEN_RAIL, function(Reader $in) : Block{
			return Blocks::POWERED_RAIL()
				->setPowered($in->readBool(StateNames::RAIL_DATA_BIT))
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 5));
		});
		$this->mapSlab(Ids::GRANITE_SLAB, Ids::GRANITE_DOUBLE_SLAB, fn() => Blocks::GRANITE_SLAB());
		$this->mapStairs(Ids::GRANITE_STAIRS, fn() => Blocks::GRANITE_STAIRS());
		$this->map(Ids::GRANITE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::GRANITE_WALL(), $in));
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
		$this->map(Ids::LAB_TABLE, fn(Reader $in) => Blocks::LAB_TABLE()
			->setFacing(Facing::opposite($in->readLegacyHorizontalFacing()))
		);
		$this->map(Ids::LADDER, function(Reader $in) : Block{
			return Blocks::LADDER()
				->setFacing($in->readHorizontalFacing());
		});
		$this->map(Ids::LANTERN, function(Reader $in) : Block{
			return Blocks::LANTERN()
				->setHanging($in->readBool(StateNames::HANGING));
		});
		$this->map(Ids::LARGE_AMETHYST_BUD, function(Reader $in) : Block{
			return Blocks::AMETHYST_CLUSTER()
				->setStage(AmethystCluster::STAGE_LARGE_BUD)
				->setFacing($in->readBlockFace());
		});
		$this->map(Ids::LAVA, fn(Reader $in) => Helper::decodeStillLiquid(Blocks::LAVA(), $in));
		$this->map(Ids::LECTERN, function(Reader $in) : Block{
			return Blocks::LECTERN()
				->setFacing($in->readCardinalHorizontalFacing())
				->setProducingSignal($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::LEVER, function(Reader $in) : Block{
			return Blocks::LEVER()
				->setActivated($in->readBool(StateNames::OPEN_BIT))
				->setFacing(match($value = $in->readString(StateNames::LEVER_DIRECTION)){
					StringValues::LEVER_DIRECTION_DOWN_NORTH_SOUTH => LeverFacing::DOWN_AXIS_Z,
					StringValues::LEVER_DIRECTION_DOWN_EAST_WEST => LeverFacing::DOWN_AXIS_X,
					StringValues::LEVER_DIRECTION_UP_NORTH_SOUTH => LeverFacing::UP_AXIS_Z,
					StringValues::LEVER_DIRECTION_UP_EAST_WEST => LeverFacing::UP_AXIS_X,
					StringValues::LEVER_DIRECTION_NORTH => LeverFacing::NORTH,
					StringValues::LEVER_DIRECTION_SOUTH => LeverFacing::SOUTH,
					StringValues::LEVER_DIRECTION_WEST => LeverFacing::WEST,
					StringValues::LEVER_DIRECTION_EAST => LeverFacing::EAST,
					default => throw $in->badValueException(StateNames::LEVER_DIRECTION, $value),
				});
		});
		$this->map(Ids::LIGHTNING_ROD, function(Reader $in) : Block{
			return Blocks::LIGHTNING_ROD()
				->setFacing($in->readFacingDirection());
		});
		$this->map(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeWeightedPressurePlate(Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT(), $in));
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
		$this->mapSlab(Ids::MOSSY_COBBLESTONE_SLAB, Ids::MOSSY_COBBLESTONE_DOUBLE_SLAB, fn() => Blocks::MOSSY_COBBLESTONE_SLAB());
		$this->mapStairs(Ids::MOSSY_COBBLESTONE_STAIRS, fn() => Blocks::MOSSY_COBBLESTONE_STAIRS());
		$this->map(Ids::MOSSY_COBBLESTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::MOSSY_COBBLESTONE_WALL(), $in));
		$this->mapSlab(Ids::MOSSY_STONE_BRICK_SLAB, Ids::MOSSY_STONE_BRICK_DOUBLE_SLAB, fn() => Blocks::MOSSY_STONE_BRICK_SLAB());
		$this->mapStairs(Ids::MOSSY_STONE_BRICK_STAIRS, fn() => Blocks::MOSSY_STONE_BRICK_STAIRS());
		$this->map(Ids::MOSSY_STONE_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::MOSSY_STONE_BRICK_WALL(), $in));
		$this->mapSlab(Ids::MUD_BRICK_SLAB, Ids::MUD_BRICK_DOUBLE_SLAB, fn() => Blocks::MUD_BRICK_SLAB());
		$this->mapStairs(Ids::MUD_BRICK_STAIRS, fn() => Blocks::MUD_BRICK_STAIRS());
		$this->map(Ids::MUD_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::MUD_BRICK_WALL(), $in));
		$this->map(Ids::MUDDY_MANGROVE_ROOTS, function(Reader $in) : Block{
			return Blocks::MUDDY_MANGROVE_ROOTS()
				->setAxis($in->readPillarAxis());
		});
		$this->mapSlab(Ids::NETHER_BRICK_SLAB, Ids::NETHER_BRICK_DOUBLE_SLAB, fn() => Blocks::NETHER_BRICK_SLAB());
		$this->mapStairs(Ids::NETHER_BRICK_STAIRS, fn() => Blocks::NETHER_BRICK_STAIRS());
		$this->map(Ids::NETHER_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::NETHER_BRICK_WALL(), $in));
		$this->map(Ids::NETHER_WART, function(Reader $in) : Block{
			return Blocks::NETHER_WART()
				->setAge($in->readBoundedInt(StateNames::AGE, 0, 3));
		});
		$this->mapSlab(Ids::NORMAL_STONE_SLAB, Ids::NORMAL_STONE_DOUBLE_SLAB, fn() => Blocks::STONE_SLAB());
		$this->mapStairs(Ids::NORMAL_STONE_STAIRS, fn() => Blocks::STONE_STAIRS());
		$this->map(Ids::OCHRE_FROGLIGHT, fn(Reader $in) => Blocks::FROGLIGHT()->setFroglightType(FroglightType::OCHRE)->setAxis($in->readPillarAxis()));
		$this->map(Ids::OXIDIZED_COPPER, fn() => Helper::decodeCopper(Blocks::COPPER(), CopperOxidation::OXIDIZED));
		$this->map(Ids::OXIDIZED_CHISELED_COPPER, fn() => Helper::decodeCopper(Blocks::CHISELED_COPPER(), CopperOxidation::OXIDIZED));
		$this->map(Ids::OXIDIZED_COPPER_GRATE, fn() => Helper::decodeCopper(Blocks::COPPER_GRATE(), CopperOxidation::OXIDIZED));
		$this->map(Ids::OXIDIZED_CUT_COPPER, fn() => Helper::decodeCopper(Blocks::CUT_COPPER(), CopperOxidation::OXIDIZED));
		$this->mapSlab(Ids::OXIDIZED_CUT_COPPER_SLAB, Ids::OXIDIZED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::OXIDIZED));
		$this->mapStairs(Ids::OXIDIZED_CUT_COPPER_STAIRS, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::OXIDIZED));
		$this->map(Ids::OXIDIZED_COPPER_BULB, function(Reader $in) : Block{
			return Helper::decodeCopper(Blocks::COPPER_BULB(), CopperOxidation::OXIDIZED)
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::OXIDIZED_COPPER_DOOR, fn(Reader $in) => Helper::decodeDoor(Helper::decodeCopper(Blocks::COPPER_DOOR(), CopperOxidation::OXIDIZED), $in));
		$this->map(Ids::OXIDIZED_COPPER_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Helper::decodeCopper(Blocks::COPPER_TRAPDOOR(), CopperOxidation::OXIDIZED), $in));
		$this->map(Ids::PEARLESCENT_FROGLIGHT, fn(Reader $in) => Blocks::FROGLIGHT()->setFroglightType(FroglightType::PEARLESCENT)->setAxis($in->readPillarAxis()));
		$this->mapSlab(Ids::PETRIFIED_OAK_SLAB, Ids::PETRIFIED_OAK_DOUBLE_SLAB, fn() => Blocks::FAKE_WOODEN_SLAB());
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
		$this->map(Ids::PITCHER_PLANT, function(Reader $in) : Block{
			return Blocks::PITCHER_PLANT()
				->setTop($in->readBool(StateNames::UPPER_BLOCK_BIT));
		});
		$this->mapSlab(Ids::POLISHED_ANDESITE_SLAB, Ids::POLISHED_ANDESITE_DOUBLE_SLAB, fn() => Blocks::POLISHED_ANDESITE_SLAB());
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
		$this->mapSlab(Ids::POLISHED_DIORITE_SLAB, Ids::POLISHED_DIORITE_DOUBLE_SLAB, fn() => Blocks::POLISHED_DIORITE_SLAB());
		$this->mapStairs(Ids::POLISHED_DIORITE_STAIRS, fn() => Blocks::POLISHED_DIORITE_STAIRS());
		$this->mapSlab(Ids::POLISHED_GRANITE_SLAB, Ids::POLISHED_GRANITE_DOUBLE_SLAB, fn() => Blocks::POLISHED_GRANITE_SLAB());
		$this->mapStairs(Ids::POLISHED_GRANITE_STAIRS, fn() => Blocks::POLISHED_GRANITE_STAIRS());
		$this->mapSlab(Ids::POLISHED_TUFF_SLAB, Ids::POLISHED_TUFF_DOUBLE_SLAB, fn() => Blocks::POLISHED_TUFF_SLAB());
		$this->mapStairs(Ids::POLISHED_TUFF_STAIRS, fn() => Blocks::POLISHED_TUFF_STAIRS());
		$this->map(Ids::POLISHED_TUFF_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::POLISHED_TUFF_WALL(), $in));
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
		$this->mapSlab(Ids::PRISMARINE_BRICK_SLAB, Ids::PRISMARINE_BRICK_DOUBLE_SLAB, fn() => Blocks::PRISMARINE_BRICKS_SLAB());
		$this->mapStairs(Ids::PRISMARINE_BRICKS_STAIRS, fn() => Blocks::PRISMARINE_BRICKS_STAIRS());
		$this->map(Ids::PRISMARINE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::PRISMARINE_WALL(), $in));
		$this->mapSlab(Ids::PRISMARINE_SLAB, Ids::PRISMARINE_DOUBLE_SLAB, fn() => Blocks::PRISMARINE_SLAB());
		$this->mapStairs(Ids::PRISMARINE_STAIRS, fn() => Blocks::PRISMARINE_STAIRS());
		$this->map(Ids::PUMPKIN, function(Reader $in) : Block{
			$in->ignored(StateNames::MC_CARDINAL_DIRECTION); //obsolete
			return Blocks::PUMPKIN();
		});
		$this->map(Ids::PUMPKIN_STEM, fn(Reader $in) => Helper::decodeStem(Blocks::PUMPKIN_STEM(), $in));
		$this->map(Ids::PURPUR_BLOCK, function(Reader $in) : Block{
			$in->ignored(StateNames::PILLAR_AXIS); //???
			return Blocks::PURPUR();
		});
		$this->map(Ids::PURPUR_PILLAR, fn(Reader $in) => Blocks::PURPUR_PILLAR()->setAxis($in->readPillarAxis()));
		$this->mapSlab(Ids::PURPUR_SLAB, Ids::PURPUR_DOUBLE_SLAB, fn() => Blocks::PURPUR_SLAB());
		$this->mapStairs(Ids::PURPUR_STAIRS, fn() => Blocks::PURPUR_STAIRS());
		$this->map(Ids::QUARTZ_BLOCK, function(Reader $in) : Opaque{
			$in->ignored(StateNames::PILLAR_AXIS);
			return Blocks::QUARTZ();
		});
		$this->map(Ids::QUARTZ_PILLAR, function(Reader $in) : Block{
			return Blocks::QUARTZ_PILLAR()
				->setAxis($in->readPillarAxis());
		});
		$this->mapSlab(Ids::QUARTZ_SLAB, Ids::QUARTZ_DOUBLE_SLAB, fn() => Blocks::QUARTZ_SLAB());
		$this->mapStairs(Ids::QUARTZ_STAIRS, fn() => Blocks::QUARTZ_STAIRS());
		$this->map(Ids::RAIL, function(Reader $in) : Block{
			return Blocks::RAIL()
				->setShape($in->readBoundedInt(StateNames::RAIL_DIRECTION, 0, 9));
		});
		$this->map(Ids::RED_MUSHROOM_BLOCK, fn(Reader $in) => Helper::decodeMushroomBlock(Blocks::RED_MUSHROOM_BLOCK(), $in));
		$this->mapSlab(Ids::RED_NETHER_BRICK_SLAB, Ids::RED_NETHER_BRICK_DOUBLE_SLAB, fn() => Blocks::RED_NETHER_BRICK_SLAB());
		$this->mapStairs(Ids::RED_NETHER_BRICK_STAIRS, fn() => Blocks::RED_NETHER_BRICK_STAIRS());
		$this->map(Ids::RED_NETHER_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::RED_NETHER_BRICK_WALL(), $in));
		$this->mapSlab(Ids::RED_SANDSTONE_SLAB, Ids::RED_SANDSTONE_DOUBLE_SLAB, fn() => Blocks::RED_SANDSTONE_SLAB());
		$this->mapStairs(Ids::RED_SANDSTONE_STAIRS, fn() => Blocks::RED_SANDSTONE_STAIRS());
		$this->map(Ids::RED_SANDSTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::RED_SANDSTONE_WALL(), $in));
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
		$this->mapSlab(Ids::SANDSTONE_SLAB, Ids::SANDSTONE_DOUBLE_SLAB, fn() => Blocks::SANDSTONE_SLAB());
		$this->mapStairs(Ids::SANDSTONE_STAIRS, fn() => Blocks::SANDSTONE_STAIRS());
		$this->map(Ids::SANDSTONE_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::SANDSTONE_WALL(), $in));
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
		$this->map(Ids::SMALL_DRIPLEAF_BLOCK, function(Reader $in) : Block{
			return Blocks::SMALL_DRIPLEAF()
				->setFacing($in->readCardinalHorizontalFacing())
				->setTop($in->readBool(StateNames::UPPER_BLOCK_BIT));
		});
		$this->map(Ids::SMOOTH_QUARTZ, function(Reader $in) : Block{
			$in->ignored(StateNames::PILLAR_AXIS);
			return Blocks::SMOOTH_QUARTZ();
		});
		$this->mapSlab(Ids::SMOOTH_QUARTZ_SLAB, Ids::SMOOTH_QUARTZ_DOUBLE_SLAB, fn() => Blocks::SMOOTH_QUARTZ_SLAB());
		$this->mapStairs(Ids::SMOOTH_QUARTZ_STAIRS, fn() => Blocks::SMOOTH_QUARTZ_STAIRS());
		$this->mapSlab(Ids::SMOOTH_RED_SANDSTONE_SLAB, Ids::SMOOTH_RED_SANDSTONE_DOUBLE_SLAB, fn() => Blocks::SMOOTH_RED_SANDSTONE_SLAB());
		$this->mapStairs(Ids::SMOOTH_RED_SANDSTONE_STAIRS, fn() => Blocks::SMOOTH_RED_SANDSTONE_STAIRS());
		$this->mapSlab(Ids::SMOOTH_SANDSTONE_SLAB, Ids::SMOOTH_SANDSTONE_DOUBLE_SLAB, fn() => Blocks::SMOOTH_SANDSTONE_SLAB());
		$this->mapStairs(Ids::SMOOTH_SANDSTONE_STAIRS, fn() => Blocks::SMOOTH_SANDSTONE_STAIRS());
		$this->mapSlab(Ids::SMOOTH_STONE_SLAB, Ids::SMOOTH_STONE_DOUBLE_SLAB, fn() => Blocks::SMOOTH_STONE_SLAB());
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
		$this->map(Ids::SOUL_LANTERN, function(Reader $in) : Block{
			return Blocks::SOUL_LANTERN()
				->setHanging($in->readBool(StateNames::HANGING));
		});
		$this->map(Ids::SOUL_TORCH, function(Reader $in) : Block{
			return Blocks::SOUL_TORCH()
				->setFacing($in->readTorchFacing());
		});
		$this->map(Ids::STANDING_BANNER, function(Reader $in) : Block{
			return Blocks::BANNER()
				->setRotation($in->readBoundedInt(StateNames::GROUND_SIGN_DIRECTION, 0, 15));
		});
		$this->mapSlab(Ids::STONE_BRICK_SLAB, Ids::STONE_BRICK_DOUBLE_SLAB, fn() => Blocks::STONE_BRICK_SLAB());
		$this->mapStairs(Ids::STONE_BRICK_STAIRS, fn() => Blocks::STONE_BRICK_STAIRS());
		$this->map(Ids::STONE_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::STONE_BRICK_WALL(), $in));
		$this->map(Ids::STONE_BUTTON, fn(Reader $in) => Helper::decodeButton(Blocks::STONE_BUTTON(), $in));
		$this->map(Ids::STONE_PRESSURE_PLATE, fn(Reader $in) => Helper::decodeSimplePressurePlate(Blocks::STONE_PRESSURE_PLATE(), $in));
		$this->mapStairs(Ids::STONE_STAIRS, fn() => Blocks::COBBLESTONE_STAIRS());
		$this->map(Ids::STONECUTTER_BLOCK, function(Reader $in) : Block{
			return Blocks::STONECUTTER()
				->setFacing($in->readCardinalHorizontalFacing());
		});
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
		$this->map(Ids::TRAPPED_CHEST, function(Reader $in) : Block{
			return Blocks::TRAPPED_CHEST()
				->setFacing($in->readCardinalHorizontalFacing());
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
		$this->mapSlab(Ids::TUFF_BRICK_SLAB, Ids::TUFF_BRICK_DOUBLE_SLAB, fn() => Blocks::TUFF_BRICK_SLAB());
		$this->mapStairs(Ids::TUFF_BRICK_STAIRS, fn() => Blocks::TUFF_BRICK_STAIRS());
		$this->map(Ids::TUFF_BRICK_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::TUFF_BRICK_WALL(), $in));
		$this->mapSlab(Ids::TUFF_SLAB, Ids::TUFF_DOUBLE_SLAB, fn() => Blocks::TUFF_SLAB());
		$this->mapStairs(Ids::TUFF_STAIRS, fn() => Blocks::TUFF_STAIRS());
		$this->map(Ids::TUFF_WALL, fn(Reader $in) => Helper::decodeWall(Blocks::TUFF_WALL(), $in));
		$this->map(Ids::TWISTING_VINES, function(Reader $in) : Block{
			return Blocks::TWISTING_VINES()
				->setAge($in->readBoundedInt(StateNames::TWISTING_VINES_AGE, 0, 25));
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
		$this->map(Ids::VERDANT_FROGLIGHT, fn(Reader $in) => Blocks::FROGLIGHT()->setFroglightType(FroglightType::VERDANT)->setAxis($in->readPillarAxis()));
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
		$this->map(Ids::WAXED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::COPPER(), CopperOxidation::NONE));
		$this->map(Ids::WAXED_CHISELED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CHISELED_COPPER(), CopperOxidation::NONE));
		$this->map(Ids::WAXED_COPPER_GRATE, fn() => Helper::decodeWaxedCopper(Blocks::COPPER_GRATE(), CopperOxidation::NONE));
		$this->map(Ids::WAXED_CUT_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER(), CopperOxidation::NONE));
		$this->mapSlab(Ids::WAXED_CUT_COPPER_SLAB, Ids::WAXED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::NONE));
		$this->mapStairs(Ids::WAXED_CUT_COPPER_STAIRS, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::NONE));
		$this->map(Ids::WAXED_COPPER_BULB, function(Reader $in) : Block{
			return Helper::decodeWaxedCopper(Blocks::COPPER_BULB(), CopperOxidation::NONE)
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::WAXED_COPPER_DOOR, fn(Reader $in) => Helper::decodeDoor(Helper::decodeWaxedCopper(Blocks::COPPER_DOOR(), CopperOxidation::NONE), $in));
		$this->map(Ids::WAXED_COPPER_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Helper::decodeWaxedCopper(Blocks::COPPER_TRAPDOOR(), CopperOxidation::NONE), $in));
		$this->map(Ids::WAXED_EXPOSED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::COPPER(), CopperOxidation::EXPOSED));
		$this->map(Ids::WAXED_EXPOSED_CHISELED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CHISELED_COPPER(), CopperOxidation::EXPOSED));
		$this->map(Ids::WAXED_EXPOSED_COPPER_GRATE, fn() => Helper::decodeWaxedCopper(Blocks::COPPER_GRATE(), CopperOxidation::EXPOSED));
		$this->map(Ids::WAXED_EXPOSED_CUT_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER(), CopperOxidation::EXPOSED));
		$this->mapSlab(Ids::WAXED_EXPOSED_CUT_COPPER_SLAB, Ids::WAXED_EXPOSED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::EXPOSED));
		$this->mapStairs(Ids::WAXED_EXPOSED_CUT_COPPER_STAIRS, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::EXPOSED));
		$this->map(Ids::WAXED_EXPOSED_COPPER_BULB, function(Reader $in) : Block{
			return Helper::decodeWaxedCopper(Blocks::COPPER_BULB(), CopperOxidation::EXPOSED)
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::WAXED_EXPOSED_COPPER_DOOR, fn(Reader $in) => Helper::decodeDoor(Helper::decodeWaxedCopper(Blocks::COPPER_DOOR(), CopperOxidation::EXPOSED), $in));
		$this->map(Ids::WAXED_EXPOSED_COPPER_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Helper::decodeWaxedCopper(Blocks::COPPER_TRAPDOOR(), CopperOxidation::EXPOSED), $in));
		$this->map(Ids::WAXED_OXIDIZED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::COPPER(), CopperOxidation::OXIDIZED));
		$this->map(Ids::WAXED_OXIDIZED_CHISELED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CHISELED_COPPER(), CopperOxidation::OXIDIZED));
		$this->map(Ids::WAXED_OXIDIZED_COPPER_GRATE, fn() => Helper::decodeWaxedCopper(Blocks::COPPER_GRATE(), CopperOxidation::OXIDIZED));
		$this->map(Ids::WAXED_OXIDIZED_CUT_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER(), CopperOxidation::OXIDIZED));
		$this->mapSlab(Ids::WAXED_OXIDIZED_CUT_COPPER_SLAB, Ids::WAXED_OXIDIZED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::OXIDIZED));
		$this->mapStairs(Ids::WAXED_OXIDIZED_CUT_COPPER_STAIRS, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::OXIDIZED));
		$this->map(Ids::WAXED_OXIDIZED_COPPER_BULB, function(Reader $in) : Block{
			return Helper::decodeWaxedCopper(Blocks::COPPER_BULB(), CopperOxidation::OXIDIZED)
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::WAXED_OXIDIZED_COPPER_DOOR, fn(Reader $in) => Helper::decodeDoor(Helper::decodeWaxedCopper(Blocks::COPPER_DOOR(), CopperOxidation::OXIDIZED), $in));
		$this->map(Ids::WAXED_OXIDIZED_COPPER_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Helper::decodeWaxedCopper(Blocks::COPPER_TRAPDOOR(), CopperOxidation::OXIDIZED), $in));
		$this->map(Ids::WAXED_WEATHERED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::COPPER(), CopperOxidation::WEATHERED));
		$this->map(Ids::WAXED_WEATHERED_CHISELED_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CHISELED_COPPER(), CopperOxidation::WEATHERED));
		$this->map(Ids::WAXED_WEATHERED_COPPER_GRATE, fn() => Helper::decodeWaxedCopper(Blocks::COPPER_GRATE(), CopperOxidation::WEATHERED));
		$this->map(Ids::WAXED_WEATHERED_CUT_COPPER, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER(), CopperOxidation::WEATHERED));
		$this->mapSlab(Ids::WAXED_WEATHERED_CUT_COPPER_SLAB, Ids::WAXED_WEATHERED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::WEATHERED));
		$this->mapStairs(Ids::WAXED_WEATHERED_CUT_COPPER_STAIRS, fn() => Helper::decodeWaxedCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::WEATHERED));
		$this->map(Ids::WAXED_WEATHERED_COPPER_BULB, function(Reader $in) : Block{
			return Helper::decodeWaxedCopper(Blocks::COPPER_BULB(), CopperOxidation::WEATHERED)
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::WAXED_WEATHERED_COPPER_DOOR, fn(Reader $in) => Helper::decodeDoor(Helper::decodeWaxedCopper(Blocks::COPPER_DOOR(), CopperOxidation::WEATHERED), $in));
		$this->map(Ids::WAXED_WEATHERED_COPPER_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Helper::decodeWaxedCopper(Blocks::COPPER_TRAPDOOR(), CopperOxidation::WEATHERED), $in));
		$this->map(Ids::WEATHERED_COPPER, fn() => Helper::decodeCopper(Blocks::COPPER(), CopperOxidation::WEATHERED));
		$this->map(Ids::WEATHERED_CHISELED_COPPER, fn() => Helper::decodeCopper(Blocks::CHISELED_COPPER(), CopperOxidation::WEATHERED));
		$this->map(Ids::WEATHERED_COPPER_GRATE, fn() => Helper::decodeCopper(Blocks::COPPER_GRATE(), CopperOxidation::WEATHERED));
		$this->map(Ids::WEATHERED_CUT_COPPER, fn() => Helper::decodeCopper(Blocks::CUT_COPPER(), CopperOxidation::WEATHERED));
		$this->mapSlab(Ids::WEATHERED_CUT_COPPER_SLAB, Ids::WEATHERED_DOUBLE_CUT_COPPER_SLAB, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_SLAB(), CopperOxidation::WEATHERED));
		$this->mapStairs(Ids::WEATHERED_CUT_COPPER_STAIRS, fn() => Helper::decodeCopper(Blocks::CUT_COPPER_STAIRS(), CopperOxidation::WEATHERED));
		$this->map(Ids::WEATHERED_COPPER_BULB, function(Reader $in) : Block{
			return Helper::decodeCopper(Blocks::COPPER_BULB(), CopperOxidation::WEATHERED)
				->setLit($in->readBool(StateNames::LIT))
				->setPowered($in->readBool(StateNames::POWERED_BIT));
		});
		$this->map(Ids::WEATHERED_COPPER_DOOR, fn(Reader $in) => Helper::decodeDoor(Helper::decodeCopper(Blocks::COPPER_DOOR(), CopperOxidation::WEATHERED), $in));
		$this->map(Ids::WEATHERED_COPPER_TRAPDOOR, fn(Reader $in) => Helper::decodeTrapdoor(Helper::decodeCopper(Blocks::COPPER_TRAPDOOR(), CopperOxidation::WEATHERED), $in));
		$this->map(Ids::WEEPING_VINES, function(Reader $in) : Block{
			return Blocks::WEEPING_VINES()
				->setAge($in->readBoundedInt(StateNames::WEEPING_VINES_AGE, 0, 25));
		});
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
