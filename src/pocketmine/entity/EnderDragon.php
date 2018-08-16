<?php

declare(strict_types=1);

namespace pocketmine\entity;

class EnderDragon extends Boss{

	const NETWORK_ID = self::ENDER_DRAGON;

	public $width = 16;
	public $height = 8.0;

	public function getName(): string{
		return "Ender Dragon";
	}
}