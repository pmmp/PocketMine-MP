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

use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class PinkPetals extends Flowable{
	use HorizontalFacingTrait;

	public const MIN_COUNT = 1;
	public const MAX_COUNT = 4;

	protected int $count = self::MIN_COUNT;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->boundedInt(2, self::MIN_COUNT, self::MAX_COUNT, $this->count);
	}

	public function getCount() : int{
		return $this->count;
	}

	/** @return $this */
	public function setCount(int $count) : self{
		if($count < self::MIN_COUNT || $count > self::MAX_COUNT){
			throw new \InvalidArgumentException("Count must be in range " . self::MIN_COUNT . " ... " . self::MAX_COUNT);
		}
		$this->count = $count;
		return $this;
	}

	private function canBeSupportedBy(Block $block) : bool{
		//TODO: Moss block
		return $block->hasTypeTag(BlockTypeTags::DIRT) || $block->hasTypeTag(BlockTypeTags::MUD);
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		return ($blockReplace instanceof PinkPetals && $blockReplace->getCount() < self::MAX_COUNT) || parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$block = $blockReplace->getSide(Facing::DOWN);
		if(!$this->canBeSupportedBy($block)){
			return false;
		}
		if($blockReplace instanceof PinkPetals && $blockReplace->getCount() < self::MAX_COUNT){
			$this->count = $blockReplace->getCount() + 1;
			$this->facing = $blockReplace->getFacing();
		}elseif($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer && $this->grow($player)){
			$item->pop();
			return true;
		}
		return false;
	}

	private function grow(?Player $player) : bool{
		if($this->count < self::MAX_COUNT){
			$ev = new BlockGrowEvent($this, (clone $this)->setCount($this->count + 1), $player);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
			$this->position->getWorld()->setBlock($this->position, $ev->getNewState());
		}else{
			$this->position->getWorld()->dropItem($this->position->add(0, 0.5, 0), $this->asItem());
		}
		return true;
	}

	public function getFlameEncouragement() : int{
		return 60;
	}

	public function getFlammability() : int{
		return 100;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [$this->asItem()->setCount($this->count)];
	}
}