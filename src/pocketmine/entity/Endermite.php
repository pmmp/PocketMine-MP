<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Endermite extends Monster{

	const NETWORK_ID = self::ENDERMITE;

	public function getName(): string{
		return "Endermite";
	}
}
