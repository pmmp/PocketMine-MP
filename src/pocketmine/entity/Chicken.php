<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Chicken extends Animal{

	const NETWORK_ID = self::CHICKEN;

	public function getName(): string{
		return "Chicken";
	}
}
