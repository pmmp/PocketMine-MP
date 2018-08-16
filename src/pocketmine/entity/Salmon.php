<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Salmon extends Fish{

	const NETWORK_ID = self::SALMON;

	public $width = 0.7;
	public $height = 0.4;

	public function getName(): string{
		return "Salmon";
	}
}