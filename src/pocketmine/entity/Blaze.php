<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Blaze extends Monster{

	const NETWORK_ID = self::BLAZE;

	public $width = 0.6;
	public $height = 1.8;

	public function getName(): string{
		return "Blaze";
	}
}
