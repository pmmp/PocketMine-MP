<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Cod extends Fish{

	const NETWORK_ID = self::FISH;

	public function getName(): string{
		return "Cod";
	}
}