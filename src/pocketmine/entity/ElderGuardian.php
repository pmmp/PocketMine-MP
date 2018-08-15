<?php

declare(strict_types=1);

namespace pocketmine\entity;

class ElderGuardian extends Boss{

	const NETWORK_ID = self::ELDER_GUARDIAN;

	public function getName(): string{
		return "Elder Guardian";
	}
}