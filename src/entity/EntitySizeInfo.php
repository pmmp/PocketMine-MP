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

namespace pocketmine\entity;

use function min;

final class EntitySizeInfo{
	/** @var float */
	private $height;
	/** @var float */
	private $width;
	/** @var float */
	private $eyeHeight;

	public function __construct(float $height, float $width, ?float $eyeHeight = null){
		$this->height = $height;
		$this->width = $width;
		$this->eyeHeight = $eyeHeight ?? min($this->height / 2 + 0.1, $this->height);
	}

	public function getHeight() : float{ return $this->height; }

	public function getWidth() : float{ return $this->width; }

	public function getEyeHeight() : float{ return $this->eyeHeight; }

	public function scale(float $newScale) : self{
		return new self(
			$this->height * $newScale,
			$this->width * $newScale,
			$this->eyeHeight * $newScale
		);
	}
}
