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
use pocketmine\event\block\BlockBurnEvent;
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
		}else{
			$this->level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$down = $this->getSide(Vector3::SIDE_DOWN);

		$result = null;
		if($this->meta < 15 and mt_rand(0, 2) === 0){
			$this->meta++;
			$result = $this;
		}
		$canSpread = true;

		if(!$down->burnsForever()){
			//TODO: check rain
			if($this->meta === 15){
				if(!$down->isFlammable() and mt_rand(0, 3) === 3){ //1/4 chance to extinguish
					$canSpread = false;
					$result = BlockFactory::get(Block::AIR);
				}
			}elseif(!$this->hasAdjacentFlammableBlocks()){
				$canSpread = false;
				if(!$down->isSolid() or $this->meta > 3){ //fire older than 3, or without a solid block below
					$result = BlockFactory::get(Block::AIR);
				}
			}
		}

		if($result !== null){
			$this->level->setBlock($this, $result);
		}

		$this->level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));

		if($canSpread){
			//TODO: raise upper bound for chance in humid biomes

			foreach($this->getHorizontalSides() as $side){
				$this->burnBlock($side, 300);
			}

			//vanilla uses a 250 upper bound here, but I don't think they intended to increase the chance of incineration
			$this->burnBlock($this->getSide(Vector3::SIDE_UP), 350);
			$this->burnBlock($this->getSide(Vector3::SIDE_DOWN), 350);

			//TODO: fire spread
		}
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

	private function burnBlock(Block $block, int $chanceBound) : void{
		if(mt_rand(0, $chanceBound) < $block->getFlammability()){
			$this->level->getServer()->getPluginManager()->callEvent($ev = new BlockBurnEvent($block, $this));
			if(!$ev->isCancelled()){
				$block->onIncinerate();

				if(mt_rand(0, $this->meta + 9) < 5){ //TODO: check rain
					$this->level->setBlock($block, BlockFactory::get(Block::FIRE, min(15, $this->meta + (mt_rand(0, 4) >> 2))));
				}else{
					$this->level->setBlock($block, BlockFactory::get(Block::AIR));
				}
			}
		}
	}
}
