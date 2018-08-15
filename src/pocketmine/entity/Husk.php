<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Husk extends Zombie{

	const NETWORK_ID = self::HUSK;

	public function getName(): string{
		return "Husk";
	}
}