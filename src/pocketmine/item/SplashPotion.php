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
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class SplashPotion extends ProjectileItem{
	public function __construct(int $meta = 0){
		parent::__construct(self::SPLASH_POTION, $meta, "Splash Potion");
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getProjectileEntityType() : string{
		return "SplashPotion";
	}

	public function getThrowForce() : float{
		return 1.1;
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		$nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight(), 0), $directionVector, $player->yaw, $player->pitch);
		$nbt->PotionId = new ShortTag("PotionId", $this->meta);
		$projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);
		if($projectile !== null){
			$projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));
		}
		$this->count--;
		if($projectile instanceof Projectile){
			$player->getServer()->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($projectile));
			if($projectileEv->isCancelled()){
				$projectile->flagForDespawn();
			}else{
				$projectile->spawnToAll();
			}
		}else{
			$projectile->spawnToAll();
		}
		return true;
	}
}

