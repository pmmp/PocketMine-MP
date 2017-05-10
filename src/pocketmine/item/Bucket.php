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

namespace pocketmine\item;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\Player;

class Bucket extends Item implements Consumable{

	public function onClickBlock(Player $player, Block $block, Block $blockClicked, int $face, float $fx, float $fy, float $fz){
		$bucketBlock = Block::get($this->meta);

		if($bucketBlock instanceof Air){
			if($blockClicked instanceof Liquid and $blockClicked->getDamage() === 0){
				$result = clone $this;
				$result->setDamage($blockClicked->getId());
				$player->getServer()->getPluginManager()->callEvent($ev = new PlayerBucketFillEvent($player, $block, $face, $this, $result));
				if(!$ev->isCancelled()){
					$player->getLevel()->setBlock($blockClicked, Block::get(Block::AIR), true, true);
					if($player->isSurvival()){
						$player->getInventory()->setItemInHand($ev->getItem());
					}
					return true;
				}else{
					$player->getInventory()->sendContents($player);
				}
			}
		}elseif($bucketBlock instanceof Liquid){
			$result = clone $this;
			$result->setDamage(0);
			$player->getServer()->getPluginManager()->callEvent($ev = new PlayerBucketEmptyEvent($player, $block, $face, $this, $result));
			if(!$ev->isCancelled()){
				$player->getLevel()->setBlock($block, $bucketBlock, true, true);
				if($player->isSurvival()){
					$player->getInventory()->setItemInHand($ev->getItem());
				}
				return true;
			}else{
				$player->getInventory()->sendContents($player);
			}
		}

		return false;
	}

	public function canBeConsumed() : bool{
		return $this->meta === 1; //Milk
	}

	public function canBeConsumedBy(Entity $entity) : bool{
		return $entity instanceof Human;
	}

	public function getAdditionalEffects() : array{
		return [];
	}

	public function getResidue(){
		return Item::get(Item::BUCKET, 0, 1);
	}

	public function onConsume(Entity $entity){
		$entity->removeAllEffects();
	}
}