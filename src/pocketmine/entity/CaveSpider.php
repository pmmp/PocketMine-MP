<?php

declare(strict_types=1);

namespace pocketmine\entity;

class CaveSpider extends Monster{

	const NETWORK_ID = self::CAVE_SPIDER;

	public function getName(): string{
		return "Cave Spider";
	}
}
