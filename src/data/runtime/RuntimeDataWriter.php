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

use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\WallConnectionType;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use function array_flip;
use function log;
use function spl_object_id;

final class RuntimeDataWriter implements RuntimeDataDescriber{
	use LegacyRuntimeEnumDescriberTrait;

	private int $value = 0;
	private int $offset = 0;

	public function __construct(
		private int $maxBits
	){}

	public function writeInt(int $bits, int $value) : void{
		if($this->offset + $bits > $this->maxBits){
			throw new \InvalidArgumentException("Bit buffer cannot be larger than $this->maxBits bits (already have $this->offset bits)");
		}
		if(($value & (~0 << $bits)) !== 0){
			throw new \InvalidArgumentException("Value $value does not fit into $bits bits");
		}

		$this->value |= ($value << $this->offset);
		$this->offset += $bits;
	}

	public function int(int $bits, int &$value) : void{
		$this->writeInt($bits, $value);
	}

	/**
	 * @deprecated Use {@link self::boundedIntAuto()} instead.
	 */
	public function boundedInt(int $bits, int $min, int $max, int &$value) : void{
		$offset = $this->offset;
		$this->writeBoundedIntAuto($min, $max, $value);
		$actualBits = $this->offset - $offset;
		if($actualBits !== $bits){
			throw new \InvalidArgumentException("Bits should be $actualBits for the given bounds, but received $bits. Use boundedIntAuto() for automatic bits calculation.");
		}
	}

	private function writeBoundedIntAuto(int $min, int $max, int $value) : void{
		if($value < $min || $value > $max){
			throw new \InvalidArgumentException("Value $value is outside the range $min - $max");
		}
		$bits = ((int) log($max - $min, 2)) + 1;
		$this->writeInt($bits, $value - $min);
	}

	public function boundedIntAuto(int $min, int $max, int &$value) : void{
		$this->writeBoundedIntAuto($min, $max, $value);
	}

	protected function writeBool(bool $value) : void{
		$this->writeInt(1, $value ? 1 : 0);
	}

	public function bool(bool &$value) : void{
		$this->writeBool($value);
	}

	public function horizontalFacing(int &$facing) : void{
		$this->writeInt(2, match($facing){
			Facing::NORTH => 0,
			Facing::EAST => 1,
			Facing::SOUTH => 2,
			Facing::WEST => 3,
			default => throw new \InvalidArgumentException("Invalid horizontal facing $facing")
		});
	}

	/**
	 * @param int[] $faces
	 */
	public function facingFlags(array &$faces) : void{
		$uniqueFaces = array_flip($faces);
		foreach(Facing::ALL as $facing){
			$this->writeBool(isset($uniqueFaces[$facing]));
		}
	}

	/**
	 * @param int[] $faces
	 */
	public function horizontalFacingFlags(array &$faces) : void{
		$uniqueFaces = array_flip($faces);
		foreach(Facing::HORIZONTAL as $facing){
			$this->writeBool(isset($uniqueFaces[$facing]));
		}
	}

	public function facing(int &$facing) : void{
		$this->writeInt(3, match($facing){
			0 => Facing::DOWN,
			1 => Facing::UP,
			2 => Facing::NORTH,
			3 => Facing::SOUTH,
			4 => Facing::WEST,
			5 => Facing::EAST,
			default => throw new \InvalidArgumentException("Invalid facing $facing")
		});
	}

	public function facingExcept(int &$facing, int $except) : void{
		$this->facing($facing);
	}

	public function axis(int &$axis) : void{
		$this->writeInt(2, match($axis){
			Axis::X => 0,
			Axis::Z => 1,
			Axis::Y => 2,
			default => throw new \InvalidArgumentException("Invalid axis $axis")
		});
	}

	public function horizontalAxis(int &$axis) : void{
		$this->writeInt(1, match($axis){
			Axis::X => 0,
			Axis::Z => 1,
			default => throw new \InvalidArgumentException("Invalid horizontal axis $axis")
		});
	}

	/**
	 * @param WallConnectionType[] $connections
	 * @phpstan-param array<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST, WallConnectionType> $connections
	 */
	public function wallConnections(array &$connections) : void{
		$packed = 0;
		$offset = 0;
		foreach(Facing::HORIZONTAL as $facing){
			$packed += match($connections[$facing] ?? null){
				null => 0,
				WallConnectionType::SHORT => 1,
				WallConnectionType::TALL => 2,
			} * (3 ** $offset);
			$offset++;
		}
		$this->writeBoundedIntAuto(0, (3 ** 4) - 1, $packed);
	}

	/**
	 * @param BrewingStandSlot[] $slots
	 * @phpstan-param array<int, BrewingStandSlot> $slots
	 *
	 * @deprecated Use {@link enumSet()} instead.
	 */
	public function brewingStandSlots(array &$slots) : void{
		$this->enumSet($slots, BrewingStandSlot::cases());
	}

	public function railShape(int &$railShape) : void{
		$this->int(4, $railShape);
	}

	public function straightOnlyRailShape(int &$railShape) : void{
		$this->int(3, $railShape);
	}

	public function enum(\UnitEnum &$case) : void{
		$metadata = RuntimeEnumMetadata::from($case);
		$this->writeInt($metadata->bits, $metadata->enumToInt($case));
	}

	public function enumSet(array &$set, array $allCases) : void{
		foreach($allCases as $case){
			$this->writeBool(isset($set[spl_object_id($case)]));
		}
	}

	public function getValue() : int{ return $this->value; }

	public function getOffset() : int{ return $this->offset; }
}
