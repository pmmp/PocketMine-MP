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

use InvalidArgumentException;
use pocketmine\block\inventory\BeaconInventory;
use pocketmine\block\tile\Beacon as TileBeacon;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function in_array;

final class Beacon extends Transparent{

	public function getLightLevel() : int{
		return 15;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$player->setCurrentWindow(new BeaconInventory($this->position));
		}

		return true;
	}

	public function onScheduledUpdate() : void{
		$tile = $this->position->getWorld()->getTile($this->position);
		if(!$tile instanceof TileBeacon){
			return;
		}
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20 * 3);

		$primaryE = $tile->getPrimaryEffect();
		$secondaryE = $tile->getSecondaryEffect();

		if($primaryE === 0 && $secondaryE === 0 || !$this->viewSky()){
			return;
		}

		$beaconLevel = 0;
		for($i = 1; $i <= 4; $i++){
			if(!$this->isBeaconLevelValid($i)){
				break;
			}
			$beaconLevel++;
		}
		if($beaconLevel > 0){
			$radius = (10 * $beaconLevel) + 10;
			$effectDuration = 9 + (2 * $beaconLevel);

			foreach($this->position->getWorld()->getPlayers() as $player){
				if($player->getPosition()->distance($this->position) <= $radius){
					if($primaryE === $secondaryE){
						$player->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId($secondaryE), $effectDuration * 20, 1));
						break;
					}
					foreach([$primaryE, $secondaryE] as $enchantment){
						if($enchantment !== 0){
							$player->getEffects()->add(new EffectInstance(EffectIdMap::getInstance()->fromId($enchantment), $effectDuration * 20));
						}
					}
				}
			}
		}
	}

	public function isBeaconLevelValid(int $level) : bool{
		if($level < 1 || $level > 4){
			throw new InvalidArgumentException("Beacon level must be in range 1-4, $level given");
		}

		for($x = -$level; $x <= $level; $x++){
			for($z = -$level; $z <= $level; $z++){
				$block = $this->position->getWorld()->getBlock($this->position->add($x, 0, $z)->subtract(0, $level, 0));
				$allowedBlockId = [
					BlockLegacyIds::IRON_BLOCK,
					BlockLegacyIds::GOLD_BLOCK,
					BlockLegacyIds::DIAMOND_BLOCK,
					BlockLegacyIds::EMERALD_BLOCK
					//TODO netherite block
				];
				if(!in_array($block->getId(), $allowedBlockId, true)){
					return false;
				}
			}
		}
		return true;
	}

	public function viewSky() : bool{
		for($y = 0; $y <= $this->position->getWorld()->getMaxY() - $this->position->getFloorY(); $y++){
			$block = $this->position->getWorld()->getBlock($this->position->add(0, $y, 0));
			if(!$block instanceof Transparent && !$block instanceof Bedrock){
				return false;
			}
		}
		return true;
	}
}
