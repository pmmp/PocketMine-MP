<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\utils\SingletonTrait;

final class SpawnEggEntityRegistry{
	use SingletonTrait;

	/**
	 * @phpstan-var array<int, string>
	 * @var string[]
	 */
	private array $entityMap = [];

	private function __construct(){
		$this->register(EntityIds::SQUID, VanillaItems::SQUID_SPAWN_EGG());
		$this->register(EntityIds::VILLAGER, VanillaItems::VILLAGER_SPAWN_EGG());
		$this->register(EntityIds::ZOMBIE, VanillaItems::ZOMBIE_SPAWN_EGG());
	}

	public function register(string $entitySaveId, SpawnEgg $spawnEgg) : void{
		$this->entityMap[$spawnEgg->getTypeId()] = $entitySaveId;
	}

	public function getEntityId(SpawnEgg $item) : ?string{
		return $this->entityMap[$item->getTypeId()] ?? null;
	}
}
