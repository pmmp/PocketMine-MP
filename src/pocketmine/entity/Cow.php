<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Cow extends Animal{

	const NETWORK_ID = self::COW;

	public function getName(): string{
		return "Cow";
	}
}
