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

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Sugarcane extends Flowable{

	/** @var int */
	protected $age = 0;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? BlockBreakInfo::instant());
	}

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->age = BlockDataValidator::readBoundedInt("age", $stateMeta, 0, 15);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof Fertilizer){
			if($this->getSide(Facing::DOWN)->getId() !== BlockLegacyIds::SUGARCANE_BLOCK){
				for($y = 1; $y < 3; ++$y){
					$b = $this->getWorld()->getBlockAt($this->x, $this->y + $y, $this->z);
					if($b->getId() === BlockLegacyIds::AIR){
						$ev = new BlockGrowEvent($b, BlockFactory::get(BlockLegacyIds::SUGARCANE_BLOCK));
						$ev->call();
						if($ev->isCancelled()){
							break;
						}
						$this->getWorld()->setBlock($b, $ev->getNewState());
					}else{
						break;
					}
				}
				$this->age = 0;
				$this->getWorld()->setBlock($this, $this);
			}

			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		$down = $this->getSide(Facing::DOWN);
		if($down->isTransparent() and $down->getId() !== BlockLegacyIds::SUGARCANE_BLOCK){
			$this->getWorld()->useBreakOn($this);
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->getSide(Facing::DOWN)->getId() !== BlockLegacyIds::SUGARCANE_BLOCK){
			if($this->age === 15){
				for($y = 1; $y < 3; ++$y){
					$b = $this->getWorld()->getBlockAt($this->x, $this->y + $y, $this->z);
					if($b->getId() === BlockLegacyIds::AIR){
						$this->getWorld()->setBlock($b, BlockFactory::get(BlockLegacyIds::SUGARCANE_BLOCK));
						break;
					}
				}
				$this->age = 0;
				$this->getWorld()->setBlock($this, $this);
			}else{
				++$this->age;
				$this->getWorld()->setBlock($this, $this);
			}
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() === BlockLegacyIds::SUGARCANE_BLOCK){
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}elseif($down->getId() === BlockLegacyIds::GRASS or $down->getId() === BlockLegacyIds::DIRT or $down->getId() === BlockLegacyIds::SAND){
			foreach(Facing::HORIZONTAL as $side){
				if($down->getSide($side) instanceof Water){
					return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
				}
			}
		}

		return false;
	}
}
