<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Shulker extends Monster{

	const NETWORK_ID = self::SHULKER;

	public $width = 1.0;
	public $height = 1.0;

	public function getName(): string{
		return "Shulker";
	}
}