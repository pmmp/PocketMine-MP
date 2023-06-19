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

use pocketmine\block\utils\DripleafTiltType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\sound\DripleafTiltDownSound;
use pocketmine\world\sound\DripleafTiltUpSound;

class BigDripleaf extends BaseBigDripleaf{

	protected DripleafTiltType $tilt;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo){
		$this->tilt = DripleafTiltType::NONE();
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		parent::describeBlockOnlyState($w);
		$w->dripleafTiltType($this->tilt);
	}

	protected function isHead() : bool{
		return true;
	}

	public function getTilt() : DripleafTiltType{
		return $this->tilt;
	}

	/** @return $this */
	public function setTilt(DripleafTiltType $tilt) : self{
		$this->tilt = $tilt;
		return $this;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	private function setTiltAndScheduleTick(DripleafTiltType $tilt) : void{
		$this->position->getWorld()->setBlock($this->position, $this->setTilt($tilt));
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, $tilt->getUpdateTicks());
	}

	public function onEntityInside(Entity $entity) : bool{
		if(!$entity instanceof Projectile && $this->tilt->equals(DripleafTiltType::NONE())){
			$this->setTiltAndScheduleTick(DripleafTiltType::UNSTABLE());
			return true;
		}
		return false;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		$this->setTiltAndScheduleTick(DripleafTiltType::FULL());
		$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
	}

	public function onScheduledUpdate() : void{
		if(!$this->tilt->equals(DripleafTiltType::NONE())){
			if($this->tilt->equals(DripleafTiltType::FULL())){
				$this->position->getWorld()->setBlock($this->position, $this->setTilt(DripleafTiltType::NONE()));
				$this->position->getWorld()->addSound($this->position, new DripleafTiltUpSound());
			}else{
				$this->setTiltAndScheduleTick(match ($this->tilt->id()) {
					DripleafTiltType::UNSTABLE()->id() => DripleafTiltType::PARTIAL(),
					DripleafTiltType::PARTIAL()->id() => DripleafTiltType::FULL(),
					default => throw new AssumptionFailedError("All types should be covered")
				});
				$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
			}
		}
	}

	protected function recalculateCollisionBoxes() : array{
		if(!$this->tilt->equals(DripleafTiltType::FULL())){
			return [
				AxisAlignedBB::one()
					 ->extend(Facing::DOWN, 1)
					 ->trim(Facing::DOWN, 11 / 16)
					 ->trim(Facing::UP, match($this->tilt->id()){
						 DripleafTiltType::NONE()->id(), DripleafTiltType::UNSTABLE()->id() => 1 / 16,
						 DripleafTiltType::PARTIAL()->id() => 3 / 16,
						 default => throw new AssumptionFailedError("All types should be covered")
					 })
			];
		}
		return [];
	}
}
