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


namespace pocketmine\utils;


use function ceil;
use function sqrt;
use function strlen;

class SerializedImage{
	/** @var int */
	private $width;
	/** @var int */
	private $height;
	/** @var string */
	private $data;

	public function __construct(int $width, int $height, string $data){
		if(strlen($data) !== ($width * $height) * 4) {
			$width = $height = (int) ceil(sqrt(strlen($data) / 4));
		}

		$this->width = $width;
		$this->height = $height;
		$this->data = $data;
	}

	public static function null() : SerializedImage{
		return new self(0, 0, "");
	}

	public static function fromLegacy(string $skinData) : SerializedImage{
		switch(strlen($skinData)){
			case 0:
				return self::null();
			case 64 * 32 * 4:
				return new SerializedImage(64, 32, $skinData);
			case 64 * 64 * 4:
				return new SerializedImage(64, 64, $skinData);
			case 128 * 64 * 4:
				return new SerializedImage(128, 64, $skinData);
			case 128 * 128 * 4:
				return new SerializedImage(128, 128, $skinData);
		}
		throw new \InvalidArgumentException("Unknown legacy skin size");
	}

	public function getWidth() : int{
		return $this->width;
	}

	public function getHeight() : int{
		return $this->height;
	}

	public function getData() : string{
		return $this->data;
	}
}
