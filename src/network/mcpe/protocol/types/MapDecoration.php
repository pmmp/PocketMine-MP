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

use pocketmine\color\Color;

class MapDecoration{
	/** @var int */
	private $icon;
	/** @var int */
	private $rotation;
	/** @var int */
	private $xOffset;
	/** @var int */
	private $yOffset;
	/** @var string */
	private $label;
	/** @var Color */
	private $color;

	public function __construct(int $icon, int $rotation, int $xOffset, int $yOffset, string $label, Color $color){
		$this->icon = $icon;
		$this->rotation = $rotation;
		$this->xOffset = $xOffset;
		$this->yOffset = $yOffset;
		$this->label = $label;
		$this->color = $color;
	}

	public function getIcon() : int{
		return $this->icon;
	}

	public function getRotation() : int{
		return $this->rotation;
	}

	public function getXOffset() : int{
		return $this->xOffset;
	}

	public function getYOffset() : int{
		return $this->yOffset;
	}

	public function getLabel() : string{
		return $this->label;
	}

	public function getColor() : Color{
		return $this->color;
	}
}
