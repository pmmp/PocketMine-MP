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

use pocketmine\block\utils\AgeableTrait;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

class Cactus extends Transparent{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 15;

	public function hasEntityCollision() : bool{
		return true;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
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
		if(!$this->getSide(Facing::DOWN)->hasSameTypeId($this)){
			$world = $this->position->getWorld();
			if($this->age === self::MAX_AGE){
				for($y = 1; $y < 3; ++$y){
					if(!$world->isInWorld($this->position->x, $this->position->y + $y, $this->position->z)){
						break;
					}
					$b = $world->getBlockAt($this->position->x, $this->position->y + $y, $this->position->z);
					if($b->getTypeId() === BlockTypeIds::AIR){
						BlockEventHelper::grow($b, VanillaBlocks::CACTUS(), null);
					}else{
						break;
					}
				}
				$this->age = 0;
				$world->setBlock($this->position, $this, update: false);
			}else{
				++$this->age;
				$world->setBlock($this->position, $this, update: false);
			}
		}
	}
}
