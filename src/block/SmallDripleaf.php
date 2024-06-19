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
use pocketmine\world\Position;
use function mt_rand;

class SmallDripleaf extends Transparent{
	use HorizontalFacingTrait;

	protected bool $top = false;

	public function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->bool($this->top);
	}

	public function isTop() : bool{
		return $this->top;
	}

	/** @return $this */
	public function setTop(bool $top) : self{
		$this->top = $top;
		return $this;
	}

	private function canBeSupportedBy(Block $block) : bool{
		//TODO: Moss
		//TODO: Small Dripleaf also can be placed on dirt, coarse dirt, farmland, grass blocks,
		// podzol, rooted dirt, mycelium, and mud if these blocks are underwater (needs waterlogging)
		return $block->getTypeId() === BlockTypeIds::CLAY;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->top && !$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
			return;
		}
		$face = $this->top ? Facing::DOWN : Facing::UP;
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

		$tx->addBlock($block->position, VanillaBlocks::SMALL_DRIPLEAF()
			->setFacing($this->facing)
			->setTop(true)
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

	private function canGrowTo(Position $pos) : bool{
		$world = $pos->getWorld();
		if(!$world->isInWorld($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ())){
			return false;
		}
		$block = $world->getBlock($pos);
		return $block->hasSameTypeId($this) || $block->getTypeId() === BlockTypeIds::AIR;
	}

	private function grow(?Player $player) : bool{
		$bottomBlock = $this->top ? $this->getSide(Facing::DOWN) : $this;
		if(!$this->hasSameTypeId($bottomBlock)){
			return false;
		}
		$world = $this->position->getWorld();
		$tx = new BlockTransaction($world);
		$height = mt_rand(2, 5);
		$grown = 0;
		for($i = 0; $i < $height; $i++){
			$pos = $bottomBlock->getSide(Facing::UP, $i)->position;
			if(!$this->canGrowTo($pos)){
				break;
			}
			$block = ++$grown < $height && $this->canGrowTo($pos->getSide(Facing::UP)) ?
				VanillaBlocks::BIG_DRIPLEAF_STEM() :
				VanillaBlocks::BIG_DRIPLEAF_HEAD();
			$tx->addBlock($pos, $block->setFacing($this->facing));
		}
		if($grown > 1){
			$ev = new StructureGrowEvent($bottomBlock, $tx, $player);
			$ev->call();
			if(!$ev->isCancelled()){
				return $tx->apply();
			}
		}

		return false;
	}

	public function getAffectedBlocks() : array{
		$other = $this->getSide($this->top ? Facing::DOWN : Facing::UP);
		if($other->hasSameTypeId($this)){
			return [$this, $other];
		}
		return parent::getAffectedBlocks();
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if(!$this->top){
			return [$this->asItem()];
		}
		return [];
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

	protected function recalculateCollisionBoxes() : array{
		return [];
	}
}
