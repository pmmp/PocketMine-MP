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

namespace pocketmine\block\utils;

class StructureAxes{
	public function __construct(
		protected bool $xMirror,
		protected bool $yMirror
	){}

	/**
	 * @internal
	 */
	public static function fromInt(int $mirror) : StructureAxes{
		$xMirror = ($mirror >> 0) & 1;
		$yMirror = ($mirror >> 1) & 1;
		return new StructureAxes((bool) $xMirror, (bool) $yMirror);
	}

	/**
	 * @internal
	 */
	public function toInt() : int{
		return $this->xMirror | ($this->yMirror << 1);
	}

	public function getXMirror() : bool{
		return $this->xMirror;
	}

	/**
	 * @return $this
	 */
	public function setXMirror(bool $xMirror) : self{
		$this->xMirror = $xMirror;
		return $this;
	}

	public function getYMirror() : bool{
		return $this->yMirror;
	}

	/**
	 * @return $this
	 */
	public function setYMirror(bool $yMirror) : self{
		$this->yMirror = $yMirror;
		return $this;
	}
}
