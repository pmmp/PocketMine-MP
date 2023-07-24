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

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\CakeWithCandle;
use pocketmine\block\Candle;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\FireCharge;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\FlintSteelSound;

trait CandleTrait{
	private bool $lit = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->bool($this->lit);
	}

	public function getLightLevel() : int{
		return $this->lit ? 3 : 0;
	}

	public function isLit() : bool{ return $this->lit; }

	/** @return $this */
	public function setLit(bool $lit) : self{
		$this->lit = $lit;
		return $this;
	}

	protected function hitCandle(Player $player) : bool{
		$aabb = AxisAlignedBB::one()
			->contract(1 / 16, 0, 1 / 16)
			->trim(Facing::DOWN, 0.5)
			->trim(Facing::UP, 1 - 0.85000002)
			->offset($this->position->x, $this->position->y, $this->position->z);

		$eyeVector = $player->getEyePos();
		$dVector = $eyeVector->addVector($player->getDirectionVector()->multiply(7));

		$result = $aabb->calculateIntercept($eyeVector, $dVector);
		return $result !== null;
	}

	/** @see Block::onInteract() */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if ($item->getTypeId() === ItemTypeIds::FIRE_CHARGE || $item->getTypeId() === ItemTypeIds::FLINT_AND_STEEL || $item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())) {
			if ($this->lit) {
				return true;
			}
			if ($item instanceof FireCharge) {
				$item->pop();
			}
			if ($item instanceof Durable) {
				$item->applyDamage(1);
			}

			$this->position->getWorld()->addSound($this->position, new FlintSteelSound());
			$this->position->getWorld()->setBlock($this->position, $this->setLit(true));
			return true;
		}
		if($item->isNull()){ //candle can only be extinguished with an empty hand
			if ((!$this->lit) || ($player !== null && !$this->hitCandle($player))) {
				return false;
			}
			$this->position->getWorld()->addSound($this->position, new FireExtinguishSound());
			$this->position->getWorld()->setBlock($this->position, $this->setLit(false));

			return true;
		}

		//yes, this is intentional! in vanilla, if the candle is not interacted with, a block is placed.
		return false;
	}

	/** @see Block::onProjectileHit() */
	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		if(!$this->lit && $projectile->isOnFire()){
			$this->position->getWorld()->setBlock($this->position, $this->setLit(true));
		}
	}
}
