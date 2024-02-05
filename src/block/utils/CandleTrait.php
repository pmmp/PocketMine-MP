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
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\BlazeShootSound;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\FlintSteelSound;

trait CandleTrait{
	use LightableTrait;

	public function getLightLevel() : int{
		return $this->lit ? 3 : 0;
	}

	/** @see Block::onInteract() */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item->getTypeId() === ItemTypeIds::FIRE_CHARGE || $item->getTypeId() === ItemTypeIds::FLINT_AND_STEEL || $item->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())){
			if($this->lit){
				return true;
			}
			if($item instanceof Durable){
				$item->applyDamage(1);
			}elseif($item->getTypeId() === ItemTypeIds::FIRE_CHARGE){
				$item->pop();
				//TODO: not sure if this is intentional, but it's what Bedrock currently does as of 1.20.10
				$this->position->getWorld()->addSound($this->position, new BlazeShootSound());
			}
			$this->position->getWorld()->addSound($this->position, new FlintSteelSound());
			$this->position->getWorld()->setBlock($this->position, $this->setLit(true));

			return true;
		}
		if($item->isNull()){ //candle can only be extinguished with an empty hand
			if(!$this->lit){
				return true;
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
