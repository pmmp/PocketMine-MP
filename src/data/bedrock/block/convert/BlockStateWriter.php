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

use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\SlabType;
use pocketmine\block\utils\WallConnectionType;
use pocketmine\data\bedrock\block\BlockLegacyMetadata;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\BlockStateSerializeException;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\data\bedrock\block\convert\property\EnumFromRawStateMap;
use pocketmine\data\bedrock\block\convert\property\IntFromRawStateMap;
use pocketmine\data\bedrock\block\convert\property\ValueMappings;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\AssumptionFailedError;

final class BlockStateWriter{

	/**
	 * @var Tag[]
	 * @phpstan-var array<string, Tag>
	 */
	private array $states = [];

	public function __construct(
		private string $id
	){}

	public static function create(string $id) : self{
		return new self($id);
	}

	/** @return $this */
	public function writeBool(string $name, bool $value) : self{
		$this->states[$name] = new ByteTag($value ? 1 : 0);
		return $this;
	}

	/** @return $this */
	public function writeInt(string $name, int $value) : self{
		$this->states[$name] = new IntTag($value);
		return $this;
	}

	/** @return $this */
	public function writeString(string $name, string $value) : self{
		$this->states[$name] = new StringTag($value);
		return $this;
	}

	/**
	 * @deprecated
	 * @phpstan-param IntFromRawStateMap<string> $map
	 * @return $this
	 */
	public function mapIntToString(string $name, IntFromRawStateMap $map, int $value) : self{
		$raw = $map->valueToRaw($value);
		$this->writeString($name, $raw);
		return $this;
	}

	/**
	 * @deprecated
	 * @phpstan-param IntFromRawStateMap<int> $map
	 * @return $this
	 */
	public function mapIntToInt(string $name, IntFromRawStateMap $map, int $value) : self{
		$raw = $map->valueToRaw($value);
		$this->writeInt($name, $raw);
		return $this;
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeFacingDirection(int $value) : self{
		return $this->mapIntToInt(BlockStateNames::FACING_DIRECTION, ValueMappings::getInstance()->facing, $value);
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeBlockFace(int $value) : self{
		$this->mapIntToString(BlockStateNames::MC_BLOCK_FACE, ValueMappings::getInstance()->blockFace, $value);
		return $this;
	}

	/**
	 * @deprecated
	 * @param int[] $faces
	 * @phpstan-param array<int, int> $faces
	 * @return $this
	 */
	public function writeFacingFlags(array $faces) : self{
		$result = 0;
		foreach($faces as $face){
			$result |= match($face){
				Facing::DOWN => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_DOWN,
				Facing::UP => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_UP,
				Facing::NORTH => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_NORTH,
				Facing::SOUTH => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_SOUTH,
				Facing::WEST => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_WEST,
				Facing::EAST => BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_EAST,
				default => throw new AssumptionFailedError("Unhandled face $face")
			};
		}

		return $this->writeInt(BlockStateNames::MULTI_FACE_DIRECTION_BITS, $result);
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeEndRodFacingDirection(int $value) : self{
		//end rods are stupid in bedrock and have everything except up/down the wrong way round
		return $this->writeFacingDirection(Facing::axis($value) !== Axis::Y ? Facing::opposite($value) : $value);
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeHorizontalFacing(int $value) : self{
		return $this->mapIntToInt(BlockStateNames::FACING_DIRECTION, ValueMappings::getInstance()->horizontalFacingClassic, $value);
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeWeirdoHorizontalFacing(int $value) : self{
		return $this->mapIntToInt(BlockStateNames::WEIRDO_DIRECTION, ValueMappings::getInstance()->horizontalFacing5Minus, $value);
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeLegacyHorizontalFacing(int $value) : self{
		return $this->mapIntToInt(BlockStateNames::DIRECTION, ValueMappings::getInstance()->horizontalFacingSWNE, $value);
	}

	/**
	 * @deprecated
	 * This is for trapdoors, because Mojang botched the conversion in 1.13
	 * @return $this
	 */
	public function write5MinusHorizontalFacing(int $value) : self{
		return $this->mapIntToInt(BlockStateNames::DIRECTION, ValueMappings::getInstance()->horizontalFacing5Minus, $value);
	}

	/**
	 * @deprecated
	 * Used by pumpkins as of 1.20.0.23 beta
	 * @return $this
	 */
	public function writeCardinalHorizontalFacing(int $value) : self{
		return $this->mapIntToString(BlockStateNames::MC_CARDINAL_DIRECTION, ValueMappings::getInstance()->cardinalDirection, $value);
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeCoralFacing(int $value) : self{
		return $this->mapIntToInt(BlockStateNames::CORAL_DIRECTION, ValueMappings::getInstance()->horizontalFacingCoral, $value);
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeFacingWithoutDown(int $value) : self{
		if($value === Facing::DOWN){
			throw new BlockStateSerializeException("Invalid facing DOWN");
		}
		$this->writeFacingDirection($value);
		return $this;
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeFacingWithoutUp(int $value) : self{
		if($value === Facing::UP){
			throw new BlockStateSerializeException("Invalid facing UP");
		}
		$this->writeFacingDirection($value);
		return $this;
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writePillarAxis(int $axis) : self{
		$this->mapIntToString(BlockStateNames::PILLAR_AXIS, ValueMappings::getInstance()->pillarAxis, $axis);
		return $this;
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeSlabPosition(SlabType $slabType) : self{
		$this->writeString(BlockStateNames::MC_VERTICAL_HALF, match($slabType){
			SlabType::TOP => StringValues::MC_VERTICAL_HALF_TOP,
			SlabType::BOTTOM => StringValues::MC_VERTICAL_HALF_BOTTOM,
			default => throw new BlockStateSerializeException("Invalid slab type " . $slabType->name)
		});
		return $this;
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeTorchFacing(int $facing) : self{
		$this->mapIntToString(BlockStateNames::TORCH_FACING_DIRECTION, ValueMappings::getInstance()->torchFacing, $facing);
		return $this;
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeBellAttachmentType(BellAttachmentType $attachmentType) : self{
		return $this->writeUnitEnum(BlockStateNames::ATTACHMENT, ValueMappings::getInstance()->bellAttachmentType, $attachmentType);
	}

	/**
	 * @deprecated
	 * @return $this
	 */
	public function writeWallConnectionType(string $name, ?WallConnectionType $wallConnectionType) : self{
		$this->writeString($name, match($wallConnectionType){
			null => StringValues::WALL_CONNECTION_TYPE_EAST_NONE,
			WallConnectionType::SHORT => StringValues::WALL_CONNECTION_TYPE_EAST_SHORT,
			WallConnectionType::TALL => StringValues::WALL_CONNECTION_TYPE_EAST_TALL,
		});
		return $this;
	}

	/**
	 * @deprecated
	 * @phpstan-template TEnum of \UnitEnum
	 * @phpstan-param EnumFromRawStateMap<TEnum, string> $map
	 * @phpstan-param TEnum                         $case
	 *
	 * @return $this
	 */
	public function writeUnitEnum(string $name, EnumFromRawStateMap $map, \UnitEnum $case) : self{
		$value = $map->valueToRaw($case);
		$this->writeString($name, $value);

		return $this;
	}

	public function getBlockStateData() : BlockStateData{
		return BlockStateData::current($this->id, $this->states);
	}
}
