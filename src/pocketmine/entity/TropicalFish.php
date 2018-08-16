<?php

declare(strict_types=1);

namespace pocketmine\entity;

class TropicalFish extends Fish{

	const NETWORK_ID = self::TROPICAL_FISH;

	public $width = 0.5;
	public $height = 0.4;

	public function getName(): string{
		return "Tropical Fish";
	}
}