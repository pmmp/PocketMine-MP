<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Evoker extends Illager{

	const NETWORK_ID = self::EVOCATION_ILLAGER;

	public function getName(): string{
		return "Evoker";
	}
}
