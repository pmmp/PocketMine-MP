<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Guardian extends Monster{

	const NETWORK_ID = self::GUARDIAN;

	public function getName(): string{
		return "Guardian";
	}
}