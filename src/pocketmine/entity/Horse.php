<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Horse extends Animal{

	const NETWORK_ID = self::HORSE;

	public function getName(): string{
		return "Horse";
	}
}
