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

use pocketmine\block\utils\SupportType;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityExtinguishEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\SnowflakeParticle;
use pocketmine\world\sound\BucketFillPowderSnowSound;
use function lcg_value;
use function mt_rand;

class PowderSnow extends Transparent {
	private const HORIZONTAL_SPEED_MULTIPLIER = 0.9;
	private const VERTICAL_SPEED_MULTIPLIER = 1.5;
	private const FALLING_COLLISION_HEIGHT = 0.9;

	public function isSolid() : bool {
		return false;
	}

	public function canBeReplaced() : bool {
		return true;
	}

	public function hasEntityCollision() : bool {
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array {
		return [];
	}

	public function onEntityInside(Entity $entity) : bool {
		if ($entity instanceof Living) {
			$entity->setFreezing(true);
			$motion = $entity->getMotion();
			$entity->setMotion($motion->multiply(self::HORIZONTAL_SPEED_MULTIPLIER));
			$entity->resetFallDistance();

			$bb = $entity->getBoundingBox();
			if($bb->maxY > $this->getPosition()->y && $bb->minY < $this->getPosition()->y + self::FALLING_COLLISION_HEIGHT){
				$entity->setMotion($entity->getMotion()->add(0, -0.05 * self::VERTICAL_SPEED_MULTIPLIER, 0));
			}
		}

		if(mt_rand(0, 5) === 0 && $entity->hasMovementUpdate()){
			$this->getPosition()->getWorld()->addParticle($this->getPosition()->add(lcg_value(), lcg_value(), lcg_value()), new SnowflakeParticle());
		}

		if ($entity->isOnFire()) {
			$entity->extinguish(EntityExtinguishEvent::CAUSE_POWDER_SNOW);
			$this->position->getWorld()->useBreakOn($this->position);
			//we should add an event here
		}
		return true;
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool {
		if ($player !== null && $item->getTypeId() === ItemTypeIds::BUCKET) {
			if (!$player->isCreative()) {
				$item->pop();
				$returnedItems[] = VanillaItems::POWDER_SNOW_BUCKET();
			}
			$this->getPosition()->getWorld()->setBlock($this->getPosition(), VanillaBlocks::AIR());
			$this->getPosition()->getWorld()->addSound($this->getPosition(), new BucketFillPowderSnowSound());

			return true;
		}

		return false;
	}
}
