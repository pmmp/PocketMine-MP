<?php

declare(strict_types=1);

namespace pocketmine\entity;

class ZombieVillager extends Zombie{

	const NETWORK_ID = self::ZOMBIE_VILLAGER;

	public function getName(): string{
		return "Zombie Villager";
	}
}