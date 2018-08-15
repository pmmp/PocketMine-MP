<?php

declare(strict_types=1);

namespace pocketmine\entity;

class MagmaCube extends Monster{

	const NETWORK_ID = self::MAGMA_CUBE;

	public function getName(): string{
		return "Magma Cube";
	}
}