<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Silverfish extends Monster{

	const NETWORK_ID = self::SILVERFISH;

	public $width = 0.4;
	public $height = 0.3;

	public function getName(): string{
		return "Silverfish";
	}
}