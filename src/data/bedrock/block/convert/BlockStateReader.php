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
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\SlabType;
use pocketmine\block\utils\WallConnectionType;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\BlockStateStringValues as StringValues;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Utils;
use function get_class;

final class BlockStateReader{

	/**
	 * @var true[]
	 * @phpstan-var array<string, true>
	 */
	private array $usedStates = [];

	public function __construct(
		private BlockStateData $data
	){}

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
		$this->usedStates[$name] = true;
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
		$this->usedStates[$name] = true;
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
		$this->usedStates[$name] = true;
		//TODO: only allow a specific set of values (strings are primarily used for enums)
		$tag = $this->data->getState($name);
		if($tag instanceof StringTag){
			return $tag->getValue();
		}
		throw $this->missingOrWrongTypeException($name, $tag);
	}

	/**
	 * @param int[] $mapping
	 * @phpstan-param array<int, int> $mapping
	 * @phpstan-return int
	 * @throws BlockStateDeserializeException
	 */
	private function parseFacingValue(int $value, array $mapping) : int{
		$result = $mapping[$value] ?? null;
		if($result === null){
			throw new BlockStateDeserializeException("Unmapped facing value " . $value);
		}
		return $result;
	}

	/** @throws BlockStateDeserializeException */
	public function readFacingDirection() : int{
		return $this->parseFacingValue($this->readInt(BlockStateNames::FACING_DIRECTION), [
			0 => Facing::DOWN,
			1 => Facing::UP,
			2 => Facing::NORTH,
			3 => Facing::SOUTH,
			4 => Facing::WEST,
			5 => Facing::EAST
		]);
	}

	/** @throws BlockStateDeserializeException */
	public function readEndRodFacingDirection() : int{
		$result = $this->readFacingDirection();
		return Facing::axis($result) !== Axis::Y ? Facing::opposite($result) : $result;
	}

	/** @throws BlockStateDeserializeException */
	public function readHorizontalFacing() : int{
		return $this->parseFacingValue($this->readInt(BlockStateNames::FACING_DIRECTION), [
			0 => Facing::NORTH, //should be illegal, but 1.13 allows it
			1 => Facing::NORTH, //also should be illegal
			2 => Facing::NORTH,
			3 => Facing::SOUTH,
			4 => Facing::WEST,
			5 => Facing::EAST
		]);
	}

	/** @throws BlockStateDeserializeException */
	public function readWeirdoHorizontalFacing() : int{
		return $this->parseFacingValue($this->readInt(BlockStateNames::WEIRDO_DIRECTION), [
			0 => Facing::EAST,
			1 => Facing::WEST,
			2 => Facing::SOUTH,
			3 => Facing::NORTH
		]);
	}

	/** @throws BlockStateDeserializeException */
	public function readLegacyHorizontalFacing() : int{
		return $this->parseFacingValue($this->readInt(BlockStateNames::DIRECTION), [
			0 => Facing::SOUTH,
			1 => Facing::WEST,
			2 => Facing::NORTH,
			3 => Facing::EAST
		]);
	}

	/**
	 * This is for trapdoors, because Mojang botched the conversion in 1.13
	 * @throws BlockStateDeserializeException
	 */
	public function read5MinusHorizontalFacing() : int{
		return $this->parseFacingValue($this->readInt(BlockStateNames::DIRECTION), [
			0 => Facing::EAST,
			1 => Facing::WEST,
			2 => Facing::SOUTH,
			3 => Facing::NORTH
		]);
	}

	/**
	 * Used by pumpkins as of 1.20.0.23 beta
	 * @throws BlockStateDeserializeException
	 */
	public function readCardinalHorizontalFacing() : int{
		return match($raw = $this->readString(BlockStateNames::CARDINAL_DIRECTION)){
			StringValues::CARDINAL_DIRECTION_NORTH => Facing::NORTH,
			StringValues::CARDINAL_DIRECTION_SOUTH => Facing::SOUTH,
			StringValues::CARDINAL_DIRECTION_WEST => Facing::WEST,
			StringValues::CARDINAL_DIRECTION_EAST => Facing::EAST,
			default => throw $this->badValueException(BlockStateNames::CARDINAL_DIRECTION, $raw)
		};
	}

	/** @throws BlockStateDeserializeException */
	public function readColor() : DyeColor{
		//	 * color (StringTag) = black, blue, brown, cyan, gray, green, light_blue, lime, magenta, orange, pink, purple, red, silver, white, yellow
		return match($color = $this->readString(BlockStateNames::COLOR)){
			StringValues::COLOR_BLACK => DyeColor::BLACK(),
			StringValues::COLOR_BLUE => DyeColor::BLUE(),
			StringValues::COLOR_BROWN => DyeColor::BROWN(),
			StringValues::COLOR_CYAN => DyeColor::CYAN(),
			StringValues::COLOR_GRAY => DyeColor::GRAY(),
			StringValues::COLOR_GREEN => DyeColor::GREEN(),
			StringValues::COLOR_LIGHT_BLUE => DyeColor::LIGHT_BLUE(),
			StringValues::COLOR_LIME => DyeColor::LIME(),
			StringValues::COLOR_MAGENTA => DyeColor::MAGENTA(),
			StringValues::COLOR_ORANGE => DyeColor::ORANGE(),
			StringValues::COLOR_PINK => DyeColor::PINK(),
			StringValues::COLOR_PURPLE => DyeColor::PURPLE(),
			StringValues::COLOR_RED => DyeColor::RED(),
			StringValues::COLOR_SILVER => DyeColor::LIGHT_GRAY(),
			StringValues::COLOR_WHITE => DyeColor::WHITE(),
			StringValues::COLOR_YELLOW => DyeColor::YELLOW(),
			default => throw $this->badValueException(BlockStateNames::COLOR, $color),
		};
	}

	/** @throws BlockStateDeserializeException */
	public function readCoralFacing() : int{
		return $this->parseFacingValue($this->readInt(BlockStateNames::CORAL_DIRECTION), [
			0 => Facing::WEST,
			1 => Facing::EAST,
			2 => Facing::NORTH,
			3 => Facing::SOUTH
		]);
	}

	/** @throws BlockStateDeserializeException */
	public function readFacingWithoutDown() : int{
		$result = $this->readFacingDirection();
		if($result === Facing::DOWN){ //shouldn't be legal, but 1.13 allows it
			$result = Facing::UP;
		}
		return $result;
	}

	public function readFacingWithoutUp() : int{
		$result = $this->readFacingDirection();
		if($result === Facing::UP){
			$result = Facing::DOWN; //shouldn't be legal, but 1.13 allows it
		}
		return $result;
	}

	/**
	 * @phpstan-return Axis::*
	 * @throws BlockStateDeserializeException
	 */
	public function readPillarAxis() : int{
		$rawValue = $this->readString(BlockStateNames::PILLAR_AXIS);
		$value = [
			StringValues::PILLAR_AXIS_X => Axis::X,
			StringValues::PILLAR_AXIS_Y => Axis::Y,
			StringValues::PILLAR_AXIS_Z => Axis::Z
		][$rawValue] ?? null;
		if($value === null){
			throw $this->badValueException(BlockStateNames::PILLAR_AXIS, $rawValue, "Invalid axis value");
		}
		return $value;
	}

	/** @throws BlockStateDeserializeException */
	public function readSlabPosition() : SlabType{
		return $this->readBool(BlockStateNames::TOP_SLOT_BIT) ? SlabType::TOP() : SlabType::BOTTOM();
	}

	/**
	 * @phpstan-return Facing::UP|Facing::NORTH|Facing::SOUTH|Facing::WEST|Facing::EAST
	 * @throws BlockStateDeserializeException
	 */
	public function readTorchFacing() : int{
		//TODO: horizontal directions are flipped (MCPE bug: https://bugs.mojang.com/browse/MCPE-152036)
		return match($rawValue = $this->readString(BlockStateNames::TORCH_FACING_DIRECTION)){
			StringValues::TORCH_FACING_DIRECTION_EAST => Facing::WEST,
			StringValues::TORCH_FACING_DIRECTION_NORTH => Facing::SOUTH,
			StringValues::TORCH_FACING_DIRECTION_SOUTH => Facing::NORTH,
			StringValues::TORCH_FACING_DIRECTION_TOP => Facing::UP,
			StringValues::TORCH_FACING_DIRECTION_UNKNOWN => Facing::UP, //should be illegal, but 1.13 allows it
			StringValues::TORCH_FACING_DIRECTION_WEST => Facing::EAST,
			default => throw $this->badValueException(BlockStateNames::TORCH_FACING_DIRECTION, $rawValue, "Invalid torch facing"),
		};
	}

	/** @throws BlockStateDeserializeException */
	public function readCoralType() : CoralType{
		return match($type = $this->readString(BlockStateNames::CORAL_COLOR)){
			StringValues::CORAL_COLOR_BLUE => CoralType::TUBE(),
			StringValues::CORAL_COLOR_PINK => CoralType::BRAIN(),
			StringValues::CORAL_COLOR_PURPLE => CoralType::BUBBLE(),
			StringValues::CORAL_COLOR_RED => CoralType::FIRE(),
			StringValues::CORAL_COLOR_YELLOW => CoralType::HORN(),
			default => throw $this->badValueException(BlockStateNames::CORAL_COLOR, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	public function readBellAttachmentType() : BellAttachmentType{
		return match($type = $this->readString(BlockStateNames::ATTACHMENT)){
			StringValues::ATTACHMENT_HANGING => BellAttachmentType::CEILING(),
			StringValues::ATTACHMENT_STANDING => BellAttachmentType::FLOOR(),
			StringValues::ATTACHMENT_SIDE => BellAttachmentType::ONE_WALL(),
			StringValues::ATTACHMENT_MULTIPLE => BellAttachmentType::TWO_WALLS(),
			default => throw $this->badValueException(BlockStateNames::ATTACHMENT, $type),
		};
	}

	/** @throws BlockStateDeserializeException */
	public function readWallConnectionType(string $name) : ?WallConnectionType{
		return match($type = $this->readString($name)){
			//TODO: this looks a bit confusing due to use of EAST, but the values are the same for all connections
			//we need to find a better way to auto-generate the constant names when they are reused
			//for now, using these constants is better than nothing since it still gives static analysability
			StringValues::WALL_CONNECTION_TYPE_EAST_NONE => null,
			StringValues::WALL_CONNECTION_TYPE_EAST_SHORT => WallConnectionType::SHORT(),
			StringValues::WALL_CONNECTION_TYPE_EAST_TALL => WallConnectionType::TALL(),
			default => throw $this->badValueException($name, $type),
		};
	}

	/**
	 * Explicitly mark a property as unused, so it doesn't get flagged as an error when debug mode is enabled
	 */
	public function ignored(string $name) : void{
		if($this->data->getState($name) !== null){
			$this->usedStates[$name] = true;
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
		foreach(Utils::stringifyKeys($this->data->getStates()) as $name => $tag){
			if(!isset($this->usedStates[$name])){
				throw new BlockStateDeserializeException("Unread property \"$name\"");
			}
		}
	}
}
