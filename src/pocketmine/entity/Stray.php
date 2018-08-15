<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Stray extends Skeleton{

	const NETWORK_ID = self::STRAY;

	public function getName(): string{
		return "Stray";
	}
}