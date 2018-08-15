<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Enderman extends Monster{

	const NETWORK_ID = self::ENDERMAN;

	public function getName(): string{
		return "Enderman";
	}
}
