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
use pocketmine\item\VanillaItems;
use pocketmine\world\sound\BucketFillPowderSnowSound;
use pocketmine\world\sound\Sound;

class PowderSnow extends Flowable{

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return VanillaItems::POWDER_SNOW_BUCKET();
	}

	public function getBucketFillSound() : Sound{
		return new BucketFillPowderSnowSound();
	}

	public function onEntityLand(Entity $entity) : ?float{
		$entity->resetFallDistance();
		return null;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool{
		$entity->resetFallDistance();
		if($entity instanceof Living && $entity->canFreeze()){
			$entity->setFreezeProgressState(true);
		}

		if($entity->isOnFire()){
			$entity->extinguish(EntityExtinguishEvent::CAUSE_POWDER_SNOW);
			BlockEventHelper::melt($this, VanillaBlocks::AIR());
		}
		return true;
	}
}
