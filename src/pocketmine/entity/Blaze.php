<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Blaze extends Monster{

	const NETWORK_ID = self::BLAZE;

	public function getName(): string{
		return "Blaze";
	}
}
