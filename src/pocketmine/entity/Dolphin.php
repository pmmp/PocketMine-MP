<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Dolphin extends WaterAnimal{

	const NETWORK_ID = self::DOLPHIN;

	public function getName(): string{
		return "Dolphin";
	}
}
