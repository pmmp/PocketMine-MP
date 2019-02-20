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
use pocketmine\block\BlockFactory;
use pocketmine\block\Lava;
use pocketmine\block\Liquid;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;

class LiquidBucket extends Item{
	/** @var int|null */
	protected $liquidId;

	public function __construct(int $id, int $meta, string $name, int $liquidId){
		parent::__construct($id, $meta, $name);
		$this->liquidId = $liquidId;
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getFuelTime() : int{
		if(BlockFactory::get($this->liquidId) instanceof Lava){
			return 20000;
		}

		return 0;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		if(!$blockReplace->canBeReplaced()){
			return ItemUseResult::NONE();
		}

		//TODO: move this to generic placement logic
		$resultBlock = BlockFactory::get($this->liquidId);
		if($resultBlock instanceof Liquid){ //TODO: this should never be false
			$ev = new PlayerBucketEmptyEvent($player, $blockReplace, $face, $this, ItemFactory::get(Item::BUCKET));
			$ev->call();
			if(!$ev->isCancelled()){
				$player->getLevel()->setBlock($blockReplace, $resultBlock->getFlowingForm());
				$player->getLevel()->broadcastLevelSoundEvent($blockClicked->add(0.5, 0.5, 0.5), $resultBlock->getBucketEmptySound());

				if($player->isSurvival()){
					$player->getInventory()->setItemInHand($ev->getItem());
				}
				return ItemUseResult::SUCCESS();
			}

			return ItemUseResult::FAIL();
		}

		return ItemUseResult::NONE();
	}
}
