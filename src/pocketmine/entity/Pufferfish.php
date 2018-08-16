<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Pufferfish extends Fish{

	const NETWORK_ID = self::PUFFERFISH;

	public $width = 0.35;
	public $height = 0.35;

	public function getName(): string{
		return "Pufferfish";
	}
}