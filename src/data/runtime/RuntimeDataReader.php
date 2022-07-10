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

use pocketmine\block\utils\WallConnectionType;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;

final class RuntimeDataReader{

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

	public function readBoundedInt(int $bits, int $min, int $max) : int{
		$result = $this->readInt($bits);
		if($result < $min || $result > $max){
			throw new InvalidSerializedRuntimeDataException("Value is outside the range $min - $max");
		}
		return $result;
	}

	public function readBool() : bool{
		return $this->readInt(1) === 1;
	}

	public function readHorizontalFacing() : int{
		return match($this->readInt(2)){
			0 => Facing::NORTH,
			1 => Facing::EAST,
			2 => Facing::SOUTH,
			3 => Facing::WEST,
			default => throw new AssumptionFailedError("Unreachable")
		};
	}

	public function readFacing() : int{
		return match($this->readInt(3)){
			0 => Facing::DOWN,
			1 => Facing::UP,
			2 => Facing::NORTH,
			3 => Facing::SOUTH,
			4 => Facing::WEST,
			5 => Facing::EAST,
			default => throw new InvalidSerializedRuntimeDataException("Invalid facing value")
		};
	}

	public function readAxis() : int{
		return match($this->readInt(2)){
			0 => Axis::X,
			1 => Axis::Z,
			2 => Axis::Y,
			default => throw new InvalidSerializedRuntimeDataException("Invalid axis value")
		};
	}

	public function readHorizontalAxis() : int{
		return match($this->readInt(1)){
			0 => Axis::X,
			1 => Axis::Z,
			default => throw new AssumptionFailedError("Unreachable")
		};
	}

	/**
	 * @return WallConnectionType[]
	 * @phpstan-return array<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST, WallConnectionType>
	 */
	public function readWallConnections() : array{
		$connections = [];
		//TODO: we can pack this into 7 bits instead of 8
		foreach(Facing::HORIZONTAL as $facing){
			$type = $this->readBoundedInt(2, 0, 2);
			if($type !== 0){
				$connections[$facing] = match($type){
					1 => WallConnectionType::SHORT(),
					2 => WallConnectionType::TALL(),
					default => throw new AssumptionFailedError("Unreachable")
				};
			}
		}

		return $connections;
	}

	public function getOffset() : int{ return $this->offset; }
}
