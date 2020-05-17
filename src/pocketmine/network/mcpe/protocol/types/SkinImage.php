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

use function strlen;

class SkinImage{

	/** @var int */
	private $height;
	/** @var int */
	private $width;
	/** @var string */
	private $data;

	public function __construct(int $height, int $width, string $data){
		if($height < 0 or $width < 0){
			throw new \InvalidArgumentException("Height and width cannot be negative");
		}
		if(($expected = $height * $width * 4) !== ($actual = strlen($data))){
			throw new \InvalidArgumentException("Data should be exactly $expected bytes, got $actual bytes");
		}
		$this->height = $height;
		$this->width = $width;
		$this->data = $data;
	}

	public static function fromLegacy(string $data) : SkinImage{
		switch(strlen($data)){
			case 64 * 32 * 4:
				return new self(32, 64, $data);
			case 64 * 64 * 4:
				return new self(64, 64, $data);
			case 128 * 64 * 4:
				return new self(64, 128, $data);
			case 128 * 128 * 4:
				return new self(128, 128, $data);
		}

		throw new \InvalidArgumentException("Unknown size");
	}

	public function getHeight() : int{
		return $this->height;
	}

	public function getWidth() : int{
		return $this->width;
	}

	public function getData() : string{
		return $this->data;
	}
}
