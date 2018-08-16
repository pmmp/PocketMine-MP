<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Drowned extends Zombie{

	const NETWORK_ID = self::DROWNED;

	public function getName(): string{
		return "Drowned";
	}
}
