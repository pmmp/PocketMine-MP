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

use pocketmine\block\utils\WallConnectionType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\math\Facing;

class MossyCarpet extends Flowable{

	protected bool $top = false;

	/** @var WallConnectionType[]|null */
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

	protected function hasFaces() : bool{
		if(!$this->isTop()){
			return true;
		}
		foreach(Facing::HORIZONTAL as $f){
			if($this->getSideConnection($f) !== null){
				return true;
			}
		}
		return false;
	}

	protected function recalculateConnections() : bool{
		$block = clone $this;
		$world = $this->position->getWorld();
		$changed = 0;
		foreach(Facing::HORIZONTAL as $f){
			$lastWallside = $block->getSideConnection($f);
			$wallside = null;
			if($this->getAdjacentSupportType($f)->hasEdgeSupport()){
				$wallside = !$block->isTop() ? WallConnectionType::SHORT : $block->getSideConnection($f) ?? null;

				if($wallside === WallConnectionType::SHORT){
					$above = $world->getBlockAt($this->position->x, $this->position->y + 1, $this->position->z);
					if($above instanceof MossyCarpet && $above->getSideConnection($f) !== null && $above->isTop()){
						$wallside = WallConnectionType::TALL;
					}

					if($block->isTop()){
						$below = $world->getBlockAt($this->position->x, $this->position->y - 1, $this->position->z);
						if($below instanceof MossyCarpet && $below->getSideConnection($f) === null){
							$wallside = null;
						}
					}
				}
			}
			if($lastWallside !== $wallside){
				$block->setSideConnection($f, $wallside);
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
		if(!$this->hasFaces()){
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
		return $below instanceof MossyCarpet && !$below->isTop();
	}
}
