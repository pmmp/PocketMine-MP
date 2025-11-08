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
use pocketmine\block\BlockTypeTags;
use pocketmine\block\Lava;
use pocketmine\block\Liquid;
use pocketmine\block\utils\Waterloggable;
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

		$blockClicked = $player->getWorld()->getBlock($blockClicked->getPosition()); //We might interact a displaced block
		$targetBlock = null;
		$resultBlock = null;

		$waterloggable = match(true){
			$blockClicked instanceof Waterloggable && $blockClicked->canBeWaterlogged() => $blockClicked,
			$blockReplace instanceof Waterloggable && $blockReplace->canBeWaterlogged() => $blockReplace,
			default => null
		};
		if($waterloggable !== null && ($this->liquid instanceof Water || $waterloggable->hasTypeTag(BlockTypeTags::NON_SOURCE_WATERLOGGABLE))){
			if($this->liquid instanceof Water){
				$targetBlock = $waterloggable;
				$resultBlock = (clone $targetBlock)->setContainedWater((clone $this->liquid)->setStill(false));
			}else{
				$targetBlock = $blockReplace->canBeReplaced() ? $blockReplace : (($target = $blockReplace->getSide($face))->canBeReplaced() ? $target : null);
				$resultBlock = clone $this->liquid;
			}
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
