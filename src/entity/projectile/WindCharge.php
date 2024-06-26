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

use pocketmine\block\Bell;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Button;
use pocketmine\block\CakeWithCandle;
use pocketmine\block\Candle;
use pocketmine\block\Door;
use pocketmine\block\Lever;
use pocketmine\block\Trapdoor;
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
use pocketmine\world\sound\DoorSound;
use pocketmine\world\sound\FlintSteelSound;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;

use function ceil;
use function floor;

class WindCharge extends Throwable{
	protected float $radius = 2.5;
	protected float $damage = 0.5;

	public static function getNetworkTypeId() : string{ return EntityIds::WIND_CHARGE_PROJECTILE; }

	protected function getInitialDragMultiplier() : float{ return 0; }
	protected function getInitialGravity() : float{ return 0; }

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
		if($this->getOwningEntity() === null)$ev = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $this->damage);
		else $ev = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $this->damage);
		$entityHit->attack($ev);

		$this->flagForDespawn();
	}

	protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
		parent::onHitBlock($blockHit, $hitResult);
		$bound = $this->getBound($blockHit->getPosition(), $this->radius - 1);

		for($x = $bound->minX; $x <= $bound->maxX; $x++) {
			for($y = $bound->minY; $y <= $bound->maxY; $y++) {
				for($z = $bound->minZ; $z <= $bound->maxZ; $z++) {
					$affectedBlock = $this->getWorld()->getBlockAt((int) floor($x), (int) floor($y), (int) floor($z));

					if($affectedBlock instanceof Trapdoor) {
						/** @var Trapdoor $affectedBlock */

						if($affectedBlock->getTypeId() == BlockTypeIds::IRON_TRAPDOOR) continue;
						$affectedBlock->setOpen(!$affectedBlock->isOpen());
						$world = $affectedBlock->getPosition()->getWorld();
						$world->setBlock($affectedBlock->getPosition(), $affectedBlock);
						$world->addSound($affectedBlock->getPosition(), new DoorSound());

						continue;
					}

					if($affectedBlock instanceof Door) {
						/** @var Door $affectedBlock */

						if($affectedBlock->getTypeId() == BlockTypeIds::IRON_DOOR) continue;
						$affectedBlock->setOpen(!$affectedBlock->isOpen());
						$world = $affectedBlock->getPosition()->getWorld();
						$world->setBlock($affectedBlock->getPosition(), $affectedBlock);
						$world->addSound($affectedBlock->getPosition(), new DoorSound());

						continue;
					}

					if($affectedBlock instanceof Button) {
						/** @var Button $affectedBlock */

						$affectedBlock->setPressed(!$affectedBlock->isPressed());
						$world = $affectedBlock->getPosition()->getWorld();
						$world->setBlock($affectedBlock->getPosition(), $affectedBlock);
						$world->scheduleDelayedBlockUpdate($affectedBlock->getPosition(), 1);
						$world->addSound($affectedBlock->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());

						continue;
					}

					if($affectedBlock instanceof Lever) {
						/** @var Lever $affectedBlock */

						$affectedBlock->setActivated(!$affectedBlock->isActivated());
						$world = $affectedBlock->getPosition()->getWorld();
						$world->setBlock($affectedBlock->getPosition(), $affectedBlock);
						$world->addSound(
							$affectedBlock->getPosition()->add(0.5, 0.5, 0.5),
							$affectedBlock->isActivated() ? new RedstonePowerOnSound() : new RedstonePowerOffSound()
						);

						continue;
					}

					if($affectedBlock instanceof Bell) {
						/** @var Bell $affectedBlock */

						$affectedBlock->ring($affectedBlock->getFacing());

						continue;
					}

					if($affectedBlock instanceof Candle || $affectedBlock instanceof CakeWithCandle) {
						/** @var Candle $affectedBlock */

						if(!$affectedBlock->isLit()) continue;

						$newCandle = $affectedBlock->setLit(false);
						$world = $affectedBlock->getPosition()->getWorld();
						$world->setBlock($affectedBlock->getPosition(), $newCandle);
						$world->addSound($affectedBlock->getPosition(), new FlintSteelSound());

						continue;
					}

					//TODO Decorated pots shatter on impact
				}
			}
		}
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$source = $this->getLocation();

		//TODO implement wind charge explosion sound when added.
		$this->getWorld()->addParticle($source, new WindExplosionParticle());

		$list = $source->getWorld()->getNearbyEntities($this->getBound($source, $this->radius));

		foreach($list as $entity){
			$entityPos = $entity->getPosition();
			$distance = $entityPos->distance($source) / $this->radius;
			$motion = $entityPos->subtractVector($source)->normalize();
			($entity->isUnderwater()) ? $exposure = 0.5 : $exposure = 1.5;

			$impact = (1 - $distance) * $exposure;

			if ($impact <= 0) continue;

			($distance <= 1) ? $vertical = 0 : $vertical = 0.75;
			$entity->setMotion($entity->getMotion()->addVector($motion->multiply($impact)->add(0, $vertical, 0)));
		}
	}

	private function getBound(Vector3 $source, float $radius) : AxisAlignedBB {
		$minX = (int) floor($source->x - $radius - 1);
		$maxX = (int) ceil($source->x + $radius + 1);
		$minY = (int) floor($source->y - $radius - 1);
		$maxY = (int) ceil($source->y + $radius + 1);
		$minZ = (int) floor($source->z - $radius - 1);
		$maxZ = (int) ceil($source->z + $radius + 1);

		return new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
	}
}
