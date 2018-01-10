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

namespace pocketmine\entity\projectile;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerEnderPearlTeleportEvent;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\math\Vector3;
use pocketmine\Player;

class EnderPearl extends Throwable{
	const NETWORK_ID = self::ENDER_PEARL;

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		if($this->isCollided){
			if(($player = $this->getOwningEntity()) instanceof Player and $player->isAlive() and $this->y > 0){
				$ev = new PlayerEnderPearlTeleportEvent($player, $this);
				$player->getServer()->getPluginManager()->callEvent($ev);
				if(!$ev->isCancelled()){
					$player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_FALL, 5));
					for($i = 0; $i < 5; ++$i)
						$player->getLevel()->addParticle(new PortalParticle(new Vector3($player->x + mt_rand(-15, 15) / 10, $player->y + mt_rand(0, 20) / 10, $player->z + mt_rand(-15, 15) / 10)));
					$player->getLevel()->addSound(new EndermanTeleportSound($this));
					$player->teleport($this);
				}
			}
			$this->flagForDespawn();
			return true;
		}else{
			return parent::entityBaseTick($tickDiff);
		}
	}
	
	public function onCollideWithEntity(Entity $entity){
		if($entity instanceof Player and $entity->isSpectator()){
			return;
		}
		
		$this->isCollided = true;
	}
}
