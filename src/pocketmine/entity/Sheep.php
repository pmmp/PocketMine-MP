<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Sheep extends Animal{

	const NETWORK_ID = self::SHEEP;

	public function getName(): string{
		return "Sheep";
	}
}