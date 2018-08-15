<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Wither extends Boss{

	const NETWORK_ID = self::WITHER;

	public function getName(): string{
		return "Wither";
	}
}