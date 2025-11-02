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

namespace pocketmine\item;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\world\sound\ThrowSound;

class FishingRod extends Durable{

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getMaxDurability() : int{
		return 384;
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		$world = $player->getWorld();
		$origin = $player->getEyePos();

		// scan forward for water up to a reasonable distance
		$maxDistance = 20.0;
		$step = 0.5;
		$found = false;
		$hitPos = null;
		for($d = $step; $d <= $maxDistance; $d += $step){
			$motion = $directionVector->multiply($d);
			$pos = $origin->add($motion->x, $motion->y, $motion->z);
			$x = (int)floor($pos->x);
			$y = (int)floor($pos->y);
			$z = (int)floor($pos->z);
			try{
				$block = $world->getBlockAt($x, $y, $z);
			}catch(\Throwable $e){
				continue;
			}
			if($block instanceof \pocketmine\block\Water){
				$found = true;
				$hitPos = $pos;
				break;
			}
		}

		if(!$found){
			// nothing to fish in
			return ItemUseResult::NONE;
		}

		// choose a random fish
		$roll = mt_rand(1, 100);
		if($roll <= 60){
			$fish = VanillaItems::RAW_FISH();
		} elseif($roll <= 85){
			$fish = VanillaItems::RAW_SALMON();
		} elseif($roll <= 95){
			$fish = VanillaItems::CLOWNFISH();
		} else {
			$fish = VanillaItems::PUFFERFISH();
		}

		// drop the fish at the hit position
		$dropPos = $hitPos->add(0, 0.5, 0);
		$world->dropItem($dropPos, $fish);

		// play a simple sound if available
		if(method_exists($world, 'addSound')){
			// Try to use a generic throw sound to give feedback
			try{
				$world->addSound($player->getPosition(), new \pocketmine\world\sound\ThrowSound());
			}catch(\Throwable $e){
				// ignore if sound class not available
			}
		}

		// consume durability (unless unbreakable or creative)
		if(!$player->isCreative() && $this->applyDamage(1)){
			// if broken, applyDamage will pop the item; just return success
		}

		return ItemUseResult::SUCCESS;
	}
}
