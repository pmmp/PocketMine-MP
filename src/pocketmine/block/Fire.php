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

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Server;

class Fire extends Flowable{

	protected $id = self::FIRE;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function getName() : string{
		return "Fire Block";
	}

	public function getLightLevel() : int{
		return 15;
	}

	public function isBreakable(Item $item) : bool{
		return false;
	}

	public function canBeReplaced() : bool{
		return true;
	}

	public function onEntityCollide(Entity $entity) : void{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 1);
		$entity->attack($ev);

		$ev = new EntityCombustByBlockEvent($this, $entity, 8);
		if($entity instanceof Arrow){
			$ev->setCancelled();
		}
		Server::getInstance()->getPluginManager()->callEvent($ev);
		if(!$ev->isCancelled()){
			$entity->setOnFire($ev->getDuration());
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Vector3::SIDE_DOWN)->isSolid() and !$this->hasAdjacentFlammableBlocks()){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$down = $this->getSide(Vector3::SIDE_DOWN);

		$result = null;
		if($this->meta < 15){
			$this->meta++;
			$result = $this;
		}

		if(!$down->burnsForever()){
			if($this->meta === 15){
				if(!$down->isFlammable() and mt_rand(0, 3) === 3){ //1/4 chance to extinguish
					$result = BlockFactory::get(Block::AIR);
				}
			}elseif(!$this->hasAdjacentFlammableBlocks()){
				if(!$down->isSolid() or $this->meta > 3){ //fire older than 3, or without a solid block below
					$result = BlockFactory::get(Block::AIR);
				}
			}
		}

		if($result !== null){
			$this->level->setBlock($this, $result);
			if($result->getId() !== $this->id){
				return;
			}
		}

		$this->level->scheduleDelayedBlockUpdate($this, 30 + mt_rand(0, 10));

		//TODO: fire spread
	}

	public function onScheduledUpdate() : void{
		$this->onRandomTick();
	}

	private function hasAdjacentFlammableBlocks() : bool{
		for($i = 0; $i <= 5; ++$i){
			if($this->getSide($i)->isFlammable()){
				return true;
			}
		}

		return false;
	}
}
