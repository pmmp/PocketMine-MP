<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\helper;

use pocketmine\entity\hostile\Slime;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class EntitySlimeMoveHelper extends EntityMoveHelper{
	/** @var Slime */
	protected $entity;

	protected $targetYaw = 0;
	protected $jumpTimer = 0;
	protected $speedJump = false;

	public function __construct(Slime $slime){
		parent::__construct($slime);
	}

	public function jumpWithYaw(float $yaw, bool $speedJump){
		$this->targetYaw = $yaw;
		$this->speedJump = $speedJump;
	}

	public function setSpeed(float $speedIn){
		$this->speedMultiplier = $speedIn;
		$this->needsUpdate = true;
	}

	public function onUpdate() : void{
		$this->entity->yaw = EntityLookHelper::limitAngle($this->entity->yaw, $this->targetYaw, 30.0);
		$this->entity->headYaw = $this->entity->yaw;
		$this->entity->yawOffset = $this->entity->yaw;

		if (!$this->needsUpdate){
			$this->entity->setMoveForward(0);
		}else{
			$this->needsUpdate = false;

			if ($this->entity->onGround){
				$this->entity->setAIMoveSpeed($s = $this->speedMultiplier * $this->entity->getMovementSpeed());
				$this->entity->setMoveForward($s);

				if ($this->jumpTimer-- <= 0){
					$this->jumpTimer = $this->entity->getJumpDelay();

					if ($this->speedJump){
						$this->jumpTimer /= 3;
					}

					$this->entity->getJumpHelper()->setJumping(true);

					if($this->entity->makesSoundOnJump()){
						$this->entity->level->broadcastLevelSoundEvent($this->entity, $this->entity->getSlimeSize() > 1 ? LevelSoundEventPacket::SOUND_SQUISH_BIG : LevelSoundEventPacket::SOUND_SQUISH_SMALL, -1, $this->entity::NETWORK_ID);
					}
				}else{
					$this->entity->setMoveStrafing(0);
					$this->entity->setMoveForward(0);
					$this->entity->setAIMoveSpeed(0);
				}
			}else{
				$this->entity->setAIMoveSpeed($s = $this->speedMultiplier * $this->entity->getMovementSpeed());
				$this->entity->setMoveForward($s);
			}
		}
	}
}