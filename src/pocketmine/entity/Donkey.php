<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Donkey extends Horse{

	const NETWORK_ID = self::DONKEY;

	public function getName(): string{
		return "DONKEY";
	}
}
