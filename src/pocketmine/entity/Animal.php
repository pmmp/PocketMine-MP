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

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\Player;

abstract class Animal extends Mob implements Ageable{

	protected $inLove = 0;

	public function isBaby() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_BABY);
	}

	public function getTalkInterval() : int{
		return 120;
	}

	public function isBreedingItem(Item $item) : bool{ // TODO: Apply this to all animals
		return $item->getId() === Item::WHEAT;
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos, int $slot) : bool{
		if($this->isBreedingItem($item) and $this->aiEnabled){
			if(!$this->isBaby() and !$this->isInLove()){
				$this->setInLove(true);

				if($player->isSurvival()){
					$item->pop();
				}
				return true;
			}elseif($this->isBaby()){
				if($player->isSurvival()){
					$item->pop();
				}
				return true;
			}
		}
		return false;
	}

	public function entityBaseTick(int $diff = 1) : bool{
		if($this->isInLove()){
			if($this->inLove-- > 0 and $this->inLove % 10 === 0){
				$this->broadcastEntityEvent(EntityEventPacket::LOVE_PARTICLES);
			}
		}
		return parent::entityBaseTick($diff);
	}

	public function setInLove(bool $value) : void{
		parent::setInLove($value);
		if($value){
			$this->inLove = 10;
		}
	}

	public function eatItem(Item $item) : void{
		$this->broadcastEntityEvent(EntityEventPacket::EATING_ITEM, $item->getId());
	}
}
