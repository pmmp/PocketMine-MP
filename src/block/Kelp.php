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
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function mt_rand;

class Kelp extends Transparent{

	protected int $age = 0;

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN)) && $blockReplace instanceof Water){
			$this->setAge(mt_rand(0, 24));
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}
		return false;
	}

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->age = BlockDataSerializer::readBoundedInt("age", $stateMeta, 0, 24);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getAge() : int{
		return $this->age;
	}

	/** @return $this */
	public function setAge(int $age) : self{
		if($age < 0 || $age > 24){
			throw new \InvalidArgumentException("Age must be in range 0-24");
		}
		$this->age = $age;
		return $this;
	}

	private function canBeSupportedBy(Block $block) : bool{
		return
			($block->isSolid() &&
			!$block instanceof Magma &&
			!$block instanceof SoulSand) ||
			$block instanceof Kelp;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$highestKelp = $this->position->getWorld()->getBlockAt($this->position->getFloorX(), $this->getHighestKelp(), $this->position->getFloorZ());
		if($highestKelp->getSide(Facing::UP) instanceof Water && $highestKelp instanceof Kelp && mt_rand(1, 100) <= 14){
			$this->grow($highestKelp);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof Fertilizer){
			$highest = $this->position->getWorld()->getBlockAt($this->position->getFloorX(), $this->getHighestKelp(), $this->position->getFloorZ());
			if($highest instanceof Kelp && $this->grow($highest)){
				$item->pop();
				return true;
			}
		}
		return false;
	}

	private function getHighestKelp() : int{
		for ($y = $this->position->getFloorY() + 1; $y <= $this->position->getWorld()->getMaxY(); $y++) {
			if($this->position->getWorld()->getBlockAt($this->position->getFloorX(), $y, $this->position->getFloorZ()) instanceof Water){
				return $y - 1;
			}
		}
		return $this->position->getFloorY();
	}

	private function grow(Kelp $block) : bool{
		$up = $block->getSide(Facing::UP);
		if($block->getAge() < 25 && $up instanceof Water){
			$ev = new BlockGrowEvent($block, VanillaBlocks::KELP()->setAge($block->getAge() + 1));
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
			$this->position->getWorld()->setBlock($up->position, $ev->getNewState());
			return true;
		}
		return false;
	}
}
