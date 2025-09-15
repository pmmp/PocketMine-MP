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
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateNames;
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
use function array_keys;
use function count;
use function get_class;
use function implode;

final class BlockStateReader{

	/**
	 * @var Tag[]
	 * @phpstan-var array<string, Tag>
	 */
	private array $unusedStates;

	public function __construct(
		private BlockStateData $data
	){
		$this->unusedStates = $this->data->getStates();
	}

	public function missingOrWrongTypeException(string $name, ?Tag $tag) : BlockStateDeserializeException{
		return new BlockStateDeserializeException("Property \"$name\" " . ($tag !== null ? "has unexpected type " . get_class($tag) : "is missing"));
	}

	public function badValueException(string $name, string $stringifiedValue, ?string $reason = null) : BlockStateDeserializeException{
		return new BlockStateDeserializeException(
			"Property \"$name\" has unexpected value \"$stringifiedValue\"" . (
			$reason !== null ? " ($reason)" : ""
		));
	}

	/** @throws BlockStateDeserializeException */
	public function readBool(string $name) : bool{
		unset($this->unusedStates[$name]);
		$tag = $this->data->getState($name);
		if($tag instanceof ByteTag){
			switch($tag->getValue()){
				case 0: return false;
				case 1: return true;
				default: throw $this->badValueException($name, (string) $tag->getValue());
			}
		}
		throw $this->missingOrWrongTypeException($name, $tag);
	}

	/** @throws BlockStateDeserializeException */
	public function readInt(string $name) : int{
		unset($this->unusedStates[$name]);
		$tag = $this->data->getState($name);
		if($tag instanceof IntTag){
			return $tag->getValue();
		}
		throw $this->missingOrWrongTypeException($name, $tag);
	}

	/** @throws BlockStateDeserializeException */
	public function readBoundedInt(string $name, int $min, int $max) : int{
		$result = $this->readInt($name);
		if($result < $min || $result > $max){
			throw $this->badValueException($name, (string) $result, "Must be inside the range $min ... $max");
		}
		return $result;
	}

	/** @throws BlockStateDeserializeException */
	public function readString(string $name) : string{
		unset($this->unusedStates[$name]);
		//TODO: only allow a specific set of values (strings are primarily used for enums)
		$tag = $this->data->getState($name);
		if($tag instanceof StringTag){
			return $tag->getValue();
		}
		throw $this->missingOrWrongTypeException($name, $tag);
	}

	/**
	 * @deprecated
	 * @phpstan-param IntFromRawStateMap<string> $map
	 * @throws BlockStateDeserializeException
	 */
	public function mapIntFromString(string $name, IntFromRawStateMap $map) : int{
		$raw = $this->readString($name);

		return $map->rawToValue($raw) ?? throw $this->badValueException($name, $raw);
	}

