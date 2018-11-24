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

use pocketmine\entity\Entity;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Cactus extends Transparent{

	protected $id = self::CACTUS;

	/** @var int */
	protected $age = 0;

	public function __construct(){

	}

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function readStateFromMeta(int $meta) : void{
		$this->age = $meta;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function getHardness() : float{
		return 0.4;
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function getName() : string{
		return "Cactus";
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		static $shrinkSize = 1 / 16;
		return AxisAlignedBB::one()->contract($shrinkSize, 0, $shrinkSize)->trim(Facing::UP, $shrinkSize);
	}

	public function onEntityCollide(Entity $entity) : void{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_CONTACT, 1);
		$entity->attack($ev);
	}

	public function onNearbyBlockChange() : void{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() !== self::SAND and $down->getId() !== self::CACTUS){
			$this->getLevel()->useBreakOn($this);
		}else{
			foreach(Facing::HORIZONTAL as $side){
				$b = $this->getSide($side);
				if($b->isSolid()){
					$this->getLevel()->useBreakOn($this);
					break;
				}
			}
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->getSide(Facing::DOWN)->getId() !== self::CACTUS){
			if($this->age === 15){
				for($y = 1; $y < 3; ++$y){
					$b = $this->getLevel()->getBlockAt($this->x, $this->y + $y, $this->z);
					if($b->getId() === self::AIR){
						$ev = new BlockGrowEvent($b, BlockFactory::get(Block::CACTUS));
						$ev->call();
						if(!$ev->isCancelled()){
							$this->getLevel()->setBlock($b, $ev->getNewState());
						}
					}
				}
				$this->age = 0;
				$this->getLevel()->setBlock($this, $this);
			}else{
				++$this->age;
				$this->getLevel()->setBlock($this, $this);
			}
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() === self::SAND or $down->getId() === self::CACTUS){
			foreach(Facing::HORIZONTAL as $side){
				if($this->getSide($side)->isSolid()){
					return false;
				}
			}

			return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}
}
