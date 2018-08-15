<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Vex extends Monster{

	const NETWORK_ID = self::VEX;

	public function getName(): string{
		return "Vex";
	}
}