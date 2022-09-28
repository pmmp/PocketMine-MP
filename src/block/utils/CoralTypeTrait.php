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

trait CoralTypeTrait{

	protected CoralType $coralType;
	protected bool $dead = false;

	public function getCoralType() : CoralType{ return $this->coralType; }

	/** @return $this */
	public function setCoralType(CoralType $coralType) : self{
		$this->coralType = $coralType;
		return $this;
	}

	public function isDead() : bool{ return $this->dead; }

	/** @return $this */
	public function setDead(bool $dead) : self{
		$this->dead = $dead;
		return $this;
	}
}