	/**
	 * @deprecated
	 * @phpstan-param IntFromRawStateMap<int> $map
	 * @throws BlockStateDeserializeException
	 */
	public function mapIntFromInt(string $name, IntFromRawStateMap $map) : int{
		$raw = $this->readInt($name);

		return $map->rawToValue($raw) ?? throw $this->badValueException($name, (string) $raw);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readFacingDirection() : int{
		return $this->mapIntFromInt(BlockStateNames::FACING_DIRECTION, ValueMappings::getInstance()->facing);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readBlockFace() : int{
		return $this->mapIntFromString(BlockStateNames::MC_BLOCK_FACE, ValueMappings::getInstance()->blockFace);
	}

	/**
	 * @deprecated
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function readFacingFlags() : array{
		$result = [];
		$flags = $this->readBoundedInt(BlockStateNames::MULTI_FACE_DIRECTION_BITS, 0, 63);
		foreach([
			BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_DOWN => Facing::DOWN,
			BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_UP => Facing::UP,
			BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_NORTH => Facing::NORTH,
			BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_SOUTH => Facing::SOUTH,
			BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_WEST => Facing::WEST,
			BlockLegacyMetadata::MULTI_FACE_DIRECTION_FLAG_EAST => Facing::EAST
		] as $flag => $facing){
			if(($flags & $flag) !== 0){
				$result[$facing] = $facing;
			}
		}

		return $result;
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readEndRodFacingDirection() : int{
		$result = $this->readFacingDirection();
		return Facing::axis($result) !== Axis::Y ? Facing::opposite($result) : $result;
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readHorizontalFacing() : int{
		return $this->mapIntFromInt(BlockStateNames::FACING_DIRECTION, ValueMappings::getInstance()->horizontalFacingClassic);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readWeirdoHorizontalFacing() : int{
		return $this->mapIntFromInt(BlockStateNames::WEIRDO_DIRECTION, ValueMappings::getInstance()->horizontalFacing5Minus);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readLegacyHorizontalFacing() : int{
		return $this->mapIntFromInt(BlockStateNames::DIRECTION, ValueMappings::getInstance()->horizontalFacingSWNE);
	}

	/**
	 * @deprecated
	 * This is for trapdoors, because Mojang botched the conversion in 1.13
	 * @throws BlockStateDeserializeException
	 */
	public function read5MinusHorizontalFacing() : int{
		return $this->mapIntFromInt(BlockStateNames::DIRECTION, ValueMappings::getInstance()->horizontalFacing5Minus);
	}

	/**
	 * @deprecated
	 * Used by pumpkins as of 1.20.0.23 beta
	 * @throws BlockStateDeserializeException
	 */
	public function readCardinalHorizontalFacing() : int{
		return $this->mapIntFromString(BlockStateNames::MC_CARDINAL_DIRECTION, ValueMappings::getInstance()->cardinalDirection);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readCoralFacing() : int{
		return $this->mapIntFromInt(BlockStateNames::CORAL_DIRECTION, ValueMappings::getInstance()->horizontalFacingCoral);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readFacingWithoutDown() : int{
		$result = $this->readFacingDirection();
		if($result === Facing::DOWN){ //shouldn't be legal, but 1.13 allows it
			$result = Facing::UP;
		}
		return $result;
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readFacingWithoutUp() : int{
		$result = $this->readFacingDirection();
		if($result === Facing::UP){
			$result = Facing::DOWN; //shouldn't be legal, but 1.13 allows it
		}
		return $result;
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readPillarAxis() : int{
		return $this->mapIntFromString(BlockStateNames::PILLAR_AXIS, ValueMappings::getInstance()->pillarAxis);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readSlabPosition() : SlabType{
		return match($rawValue = $this->readString(BlockStateNames::MC_VERTICAL_HALF)){
			StringValues::MC_VERTICAL_HALF_BOTTOM => SlabType::BOTTOM,
			StringValues::MC_VERTICAL_HALF_TOP => SlabType::TOP,
			default => throw $this->badValueException(BlockStateNames::MC_VERTICAL_HALF, $rawValue, "Invalid slab position"),
		};
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readTorchFacing() : int{
		return $this->mapIntFromString(BlockStateNames::TORCH_FACING_DIRECTION, ValueMappings::getInstance()->torchFacing);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readBellAttachmentType() : BellAttachmentType{
		return $this->readUnitEnum(BlockStateNames::ATTACHMENT, ValueMappings::getInstance()->bellAttachmentType);
	}

	/**
	 * @deprecated
	 * @throws BlockStateDeserializeException
	 */
	public function readWallConnectionType(string $name) : ?WallConnectionType{
		return match($type = $this->readString($name)){
			//TODO: this looks a bit confusing due to use of EAST, but the values are the same for all connections
			//we need to find a better way to auto-generate the constant names when they are reused
			//for now, using these constants is better than nothing since it still gives static analysability
			StringValues::WALL_CONNECTION_TYPE_EAST_NONE => null,
			StringValues::WALL_CONNECTION_TYPE_EAST_SHORT => WallConnectionType::SHORT,
			StringValues::WALL_CONNECTION_TYPE_EAST_TALL => WallConnectionType::TALL,
			default => throw $this->badValueException($name, $type),
		};
	}

	/**
	 * @deprecated
	 * @phpstan-template TEnum of \UnitEnum
	 * @phpstan-param EnumFromRawStateMap<TEnum, string> $map
	 * @phpstan-return TEnum
	 * @throws BlockStateDeserializeException
	 */
	public function readUnitEnum(string $name, EnumFromRawStateMap $map) : \UnitEnum{
		$value = $this->readString($name);

		$mapped = $map->rawToValue($value);
		if($mapped === null){
			throw $this->badValueException($name, $value);
		}
		return $mapped;
	}

	/**
	 * Explicitly mark a property as unused, so it doesn't get flagged as an error when debug mode is enabled
	 */
	public function ignored(string $name) : void{
		if($this->data->getState($name) !== null){
			unset($this->unusedStates[$name]);
		}else{
			throw $this->missingOrWrongTypeException($name, null);
		}
	}

	/**
	 * Used to mark unused properties that haven't been implemented yet
	 */
	public function todo(string $name) : void{
		$this->ignored($name);
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function checkUnreadProperties() : void{
		if(count($this->unusedStates) > 0){
			throw new BlockStateDeserializeException("Unread properties: " . implode(", ", array_keys($this->unusedStates)));
		}
	}
}
