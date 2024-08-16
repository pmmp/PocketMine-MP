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

use pocketmine\block\Door;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\WindExplosionParticle;
use pocketmine\world\sound\WindChargeBurstSound;
use function ceil;
use function floor;
use function round;

class WindCharge extends Throwable{

	public const RADIUS = 2.5;
	public const DAMAGE = 1;

	public static function getNetworkTypeId() : string{ return EntityIds::WIND_CHARGE_PROJECTILE; }

	protected function getInitialDragMultiplier() : float{ return 0; }
	protected function getInitialGravity() : float{ return 0; }

	public function attack(EntityDamageEvent $source) : void{
		if(!$source instanceof EntityDamageByEntityEvent || ($entity = $source->getDamager()) === null){
				return;
		}

		$this->setOwningEntity($entity);

		$this->setMotion($entity->getDirectionVector()->multiply(1.5));
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		if($this->getOwningEntity() === null) {
			$ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, self::DAMAGE);
		} else {
			$ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, self::DAMAGE);
		}

		$entityHit->attack($ev);

		$this->flagForDespawn();
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		parent::entityBaseTick($tickDiff);

		if($this->ticksLived >= 6000) {
			$this->flagForDespawn();
		}

		return true;
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$source = $this->getLocation();

		$this->getWorld()->addSound($event->getRayTraceResult()->getHitVector(), new WindChargeBurstSound());
		$this->getWorld()->addParticle($source, new WindExplosionParticle());

		$bound = $this->getBound($this->getPosition(), self::RADIUS - 1);
		for($x = $bound->minX; $x <= $bound->maxX; $x++) {
			for($y = $bound->minY; $y <= $bound->maxY; $y++) {
				for($z = $bound->minZ; $z <= $bound->maxZ; $z++) {
					$block = $this->getWorld()->getBlockAt((int) floor($x), (int) floor($y), (int) floor($z));

					// This is to avoid calling Door::onProjectileInteraction() twice due to two tiles.
					if($block instanceof Door) {
						if($block->isTop()) {
							continue;
						}
					}

					$block->onProjectileInteraction($this);
				}
			}
		}

		foreach($source->getWorld()->getCollidingEntities($this->getBound($source, self::RADIUS), $this) as $entity){

			$entityPos = $entity->getPosition();
			$distance = $entityPos->distance($source) / 2.5;
			$motion = $entityPos->subtractVector($source)->normalize();

			$exposure = 1;
			if ($entity->isUnderwater()){
				$exposure = 0.5;
			}

			$impact = (1 - $distance) * $exposure;
			if ($impact <= 0) {
				continue;
			}

			if (round($entityPos->getX(), 1) == round($source->getX(), 1) && round($entityPos->getZ(), 1) == round($source->getZ(), 1)) {
				$entity->setMotion($entity->getMotion()->add(0, 0.75 * $exposure, 0));

				return;
			}

			$entity->setMotion($entity->getMotion()->add(0, $impact * 0.4, 0)->addVector($motion->multiply($impact * $exposure)));
		}
	}

	private function getBound(Vector3 $source, float $radius) : AxisAlignedBB {
		return new AxisAlignedBB(
			(int) floor($source->x - $radius - 1),
			(int) floor($source->y - $radius - 1),
			(int) floor($source->z - $radius - 1),
			(int) ceil($source->x + $radius + 1),
			(int) ceil($source->y + $radius + 1),
			(int) ceil($source->z + $radius + 1)
		);
	}
}
