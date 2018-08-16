<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Dolphin extends WaterAnimal{

	const NETWORK_ID = self::DOLPHIN;

	// No point of reference available for these sizes.
	public $width = 2.1;
	public $height = 0.6;

	public function getName(): string{
		return "Dolphin";
	}
}
