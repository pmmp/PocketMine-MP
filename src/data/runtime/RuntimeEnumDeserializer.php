<?php

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
