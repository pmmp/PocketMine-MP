<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Sheep extends Animal{

	const NETWORK_ID = self::SHEEP;

	public $width = 0.9;
	public $height = 1.3;

	public function getName(): string{
		return "Sheep";
	}
}