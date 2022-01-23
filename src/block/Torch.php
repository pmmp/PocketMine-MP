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

use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\SlabType;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function in_array;

class Torch extends Flowable{

	protected int $facing = Facing::UP;

	protected function writeStateToMeta() : int{
		return $this->facing === Facing::UP ? 5 : 6 - BlockDataSerializer::writeHorizontalFacing($this->facing);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$facingMeta = $stateMeta & 0x7;
		$this->facing = $facingMeta === 5 ? Facing::UP : BlockDataSerializer::readHorizontalFacing(6 - $facingMeta);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getFacing() : int{ return $this->facing; }

	/** @return $this */
	public function setFacing(int $facing) : self{
		if($facing === Facing::DOWN){
			throw new \InvalidArgumentException("Torch may not face DOWN");
		}
		$this->facing = $facing;
		return $this;
	}

	public function getLightLevel() : int{
		return 14;
	}

	public function onNearbyBlockChange() : void{
		$support = $this->getSide(Facing::opposite($this->facing));

		if(!$this->isValidSupport($support, $this->facing)){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($blockClicked->canBeReplaced() && !$this->isValidSupport($blockClicked->getSide(Facing::DOWN), Facing::UP)){
			$this->facing = Facing::UP;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}elseif($face !== Facing::DOWN && $this->isValidSupport($blockClicked, $face)){
			$this->facing = $face;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}else{
			foreach([
				Facing::SOUTH,
				Facing::WEST,
				Facing::NORTH,
				Facing::EAST,
				Facing::DOWN
			] as $side){
				$block = $this->getSide($side);
				if($this->isValidSupport($block, Facing::opposite($side))){
					$this->facing = Facing::opposite($side);
					return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
				}
			}
		}
		return false;
	}

	private function isValidSupport(Block $block, int $face) : bool{
		//TODO: side of composter
		if($block instanceof Stair){
			if($face === Facing::UP && $block->isUpsideDown()){
				return true;
			}elseif($face === $block->getFacing()){
				return true;
			}
		}elseif($block instanceof Slab){
			if($face === Facing::UP && (
				$block->getSlabType()->equals(SlabType::TOP()) ||
				$block->getSlabType()->equals(SlabType::DOUBLE())
			)){
				return true;
			}elseif(in_array($face, Facing::HORIZONTAL, true) && $block->getSlabType()->equals(SlabType::DOUBLE())){
				return true;
			}
		}elseif($block instanceof Trapdoor){
			if($face === Facing::UP && $block->isTop() && !$block->isOpen()){
				return true;
			}
		}elseif($block instanceof Fence || $block instanceof Wall){
			if($face === Facing::UP){
				return true;
			}
		}elseif($block->isSolid() ||
				($block instanceof Farmland ||
				$block instanceof GrassPath ||
				$block instanceof Ice ||
				$block->getId() === BlockLegacyIds::BARRIER)){
			return true;
		}
		return false;
	}
}
