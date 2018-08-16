<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Mooshroom extends Cow{

	const NETWORK_ID = self::MOOSHROOM;

	public function getName(): string{
		return "Mooshroom";
	}
}