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

use pocketmine\block\utils\WallConnectionType;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;

final class BlockDataWriter{

	private int $value = 0;
	private int $offset = 0;

	public function __construct(
		private int $maxBits
	){}

	/** @return $this */
	public function writeInt(int $bits, int $value) : self{
		if($this->offset + $bits > $this->maxBits){
			throw new \InvalidArgumentException("Bit buffer cannot be larger than $this->maxBits bits (already have $this->offset bits)");
		}
		if(($value & (~0 << $bits)) !== 0){
			throw new \InvalidArgumentException("Value $value does not fit into $bits bits");
		}

		$this->value |= ($value << $this->offset);
		$this->offset += $bits;

		return $this;
	}

	/** @return $this */
	public function writeBool(bool $value) : self{
		return $this->writeInt(1, $value ? 1 : 0);
	}

	/** @return $this */
	public function writeHorizontalFacing(int $facing) : self{
		return $this->writeInt(2, match($facing){
			Facing::NORTH => 0,
			Facing::EAST => 1,
			Facing::SOUTH => 2,
			Facing::WEST => 3,
			default => throw new \InvalidArgumentException("Invalid horizontal facing $facing")
		});
	}

	public function writeFacing(int $facing) : self{
		return $this->writeInt(3, match($facing){
			0 => Facing::DOWN,
			1 => Facing::UP,
			2 => Facing::NORTH,
			3 => Facing::SOUTH,
			4 => Facing::WEST,
			5 => Facing::EAST,
			default => throw new \InvalidArgumentException("Invalid facing $facing")
		});
	}

	public function writeAxis(int $axis) : self{
		return $this->writeInt(2, match($axis){
			Axis::X => 0,
			Axis::Z => 1,
			Axis::Y => 2,
			default => throw new \InvalidArgumentException("Invalid axis $axis")
		});
	}

	public function writeHorizontalAxis(int $axis) : self{
		return $this->writeInt(1, match($axis){
			Axis::X => 0,
			Axis::Z => 1,
			default => throw new \InvalidArgumentException("Invalid horizontal axis $axis")
		});
	}

	/**
	 * @param WallConnectionType[] $connections
	 * @phpstan-param array<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST, WallConnectionType> $connections
	 */
	public function writeWallConnections(array $connections) : self{
		//TODO: we can pack this into 7 bits instead of 8
		foreach(Facing::HORIZONTAL as $facing){
			$this->writeInt(2, match($connections[$facing] ?? null){
				null => 0,
				WallConnectionType::SHORT() => 1,
				WallConnectionType::TALL() => 2,
				default => throw new AssumptionFailedError("Unreachable")
			});
		}

		return $this;
	}

	public function getValue() : int{ return $this->value; }

	public function getOffset() : int{ return $this->offset; }
}
