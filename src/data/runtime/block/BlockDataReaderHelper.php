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

namespace pocketmine\data\runtime\block;

use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\block\utils\SkullType;
use pocketmine\block\utils\SlabType;
use pocketmine\block\utils\StairShape;
use pocketmine\block\utils\TreeType;

final class BlockDataReaderHelper{

	public static function readBellAttachmentType(BlockDataReader $r) : BellAttachmentType{
		return match($r->readInt(2)){
			0 => \pocketmine\block\utils\BellAttachmentType::CEILING(),
			1 => \pocketmine\block\utils\BellAttachmentType::FLOOR(),
			2 => \pocketmine\block\utils\BellAttachmentType::ONE_WALL(),
			3 => \pocketmine\block\utils\BellAttachmentType::TWO_WALLS(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for BellAttachmentType")
		};
	}

	/**
	 * @return \pocketmine\block\utils\BrewingStandSlot[]
	 * @phpstan-return array<int, \pocketmine\block\utils\BrewingStandSlot>
	 */
	public static function readBrewingStandSlotKeySet(BlockDataReader $r) : array{
		$result = [];
		foreach([
			\pocketmine\block\utils\BrewingStandSlot::EAST(),
			\pocketmine\block\utils\BrewingStandSlot::NORTHWEST(),
			\pocketmine\block\utils\BrewingStandSlot::SOUTHWEST(),
		] as $member){
			if($r->readBool()){
				$result[$member->id()] = $member;
			}
		}
		return $result;
	}

	public static function readCoralType(BlockDataReader $r) : CoralType{
		return match($r->readInt(3)){
			0 => \pocketmine\block\utils\CoralType::BRAIN(),
			1 => \pocketmine\block\utils\CoralType::BUBBLE(),
			2 => \pocketmine\block\utils\CoralType::FIRE(),
			3 => \pocketmine\block\utils\CoralType::HORN(),
			4 => \pocketmine\block\utils\CoralType::TUBE(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for CoralType")
		};
	}

	public static function readDyeColor(BlockDataReader $r) : DyeColor{
		return match($r->readInt(4)){
			0 => \pocketmine\block\utils\DyeColor::BLACK(),
			1 => \pocketmine\block\utils\DyeColor::BLUE(),
			2 => \pocketmine\block\utils\DyeColor::BROWN(),
			3 => \pocketmine\block\utils\DyeColor::CYAN(),
			4 => \pocketmine\block\utils\DyeColor::GRAY(),
			5 => \pocketmine\block\utils\DyeColor::GREEN(),
			6 => \pocketmine\block\utils\DyeColor::LIGHT_BLUE(),
			7 => \pocketmine\block\utils\DyeColor::LIGHT_GRAY(),
			8 => \pocketmine\block\utils\DyeColor::LIME(),
			9 => \pocketmine\block\utils\DyeColor::MAGENTA(),
			10 => \pocketmine\block\utils\DyeColor::ORANGE(),
			11 => \pocketmine\block\utils\DyeColor::PINK(),
			12 => \pocketmine\block\utils\DyeColor::PURPLE(),
			13 => \pocketmine\block\utils\DyeColor::RED(),
			14 => \pocketmine\block\utils\DyeColor::WHITE(),
			15 => \pocketmine\block\utils\DyeColor::YELLOW(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for DyeColor")
		};
	}

	public static function readLeverFacing(BlockDataReader $r) : LeverFacing{
		return match($r->readInt(3)){
			0 => \pocketmine\block\utils\LeverFacing::DOWN_AXIS_X(),
			1 => \pocketmine\block\utils\LeverFacing::DOWN_AXIS_Z(),
			2 => \pocketmine\block\utils\LeverFacing::EAST(),
			3 => \pocketmine\block\utils\LeverFacing::NORTH(),
			4 => \pocketmine\block\utils\LeverFacing::SOUTH(),
			5 => \pocketmine\block\utils\LeverFacing::UP_AXIS_X(),
			6 => \pocketmine\block\utils\LeverFacing::UP_AXIS_Z(),
			7 => \pocketmine\block\utils\LeverFacing::WEST(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for LeverFacing")
		};
	}

	public static function readMushroomBlockType(BlockDataReader $r) : MushroomBlockType{
		return match($r->readInt(4)){
			0 => \pocketmine\block\utils\MushroomBlockType::ALL_CAP(),
			1 => \pocketmine\block\utils\MushroomBlockType::CAP_EAST(),
			2 => \pocketmine\block\utils\MushroomBlockType::CAP_MIDDLE(),
			3 => \pocketmine\block\utils\MushroomBlockType::CAP_NORTH(),
			4 => \pocketmine\block\utils\MushroomBlockType::CAP_NORTHEAST(),
			5 => \pocketmine\block\utils\MushroomBlockType::CAP_NORTHWEST(),
			6 => \pocketmine\block\utils\MushroomBlockType::CAP_SOUTH(),
			7 => \pocketmine\block\utils\MushroomBlockType::CAP_SOUTHEAST(),
			8 => \pocketmine\block\utils\MushroomBlockType::CAP_SOUTHWEST(),
			9 => \pocketmine\block\utils\MushroomBlockType::CAP_WEST(),
			10 => \pocketmine\block\utils\MushroomBlockType::PORES(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for MushroomBlockType")
		};
	}

	public static function readSkullType(BlockDataReader $r) : SkullType{
		return match($r->readInt(3)){
			0 => \pocketmine\block\utils\SkullType::CREEPER(),
			1 => \pocketmine\block\utils\SkullType::DRAGON(),
			2 => \pocketmine\block\utils\SkullType::PLAYER(),
			3 => \pocketmine\block\utils\SkullType::SKELETON(),
			4 => \pocketmine\block\utils\SkullType::WITHER_SKELETON(),
			5 => \pocketmine\block\utils\SkullType::ZOMBIE(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for SkullType")
		};
	}

	public static function readSlabType(BlockDataReader $r) : SlabType{
		return match($r->readInt(2)){
			0 => \pocketmine\block\utils\SlabType::BOTTOM(),
			1 => \pocketmine\block\utils\SlabType::DOUBLE(),
			2 => \pocketmine\block\utils\SlabType::TOP(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for SlabType")
		};
	}

	public static function readStairShape(BlockDataReader $r) : StairShape{
		return match($r->readInt(3)){
			0 => \pocketmine\block\utils\StairShape::INNER_LEFT(),
			1 => \pocketmine\block\utils\StairShape::INNER_RIGHT(),
			2 => \pocketmine\block\utils\StairShape::OUTER_LEFT(),
			3 => \pocketmine\block\utils\StairShape::OUTER_RIGHT(),
			4 => \pocketmine\block\utils\StairShape::STRAIGHT(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for StairShape")
		};
	}

	public static function readTreeType(BlockDataReader $r) : TreeType{
		return match($r->readInt(3)){
			0 => \pocketmine\block\utils\TreeType::ACACIA(),
			1 => \pocketmine\block\utils\TreeType::BIRCH(),
			2 => \pocketmine\block\utils\TreeType::DARK_OAK(),
			3 => \pocketmine\block\utils\TreeType::JUNGLE(),
			4 => \pocketmine\block\utils\TreeType::OAK(),
			5 => \pocketmine\block\utils\TreeType::SPRUCE(),
			default => throw new \pocketmine\block\utils\InvalidBlockStateException("Invalid serialized value for TreeType")
		};
	}

}
