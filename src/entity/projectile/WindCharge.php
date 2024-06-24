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

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\WindExplosionParticle;

class WindCharge extends Throwable{
    private float $radius= 2.5;

	public static function getNetworkTypeId() : string{ return EntityIds::WIND_CHARGE_PROJECTILE; }

    protected function getInitialDragMultiplier() : float{ return 0; }
    protected function getInitialGravity() : float{ return 0; }
    

	protected function onHit(ProjectileHitEvent $event) : void{
        $source = $this->getPosition();

        //TODO implement wind charge explosion sound when added.
        $this->getWorld()->addParticle($source, new WindExplosionParticle());

        $minX = (int) floor($source->x - $this->radius - 1);
		$maxX = (int) ceil($source->x + $this->radius + 1);
		$minY = (int) floor($source->y - $this->radius - 1);
		$maxY = (int) ceil($source->y + $this->radius + 1);
		$minZ = (int) floor($source->z - $this->radius - 1);
		$maxZ = (int) ceil($source->z + $this->radius + 1);

        $bound = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
        $list = $source->getWorld()->getNearbyEntities($bound);

        foreach($list as $entity){
            $entityPos = $entity->getPosition();
			$distance = $entityPos->distance($source) / $this->radius;
			$motion = $entityPos->subtractVector($source)->normalize();
            $impact = (1 - $distance) * ($exposure = 1.5);

            if ($impact <= 0) continue;

            ($distance <= 1) ? $vertical = 0 : $vertical = 0.75;
			$entity->setMotion($entity->getMotion()->addVector($motion->multiply($impact)->add(0, $vertical, 0)));
        }
	}
}
