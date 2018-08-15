<?php

declare(strict_types=1);

namespace pocketmine\entity;

class PolarBear extends Monster{

	const NETWORK_ID = self::POLAR_BEAR;

	public function getName(): string{
		return "Polar Bear";
	}
}