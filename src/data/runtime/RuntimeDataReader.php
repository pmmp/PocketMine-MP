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
use pocketmine\block\utils\RailConnectionInfo;
use pocketmine\block\utils\WallConnectionType;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;
use function get_class;
use function intdiv;
use function log;
use function spl_object_id;

final class RuntimeDataReader implements RuntimeDataDescriber{
	use LegacyRuntimeEnumDescriberTrait;

	private int $offset = 0;

	public function __construct(
		private int $maxBits,
		private int $value
	){}

	public function readInt(int $bits) : int{
		$bitsLeft = $this->maxBits - $this->offset;
		if($bits > $bitsLeft){
			throw new \InvalidArgumentException("No bits left in buffer (need $bits, have $bitsLeft");
		}

		$value = ($this->value >> $this->offset) & ~(~0 << $bits);
		$this->offset += $bits;
		return $value;
	}

	public function int(int $bits, int &$value) : void{
		$value = $this->readInt($bits);
	}

	/**
	 * @deprecated Use {@link self::boundedIntAuto()} instead.
	 */
	public function boundedInt(int $bits, int $min, int $max, int &$value) : void{
		$offset = $this->offset;
		$this->boundedIntAuto($min, $max, $value);
		$actualBits = $this->offset - $offset;
		if($this->offset !== $offset + $bits){
			throw new \InvalidArgumentException("Bits should be $actualBits for the given bounds, but received $bits. Use boundedIntAuto() for automatic bits calculation.");
		}
	}

	private function readBoundedIntAuto(int $min, int $max) : int{
		$bits = ((int) log($max - $min, 2)) + 1;
		$result = $this->readInt($bits) + $min;
		if($result < $min || $result > $max){
			throw new InvalidSerializedRuntimeDataException("Value is outside the range $min - $max");
		}
		return $result;
	}

	public function boundedIntAuto(int $min, int $max, int &$value) : void{
		$value = $this->readBoundedIntAuto($min, $max);
	}

	protected function readBool() : bool{
		return $this->readInt(1) === 1;
	}

	public function bool(bool &$value) : void{
		$value = $this->readBool();
	}

	public function horizontalFacing(int &$facing) : void{
		$facing = match($this->readInt(2)){
			0 => Facing::NORTH,
			1 => Facing::EAST,
			2 => Facing::SOUTH,
			3 => Facing::WEST,
			default => throw new AssumptionFailedError("Unreachable")
		};
	}

	/**
	 * @param int[] $faces
	 */
	public function facingFlags(array &$faces) : void{
		$result = [];
		foreach(Facing::ALL as $facing){
			if($this->readBool()){
				$result[$facing] = $facing;
			}
		}

		$faces = $result;
	}

	/**
	 * @param int[] $faces
	 */
	public function horizontalFacingFlags(array &$faces) : void{
		$result = [];
		foreach(Facing::HORIZONTAL as $facing){
			if($this->readBool()){
				$result[$facing] = $facing;
			}
		}

		$faces = $result;
	}

	public function facing(int &$facing) : void{
		$facing = match($this->readInt(3)){
			0 => Facing::DOWN,
			1 => Facing::UP,
			2 => Facing::NORTH,
			3 => Facing::SOUTH,
			4 => Facing::WEST,
			5 => Facing::EAST,
			default => throw new InvalidSerializedRuntimeDataException("Invalid facing value")
		};
	}

	public function facingExcept(int &$facing, int $except) : void{
		$result = 0;
		$this->facing($result);
		if($result === $except){
			throw new InvalidSerializedRuntimeDataException("Illegal facing value");
		}

		$facing = $result;
	}

	public function axis(int &$axis) : void{
		$axis = match($this->readInt(2)){
			0 => Axis::X,
			1 => Axis::Z,
			2 => Axis::Y,
			default => throw new InvalidSerializedRuntimeDataException("Invalid axis value")
		};
	}

	public function horizontalAxis(int &$axis) : void{
		$axis = match($this->readInt(1)){
			0 => Axis::X,
			1 => Axis::Z,
			default => throw new AssumptionFailedError("Unreachable")
		};
	}

	/**
	 * @param WallConnectionType[] $connections
	 * @phpstan-param array<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST, WallConnectionType> $connections
	 */
	public function wallConnections(array &$connections) : void{
		$result = [];
		$offset = 0;
		$packed = $this->readBoundedIntAuto(0, (3 ** 4) - 1);
		foreach(Facing::HORIZONTAL as $facing){
			$type = intdiv($packed,  (3 ** $offset)) % 3;
			if($type !== 0){
				$result[$facing] = match($type){
					1 => WallConnectionType::SHORT,
					2 => WallConnectionType::TALL,
					default => throw new AssumptionFailedError("Unreachable")
				};
			}
			$offset++;
		}

		$connections = $result;
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
		$result = $this->readInt(4);
		if(!isset(RailConnectionInfo::CONNECTIONS[$result]) && !isset(RailConnectionInfo::CURVE_CONNECTIONS[$result])){
			throw new InvalidSerializedRuntimeDataException("Invalid rail shape $result");
		}

		$railShape = $result;
	}

	public function straightOnlyRailShape(int &$railShape) : void{
		$result = $this->readInt(3);
		if(!isset(RailConnectionInfo::CONNECTIONS[$result])){
			throw new InvalidSerializedRuntimeDataException("No rail shape matches meta $result");
		}

		$railShape = $result;
	}

	public function enum(\UnitEnum &$case) : void{
		$metadata = RuntimeEnumMetadata::from($case);
		$raw = $this->readInt($metadata->bits);
		$result = $metadata->intToEnum($raw);
		if($result === null){
			throw new InvalidSerializedRuntimeDataException("Invalid serialized value $raw for " . get_class($case));
		}

		$case = $result;
	}

	public function enumSet(array &$set, array $allCases) : void{
		$result = [];
		foreach($allCases as $case){
			if($this->readBool()){
				$result[spl_object_id($case)] = $case;
			}
		}
		$set = $result;
	}

	public function getOffset() : int{ return $this->offset; }
}
