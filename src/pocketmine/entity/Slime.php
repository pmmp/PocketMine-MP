<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Slime extends Monster{

	const NETWORK_ID = self::SLIME;

	public function getName(): string{
		return "Slime";
	}
}