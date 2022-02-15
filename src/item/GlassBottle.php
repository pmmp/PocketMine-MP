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

use pocketmine\block\Block;
use pocketmine\block\Water;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class GlassBottle extends Item{

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		if($blockClicked instanceof Water){
			$waterPotion = VanillaItems::WATER_POTION();
			$stack = clone $this;
			$this->pop();

			if($player->hasFiniteResources() && $this->getCount() === 0){
				$player->getInventory()->setItemInHand($waterPotion);
				return ItemUseResult::SUCCESS();
			}

			foreach($player->getInventory()->addItem($waterPotion) as $remains){
				$dropEvent = new PlayerDropItemEvent($player, $remains);
				$dropEvent->call();
				if($dropEvent->isCancelled()){
					$player->getInventory()->setItemInHand($stack);
					return ItemUseResult::FAIL();
				}
				$player->dropItem($dropEvent->getItem());
			}

			return ItemUseResult::SUCCESS();
		}

		return ItemUseResult::NONE();
	}
}
