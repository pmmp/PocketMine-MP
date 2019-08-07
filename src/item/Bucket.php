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
use pocketmine\block\Liquid;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Bucket extends Item{

	public function getMaxStackSize() : int{
		return 16;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		//TODO: move this to generic placement logic
		if($blockClicked instanceof Liquid and $blockClicked->isSource()){
			$stack = clone $this;
			$stack->pop();

			$resultItem = ItemFactory::get(ItemIds::BUCKET, $blockClicked->getFlowingForm()->getId());
			$ev = new PlayerBucketFillEvent($player, $blockReplace, $face, $this, $resultItem);
			$ev->call();
			if(!$ev->isCancelled()){
				$player->getWorld()->setBlock($blockClicked->getPos(), VanillaBlocks::AIR());
				$player->getWorld()->addSound($blockClicked->getPos()->add(0.5, 0.5, 0.5), $blockClicked->getBucketFillSound());
				if($player->hasFiniteResources()){
					if($stack->getCount() === 0){
						$player->getInventory()->setItemInHand($ev->getItem());
					}else{
						$player->getInventory()->setItemInHand($stack);
						$player->getInventory()->addItem($ev->getItem());
					}
				}else{
					$player->getInventory()->addItem($ev->getItem());
				}
				return ItemUseResult::SUCCESS();
			}

			return ItemUseResult::FAIL();
		}

		return ItemUseResult::NONE();
	}
}
