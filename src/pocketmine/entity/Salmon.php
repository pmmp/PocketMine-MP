<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Salmon extends Fish{

	const NETWORK_ID = self::SALMON;

	public function getName(): string{
		return "Salmon";
	}
}