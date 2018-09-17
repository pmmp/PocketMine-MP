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

use pocketmine\block\Block;

/**
 * Trait used by blocks which come in the usual 16 colours.
 */
trait ColorBlockTrait{

	/** @var int */
	protected $variant = 0;

	/**
	 * @see Block::getDamage()
	 * @return int
	 */
	public function getDamage() : int{
		return $this->variant;
	}

	/**
	 * @see Block::setDamage()
	 * @param int $meta
	 */
	public function setDamage(int $meta) : void{
		$this->variant = $meta;
	}

	/**
	 * @see Block::getVariant()
	 * @return int
	 */
	public function getVariant() : int{
		return $this->variant;
	}

	/**
	 * Returns the suffix for this coloured block's name.
	 * @return string
	 */
	abstract protected function getNameSuffix() : string;

	/**
	 * @see Block::getName()
	 * @return string
	 */
	public function getName() : string{
		static $names = [
			0 => "White",
			1 => "Orange",
			2 => "Magenta",
			3 => "Light Blue",
			4 => "Yellow",
			5 => "Lime",
			6 => "Pink",
			7 => "Gray",
			8 => "Light Gray",
			9 => "Cyan",
			10 => "Purple",
			11 => "Blue",
			12 => "Brown",
			13 => "Green",
			14 => "Red",
			15 => "Black"
		];

		return ($names[$this->variant] ?? "Unknown") . " " . $this->getNameSuffix();
	}
}
