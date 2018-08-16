<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Wither extends Boss{

	const NETWORK_ID = self::WITHER;

	public $width = 0.9;
	public $height = 3.5;

	public function getName(): string{
		return "Wither";
	}
}