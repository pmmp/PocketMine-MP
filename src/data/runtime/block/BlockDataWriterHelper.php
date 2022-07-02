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

final class BlockDataWriterHelper{

	public static function writeBellAttachmentType(BlockDataWriter $w, BellAttachmentType $value) : void{
		$w->writeInt(2, match($value){
			\pocketmine\block\utils\BellAttachmentType::CEILING() => 0,
			\pocketmine\block\utils\BellAttachmentType::FLOOR() => 1,
			\pocketmine\block\utils\BellAttachmentType::ONE_WALL() => 2,
			\pocketmine\block\utils\BellAttachmentType::TWO_WALLS() => 3,
			default => throw new \pocketmine\utils\AssumptionFailedError("All BellAttachmentType cases should be covered")
		});
	}

	/**
	 * @param \pocketmine\block\utils\BrewingStandSlot[] $value
	 * @phpstan-param array<int, \pocketmine\block\utils\BrewingStandSlot> $value
	 */
	public static function writeBrewingStandSlotKeySet(BlockDataWriter $w, array $value) : void{
		foreach([
			\pocketmine\block\utils\BrewingStandSlot::EAST(),
			\pocketmine\block\utils\BrewingStandSlot::NORTHWEST(),
			\pocketmine\block\utils\BrewingStandSlot::SOUTHWEST(),
		] as $member){
			$w->writeBool(isset($value[$member->id()]));
		}
	}

	public static function writeCoralType(BlockDataWriter $w, CoralType $value) : void{
		$w->writeInt(3, match($value){
			\pocketmine\block\utils\CoralType::BRAIN() => 0,
			\pocketmine\block\utils\CoralType::BUBBLE() => 1,
			\pocketmine\block\utils\CoralType::FIRE() => 2,
			\pocketmine\block\utils\CoralType::HORN() => 3,
			\pocketmine\block\utils\CoralType::TUBE() => 4,
			default => throw new \pocketmine\utils\AssumptionFailedError("All CoralType cases should be covered")
		});
	}

	public static function writeDyeColor(BlockDataWriter $w, DyeColor $value) : void{
		$w->writeInt(4, match($value){
			\pocketmine\block\utils\DyeColor::BLACK() => 0,
			\pocketmine\block\utils\DyeColor::BLUE() => 1,
			\pocketmine\block\utils\DyeColor::BROWN() => 2,
			\pocketmine\block\utils\DyeColor::CYAN() => 3,
			\pocketmine\block\utils\DyeColor::GRAY() => 4,
			\pocketmine\block\utils\DyeColor::GREEN() => 5,
			\pocketmine\block\utils\DyeColor::LIGHT_BLUE() => 6,
			\pocketmine\block\utils\DyeColor::LIGHT_GRAY() => 7,
			\pocketmine\block\utils\DyeColor::LIME() => 8,
			\pocketmine\block\utils\DyeColor::MAGENTA() => 9,
			\pocketmine\block\utils\DyeColor::ORANGE() => 10,
			\pocketmine\block\utils\DyeColor::PINK() => 11,
			\pocketmine\block\utils\DyeColor::PURPLE() => 12,
			\pocketmine\block\utils\DyeColor::RED() => 13,
			\pocketmine\block\utils\DyeColor::WHITE() => 14,
			\pocketmine\block\utils\DyeColor::YELLOW() => 15,
			default => throw new \pocketmine\utils\AssumptionFailedError("All DyeColor cases should be covered")
		});
	}

	public static function writeLeverFacing(BlockDataWriter $w, LeverFacing $value) : void{
		$w->writeInt(3, match($value){
			\pocketmine\block\utils\LeverFacing::DOWN_AXIS_X() => 0,
			\pocketmine\block\utils\LeverFacing::DOWN_AXIS_Z() => 1,
			\pocketmine\block\utils\LeverFacing::EAST() => 2,
			\pocketmine\block\utils\LeverFacing::NORTH() => 3,
			\pocketmine\block\utils\LeverFacing::SOUTH() => 4,
			\pocketmine\block\utils\LeverFacing::UP_AXIS_X() => 5,
			\pocketmine\block\utils\LeverFacing::UP_AXIS_Z() => 6,
			\pocketmine\block\utils\LeverFacing::WEST() => 7,
			default => throw new \pocketmine\utils\AssumptionFailedError("All LeverFacing cases should be covered")
		});
	}

