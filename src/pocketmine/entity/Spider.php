<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Spider extends Monster{

	const NETWORK_ID = self::SPIDER;

	public function getName(): string{
		return "Spider";
	}
}