<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Creeper extends Monster{

	const NETWORK_ID = self::CREEPER;

	public function getName(): string{
		return "Creeper";
	}
}
