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

use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\WallConnectionType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class MossyCarpet extends Flowable{

	protected bool $top = false;

	/** @var WallConnectionType[] */
	protected array $sides = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->wallConnections($this->sides);
		$w->bool($this->top);
	}

	public function getSideConnection(int $face) : ?WallConnectionType{
		return $this->sides[$face] ?? null;
	}

	public function setSideConnection(int $face, ?WallConnectionType $value) : self{
		if($value === null){
			unset($this->sides[$face]);
			return $this;
		}
		$this->sides[$face] = $value;
		return $this;
	}

	public function isTop() : bool{ return $this->top; }

	public function setTop(bool $top) : self{
		$this->top = $top;
		return $this;
	}

	public function getDrops(Item $item) : array{
		return $this->isTop() ? [] : parent::getDrops($item);
	}

	protected function hasFaces() : bool{
		foreach(Facing::HORIZONTAL as $f){
			if($this->getSideConnection($f) !== null){
				return true;
			}
		}
		return false;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->recalculateConnections();
		$tx->addBlock($blockReplace->position, $this);

		$up = $blockReplace->getSide(Facing::UP);
		if(($up->canBeReplaced() || $up->hasSameTypeId($this)) && $this->hasFaces()){
			$top = $this->createTopperWithSide($this);
			if($top !== null){
				$tx->addBlock($up->position, $top);
				return true;
			}
			return true;
		}

		return false;
	}

	protected function recalculateConnections() : bool{
		$world = $this->position->getWorld();
		$changed = 0;
		foreach(Facing::HORIZONTAL as $f){
			$lastWallside = $this->getSideConnection($f);
			$wallside = null;
			if($this->getAdjacentSupportType($f)->hasEdgeSupport()){
				$wallside = !$this->isTop() ? WallConnectionType::SHORT : $this->getSideConnection($f) ?? null;

				if($wallside === WallConnectionType::SHORT){
					$above = $world->getBlockAt($this->position->x, $this->position->y + 1, $this->position->z);
					if($above instanceof MossyCarpet && $above->hasSameTypeId($this) && $above->getSideConnection($f) !== null && $above->isTop()){
						$wallside = WallConnectionType::TALL;
					}

					if($this->isTop()){
						$below = $world->getBlockAt($this->position->x, $this->position->y - 1, $this->position->z);
						if($below instanceof MossyCarpet && $below->hasSameTypeId($this) && $below->getSideConnection($f) === null){
							$wallside = null;
						}
					}
				}
			}
			if($lastWallside !== $wallside){
				$this->setSideConnection($f, $wallside);
				$changed++;
			}
		}
		return $changed > 0;
	}

	public function onNearbyBlockChange() : void{
		$world = $this->position->getWorld();
		if(!$this->canSurvive()){
			$world->useBreakOn($this->position);
			return;
		}
		$updated = $this->recalculateConnections();
		if($this->isTop() && !$this->hasFaces()){
			$world->useBreakOn($this->position);
			return;
		}
		if($updated){
			$world->setBlock($this->position, $this);
		}
	}

	protected function canSurvive() : bool{
		$below = $this->getSide(Facing::DOWN);
		if(!$this->isTop()){
			return $below->getTypeId() !== BlockTypeIds::AIR;
		}
		return $below instanceof MossyCarpet && $below->hasSameTypeId($this) && !$below->isTop();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer){
			if($this->isTop()){
				return false;
			}
			$candidate = $this->createTopperWithSide($this);
			if($candidate !== null){
				if(BlockEventHelper::grow($this->getSide(Facing::UP), $candidate, $player)){
					$item->pop();
				}
				return true;
			}
			return false;
		}
		return false;
	}

	private function createTopperWithSide(Block $base) : ?MossyCarpet{
		$above = $base->getSide(Facing::UP);
		if($base instanceof MossyCarpet && $base->hasSameTypeId($this) && $above->canBeReplaced()){
			$new = clone $this;
			$new->setTop(true);
			foreach(Facing::HORIZONTAL as $f){
				$side = null;
				if($above->getAdjacentSupportType($f)->hasEdgeSupport()){
					if($base->getSideConnection($f) !== null){
						$side = WallConnectionType::SHORT;
					}
				}
				$new->setSideConnection($f, $side);
			}
			return $new->hasFaces() ? $new : null;
		}
		return null;
	}
}
