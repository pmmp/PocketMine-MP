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
use pocketmine\math\Facing;
use function count;
use function log;

final class RuntimeDataSizeCalculator implements RuntimeDataDescriber{
	use LegacyRuntimeEnumDescriberTrait;

	private int $bits = 0;

	protected function addBits(int $bits) : void{
		$this->bits += $bits;
	}

	public function getBitsUsed() : int{
		return $this->bits;
	}

	public function int(int $bits, int &$value) : void{
		$this->addBits($bits);
	}

	/**
	 * @deprecated Use {@link self::boundedIntAuto()} instead.
	 */
	public function boundedInt(int $bits, int $min, int $max, int &$value) : void{
		$currentBits = $this->bits;
		$this->boundedIntAuto($min, $max, $value);
		$actualBits = $this->bits - $currentBits;
		if($actualBits !== $bits){
			throw new \InvalidArgumentException("Bits should be $actualBits for the given bounds, but received $bits. Use boundedIntAuto() for automatic bits calculation.");
		}
	}

	public function boundedIntAuto(int $min, int $max, int &$value) : void{
		$this->addBits(((int) log($max - $min, 2)) + 1);
	}

	public function bool(bool &$value) : void{
		$this->addBits(1);
	}

	public function horizontalFacing(int &$facing) : void{
		$this->addBits(2);
	}

	public function facingFlags(array &$faces) : void{
		$this->addBits(count(Facing::ALL));
	}

	public function horizontalFacingFlags(array &$faces) : void{
		$this->addBits(count(Facing::HORIZONTAL));
	}

	public function facing(int &$facing) : void{
		$this->addBits(3);
	}

	public function facingExcept(int &$facing, int $except) : void{
		$this->facing($facing);
	}

	public function axis(int &$axis) : void{
		$this->addBits(2);
	}

	public function horizontalAxis(int &$axis) : void{
		$this->addBits(1);
	}

	public function wallConnections(array &$connections) : void{
		$this->addBits(7);
	}

	public function brewingStandSlots(array &$slots) : void{
		$this->addBits(count(BrewingStandSlot::cases()));
	}

	public function railShape(int &$railShape) : void{
		$this->addBits(4);
	}

	public function straightOnlyRailShape(int &$railShape) : void{
		$this->addBits(3);
	}

	public function enum(\UnitEnum &$case) : void{
		$metadata = RuntimeEnumMetadata::from($case);
		$this->addBits($metadata->bits);
	}

	public function enumSet(array &$set, array $allCases) : void{
		$this->addBits(count($allCases));
	}
}
