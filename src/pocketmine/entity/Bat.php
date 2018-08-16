<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Bat extends Animal{

	const NETWORK_ID = self::BAT;

	public $width = 0.6;
	public $height = 1.8;

	public function getName(): string{
		return "Bat";
	}
}
