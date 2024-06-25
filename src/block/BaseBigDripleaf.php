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
use pocketmine\block\utils\SupportType;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

abstract class BaseBigDripleaf extends Transparent{
	use HorizontalFacingTrait;

	abstract protected function isHead() : bool;

	private function canBeSupportedBy(Block $block, bool $head) : bool{
		//TODO: Moss block
		return
			($block instanceof BaseBigDripleaf && $block->isHead() === $head) ||
			$block->getTypeId() === BlockTypeIds::CLAY ||
			$block->hasTypeTag(BlockTypeTags::DIRT) ||
			$block->hasTypeTag(BlockTypeTags::MUD);
	}

	public function onNearbyBlockChange() : void{
		if(
			(!$this->isHead() && !$this->getSide(Facing::UP) instanceof BaseBigDripleaf) ||
			!$this->canBeSupportedBy($this->getSide(Facing::DOWN), false)
		){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$block = $blockReplace->getSide(Facing::DOWN);
		if(!$this->canBeSupportedBy($block, true)){
			return false;
		}
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}
		if($block instanceof BaseBigDripleaf){
			$this->facing = $block->facing;
			$tx->addBlock($block->position, VanillaBlocks::BIG_DRIPLEAF_STEM()->setFacing($this->facing));
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

	private function seekToHead() : ?BaseBigDripleaf{
		if($this->isHead()){
			return $this;
		}
		$step = 1;
		while(($next = $this->getSide(Facing::UP, $step)) instanceof BaseBigDripleaf){
			if($next->isHead()){
				return $next;
			}
			$step++;
		}
		return null;
	}

	private function grow(?Player $player) : bool{
		$head = $this->seekToHead();
		if($head === null){
			return false;
		}
		$pos = $head->position;
		$up = $pos->up();
		$world = $pos->getWorld();
		if(
			!$world->isInWorld($up->getFloorX(), $up->getFloorY(), $up->getFloorZ()) ||
			$world->getBlock($up)->getTypeId() !== BlockTypeIds::AIR
		){
			return false;
		}

		$tx = new BlockTransaction($world);

		$tx->addBlock($pos, VanillaBlocks::BIG_DRIPLEAF_STEM()->setFacing($head->facing));
		$tx->addBlock($up, VanillaBlocks::BIG_DRIPLEAF_HEAD()->setFacing($head->facing));

		$ev = new StructureGrowEvent($head, $tx, $player);
		$ev->call();

		if(!$ev->isCancelled()){
			return $tx->apply();
		}
		return false;
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}
}
