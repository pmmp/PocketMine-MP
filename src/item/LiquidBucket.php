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
use pocketmine\block\Lava;
use pocketmine\block\Liquid;
use pocketmine\block\utils\CoveredByWater;
use pocketmine\block\Water;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class LiquidBucket extends Item{
	private Liquid $liquid;

	public function __construct(ItemIdentifier $identifier, string $name, Liquid $liquid){
		parent::__construct($identifier, $name);
		$this->liquid = $liquid;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getFuelTime() : int{
		if($this->liquid instanceof Lava){
			return 20000;
		}

		return 0;
	}

	public function getFuelResidue() : Item{
		return VanillaItems::BUCKET();
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
		//TODO: move this to generic placement logic

		$targetBlock = null;
		$resultBlock = null;

		$coveredBlock = match(true){
			$blockClicked instanceof CoveredByWater && $blockClicked->canBeCovered() => $blockClicked,
			$blockReplace instanceof CoveredByWater && $blockReplace->canBeCovered() => $blockReplace,
			default => null
		};
		if($coveredBlock !== null && ($this->liquid instanceof Water || $coveredBlock->canBeCoveredByFlowing())){
			$targetBlock = $coveredBlock;
			$resultBlock = $this->liquid instanceof Water ?
				(clone $targetBlock)->setWaterCover((clone $this->liquid)->setStill(false)) :
				clone $this->liquid;
		}elseif($blockReplace->canBeReplaced()){
			$targetBlock = $blockReplace;
			$resultBlock = clone $this->liquid;
		}

		if($targetBlock === null || $resultBlock === null){
			return ItemUseResult::NONE;
		}

		$ev = new PlayerBucketEmptyEvent($player, $targetBlock, $face, $this, VanillaItems::BUCKET());
		$ev->call();
		if(!$ev->isCancelled()){
			$player->getWorld()->setBlock($targetBlock->getPosition(), $resultBlock);
			$player->getWorld()->addSound($targetBlock->getPosition()->add(0.5, 0.5, 0.5), $this->liquid->getBucketEmptySound());

			$this->pop();
			$returnedItems[] = $ev->getItem();
			return ItemUseResult::SUCCESS;
		}

		return ItemUseResult::FAIL;
	}

	public function getLiquid() : Liquid{
		return $this->liquid;
	}
}
