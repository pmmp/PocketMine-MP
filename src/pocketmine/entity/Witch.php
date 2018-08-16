<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Witch extends Monster{

	const NETWORK_ID = self::WITCH;

	public $width = 0.6;
	public $height = 1.95;

	public function getName(): string{
		return "Witch";
	}
}