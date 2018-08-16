<?php

declare(strict_types=1);

namespace pocketmine\entity;

class ZombieHorse extends Horse{

	const NETWORK_ID = self::ZOMBIE_HORSE;

	public function getName(): string{
		return "Zombie Horse";
	}
}