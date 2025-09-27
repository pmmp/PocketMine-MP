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
use function get_class;
use function intdiv;
use function log;
use function spl_object_id;

final class RuntimeDataReader implements RuntimeDataDescriber{
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

	public function facingExcept(Facing &$facing, Facing $except) : void{
		$result = Facing::DOWN;
		$this->enum($result);
		if($result === $except){
			throw new InvalidSerializedRuntimeDataException("Illegal facing value");
		}

		$facing = $result;
	}

	public function horizontalAxis(Axis &$axis) : void{
		$axis = match($this->readInt(1)){
			0 => Axis::X,
			1 => Axis::Z,
			default => throw new AssumptionFailedError("Unreachable")
		};
	}

	/**
	 * @param WallConnectionType[] $connections
	 * @phpstan-param array<value-of<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST>, WallConnectionType> $connections
	 */
	public function wallConnections(array &$connections) : void{
		$result = [];
		$offset = 0;
		$packed = $this->readBoundedIntAuto(0, (3 ** 4) - 1);
		foreach(Facing::HORIZONTAL as $facing){
			$type = intdiv($packed,  (3 ** $offset)) % 3;
			if($type !== 0){
				$result[$facing->value] = match($type){
					1 => WallConnectionType::SHORT,
					2 => WallConnectionType::TALL,
					default => throw new AssumptionFailedError("Unreachable")
				};
			}
			$offset++;
		}

		$connections = $result;
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
