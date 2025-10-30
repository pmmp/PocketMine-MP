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

use pocketmine\block\utils\Ageable;
use pocketmine\block\utils\AgeableTrait;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use function mt_rand;

class Cactus extends Transparent implements Ageable{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 15;
	public const MAX_HEIGHT = 3;

	public function hasEntityCollision() : bool{
		return true;
	}

	protected function recalculateCollisionBoxes() : array{
		$shrinkSize = 1 / 16;
		return [AxisAlignedBB::one()->contract($shrinkSize, 0, $shrinkSize)->trim(Facing::UP, $shrinkSize)];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function onEntityInside(Entity $entity) : bool{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_CONTACT, 1);
		$entity->attack($ev);
		return true;
	}

	private function canBeSupportedAt(Block $block) : bool{
		$supportBlock = $block->getSide(Facing::DOWN);
		if(!$supportBlock->hasSameTypeId($this) && !$supportBlock->hasTypeTag(BlockTypeTags::SAND)){
			return false;
		}
		foreach(Facing::HORIZONTAL as $side){
			if($block->getSide($side)->isSolid()){
				return false;
			}
		}

		return true;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$up = $this->getSide(Facing::UP);
		if($up->getTypeId() !== BlockTypeIds::AIR){
			return;
		}

		$world = $this->position->getWorld();

		if(!$world->isInWorld($up->position->x, $up->position->y, $up->position->z)){
			return;
		}

		$height = 1;
		while($height < self::MAX_HEIGHT && $this->getSide(Facing::DOWN, $height)->hasSameTypeId($this)){
			$height++;
		}

		if($this->age === 9){
			$canGrowFlower = true;
			foreach(Facing::HORIZONTAL as $side){
				if($up->getSide($side)->isSolid()){
					$canGrowFlower = false;
					break;
				}
			}

			if($canGrowFlower){
				$chance = $height >= self::MAX_HEIGHT ? 25 : 10;
				if(mt_rand(1, 100) <= $chance){
					if(BlockEventHelper::grow($up, VanillaBlocks::CACTUS_FLOWER(), null)){
						$this->age = 0;
						$world->setBlock($this->position, $this, update: false);
					}
					return;
				}
			}
		}

		if($this->age === self::MAX_AGE){
			$this->age = 0;

			if($height < self::MAX_HEIGHT){
				BlockEventHelper::grow($up, VanillaBlocks::CACTUS(), null);
			}
		}else{
			++$this->age;
		}
		$world->setBlock($this->position, $this, update: false);
	}
}
