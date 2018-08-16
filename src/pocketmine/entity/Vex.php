<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Vex extends Monster{

	const NETWORK_ID = self::VEX;

	public $width = 0.4;
	public $height = 0.8;

	public function getName(): string{
		return "Vex";
	}
}