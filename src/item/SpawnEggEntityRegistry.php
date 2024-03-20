<?php

namespace pocketmine\item;

use pocketmine\entity\EntityFactory;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;

final class SpawnEggEntityRegistry{
	use SingletonTrait;

	/**
	 * @phpstan-var array<string, SpawnEgg>
	 * @var SpawnEgg[]
	 */
	private array $entityMap = [];

	private function __construct(){
		$entityFactory = EntityFactory::getInstance();
		$this->register($entityFactory->getSaveId(Squid::class), VanillaItems::SQUID_SPAWN_EGG());
		$this->register($entityFactory->getSaveId(Villager::class), VanillaItems::VILLAGER_SPAWN_EGG());
		$this->register($entityFactory->getSaveId(Zombie::class), VanillaItems::ZOMBIE_SPAWN_EGG());
	}

	public function register(string $entitySaveId, SpawnEgg $spawnEgg) : void{
		$this->entityMap[$entitySaveId] = $spawnEgg;
	}

	public function getEntityId(SpawnEgg $item) : ?string{
		foreach(Utils::stringifyKeys($this->entityMap) as $entitySaveId => $spawnEgg){
			if($spawnEgg->equals($item, false, false)){
				return $entitySaveId;
			}
		}
		return null;
	}
}