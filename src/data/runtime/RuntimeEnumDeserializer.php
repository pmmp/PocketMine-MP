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
final class RuntimeEnumDeserializer{

	public static function readBellAttachmentType(RuntimeDataReader $r) : \pocketmine\block\utils\BellAttachmentType{
		return match($r->readInt(2)){
			0 => \pocketmine\block\utils\BellAttachmentType::CEILING(),
			1 => \pocketmine\block\utils\BellAttachmentType::FLOOR(),
			2 => \pocketmine\block\utils\BellAttachmentType::ONE_WALL(),
			3 => \pocketmine\block\utils\BellAttachmentType::TWO_WALLS(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for BellAttachmentType")
		};
	}

	public static function readCoralType(RuntimeDataReader $r) : \pocketmine\block\utils\CoralType{
		return match($r->readInt(3)){
			0 => \pocketmine\block\utils\CoralType::BRAIN(),
			1 => \pocketmine\block\utils\CoralType::BUBBLE(),
			2 => \pocketmine\block\utils\CoralType::FIRE(),
			3 => \pocketmine\block\utils\CoralType::HORN(),
			4 => \pocketmine\block\utils\CoralType::TUBE(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for CoralType")
		};
	}

	public static function readDyeColor(RuntimeDataReader $r) : \pocketmine\block\utils\DyeColor{
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
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for DyeColor")
		};
	}

	public static function readLeverFacing(RuntimeDataReader $r) : \pocketmine\block\utils\LeverFacing{
		return match($r->readInt(3)){
			0 => \pocketmine\block\utils\LeverFacing::DOWN_AXIS_X(),
			1 => \pocketmine\block\utils\LeverFacing::DOWN_AXIS_Z(),
			2 => \pocketmine\block\utils\LeverFacing::EAST(),
			3 => \pocketmine\block\utils\LeverFacing::NORTH(),
			4 => \pocketmine\block\utils\LeverFacing::SOUTH(),
			5 => \pocketmine\block\utils\LeverFacing::UP_AXIS_X(),
			6 => \pocketmine\block\utils\LeverFacing::UP_AXIS_Z(),
			7 => \pocketmine\block\utils\LeverFacing::WEST(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for LeverFacing")
		};
	}

	public static function readMushroomBlockType(RuntimeDataReader $r) : \pocketmine\block\utils\MushroomBlockType{
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
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for MushroomBlockType")
		};
	}

	public static function readPotionType(RuntimeDataReader $r) : \pocketmine\item\PotionType{
		return match($r->readInt(6)){
			0 => \pocketmine\item\PotionType::AWKWARD(),
			1 => \pocketmine\item\PotionType::FIRE_RESISTANCE(),
			2 => \pocketmine\item\PotionType::HARMING(),
			3 => \pocketmine\item\PotionType::HEALING(),
			4 => \pocketmine\item\PotionType::INVISIBILITY(),
			5 => \pocketmine\item\PotionType::LEAPING(),
			6 => \pocketmine\item\PotionType::LONG_FIRE_RESISTANCE(),
			7 => \pocketmine\item\PotionType::LONG_INVISIBILITY(),
			8 => \pocketmine\item\PotionType::LONG_LEAPING(),
			9 => \pocketmine\item\PotionType::LONG_MUNDANE(),
			10 => \pocketmine\item\PotionType::LONG_NIGHT_VISION(),
			11 => \pocketmine\item\PotionType::LONG_POISON(),
			12 => \pocketmine\item\PotionType::LONG_REGENERATION(),
			13 => \pocketmine\item\PotionType::LONG_SLOWNESS(),
			14 => \pocketmine\item\PotionType::LONG_SLOW_FALLING(),
			15 => \pocketmine\item\PotionType::LONG_STRENGTH(),
			16 => \pocketmine\item\PotionType::LONG_SWIFTNESS(),
			17 => \pocketmine\item\PotionType::LONG_TURTLE_MASTER(),
			18 => \pocketmine\item\PotionType::LONG_WATER_BREATHING(),
			19 => \pocketmine\item\PotionType::LONG_WEAKNESS(),
			20 => \pocketmine\item\PotionType::MUNDANE(),
			21 => \pocketmine\item\PotionType::NIGHT_VISION(),
			22 => \pocketmine\item\PotionType::POISON(),
			23 => \pocketmine\item\PotionType::REGENERATION(),
			24 => \pocketmine\item\PotionType::SLOWNESS(),
			25 => \pocketmine\item\PotionType::SLOW_FALLING(),
			26 => \pocketmine\item\PotionType::STRENGTH(),
			27 => \pocketmine\item\PotionType::STRONG_HARMING(),
			28 => \pocketmine\item\PotionType::STRONG_HEALING(),
			29 => \pocketmine\item\PotionType::STRONG_LEAPING(),
			30 => \pocketmine\item\PotionType::STRONG_POISON(),
			31 => \pocketmine\item\PotionType::STRONG_REGENERATION(),
			32 => \pocketmine\item\PotionType::STRONG_STRENGTH(),
			33 => \pocketmine\item\PotionType::STRONG_SWIFTNESS(),
			34 => \pocketmine\item\PotionType::STRONG_TURTLE_MASTER(),
			35 => \pocketmine\item\PotionType::SWIFTNESS(),
			36 => \pocketmine\item\PotionType::THICK(),
			37 => \pocketmine\item\PotionType::TURTLE_MASTER(),
			38 => \pocketmine\item\PotionType::WATER(),
			39 => \pocketmine\item\PotionType::WATER_BREATHING(),
			40 => \pocketmine\item\PotionType::WEAKNESS(),
			41 => \pocketmine\item\PotionType::WITHER(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for PotionType")
		};
	}

	public static function readSkullType(RuntimeDataReader $r) : \pocketmine\block\utils\SkullType{
		return match($r->readInt(3)){
			0 => \pocketmine\block\utils\SkullType::CREEPER(),
			1 => \pocketmine\block\utils\SkullType::DRAGON(),
			2 => \pocketmine\block\utils\SkullType::PLAYER(),
			3 => \pocketmine\block\utils\SkullType::SKELETON(),
			4 => \pocketmine\block\utils\SkullType::WITHER_SKELETON(),
			5 => \pocketmine\block\utils\SkullType::ZOMBIE(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for SkullType")
		};
	}

	public static function readSlabType(RuntimeDataReader $r) : \pocketmine\block\utils\SlabType{
		return match($r->readInt(2)){
			0 => \pocketmine\block\utils\SlabType::BOTTOM(),
			1 => \pocketmine\block\utils\SlabType::DOUBLE(),
			2 => \pocketmine\block\utils\SlabType::TOP(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for SlabType")
		};
	}

}
