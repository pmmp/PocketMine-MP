<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Enderman extends Monster{

	const NETWORK_ID = self::ENDERMAN;

	public $width = 0.6;
	public $height = 2.9;

	public function getName(): string{
		return "Enderman";
	}
}
