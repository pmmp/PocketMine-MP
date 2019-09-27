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

use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class EntityLookHelper{

	/** @var Mob */
	protected $entity;
	protected $deltaLookYaw = 0.0;
	protected $deltaLookPitch = 0.0;
	protected $isLooking = false;
	protected $posX = 0;
	protected $posY = 0;
	protected $posZ = 0;

	public function __construct(Mob $mob){
		$this->entity = $mob;
	}

	public function setLookPositionWithEntity(Entity $entity, float $deltaYaw, float $deltaPitch) : void{
		$this->setLookPosition($entity->x, $entity->y + $entity->getEyeHeight(), $entity->z, $deltaYaw, $deltaPitch);
	}

	public function setLookPosition(float $x, float $y, float $z, float $deltaYaw, float $deltaPitch) : void{
		$this->posX = $x;
		$this->posY = $y;
		$this->posZ = $z;
		$this->deltaLookYaw = $deltaYaw;
		$this->deltaLookPitch = $deltaPitch;
		$this->isLooking = true;
	}

	public function onUpdate() : void{
		$this->entity->pitch = 0;
		if($this->isLooking){
			$this->isLooking = false;

			$d0 = $this->posX - $this->entity->x;
			$d1 = $this->posY - ($this->entity->y + $this->entity->getEyeHeight());
			$d2 = $this->posZ - $this->entity->z;
			$d3 = sqrt($d0 * $d0 + $d2 * $d2);
			$f = (atan2($d2, $d0) * 180 / M_PI) - 90;
			$f1 = -(atan2($d1, $d3) * 180 / M_PI);

			$this->entity->pitch = self::updateRotation($this->entity->pitch, $f1, $this->deltaLookPitch);
			$this->entity->headYaw = self::updateRotation($this->entity->headYaw, $f, $this->deltaLookYaw);
		}else{
			$this->entity->headYaw = self::updateRotation($this->entity->headYaw, $this->entity->yawOffset, 10);
		}
		$f2 = self::wrapAngleTo180($this->entity->headYaw - $this->entity->yawOffset);
		if($this->entity->getNavigator()->isBusy()){
			if($f2 < -75){
				$this->entity->headYaw = $this->entity->yawOffset - 75;
			}
			if($f2 > 75){
				$this->entity->headYaw = $this->entity->yawOffset + 75;
			}
		}
	}

	public static function updateRotation($rot, $rot2, $rot3) : float{
		$f = self::wrapAngleTo180($rot2 - $rot);
		if($f > $rot3){
			$f = $rot3;
		}
		if($f < -$rot3){
			$f = -$rot3;
		}
		return $rot + $f;
	}

	public static function wrapAngleTo180(float $angle) : float{
		$angle %= 360;
		if($angle > 180){
			$angle -= 360;
		}
		if($angle < -180){
			$angle += 360;
		}

		return $angle;
	}

	public static function limitAngle(float $a, float $b, float $c){
		$f = self::wrapAngleTo180($b - $a);

		if($f > $c){
			$f = $c;
		}

		if($f < -$c){
			$f = -$c;
		}

		$f1 = $a + $f;

		if($f1 < 0){
			$f1 += 360;
		}elseif($f1 > 360){
			$f1 -= 360;
		}

		return $f1;
	}

	public function isLooking() : bool{
		return $this->isLooking;
	}

	public function getLookPosX() : float{
		return $this->posX;
	}

	public function getLookPosY() : float{
		return $this->posY;
	}

	public function getLookPosZ() : float{
		return $this->posZ;
	}
}
