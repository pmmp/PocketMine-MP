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
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

final class Beacon extends Transparent{
	public const ALLOWED_ITEM_IDS = [
		ItemIds::IRON_INGOT => true,
		ItemIds::GOLD_INGOT => true,
		ItemIds::DIAMOND => true,
		ItemIds::EMERALD => true
	];

	private const ALLOWED_BLOCK_IDS = [
		BlockLegacyIds::IRON_BLOCK => true,
		BlockLegacyIds::GOLD_BLOCK => true,
		BlockLegacyIds::DIAMOND_BLOCK => true,
		BlockLegacyIds::EMERALD_BLOCK => true
		//TODO netherite block
	];

	private Effect $primaryEffect;
	private ?Effect $secondaryEffect;

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileBeacon){
			$effectIdMap = EffectIdMap::getInstance();
			$primaryEffectId = $tile->getPrimaryEffect();
			$primaryEffect = $effectIdMap->fromId($primaryEffectId);
			if($primaryEffect === null){
				throw new InvalidBlockStateException("Invalid primary effect $primaryEffectId");
			}
			$this->primaryEffect = $primaryEffect;
			$this->secondaryEffect = $effectIdMap->fromId($tile->getSecondaryEffect());
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileBeacon){
			$effectIdMap = EffectIdMap::getInstance();
			$tile->setPrimaryEffect($effectIdMap->toId($this->primaryEffect));
			if($this->secondaryEffect instanceof Effect){
				$tile->setSecondaryEffect($effectIdMap->toId($this->secondaryEffect));
			}
		}
	}

	public function getPrimaryEffect() : Effect{
		return $this->primaryEffect;
	}

	/** @return $this */
	public function setPrimaryEffect(Effect $primaryEffect) : self{
		$this->primaryEffect = $primaryEffect;
		return $this;
	}

	public function getSecondaryEffect() : ?Effect{
		return $this->secondaryEffect;
	}

	/** @return $this */
	public function setSecondaryEffect(?Effect $secondaryEffect) : self{
		$this->secondaryEffect = $secondaryEffect;
		return $this;
	}

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
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 20 * 3);

		if(!$this->viewSky()){
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

			$world = $this->position->getWorld();
			$aabb = $this->getCollisionBoxes()[0]->expandedCopy($radius, $radius, $radius)->addCoord(0, $world->getMaxY(), 0);
			if($this->primaryEffect === $this->secondaryEffect){
				foreach($world->getNearbyEntities($aabb) as $entity){
					if($entity instanceof Player){
						$entity->getEffects()->add(new EffectInstance($this->primaryEffect, $effectDuration * 20, 1));
					}
				}
			}else{
				$effects = [$this->primaryEffect];
				if($this->secondaryEffect !== null){
					$effects[] = $this->secondaryEffect;
				}
				foreach($world->getNearbyEntities($aabb) as $entity){
					if($entity instanceof Player){
						foreach($effects as $effect){
							$entity->getEffects()->add(new EffectInstance($effect, $effectDuration * 20, 0));
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

		$world = $this->position->getWorld();
		$pos = $this->position->subtract(0, $level, 0);
		for($x = -$level; $x <= $level; $x++){
			for($z = -$level; $z <= $level; $z++){
				$block = $world->getBlock($pos->add($x, 0, $z));
				if(!isset(self::ALLOWED_BLOCK_IDS[$block->getId()])){
					return false;
				}
			}
		}
		return true;
	}

	public function viewSky() : bool{
		$world = $this->position->getWorld();
		$maxY = $world->getMaxY();
		$block = $this;
		for($y = 0; $y <= $maxY; $y++){
			$block = $world->getBlock($block->position->up());
			if(!$block instanceof Transparent && !$block instanceof Bedrock){
				return false;
			}
		}
		return true;
	}
}
