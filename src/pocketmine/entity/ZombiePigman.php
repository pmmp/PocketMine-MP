<?php

declare(strict_types=1);

namespace pocketmine\entity;

class ZombiePigman extends Zombie{

	const NETWORK_ID = self::ZOMBIE_PIGMAN;

	public function getName(): string{
		return "Zombie Pigman";
	}
}