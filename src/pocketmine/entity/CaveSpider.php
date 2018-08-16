<?php

declare(strict_types=1);

namespace pocketmine\entity;

class CaveSpider extends Monster{

	const NETWORK_ID = self::CAVE_SPIDER;

	public $width = 0.7;
	public $height = 0.5;

	public function getName(): string{
		return "Cave Spider";
	}
}
