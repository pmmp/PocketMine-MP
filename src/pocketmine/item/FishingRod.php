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
use pocketmine\entity\projectile\FishingHook;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\player\PlayerFishEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;

class FishingRod extends Tool{

	public function __construct(){
		parent::__construct(self::FISHING_ROD, 0, "Fishing Rod");
	}

	public function getEnchantability() : int{
		return 1;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getMaxDurability() : int{
		return 65;
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		if($player->getFishingHook() === null){
			$hook = new FishingHook($player->level, Entity::createBaseNBT($player->add(0, $player->getEyeHeight() - 0.1, 0), $player->getDirectionVector()->multiply(0.4)), $player);
			($ev = new PlayerFishEvent($player, $hook, PlayerFishEvent::STATE_FISHING))->call();
			if($ev->isCancelled()){
				$hook->flagForDespawn();
			}else{
				$hook->spawnToAll();
			}
			return true;
		}else{
			$hook = $player->getFishingHook();
			$hook->handleHookRetraction();
			$this->applyDamage(1);
		}

		return true;
	}
}
