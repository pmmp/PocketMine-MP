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

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\entity\EntityCombustByBlockEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use function min;
use function mt_rand;

class Fire extends Flowable{

	/** @var int */
	protected $age = 0;

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->age = BlockDataValidator::readBoundedInt("age", $stateMeta, 0, 15);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function hasEntityCollision() : bool{
		return true;
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

	public function onEntityInside(Entity $entity) : void{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 1);
		$entity->attack($ev);

		$ev = new EntityCombustByBlockEvent($this, $entity, 8);
		if($entity instanceof Arrow){
			$ev->setCancelled();
		}
		$ev->call();
		if(!$ev->isCancelled()){
			$entity->setOnFire($ev->getDuration());
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Facing::DOWN)->isSolid() and !$this->hasAdjacentFlammableBlocks()){
			$this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR));
		}else{
			$this->level->scheduleDelayedBlockUpdate($this, mt_rand(30, 40));
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$down = $this->getSide(Facing::DOWN);

		$result = null;
		if($this->age < 15 and mt_rand(0, 2) === 0){
			$this->age++;
			$result = $this;
		}
		$canSpread = true;

		if(!$down->burnsForever()){
			//TODO: check rain
			if($this->age === 15){
				if(!$down->isFlammable() and mt_rand(0, 3) === 3){ //1/4 chance to extinguish
					$canSpread = false;
					$result = BlockFactory::get(Block::AIR);
				}
			}elseif(!$this->hasAdjacentFlammableBlocks()){
				$canSpread = false;
				if(!$down->isSolid() or $this->age > 3){
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
			$this->burnBlock($this->getSide(Facing::UP), 350);
			$this->burnBlock($this->getSide(Facing::DOWN), 350);

			//TODO: fire spread
		}
	}

	public function onScheduledUpdate() : void{
		$this->onRandomTick();
	}

	private function hasAdjacentFlammableBlocks() : bool{
		foreach(Facing::ALL as $face){
			if($this->getSide($face)->isFlammable()){
				return true;
			}
		}

		return false;
	}

	private function burnBlock(Block $block, int $chanceBound) : void{
		if(mt_rand(0, $chanceBound) < $block->getFlammability()){
			$ev = new BlockBurnEvent($block, $this);
			$ev->call();
			if(!$ev->isCancelled()){
				$block->onIncinerate();

				if(mt_rand(0, $this->age + 9) < 5){ //TODO: check rain
					$fire = clone $this;
					$fire->age = min(15, $fire->age + (mt_rand(0, 4) >> 2));
					$this->level->setBlock($block, $fire);
				}else{
					$this->level->setBlock($block, BlockFactory::get(Block::AIR));
				}
			}
		}
	}
}
