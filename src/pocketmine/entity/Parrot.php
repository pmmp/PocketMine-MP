<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Parrot extends Animal{

	const NETWORK_ID = self::PARROT;

	public function getName(): string{
		return "Parrot";
	}
}