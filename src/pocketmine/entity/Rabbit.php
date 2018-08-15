<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Rabbit extends Animal{

	const NETWORK_ID = self::RABBIT;

	public function getName(): string{
		return "Rabbit";
	}
}