<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Wolf extends Monster{

	const NETWORK_ID = self::WOLF;

	public function getName(): string{
		return "Wolf";
	}
}