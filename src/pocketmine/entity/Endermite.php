<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Endermite extends Monster{

	const NETWORK_ID = self::ENDERMITE;

	public $width = 0.3;
	public $height = 0.4;

	public function getName(): string{
		return "Endermite";
	}
}
