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

namespace pocketmine\entity\projectile;

use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class EnderPearl extends Throwable{
	public const NETWORK_ID = self::ENDER_PEARL;

	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult{
		if($block->getId() !== Block::AIR and empty($block->getCollisionBoxes())){
			//TODO: remove this once block collision boxes are fixed properly
			$bb = new AxisAlignedBB(
				$block->x,
				$block->y,
				$block->z,
				$block->x + 1,
				$block->y + 1,
				$block->z + 1
			);

			return $bb->calculateIntercept($start, $end);
		}

		return parent::calculateInterceptWithBlock($block, $start, $end);
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$owner = $this->getOwningEntity();
		if($owner !== null){
			//TODO: check end gateways (when they are added)
			//TODO: spawn endermites at origin

			$this->level->broadcastLevelEvent($owner, LevelEventPacket::EVENT_PARTICLE_ENDERMAN_TELEPORT);
			$this->level->addSound(new EndermanTeleportSound($owner));
			$owner->teleport($event->getRayTraceResult()->getHitVector());
			$this->level->addSound(new EndermanTeleportSound($owner));

			$owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
		}

		$this->flagForDespawn();
	}
}
