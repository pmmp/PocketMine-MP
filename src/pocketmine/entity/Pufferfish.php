<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Pufferfish extends Fish{

	const NETWORK_ID = self::PUFFERFISH;

	public function getName(): string{
		return "Pufferfish";
	}
}