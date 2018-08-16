<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Slime extends Monster{

	const NETWORK_ID = self::SLIME;

	public $width = 0.5;
	public $height = 0.5;

	public function getName(): string{
		return "Slime";
	}
}