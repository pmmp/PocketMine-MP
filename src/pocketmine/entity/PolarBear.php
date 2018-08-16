<?php

declare(strict_types=1);

namespace pocketmine\entity;

class PolarBear extends Monster{

	const NETWORK_ID = self::POLAR_BEAR;

	public $width = 1.3;
	public $height = 1.4;

	public function getName(): string{
		return "Polar Bear";
	}
}