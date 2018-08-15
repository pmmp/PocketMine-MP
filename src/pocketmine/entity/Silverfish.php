<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Silverfish extends Monster{

	const NETWORK_ID = self::SILVERFISH;

	public function getName(): string{
		return "Silverfish";
	}
}