<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Mule extends Horse{

	const NETWORK_ID = self::MULE;

	public function getName(): string{
		return "Mule";
	}
}