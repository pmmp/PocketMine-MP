<?php

declare(strict_types=1);

namespace pocketmine\entity;

class SnowGolem extends Monster{

	const NETWORK_ID = self::SNOW_GOLEM;

	public function getName(): string{
		return "Snow Golem";
	}
}