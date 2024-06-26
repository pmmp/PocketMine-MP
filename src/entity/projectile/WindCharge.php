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
use pocketmine\block\ChorusFlower;
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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\WindExplosionParticle;
use pocketmine\world\sound\DoorSound;
use pocketmine\world\sound\FlintSteelSound;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;
use pocketmine\world\sound\WindChargeBurstSound;

use function ceil;
use function floor;
use function round;
use function time;

class WindCharge extends Throwable{
	protected float $radius = 2.5;
	protected float $damage = 0.5;

	protected float $createdAt = 0;
	protected float $lifetime = 300;

	public static function getNetworkTypeId() : string{ return EntityIds::WIND_CHARGE_PROJECTILE; }

	protected function getInitialDragMultiplier() : float{ return 0; }
	protected function getInitialGravity() : float{ return 0; }

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->createdAt = time();
	}

	public function attack(EntityDamageEvent $source) : void{
		if(!$source instanceof EntityDamageByEntityEvent) return;
		/** @var EntityDamageByEntityEvent $source */

		if(($entity = $source->getDamager()) == null) return;
		$this->setOwningEntity($entity);

		$this->setMotion($entity->getDirectionVector()->multiply(1.5));
	}

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

					$this->checkAffect($affectedBlock);
				}
			}
		}
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		parent::entityBaseTick($tickDiff);

		if($this->createdAt + $this->lifetime <= time()) $this->flagForDespawn();

		return true;
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		$source = $this->getLocation();

		$this->getWorld()->addSound($event->getRayTraceResult()->getHitVector(), new WindChargeBurstSound());
		$this->getWorld()->addParticle($source, new WindExplosionParticle());

		$list = $source->getWorld()->getNearbyEntities($this->getBound($source, $this->radius));

		foreach($list as $entity){
			if($entity == $this) continue;

			$entityPos = $entity->getPosition();
			$distance = $entityPos->distance($source) / $this->radius;
			$motion = $entityPos->subtractVector($source)->normalize();

			($entity->isUnderwater()) ? $exposure = 0.5 : $exposure = 1.5;
			$impact = (1 - $distance) * $exposure;

			if ($impact <= 0) continue;

			if (round($entityPos->getX(), 1) == round($source->getX(), 1) && round($entityPos->getZ(), 1) == round($source->getZ(), 1)) {
				$entity->setMotion($entity->getMotion()->add(0, 0.5 * $exposure, 0));

				return;
			}

			$entity->setMotion($entity->getMotion()->add(0, $impact * 0.4, 0)->addVector($motion->multiply($impact)));
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

	private function checkAffect(Block $affectedBlock) : void {
		if($affectedBlock instanceof Trapdoor) {
			if($affectedBlock->getTypeId() == BlockTypeIds::IRON_TRAPDOOR) return;
			$affectedBlock->setOpen(!$affectedBlock->isOpen());
			$world = $affectedBlock->getPosition()->getWorld();
			$world->setBlock($affectedBlock->getPosition(), $affectedBlock);
			$world->addSound($affectedBlock->getPosition(), new DoorSound());

			return;
		}

		if($affectedBlock instanceof Door) {
			if($affectedBlock->getTypeId() == BlockTypeIds::IRON_DOOR) return;
			$affectedBlock->setOpen(!$affectedBlock->isOpen());
			$world = $affectedBlock->getPosition()->getWorld();
			$world->setBlock($affectedBlock->getPosition(), $affectedBlock);
			$world->addSound($affectedBlock->getPosition(), new DoorSound());

			return;
		}

		if($affectedBlock instanceof Button) {
			($affectedBlock->getTypeId() == BlockTypeIds::STONE_BUTTON || $affectedBlock->getTypeId() == BlockTypeIds::POLISHED_BLACKSTONE_BUTTON) ? $delay = 20 : $delay = 30;

			$affectedBlock->setPressed(!$affectedBlock->isPressed());
			$world = $affectedBlock->getPosition()->getWorld();
			$world->setBlock($affectedBlock->getPosition(), $affectedBlock);
			$world->scheduleDelayedBlockUpdate($affectedBlock->getPosition(), $delay);
			$world->addSound($affectedBlock->getPosition()->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());

			return;
		}

		if($affectedBlock instanceof Lever) {
			$affectedBlock->setActivated(!$affectedBlock->isActivated());
			$world = $affectedBlock->getPosition()->getWorld();
			$world->setBlock($affectedBlock->getPosition(), $affectedBlock);
			$world->addSound(
				$affectedBlock->getPosition()->add(0.5, 0.5, 0.5),
				$affectedBlock->isActivated() ? new RedstonePowerOnSound() : new RedstonePowerOffSound()
			);

			return;
		}

		if($affectedBlock instanceof Bell) {
			$affectedBlock->ring($affectedBlock->getFacing());

			return;
		}

		if($affectedBlock instanceof ChorusFlower) {
			$affectedBlock->getPosition()->getWorld()->useBreakOn($affectedBlock->getPosition());

			return;
		}

		if($affectedBlock instanceof Candle || $affectedBlock instanceof CakeWithCandle) {
			if(!$affectedBlock->isLit()) return;

			$newCandle = $affectedBlock->setLit(false);
			$world = $affectedBlock->getPosition()->getWorld();
			$world->setBlock($affectedBlock->getPosition(), $newCandle);
			$world->addSound($affectedBlock->getPosition(), new FlintSteelSound());

			return;
		}

		//TODO Decorated pots shatter on impact
	}
}
