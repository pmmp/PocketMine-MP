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

use pocketmine\event\block\BlockMeltEvent;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\player\Player;

class Ice extends Transparent{

	public function getLightFilter() : int{
		return 2;
	}

	public function getFrictionFactor() : float{
		return 0.98;
	}

	public function onBreak(Item $item, ?Player $player = null) : bool{
		if(($player === null || $player->isSurvival()) && !$item->hasEnchantment(VanillaEnchantments::SILK_TOUCH())){
			$this->position->getWorld()->setBlock($this->position, VanillaBlocks::WATER());
			return true;
		}
		return parent::onBreak($item, $player);
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$world = $this->position->getWorld();
		if($world->getHighestAdjacentBlockLight($this->position->x, $this->position->y, $this->position->z) >= 12){
			$ev = new BlockMeltEvent($this, VanillaBlocks::WATER());
			$ev->call();
			if(!$ev->isCancelled()){
				$world->setBlock($this->position, $ev->getNewState());
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}
}
