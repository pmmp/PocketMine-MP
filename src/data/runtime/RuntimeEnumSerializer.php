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

namespace pocketmine\data\runtime;

/**
 * This class is auto-generated. Do not edit it manually.
 * @see build/generate-runtime-enum-serializers.php
 */
final class RuntimeEnumSerializer{

	public static function writeBellAttachmentType(RuntimeDataWriter $w, \pocketmine\block\utils\BellAttachmentType $value) : void{
		$w->writeInt(2, match($value){
			\pocketmine\block\utils\BellAttachmentType::CEILING() => 0,
			\pocketmine\block\utils\BellAttachmentType::FLOOR() => 1,
			\pocketmine\block\utils\BellAttachmentType::ONE_WALL() => 2,
			\pocketmine\block\utils\BellAttachmentType::TWO_WALLS() => 3,
			default => throw new \pocketmine\utils\AssumptionFailedError("All BellAttachmentType cases should be covered")
		});
	}

	public static function writeCopperOxidation(RuntimeDataWriter $w, \pocketmine\block\utils\CopperOxidation $value) : void{
		$w->writeInt(2, match($value){
			\pocketmine\block\utils\CopperOxidation::EXPOSED() => 0,
			\pocketmine\block\utils\CopperOxidation::NONE() => 1,
			\pocketmine\block\utils\CopperOxidation::OXIDIZED() => 2,
			\pocketmine\block\utils\CopperOxidation::WEATHERED() => 3,
			default => throw new \pocketmine\utils\AssumptionFailedError("All CopperOxidation cases should be covered")
		});
	}

	public static function writeCoralType(RuntimeDataWriter $w, \pocketmine\block\utils\CoralType $value) : void{
		$w->writeInt(3, match($value){
			\pocketmine\block\utils\CoralType::BRAIN() => 0,
			\pocketmine\block\utils\CoralType::BUBBLE() => 1,
			\pocketmine\block\utils\CoralType::FIRE() => 2,
			\pocketmine\block\utils\CoralType::HORN() => 3,
			\pocketmine\block\utils\CoralType::TUBE() => 4,
			default => throw new \pocketmine\utils\AssumptionFailedError("All CoralType cases should be covered")
		});
	}

	public static function writeDyeColor(RuntimeDataWriter $w, \pocketmine\block\utils\DyeColor $value) : void{
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

	public static function writeLeverFacing(RuntimeDataWriter $w, \pocketmine\block\utils\LeverFacing $value) : void{
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

	public static function writeMushroomBlockType(RuntimeDataWriter $w, \pocketmine\block\utils\MushroomBlockType $value) : void{
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

	public static function writePotionType(RuntimeDataWriter $w, \pocketmine\item\PotionType $value) : void{
		$w->writeInt(6, match($value){
			\pocketmine\item\PotionType::AWKWARD() => 0,
			\pocketmine\item\PotionType::FIRE_RESISTANCE() => 1,
			\pocketmine\item\PotionType::HARMING() => 2,
			\pocketmine\item\PotionType::HEALING() => 3,
			\pocketmine\item\PotionType::INVISIBILITY() => 4,
			\pocketmine\item\PotionType::LEAPING() => 5,
			\pocketmine\item\PotionType::LONG_FIRE_RESISTANCE() => 6,
			\pocketmine\item\PotionType::LONG_INVISIBILITY() => 7,
			\pocketmine\item\PotionType::LONG_LEAPING() => 8,
			\pocketmine\item\PotionType::LONG_MUNDANE() => 9,
			\pocketmine\item\PotionType::LONG_NIGHT_VISION() => 10,
			\pocketmine\item\PotionType::LONG_POISON() => 11,
			\pocketmine\item\PotionType::LONG_REGENERATION() => 12,
			\pocketmine\item\PotionType::LONG_SLOWNESS() => 13,
			\pocketmine\item\PotionType::LONG_SLOW_FALLING() => 14,
			\pocketmine\item\PotionType::LONG_STRENGTH() => 15,
			\pocketmine\item\PotionType::LONG_SWIFTNESS() => 16,
			\pocketmine\item\PotionType::LONG_TURTLE_MASTER() => 17,
			\pocketmine\item\PotionType::LONG_WATER_BREATHING() => 18,
			\pocketmine\item\PotionType::LONG_WEAKNESS() => 19,
			\pocketmine\item\PotionType::MUNDANE() => 20,
			\pocketmine\item\PotionType::NIGHT_VISION() => 21,
			\pocketmine\item\PotionType::POISON() => 22,
			\pocketmine\item\PotionType::REGENERATION() => 23,
			\pocketmine\item\PotionType::SLOWNESS() => 24,
			\pocketmine\item\PotionType::SLOW_FALLING() => 25,
			\pocketmine\item\PotionType::STRENGTH() => 26,
			\pocketmine\item\PotionType::STRONG_HARMING() => 27,
			\pocketmine\item\PotionType::STRONG_HEALING() => 28,
			\pocketmine\item\PotionType::STRONG_LEAPING() => 29,
			\pocketmine\item\PotionType::STRONG_POISON() => 30,
			\pocketmine\item\PotionType::STRONG_REGENERATION() => 31,
			\pocketmine\item\PotionType::STRONG_STRENGTH() => 32,
			\pocketmine\item\PotionType::STRONG_SWIFTNESS() => 33,
			\pocketmine\item\PotionType::STRONG_TURTLE_MASTER() => 34,
			\pocketmine\item\PotionType::SWIFTNESS() => 35,
			\pocketmine\item\PotionType::THICK() => 36,
			\pocketmine\item\PotionType::TURTLE_MASTER() => 37,
			\pocketmine\item\PotionType::WATER() => 38,
			\pocketmine\item\PotionType::WATER_BREATHING() => 39,
			\pocketmine\item\PotionType::WEAKNESS() => 40,
			\pocketmine\item\PotionType::WITHER() => 41,
			default => throw new \pocketmine\utils\AssumptionFailedError("All PotionType cases should be covered")
		});
	}

	public static function writeSkullType(RuntimeDataWriter $w, \pocketmine\block\utils\SkullType $value) : void{
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

	public static function writeSlabType(RuntimeDataWriter $w, \pocketmine\block\utils\SlabType $value) : void{
		$w->writeInt(2, match($value){
			\pocketmine\block\utils\SlabType::BOTTOM() => 0,
			\pocketmine\block\utils\SlabType::DOUBLE() => 1,
			\pocketmine\block\utils\SlabType::TOP() => 2,
			default => throw new \pocketmine\utils\AssumptionFailedError("All SlabType cases should be covered")
		});
	}

}
