<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Ghast extends Monster{

	const NETWORK_ID = self::GHAST;

	public $width = 4.0;
	public $height = 4.0;

	public function getName(): string{
		return "Ghast";
	}
}