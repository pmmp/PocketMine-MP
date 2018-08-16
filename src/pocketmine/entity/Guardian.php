<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Guardian extends Monster{

	const NETWORK_ID = self::GUARDIAN;

	public $width = 0.85;
	public $height = 0.85;

	public function getName(): string{
		return "Guardian";
	}
}