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

namespace pocketmine\block;

use pocketmine\data\runtime\block\BlockDataReader;
use pocketmine\data\runtime\block\BlockDataWriter;

abstract class SimplePressurePlate extends PressurePlate{
	protected bool $pressed = false;

	public function getRequiredStateDataBits() : int{ return 1; }

	protected function decodeState(BlockDataReader $r) : void{
		$this->pressed = $r->readBool();
	}

	protected function encodeState(BlockDataWriter $w) : void{
		$w->writeBool($this->pressed);
	}

	public function isPressed() : bool{ return $this->pressed; }

	/** @return $this */
	public function setPressed(bool $pressed) : self{
		$this->pressed = $pressed;
		return $this;
	}
}
