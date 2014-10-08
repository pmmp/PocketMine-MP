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

namespace pocketmine\entity;


use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\nbt\tag\String;
use pocketmine\nbt\tag\Byte;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;

class FallingBlock extends Entity{

	const NETWORK_ID = 66;

	public $width = 0.98;
	public $length = 0.98;
	public $height = 0.98;

	protected $gravity = 0.04;
	protected $drag = 0.02;
	protected $blockId = 0;

	public $canCollide = false;

	protected function initEntity(){
		$this->namedtag->id = new String("id", "FallingSand");
		if(isset($this->namedtag->Tile)){
			$this->blockId = $this->namedtag["Tile"];
		}

		if($this->blockId === 0){
			$this->close();
		}
	}

	public function canCollideWith(Entity $entity){
		return false;
	}

	public function getData(){
		return [];
	}

	public function onUpdate(){

		if($this->closed){
			return false;
		}

		$this->timings->startTiming();

		$this->entityBaseTick();

		if(!$this->dead){
			if($this->ticksLived === 1){
				$block = $this->level->getBlock($this->floor());
				if($block->getID() != $this->blockId){
					$this->kill();
					return true;
				}
				$this->level->setBlock($this->floor(), Block::get(0, true));

			}

			$this->motionY -= $this->gravity;

			$this->move($this->motionX, $this->motionY, $this->motionZ);

			$friction = 1 - $this->drag;

			$this->motionX *= $friction;
			$this->motionY *= 1 - $this->drag;
			$this->motionZ *= $friction;

			$pos = $this->floor();

			if($this->onGround){
				$this->kill();
				$block = $this->level->getBlock($pos);
				if(!$block->isFullBlock){
					$this->getLevel()->dropItem($this, Item::get($this->getBlock(), 0, 1));
				}else{
					$this->getLevel()->setBlock($pos, Block::get($this->getBlock(), 0), true);
				}
			}

			$this->updateMovement();
		}


		return !$this->onGround or ($this->motionX == 0 and $this->motionY == 0 and $this->motionZ == 0);
	}

	public function getBlock(){
		return $this->blockId;
	}

	public function saveNBT(){
		$this->namedtag->Tile = new Byte("Tile", $this->blockId);
	}

	public function attack($damage, $source = EntityDamageEvent::CAUSE_MAGIC){

	}

	public function heal($amount, $source = EntityRegainHealthEvent::CAUSE_MAGIC){

	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket;
		$pk->type = FallingBlock::NETWORK_ID;
		$pk->eid = $this->getID();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->did = -$this->getBlock();
		$player->dataPacket($pk);

		$pk = new SetEntityMotionPacket;
		$pk->entities = [
			[$this->getID(), $this->motionX, $this->motionY, $this->motionZ]
		];
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}
}