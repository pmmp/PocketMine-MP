<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Creeper extends Monster{

	const NETWORK_ID = self::CREEPER;

	public $width = 0.6;
	public $height = 1.7;

	public function getName(): string{
		return "Creeper";
	}
}
