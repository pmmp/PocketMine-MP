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

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\level\sound\LaunchSound;
use pocketmine\Player;

class Bow extends Tool{
	public function __construct(int $meta = 0){
		parent::__construct(self::BOW, $meta, "Bow");
	}

	public function getFuelTime() : int{
		return 200;
	}

	public function getMaxDurability() : int{
		return 385;
	}

	public function onReleaseUsing(Player $player) : bool{
		if($player->isSurvival() and !$player->getInventory()->contains(ItemFactory::get(Item::ARROW, 0, 1))){
			$player->getInventory()->sendContents($player);
			return false;
		}

		$nbt = Entity::createBaseNBT(
			$player->add(0, $player->getEyeHeight(), 0),
			$player->getDirectionVector(),
			($player->yaw > 180 ? 360 : 0) - $player->yaw,
			-$player->pitch
		);
		$nbt->setShort("Fire", $player->isOnFire() ? 45 * 60 : 0);

		$diff = $player->getItemUseDuration();
		$p = $diff / 20;
		$force = min((($p ** 2) + $p * 2) / 3, 1) * 2;


		$entity = Entity::createEntity("Arrow", $player->getLevel(), $nbt, $player, $force == 2);
		if($entity instanceof Projectile){
			$ev = new EntityShootBowEvent($player, $this, $entity, $force);

			if($force < 0.1 or $diff < 5){
				$ev->setCancelled();
			}

			$player->getServer()->getPluginManager()->callEvent($ev);

			$entity = $ev->getProjectile(); //This might have been changed by plugins

			if($ev->isCancelled()){
				$entity->flagForDespawn();
				$player->getInventory()->sendContents($player);
			}else{
				$entity->setMotion($entity->getMotion()->multiply($ev->getForce()));
				if($player->isSurvival()){
					$player->getInventory()->removeItem(ItemFactory::get(Item::ARROW, 0, 1));
					$this->applyDamage(1);
				}

				if($entity instanceof Projectile){
					$player->getServer()->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($entity));
					if($projectileEv->isCancelled()){
						$ev->getProjectile()->flagForDespawn();
					}else{
						$ev->getProjectile()->spawnToAll();
						$player->level->addSound(new LaunchSound($player), $player->getViewers());
					}
				}else{
					$entity->spawnToAll();
				}
			}
		}else{
			$entity->spawnToAll();
		}

		return true;
	}
}
