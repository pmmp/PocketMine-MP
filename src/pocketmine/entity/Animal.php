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

use pocketmine\block\Block;
use pocketmine\block\Grass;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\Player;
use function max;

abstract class Animal extends Mob implements Ageable{

	protected $inLove = 0;
	protected $spawnableBlock = Block::GRASS;

	public function getBlockPathWeight(Vector3 $pos) : float{
		return $this->level->getBlock($pos->down()) instanceof Grass ? 10 : max($this->level->getBlockSkyLightAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ()), $this->level->getBlockLightAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())) - 0.5;
	}

	public function canSpawnHere() : bool{
		return $this->level->getBlock($this->down())->getId() === $this->spawnableBlock and $this->level->getBlockSkyLightAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ()) > 8 and parent::canSpawnHere();
	}

	public function isBreedingItem(Item $item) : bool{ // TODO: Apply this to all animals
		return $item->getId() === Item::WHEAT;
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		if($this->isBreedingItem($item) and !$this->isImmobile()){
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
		return parent::onInteract($player, $item, $clickPos);
	}

	public function entityBaseTick(int $diff = 1) : bool{
		if($this->isInLove()){
			if($this->inLove-- > 0 and $this->inLove % 10 === 0){
				$this->broadcastEntityEvent(ActorEventPacket::LOVE_PARTICLES);
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
		$this->broadcastEntityEvent(ActorEventPacket::EATING_ITEM, $item->getId());
	}

	public function eatGrassBonus(Vector3 $pos) : void{
		// for sheep
	}

	public function allowLeashing() : bool{
		return !$this->isLeashed() and !$this->isImmobile();
	}

	protected function initEntity() : void{
		parent::initEntity();

		$this->inLove = $this->namedtag->getInt("InLove", 0);
	}
	
	public function canDespawn() : bool{
		return false;
	}
	
	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setInt("InLove", $this->inLove);
	}

	public function getVariant() : int{
		return $this->propertyManager->getInt(self::DATA_VARIANT);
	}

	public function setVariant(int $variant) : void{
		$this->propertyManager->setInt(self::DATA_VARIANT, $variant);
	}

	public function getMarkVariant() : int{
		return $this->propertyManager->getInt(self::DATA_MARK_VARIANT);
	}

	public function setMarkVariant(int $markVariant) : void{
		$this->propertyManager->setInt(self::DATA_MARK_VARIANT, $markVariant);
	}
}
