<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Vindicator extends Illager{

	const NETWORK_ID = self::VINDICATOR;

	public function getName(): string{
		return "Vindicator";
	}
}