	public static function writeMushroomBlockType(BlockDataWriter $w, MushroomBlockType $value) : void{
		$w->writeInt(4, match($value){
			\pocketmine\block\utils\MushroomBlockType::ALL_CAP() => 0,
			\pocketmine\block\utils\MushroomBlockType::CAP_EAST() => 1,
			\pocketmine\block\utils\MushroomBlockType::CAP_MIDDLE() => 2,
			\pocketmine\block\utils\MushroomBlockType::CAP_NORTH() => 3,
			\pocketmine\block\utils\MushroomBlockType::CAP_NORTHEAST() => 4,
			\pocketmine\block\utils\MushroomBlockType::CAP_NORTHWEST() => 5,
			\pocketmine\block\utils\MushroomBlockType::CAP_SOUTH() => 6,
			\pocketmine\block\utils\MushroomBlockType::CAP_SOUTHEAST() => 7,
			\pocketmine\block\utils\MushroomBlockType::CAP_SOUTHWEST() => 8,
			\pocketmine\block\utils\MushroomBlockType::CAP_WEST() => 9,
			\pocketmine\block\utils\MushroomBlockType::PORES() => 10,
			default => throw new \pocketmine\utils\AssumptionFailedError("All MushroomBlockType cases should be covered")
		});
	}

	public static function writeSkullType(BlockDataWriter $w, SkullType $value) : void{
		$w->writeInt(3, match($value){
			\pocketmine\block\utils\SkullType::CREEPER() => 0,
			\pocketmine\block\utils\SkullType::DRAGON() => 1,
			\pocketmine\block\utils\SkullType::PLAYER() => 2,
			\pocketmine\block\utils\SkullType::SKELETON() => 3,
			\pocketmine\block\utils\SkullType::WITHER_SKELETON() => 4,
			\pocketmine\block\utils\SkullType::ZOMBIE() => 5,
			default => throw new \pocketmine\utils\AssumptionFailedError("All SkullType cases should be covered")
		});
	}

	public static function writeSlabType(BlockDataWriter $w, SlabType $value) : void{
		$w->writeInt(2, match($value){
			\pocketmine\block\utils\SlabType::BOTTOM() => 0,
			\pocketmine\block\utils\SlabType::DOUBLE() => 1,
			\pocketmine\block\utils\SlabType::TOP() => 2,
			default => throw new \pocketmine\utils\AssumptionFailedError("All SlabType cases should be covered")
		});
	}

	public static function writeStairShape(BlockDataWriter $w, StairShape $value) : void{
		$w->writeInt(3, match($value){
			\pocketmine\block\utils\StairShape::INNER_LEFT() => 0,
			\pocketmine\block\utils\StairShape::INNER_RIGHT() => 1,
			\pocketmine\block\utils\StairShape::OUTER_LEFT() => 2,
			\pocketmine\block\utils\StairShape::OUTER_RIGHT() => 3,
			\pocketmine\block\utils\StairShape::STRAIGHT() => 4,
			default => throw new \pocketmine\utils\AssumptionFailedError("All StairShape cases should be covered")
		});
	}

	public static function writeTreeType(BlockDataWriter $w, TreeType $value) : void{
		$w->writeInt(3, match($value){
			\pocketmine\block\utils\TreeType::ACACIA() => 0,
			\pocketmine\block\utils\TreeType::BIRCH() => 1,
			\pocketmine\block\utils\TreeType::DARK_OAK() => 2,
			\pocketmine\block\utils\TreeType::JUNGLE() => 3,
			\pocketmine\block\utils\TreeType::OAK() => 4,
			\pocketmine\block\utils\TreeType::SPRUCE() => 5,
			default => throw new \pocketmine\utils\AssumptionFailedError("All TreeType cases should be covered")
		});
	}

}
