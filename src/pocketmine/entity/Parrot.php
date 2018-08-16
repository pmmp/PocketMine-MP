<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Parrot extends Animal{

	const NETWORK_ID = self::PARROT;

	public $width = 0.5;
	public $height = 0.9;

	public function getName(): string{
		return "Parrot";
	}
}