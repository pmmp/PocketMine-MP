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

namespace pocketmine\item;

use pocketmine\entity\Effect;
use pocketmine\entity\Human;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\Player;
use pocketmine\Server;

abstract class Food extends Item{
	public abstract function getFoodRestore() : int;

	public abstract function getSaturationRestore() : float;

	public function getResidue() : Item{
		if($this->getCount() === 1){
			return Item::get(0);
		}else{
			$new = clone $this;
			$new->count--;
			return $new;
		}
	}

	/**
	 * @return Effect[]
	 */
	public function getAdditionEffects() : array{
		return [];
	}

	public function onEat(Human $human){
		$pk = new EntityEventPacket();
		$pk->eid = $human->getId();
		$pk->event = EntityEventPacket::USE_ITEM;
		if($human instanceof Player){
			$human->dataPacket($pk);
		}
		Server::broadcastPacket($human->getViewers(), $pk);
		
		$human->addSaturation($this->getSaturationRestore());
		$human->addFood($this->getFoodRestore());
		foreach($this->getAdditionEffects() as $effect){
			$human->addEffect($effect);
		}
		$human->getInventory()->setItemInHand($this->getResidue());
	}
}
