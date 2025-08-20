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
use pocketmine\block\DoublePitcherCrop;
use pocketmine\block\PitcherCrop;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\Colored;
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

	private function registerDeserializers() : void{
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
		$this->map(Ids::MUSHROOM_STEM, fn(Reader $in) => match($in->readBoundedInt(StateNames::HUGE_MUSHROOM_BITS, 0, 15)){
			BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM => Blocks::ALL_SIDED_MUSHROOM_STEM(),
			BlockLegacyMetadata::MUSHROOM_BLOCK_STEM => Blocks::MUSHROOM_STEM(),
			default => throw new BlockStateDeserializeException("This state does not exist"),
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
