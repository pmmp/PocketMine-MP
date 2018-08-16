<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Rabbit extends Animal{

	const NETWORK_ID = self::RABBIT;

	public $width = 0.4;
	public $height = 0.5;

	public function getName(): string{
		return "Rabbit";
	}
}