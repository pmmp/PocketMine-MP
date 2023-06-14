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
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function mt_rand;

class SmallDripleaf extends Transparent{
	use HorizontalFacingTrait;

	protected bool $upperBlock = false;

	public function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->bool($this->upperBlock);
	}

	public function isUpperBlock() : bool{
		return $this->upperBlock;
	}

	/** @return $this */
	public function setUpperBlock(bool $upperBlock) : self{
		$this->upperBlock = $upperBlock;
		return $this;
	}

	private function canBeSupportedBy(Block $block) : bool{
		//TODO: Moss
		//TODO: Small Dripleaf also can be placed on dirt, coarse dirt, farmland, grass blocks,
		// podzol, rooted dirt, mycelium, and mud if these blocks are underwater (needs waterlogging)
		return $block->getTypeId() === BlockTypeIds::CLAY;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->upperBlock && !$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
			return;
		}
		$face = $this->upperBlock ? Facing::DOWN : Facing::UP;
		if(!$this->getSide($face)->hasSameTypeId($this)){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$block = $blockReplace->getSide(Facing::UP);
		if($block->getTypeId() !== BlockTypeIds::AIR || !$this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN))){
			return false;
		}
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}

		$tx->addBlock($block->getPosition(), VanillaBlocks::SMALL_DRIPLEAF()
			->setFacing($this->facing)
			->setUpperBlock(true)
		);
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
		$bottomBlock = $this->upperBlock ? $this->getSide(Facing::DOWN) : $this;
		if(!$this->hasSameTypeId($bottomBlock)){
			return false;
		}
		$top = $bottomBlock->getPosition();
		$world = $top->getWorld();
		$tx = new BlockTransaction($world);
		$growed = 0;

		for($i = 0; $i < mt_rand(2, 5); $i++){
			$pos = $bottomBlock->getSide(Facing::UP, $i)->getPosition();
			if(!$world->isInWorld($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())){
				break;
			}
			$replace = $world->getBlock($pos);
			if(!$replace->hasSameTypeId($this) && $replace->getTypeId() !== BlockTypeIds::AIR){
				break;
			}
			$tx->addBlock($pos, VanillaBlocks::BIG_DRIPLEAF()->setFacing($this->facing));
			$growed++;
			$top = $pos;
		}

		if($growed > 1){
			$block = $tx->fetchBlock($top);
			if($block instanceof BigDripleaf && $block->getTypeId() === BlockTypeIds::BIG_DRIPLEAF){
				$block->setHead(true);

				$ev = new StructureGrowEvent($bottomBlock, $tx, $player);
				$ev->call();
				if(!$ev->isCancelled()){
					return $tx->apply();
				}
			}
		}

		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if(($item->getBlockToolType() & BlockToolType::SHEARS) !== 0){
			return [$this->asItem()];
		}
		return [];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE();
	}

	protected function recalculateCollisionBoxes() : array{
		return [];
	}
}
