<?php

declare(strict_types=1);

namespace pocketmine\entity;

class MagmaCube extends Monster{

	const NETWORK_ID = self::MAGMA_CUBE;

	public $width = 0.5;
	public $height = 0.5;

	public function getName(): string{
		return "Magma Cube";
	}
}