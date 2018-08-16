<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Horse extends Animal{

	const NETWORK_ID = self::HORSE;

	public $width = 1.3965;
	public $height = 1.6;

	public function getName(): string{
		return "Horse";
	}
}
