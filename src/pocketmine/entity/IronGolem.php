<?php

declare(strict_types=1);

namespace pocketmine\entity;

class IronGolem extends Monster{

	const NETWORK_ID = self::IRON_GOLEM;

	public $width = 1.4;
	public $height = 2.7;

	public function getName(): string{
		return "Iron Golem";
	}
}