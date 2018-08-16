<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Cod extends Fish{

	const NETWORK_ID = self::FISH;

	public $width = 0.5;
	public $height = 0.3;

	public function getName(): string{
		return "Cod";
	}
}