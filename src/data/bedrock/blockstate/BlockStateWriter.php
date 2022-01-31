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

use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\SlabType;
use pocketmine\data\bedrock\blockstate\BlockStateValues as Values;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\nbt\tag\CompoundTag;

final class BlockStateWriter{

	private CompoundTag $states;

	public function __construct(
		private string $id
	){
		$this->states = CompoundTag::create();
	}

	/** @return $this */
	public function writeBool(string $name, bool $value) : self{
		$this->states->setByte($name, $value ? 1 : 0);
		return $this;
	}

	/** @return $this */
	public function writeInt(string $name, int $value) : self{
		$this->states->setInt($name, $value);
		return $this;
	}

	/** @return $this */
	public function writeString(string $name, string $value) : self{
		$this->states->setString($name, $value);
		return $this;
	}

	/** @return $this */
	public function writeFacingDirection(int $value) : self{
		$this->writeInt(BlockStateNames::FACING_DIRECTION, match($value){
			Facing::DOWN => 0,
			Facing::UP => 1,
			Facing::NORTH => 2,
			Facing::SOUTH => 3,
			Facing::WEST => 4,
			Facing::EAST => 5,
			default => throw new BlockStateSerializeException("Invalid Facing $value")
		});
		return $this;
	}

	/** @return $this */
	public function writeHorizontalFacing(int $value) : self{
		if($value === Facing::UP || $value === Facing::DOWN){
			throw new BlockStateSerializeException("Y-axis facing is not allowed");
		}

		return $this->writeFacingDirection($value);
	}

	/** @return $this */
	public function writeWeirdoHorizontalFacing(int $value) : self{
		$this->writeInt(BlockStateNames::WEIRDO_DIRECTION, match($value){
			Facing::EAST => 0,
			Facing::WEST => 1,
			Facing::SOUTH => 2,
			Facing::NORTH => 3,
			default => throw new BlockStateSerializeException("Invalid horizontal facing $value")
		});
		return $this;
	}

	/** @return $this */
	public function writeLegacyHorizontalFacing(int $value) : self{
		$this->writeInt(BlockStateNames::DIRECTION, match($value){
			Facing::SOUTH => 0,
			Facing::WEST => 1,
			Facing::NORTH => 2,
			Facing::EAST => 3,
			default => throw new BlockStateSerializeException("Invalid horizontal facing $value")
		});
		return $this;
	}

	/** @return $this */
	public function writeColor(DyeColor $color) : self{
		$this->writeString(BlockStateNames::COLOR, match($color->id()){
			DyeColor::BLACK()->id() => Values::COLOR_BLACK,
			DyeColor::BLUE()->id() => Values::COLOR_BLUE,
			DyeColor::BROWN()->id() => Values::COLOR_BROWN,
			DyeColor::CYAN()->id() => Values::COLOR_CYAN,
			DyeColor::GRAY()->id() => Values::COLOR_GRAY,
			DyeColor::GREEN()->id() => Values::COLOR_GREEN,
			DyeColor::LIGHT_BLUE()->id() => Values::COLOR_LIGHT_BLUE,
			DyeColor::LIGHT_GRAY()->id() => Values::COLOR_SILVER,
			DyeColor::LIME()->id() => Values::COLOR_LIME,
			DyeColor::MAGENTA()->id() => Values::COLOR_MAGENTA,
			DyeColor::ORANGE()->id() => Values::COLOR_ORANGE,
			DyeColor::PINK()->id() => Values::COLOR_PINK,
			DyeColor::PURPLE()->id() => Values::COLOR_PURPLE,
			DyeColor::RED()->id() => Values::COLOR_RED,
			DyeColor::WHITE()->id() => Values::COLOR_WHITE,
			DyeColor::YELLOW()->id() => Values::COLOR_YELLOW,
			default => throw new BlockStateSerializeException("Invalid Color " . $color->name())
		});
		return $this;
	}

