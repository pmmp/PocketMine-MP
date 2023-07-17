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

use pocketmine\block\utils\DripleafState;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\sound\DripleafTiltDownSound;
use pocketmine\world\sound\DripleafTiltUpSound;

class BigDripleafHead extends BaseBigDripleaf{

	protected DripleafState $leafState;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo){
		$this->leafState = DripleafState::STABLE();
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		parent::describeBlockOnlyState($w);
		$w->dripleafState($this->leafState);
	}

	protected function isHead() : bool{
		return true;
	}

	public function getLeafState() : DripleafState{
		return $this->leafState;
	}

	/** @return $this */
	public function setLeafState(DripleafState $leafState) : self{
		$this->leafState = $leafState;
		return $this;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	private function setTiltAndScheduleTick(DripleafState $tilt) : void{
		$this->position->getWorld()->setBlock($this->position, $this->setLeafState($tilt));
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, $tilt->getUpdateTicks());
	}

	public function onEntityInside(Entity $entity) : bool{
		if(!$entity instanceof Projectile && $this->leafState->equals(DripleafState::STABLE())){
			$this->setTiltAndScheduleTick(DripleafState::UNSTABLE());
			return true;
		}
		return false;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		$this->setTiltAndScheduleTick(DripleafState::FULL_TILT());
		$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
	}

	public function onScheduledUpdate() : void{
		if(!$this->leafState->equals(DripleafState::STABLE())){
			if($this->leafState->equals(DripleafState::FULL_TILT())){
				$this->position->getWorld()->setBlock($this->position, $this->setLeafState(DripleafState::STABLE()));
				$this->position->getWorld()->addSound($this->position, new DripleafTiltUpSound());
			}else{
				$this->setTiltAndScheduleTick(match ($this->leafState->id()) {
					DripleafState::UNSTABLE()->id() => DripleafState::PARTIAL_TILT(),
					DripleafState::PARTIAL_TILT()->id() => DripleafState::FULL_TILT(),
					default => throw new AssumptionFailedError("All types should be covered")
				});
				$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
			}
		}
	}

	protected function recalculateCollisionBoxes() : array{
		if(!$this->leafState->equals(DripleafState::FULL_TILT())){
			return [
				AxisAlignedBB::one()
					 ->trim(Facing::DOWN, 11 / 16)
					 ->trim(Facing::UP, match($this->leafState->id()){
						 DripleafState::STABLE()->id(), DripleafState::UNSTABLE()->id() => 1 / 16,
						 DripleafState::PARTIAL_TILT()->id() => 3 / 16,
						 default => throw new AssumptionFailedError("All types should be covered")
					 })
			];
		}
		return [];
	}
}
