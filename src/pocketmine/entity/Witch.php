<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Witch extends Monster{

	const NETWORK_ID = self::WITCH;

	public function getName(): string{
		return "Witch";
	}
}