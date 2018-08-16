<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Chicken extends Animal{

	const NETWORK_ID = self::CHICKEN;

	public $width = 0.6;
	public $height = 1.8;

	public function getName(): string{
		return "Chicken";
	}
}
