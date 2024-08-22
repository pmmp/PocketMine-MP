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

namespace pocketmine\entity;

use pocketmine\entity\animation\SquidInkCloudAnimation;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use function atan2;
use function mt_rand;
use function sqrt;
use const M_PI;

class Squid extends WaterAnimal{

	public static function getNetworkTypeId() : string{ return EntityIds::SQUID; }

	public ?Vector3 $swimDirection = null;
	public float $swimSpeed = 0.1;

	private int $switchDirectionTicker = 0;

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.95, 0.95); }

	public function initEntity(CompoundTag $nbt) : void{
		$this->setMaxHealth(10);
		parent::initEntity($nbt);
	}

	public function getName() : string{
		return "Squid";
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		if($source->isCancelled()){
			return;
		}

		if($source instanceof EntityDamageByEntityEvent){
			$this->swimSpeed = mt_rand(150, 350) / 2000;
			$e = $source->getDamager();
			if($e !== null){
				$this->swimDirection = $this->location->subtractVector($e->location)->normalize();
			}

			$this->broadcastAnimation(new SquidInkCloudAnimation($this));
		}
	}

	private function generateRandomDirection() : Vector3{
		return new Vector3(mt_rand(-1000, 1000) / 1000, mt_rand(-500, 500) / 1000, mt_rand(-1000, 1000) / 1000);
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		if(++$this->switchDirectionTicker === 100){
			$this->switchDirectionTicker = 0;
			if(mt_rand(0, 100) < 50){
				$this->swimDirection = null;
			}
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->isAlive()){

			if($this->location->y > 62 && $this->swimDirection !== null){
				$this->swimDirection = $this->swimDirection->withComponents(null, -0.5, null);
			}

			$inWater = $this->isUnderwater();
			$this->setHasGravity(!$inWater);
			if(!$inWater){
				$this->swimDirection = null;
			}elseif($this->swimDirection !== null){
				if($this->motion->lengthSquared() <= $this->swimDirection->lengthSquared()){
					$this->motion = $this->swimDirection->multiply($this->swimSpeed);
				}
			}else{
				$this->swimDirection = $this->generateRandomDirection();
				$this->swimSpeed = mt_rand(50, 100) / 2000;
			}

			$f = sqrt(($this->motion->x ** 2) + ($this->motion->z ** 2));
			$this->setRotation(
				-atan2($this->motion->x, $this->motion->z) * 180 / M_PI,
				-atan2($f, $this->motion->y) * 180 / M_PI
			);
		}

		return $hasUpdate;
	}

	public function getDrops() : array{
		return [
			VanillaItems::INK_SAC()->setCount(mt_rand(1, 3))
		];
	}
}
