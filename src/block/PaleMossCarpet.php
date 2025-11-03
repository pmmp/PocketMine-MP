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
use pocketmine\block\utils\PaleMossCarpetVineGrowth;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class PaleMossCarpet extends Flowable{
	use StaticSupportTrait;

	/** @var PaleMossCarpetVineGrowth[] */
	protected array $sides = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->paleMossCarpetSides($this->sides);
	}

	public function getVineGrowth(int $face) : PaleMossCarpetVineGrowth{
		return $this->sides[$face] ?? PaleMossCarpetVineGrowth::NONE;
	}

	public function setVineGrowth(int $face, PaleMossCarpetVineGrowth $value) : self{
		if($value === PaleMossCarpetVineGrowth::NONE){
			unset($this->sides[$face]);
			return $this;
		}
		$this->sides[$face] = $value;
		return $this;
	}

	public function isCarpetPart() : bool{ return true; }

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 15 / 16)];
	}

	protected function hasFaces() : bool{
		foreach(Facing::HORIZONTAL as $f){
			if($this->getVineGrowth($f) !== PaleMossCarpetVineGrowth::NONE){
				return true;
			}
		}
		return false;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->recalculateConnections();
		$tx->addBlock($blockReplace->position, $this);

		$up = $blockReplace->getSide(Facing::UP);
		if(($up->canBeReplaced() || $up instanceof PaleMossCarpet) && $this->hasFaces()){
			$top = $this->createTopperWithSide($this);
			if($top !== null){
				$tx->addBlock($up->position, $top);
			}

			return true;
		}
		return true;
	}

	protected function recalculateConnections() : bool{
		$changed = 0;

		foreach(Facing::HORIZONTAL as $f){
			$lastSide = $this->getVineGrowth($f);
			$side = PaleMossCarpetVineGrowth::NONE;

			if($this->getAdjacentSupportType($f)->hasEdgeSupport()){
				$side = !$this->isCarpetPart() ? $this->getVineGrowth($f) : PaleMossCarpetVineGrowth::HALF;

				if($side === PaleMossCarpetVineGrowth::HALF){
					$above = $this->getSide(Facing::UP);
					if($above instanceof PaleMossCarpet && !$above->isCarpetPart() && $above->getVineGrowth($f) !== PaleMossCarpetVineGrowth::NONE){
						$side = PaleMossCarpetVineGrowth::FULL;
					}

					if(!$this->isCarpetPart()){
						$below = $this->getSide(Facing::DOWN);
						if($below instanceof PaleMossCarpet && $below->getVineGrowth($f) === PaleMossCarpetVineGrowth::NONE){
							$side = PaleMossCarpetVineGrowth::NONE;
						}
					}
				}
			}

			if($lastSide !== $side){
				$this->setVineGrowth($f, $side);
				$changed++;
			}
		}

		return $changed > 0;
	}

	public function onNearbyBlockChange() : void{
		$world = $this->position->getWorld();
		if(!$this->canBeSupportedAt($this)){
			$world->useBreakOn($this->position);
			return;
		}
		$updated = $this->recalculateConnections();
		if(!$this->isCarpetPart() && !$this->hasFaces()){
			$world->useBreakOn($this->position);
			return;
		}
		if($updated){
			$world->setBlock($this->position, $this);
		}
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$below = $block->getSide(Facing::DOWN);
		if($this->isCarpetPart()){
			return $below->getTypeId() !== BlockTypeIds::AIR;
		}
		return $below instanceof PaleMossCarpet && $below->isCarpetPart();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if(!$item instanceof Fertilizer || !$this->isCarpetPart()){
			return false;
		}

		$candidate = $this->createTopperWithSide($this);
		if($candidate !== null && BlockEventHelper::grow($this->getSide(Facing::UP), $candidate, $player)){
			$item->pop();
			return true;
		}
		return false;
	}

	protected function createTopperWithSide(Block $base) : ?PaleMossCarpet{
		$above = $base->getSide(Facing::UP);
		if(!$base instanceof PaleMossCarpet || (!$above->canBeReplaced() && !$above instanceof PaleMossCarpet)){
			return null;
		}

		$new = VanillaBlocks::PALE_MOSS_CARPET_VINE();

		foreach(Facing::HORIZONTAL as $f){
			$side = PaleMossCarpetVineGrowth::NONE;
			if($above->getAdjacentSupportType($f)->hasEdgeSupport() && $base->getVineGrowth($f) !== PaleMossCarpetVineGrowth::NONE){
				$side = PaleMossCarpetVineGrowth::HALF;
			}
			$new->setVineGrowth($f, $side);
		}

		return $new->hasFaces() ? $new : null;
	}

	public function getFlameEncouragement() : int{
		return 15;
	}

	public function getFlammability() : int{
		return 100;
	}
}
