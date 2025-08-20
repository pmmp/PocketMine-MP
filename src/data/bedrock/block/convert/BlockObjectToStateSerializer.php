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

use pocketmine\block\BigDripleafHead;
use pocketmine\block\BigDripleafStem;
use pocketmine\block\Block;
use pocketmine\block\DoublePitcherCrop;
use pocketmine\block\FillableCauldron;
use pocketmine\block\PitcherCrop;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\Colored;
use pocketmine\block\VanillaBlocks as Blocks;
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

	private function registerSerializers() : void{
		$this->map(Blocks::ALL_SIDED_MUSHROOM_STEM(), Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM));
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
		$this->map(Blocks::MUSHROOM_STEM(), Writer::create(Ids::MUSHROOM_STEM)
				->writeInt(StateNames::HUGE_MUSHROOM_BITS, BlockLegacyMetadata::MUSHROOM_BLOCK_STEM));
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
	}
}
