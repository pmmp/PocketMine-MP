<?php

declare(strict_types=1);

namespace pocketmine\entity;

class EnderDragon extends Boss{

	const NETWORK_ID = self::ENDER_DRAGON;

	public function getName(): string{
		return "Ender Dragon";
	}
}