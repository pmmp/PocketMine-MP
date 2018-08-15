<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Pig extends Animal{

	const NETWORK_ID = self::PIG;

	public function getName(): string{
		return "Pig";
	}
}