<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Bat extends Animal{

	const NETWORK_ID = self::BAT;

	public function getName(): string{
		return "Bat";
	}
}
