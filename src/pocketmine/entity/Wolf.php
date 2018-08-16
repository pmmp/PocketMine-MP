<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Wolf extends Monster{

	const NETWORK_ID = self::WOLF;

	public $width = 0.6;
	public $height = 0.85;

	public function getName(): string{
		return "Wolf";
	}
}