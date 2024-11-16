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
use pocketmine\world\sound\DripleafTiltDownSound;
use pocketmine\world\sound\DripleafTiltUpSound;

class BigDripleafHead extends BaseBigDripleaf{

	protected DripleafState $leafState = DripleafState::STABLE;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		parent::describeBlockOnlyState($w);
		$w->enum($this->leafState);
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
		$delay = $tilt->getScheduledUpdateDelayTicks();
		if($delay !== null){
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, $delay);
		}
	}

	private function getLeafTopOffset() : float{
		return match($this->leafState){
			DripleafState::STABLE, DripleafState::UNSTABLE => 1 / 16,
			DripleafState::PARTIAL_TILT => 3 / 16,
			default => 0
		};
	}

	public function onEntityInside(Entity $entity) : bool{
		if(!$entity instanceof Projectile && $this->leafState === DripleafState::STABLE){
			//the entity must be standing on top of the leaf - do not collapse if the entity is standing underneath
			$intersection = AxisAlignedBB::one()
				->offset($this->position->x, $this->position->y, $this->position->z)
				->trim(Facing::DOWN, 1 - $this->getLeafTopOffset());
			if($entity->getBoundingBox()->intersectsWith($intersection)){
				$this->setTiltAndScheduleTick(DripleafState::UNSTABLE);
				return false;
			}
		}
		return true;
	}

	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		if($this->leafState !== DripleafState::FULL_TILT){
			$this->setTiltAndScheduleTick(DripleafState::FULL_TILT);
			$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
		}
	}

	public function onScheduledUpdate() : void{
		if($this->leafState !== DripleafState::STABLE){
			if($this->leafState === DripleafState::FULL_TILT){
				$this->position->getWorld()->setBlock($this->position, $this->setLeafState(DripleafState::STABLE));
				$this->position->getWorld()->addSound($this->position, new DripleafTiltUpSound());
			}else{
				$this->setTiltAndScheduleTick(match($this->leafState){
					DripleafState::UNSTABLE => DripleafState::PARTIAL_TILT,
					DripleafState::PARTIAL_TILT => DripleafState::FULL_TILT,
				});
				$this->position->getWorld()->addSound($this->position, new DripleafTiltDownSound());
			}
		}
	}

	protected function recalculateCollisionBoxes() : array{
		if($this->leafState !== DripleafState::FULL_TILT){
			return [
				AxisAlignedBB::one()
					->trim(Facing::DOWN, 11 / 16)
					->trim(Facing::UP, $this->getLeafTopOffset())
			];
		}
		return [];
	}
}
