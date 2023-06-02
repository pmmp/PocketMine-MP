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
trait RuntimeEnumDeserializerTrait{

	abstract protected function readInt(int $bits) : int;

	public function bellAttachmentType(\pocketmine\block\utils\BellAttachmentType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \pocketmine\block\utils\BellAttachmentType::CEILING(),
			1 => \pocketmine\block\utils\BellAttachmentType::FLOOR(),
			2 => \pocketmine\block\utils\BellAttachmentType::ONE_WALL(),
			3 => \pocketmine\block\utils\BellAttachmentType::TWO_WALLS(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for BellAttachmentType")
		};
	}

	public function copperOxidation(\pocketmine\block\utils\CopperOxidation &$value) : void{
		$value = match($this->readInt(2)){
			0 => \pocketmine\block\utils\CopperOxidation::EXPOSED(),
			1 => \pocketmine\block\utils\CopperOxidation::NONE(),
			2 => \pocketmine\block\utils\CopperOxidation::OXIDIZED(),
			3 => \pocketmine\block\utils\CopperOxidation::WEATHERED(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for CopperOxidation")
		};
	}

	public function coralType(\pocketmine\block\utils\CoralType &$value) : void{
		$value = match($this->readInt(3)){
			0 => \pocketmine\block\utils\CoralType::BRAIN(),
			1 => \pocketmine\block\utils\CoralType::BUBBLE(),
			2 => \pocketmine\block\utils\CoralType::FIRE(),
			3 => \pocketmine\block\utils\CoralType::HORN(),
			4 => \pocketmine\block\utils\CoralType::TUBE(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for CoralType")
		};
	}

	public function dirtType(\pocketmine\block\utils\DirtType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \pocketmine\block\utils\DirtType::COARSE(),
			1 => \pocketmine\block\utils\DirtType::NORMAL(),
			2 => \pocketmine\block\utils\DirtType::ROOTED(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for DirtType")
		};
	}

	public function dyeColor(\pocketmine\block\utils\DyeColor &$value) : void{
		$value = match($this->readInt(4)){
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

	public function froglightType(\pocketmine\block\utils\FroglightType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \pocketmine\block\utils\FroglightType::OCHRE(),
			1 => \pocketmine\block\utils\FroglightType::PEARLESCENT(),
			2 => \pocketmine\block\utils\FroglightType::VERDANT(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for FroglightType")
		};
	}

	public function leverFacing(\pocketmine\block\utils\LeverFacing &$value) : void{
		$value = match($this->readInt(3)){
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

	public function medicineType(\pocketmine\item\MedicineType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \pocketmine\item\MedicineType::ANTIDOTE(),
			1 => \pocketmine\item\MedicineType::ELIXIR(),
			2 => \pocketmine\item\MedicineType::EYE_DROPS(),
			3 => \pocketmine\item\MedicineType::TONIC(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for MedicineType")
		};
	}

	public function mobHeadType(\pocketmine\block\utils\MobHeadType &$value) : void{
		$value = match($this->readInt(3)){
			0 => \pocketmine\block\utils\MobHeadType::CREEPER(),
			1 => \pocketmine\block\utils\MobHeadType::DRAGON(),
			2 => \pocketmine\block\utils\MobHeadType::PLAYER(),
			3 => \pocketmine\block\utils\MobHeadType::SKELETON(),
			4 => \pocketmine\block\utils\MobHeadType::WITHER_SKELETON(),
			5 => \pocketmine\block\utils\MobHeadType::ZOMBIE(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for MobHeadType")
		};
	}

	public function mushroomBlockType(\pocketmine\block\utils\MushroomBlockType &$value) : void{
		$value = match($this->readInt(4)){
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

	public function potionType(\pocketmine\item\PotionType &$value) : void{
		$value = match($this->readInt(6)){
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

	public function slabType(\pocketmine\block\utils\SlabType &$value) : void{
		$value = match($this->readInt(2)){
			0 => \pocketmine\block\utils\SlabType::BOTTOM(),
			1 => \pocketmine\block\utils\SlabType::DOUBLE(),
			2 => \pocketmine\block\utils\SlabType::TOP(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for SlabType")
		};
	}

	public function suspiciousStewType(\pocketmine\item\SuspiciousStewType &$value) : void{
		$value = match($this->readInt(4)){
			0 => \pocketmine\item\SuspiciousStewType::ALLIUM(),
			1 => \pocketmine\item\SuspiciousStewType::AZURE_BLUET(),
			2 => \pocketmine\item\SuspiciousStewType::BLUE_ORCHID(),
			3 => \pocketmine\item\SuspiciousStewType::CORNFLOWER(),
			4 => \pocketmine\item\SuspiciousStewType::DANDELION(),
			5 => \pocketmine\item\SuspiciousStewType::LILY_OF_THE_VALLEY(),
			6 => \pocketmine\item\SuspiciousStewType::OXEYE_DAISY(),
			7 => \pocketmine\item\SuspiciousStewType::POPPY(),
			8 => \pocketmine\item\SuspiciousStewType::TULIP(),
			9 => \pocketmine\item\SuspiciousStewType::WITHER_ROSE(),
			default => throw new InvalidSerializedRuntimeDataException("Invalid serialized value for SuspiciousStewType")
		};
	}

}
