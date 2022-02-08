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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\NetworkBinaryStream;
use pocketmine\utils\Binary;

final class SubChunkPositionOffset{

	public function __construct(
		private int $xOffset,
		private int $yOffset,
		private int $zOffset,
	){
		self::clampOffset($this->xOffset);
		self::clampOffset($this->yOffset);
		self::clampOffset($this->zOffset);
	}

	private static function clampOffset(int $v) : void{
		if($v < -128 || $v > 127){
			throw new \InvalidArgumentException("Offsets must be within the range of a byte (-128 ... 127)");
		}
	}

	public function getXOffset() : int{ return $this->xOffset; }

	public function getYOffset() : int{ return $this->yOffset; }

	public function getZOffset() : int{ return $this->zOffset; }

	public static function read(NetworkBinaryStream $in) : self{
		$xOffset = Binary::signByte($in->getByte());
		$yOffset = Binary::signByte($in->getByte());
		$zOffset = Binary::signByte($in->getByte());

		return new self($xOffset, $yOffset, $zOffset);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putByte($this->xOffset);
		$out->putByte($this->yOffset);
		$out->putByte($this->zOffset);
	}
}
