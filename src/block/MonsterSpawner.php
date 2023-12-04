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

use pocketmine\block\tile\MonsterSpawner as TileSpawner;
use pocketmine\block\utils\SupportType;
use pocketmine\item\Item;
use pocketmine\item\SpawnEgg;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function mt_rand;

class MonsterSpawner extends Transparent{

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	protected function getXpDropAmount() : int{
		return mt_rand(15, 43);
	}

	public function onScheduledUpdate() : void{
		$world = $this->position->getWorld();
		$spawner = $world->getTile($this->position);
		if($spawner instanceof TileSpawner && $spawner->onUpdate()){
			$world->scheduleDelayedBlockUpdate($this->position, 1);
		}
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof SpawnEgg){
			$spawner = $this->position->getWorld()->getTile($this->position);
			if($spawner instanceof TileSpawner){
				$spawner->setEntityTypeId($item->getEntityTypeId());
				$this->position->getWorld()->setBlock($this->position, $this);
				$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
				return true;
			}
		}
		return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
	}
}
