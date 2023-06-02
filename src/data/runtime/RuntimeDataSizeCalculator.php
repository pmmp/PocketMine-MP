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

final class RuntimeDataSizeCalculator implements RuntimeDataDescriber{
	use RuntimeEnumSizeCalculatorTrait;

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

	public function boundedInt(int $bits, int $min, int $max, int &$value) : void{
		$this->addBits($bits);
	}

	public function bool(bool &$value) : void{
		$this->addBits(1);
	}

	public function horizontalFacing(int &$facing) : void{
		$this->addBits(2);
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
		$this->addBits(count(BrewingStandSlot::getAll()));
	}

	public function railShape(int &$railShape) : void{
		$this->addBits(4);
	}

	public function straightOnlyRailShape(int &$railShape) : void{
		$this->addBits(3);
	}
}
