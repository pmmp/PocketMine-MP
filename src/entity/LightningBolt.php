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

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\sound\ThunderSound;

final class LightningBolt extends Entity{

	public static function getNetworkTypeId() : string{
		return EntityIds::LIGHTNING_BOLT;
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.8, 0.6);
	}

	protected function getInitialGravity() : float{
		return 0.0;
	}

	protected function getInitialDragMultiplier() : float{
		return 0.02;
	}

	public function getName() : string{
		return "Lightning Bolt";
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$this->setNameTagVisible(false);
	}

	public function onFirstUpdate(int $currentTick) : void{
		parent::onFirstUpdate($currentTick);
		$this->getWorld()->addSound($this->location, new ThunderSound());
		foreach($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expandedCopy(3, 3, 3), $this) as $entity){
			if($entity instanceof Living){
				$entity->attack(new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_LIGHTNING, 5.0));
			}
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->ticksLived > 20){
			$this->flagForDespawn();
		}

		return $hasUpdate;
	}
}