	/** @return $this */
	public function writeCoralFacing(int $value) : self{
		$this->writeInt(BlockStateNames::CORAL_DIRECTION, match($value){
			Facing::WEST => 0,
			Facing::EAST => 1,
			Facing::NORTH => 2,
			Facing::SOUTH => 3,
			default => throw new BlockStateSerializeException("Invalid horizontal facing $value")
		});
		return $this;
	}

	/** @return $this */
	public function writeFacingWithoutDown(int $value) : self{
		if($value === Facing::DOWN){
			throw new BlockStateSerializeException("Invalid facing DOWN");
		}
		$this->writeFacingDirection($value);
		return $this;
	}

	/** @return $this */
	public function writeFacingWithoutUp(int $value) : self{
		if($value === Facing::UP){
			throw new BlockStateSerializeException("Invalid facing UP");
		}
		$this->writeFacingDirection($value);
		return $this;
	}

	/** @return $this */
	public function writePillarAxis(int $axis) : self{
		$this->writeString(BlockStateNames::PILLAR_AXIS, match($axis){
			Axis::X => Values::PILLAR_AXIS_X,
			Axis::Y => Values::PILLAR_AXIS_Y,
			Axis::Z => Values::PILLAR_AXIS_Z,
			default => throw new BlockStateSerializeException("Invalid axis $axis")
		});
		return $this;
	}

	/** @return $this */
	public function writeSlabPosition(SlabType $slabType) : self{
		$this->writeBool(BlockStateNames::TOP_SLOT_BIT, match($slabType->id()){
			SlabType::TOP()->id() => true,
			SlabType::BOTTOM()->id() => false,
			default => throw new BlockStateSerializeException("Invalid slab type " . $slabType->name())
		});
		return $this;
	}

	/** @return $this */
	public function writeTorchFacing(int $facing) : self{
		$this->writeString(BlockStateNames::TORCH_FACING_DIRECTION, match($facing){
			Facing::UP => Values::TORCH_FACING_DIRECTION_TOP,
			Facing::NORTH => Values::TORCH_FACING_DIRECTION_NORTH,
			Facing::SOUTH => Values::TORCH_FACING_DIRECTION_SOUTH,
			Facing::WEST => Values::TORCH_FACING_DIRECTION_WEST,
			Facing::EAST => Values::TORCH_FACING_DIRECTION_EAST,
			default => throw new BlockStateSerializeException("Invalid Torch facing $facing")
		});
		return $this;
	}

	/** @return $this */
	public function writeCoralType(CoralType $coralType) : self{
		$this->writeString(BlockStateNames::CORAL_COLOR, match($coralType->id()){
			CoralType::TUBE()->id() => Values::CORAL_COLOR_BLUE,
			CoralType::BRAIN()->id() => Values::CORAL_COLOR_PINK,
			CoralType::BUBBLE()->id() => Values::CORAL_COLOR_PURPLE,
			CoralType::FIRE()->id() => Values::CORAL_COLOR_RED,
			CoralType::HORN()->id() => Values::CORAL_COLOR_YELLOW,
			default => throw new BlockStateSerializeException("Invalid Coral type " . $coralType->name())
		});
		return $this;
	}

	/** @return $this */
	public function writeBellAttachmentType(BellAttachmentType $attachmentType) : self{
		$this->writeString(BlockStateNames::ATTACHMENT, match($attachmentType->id()){
			BellAttachmentType::FLOOR()->id() => Values::ATTACHMENT_STANDING,
			BellAttachmentType::CEILING()->id() => Values::ATTACHMENT_HANGING,
			BellAttachmentType::ONE_WALL()->id() => Values::ATTACHMENT_SIDE,
			BellAttachmentType::TWO_WALLS()->id() => Values::ATTACHMENT_MULTIPLE,
			default => throw new BlockStateSerializeException("Invalid Bell attachment type " . $attachmentType->name())
		});
		return $this;
	}

	public function writeBlockStateNbt() : CompoundTag{
		//TODO: add `version` field
		return CompoundTag::create()
			->setString("name", $this->id)
			->setTag("states", $this->states);
	}
}
