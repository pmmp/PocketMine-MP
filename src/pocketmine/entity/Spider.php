<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Spider extends Monster{

	const NETWORK_ID = self::SPIDER;

	public $width = 1.4;
	public $height = 0.9;

	public function getName(): string{
		return "Spider";
	}
}