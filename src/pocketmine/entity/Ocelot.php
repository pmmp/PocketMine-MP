<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Ocelot extends Animal{

	const NETWORK_ID = self::OCELOT;

	public function getName(): string{
		return "Ocelot";
	}
}