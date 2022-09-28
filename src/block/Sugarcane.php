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
use pocketmine\world\Position;

class Sugarcane extends Flowable{
	public const MAX_AGE = 15;

	protected int $age = 0;

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->age = BlockDataSerializer::readBoundedInt("age", $stateMeta, 0, self::MAX_AGE);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	private function seekToBottom() : Position{
		$world = $this->position->getWorld();
		$bottom = $this->position;
		while(($next = $world->getBlock($bottom->down()))->isSameType($this)){
			$bottom = $next->position;
		}
		return $bottom;
	}

	private function grow(Position $pos) : bool{
		$grew = false;
		$world = $pos->getWorld();
		for($y = 1; $y < 3; ++$y){
			if(!$world->isInWorld($pos->x, $pos->y + $y, $pos->z)){
				break;
			}
			$b = $world->getBlockAt($pos->x, $pos->y + $y, $pos->z);
			if($b->getId() === BlockLegacyIds::AIR){
				$ev = new BlockGrowEvent($b, VanillaBlocks::SUGARCANE());
				$ev->call();
				if($ev->isCancelled()){
					break;
				}
				$world->setBlock($b->position, $ev->getNewState());
				$grew = true;
			}elseif(!$b->isSameType($this)){
				break;
			}
		}
		$this->age = 0;
		$world->setBlock($pos, $this);
		return $grew;
	}

	public function getAge() : int{ return $this->age; }

	/** @return $this */
	public function setAge(int $age) : self{
		if($age < 0 || $age > self::MAX_AGE){
			throw new \InvalidArgumentException("Age must be in range 0 ... " . self::MAX_AGE);
		}
		$this->age = $age;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof Fertilizer){
			if($this->grow($this->seekToBottom())){
				$item->pop();
			}

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		$down = $this->getSide(Facing::DOWN);
		if(!$this->isValidSupport($down)){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(!$this->getSide(Facing::DOWN)->isSameType($this)){
			if($this->age === self::MAX_AGE){
				$this->grow($this->position);
			}else{
				++$this->age;
				$this->position->getWorld()->setBlock($this->position, $this);
			}
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN);
		if($down->isSameType($this)){
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}elseif($this->isValidSupport($down)){
			foreach(Facing::HORIZONTAL as $side){
				$sideBlock = $down->getSide($side);
				if($sideBlock instanceof Water || $sideBlock instanceof FrostedIce){
					return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
				}
			}
		}

		return false;
	}

	private function isValidSupport(Block $block) : bool{
		$id = $block->getId();
		//TODO: rooted dirt, moss block
		return $block->isSameType($this)
			|| $id === BlockLegacyIds::GRASS
			|| $id === BlockLegacyIds::DIRT
			|| $id === BlockLegacyIds::PODZOL
			|| $id === BlockLegacyIds::MYCELIUM
			|| $id === BlockLegacyIds::SAND;
	}
}
