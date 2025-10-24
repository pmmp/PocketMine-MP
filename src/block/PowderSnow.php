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

use pocketmine\block\utils\BlockEventHelper;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityExtinguishEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\BucketFillPowderSnowSound;

class PowderSnow extends Flowable{

	public function hasEntityCollision() : bool{
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function onEntityLand(Entity $entity) : ?float{
		$entity->resetFallDistance();
		return null;
	}

	public function onEntityInside(Entity $entity) : bool{
		$entity->resetFallDistance();
		if($entity instanceof Living && $entity->isFreezable()){
			$entity->setAccumulatingFreeze(true);
		}

		if($entity->isOnFire()){
			$entity->extinguish(EntityExtinguishEvent::CAUSE_POWDER_SNOW);
			BlockEventHelper::melt($this, VanillaBlocks::AIR());
		}
		return true;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player !== null && $item->getTypeId() === ItemTypeIds::BUCKET){
			if(!$player->isCreative()){
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
