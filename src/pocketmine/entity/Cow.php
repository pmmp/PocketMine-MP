<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Cow extends Animal{

	const NETWORK_ID = self::COW;

	public $width = 0.9;
	public $height = 1.4;

	public function getName(): string{
		return "Cow";
	}
}
