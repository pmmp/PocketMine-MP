<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Ocelot extends Animal{

	const NETWORK_ID = self::OCELOT;

	public $width = 0.6;
	public $height = 0.7;

	public function getName(): string{
		return "Ocelot";
	}
}