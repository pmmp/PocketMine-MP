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
use pocketmine\math\Vector3;
use pocketmine\Player;

class Cactus extends Transparent{

	protected $id = self::CACTUS;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
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

		return new AxisAlignedBB(
			$this->x + 0.0625,
			$this->y + 0.0625,
			$this->z + 0.0625,
			$this->x + 0.9375,
			$this->y + 0.9375,
			$this->z + 0.9375
		);
	}

	public function onEntityCollide(Entity $entity) : void{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_CONTACT, 1);
		$entity->attack($ev);
	}

	public function onNearbyBlockChange() : void{
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() !== self::SAND and $down->getId() !== self::CACTUS){
			$this->getLevel()->useBreakOn($this);
		}else{
			for($side = 2; $side <= 5; ++$side){
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
		if($this->getSide(Vector3::SIDE_DOWN)->getId() !== self::CACTUS){
			if($this->meta === 0x0f){
				for($y = 1; $y < 3; ++$y){
					$b = $this->getLevel()->getBlockAt($this->x, $this->y + $y, $this->z);
					if($b->getId() === self::AIR){
						$ev = new BlockGrowEvent($b, BlockFactory::get(Block::CACTUS));
						$ev->call();
						if($ev->isCancelled()){
							break;
						}
						$this->getLevel()->setBlock($b, $ev->getNewState(), true);
					}else{
						break;
					}
				}
				$this->meta = 0;
				$this->getLevel()->setBlock($this, $this);
			}else{
				++$this->meta;
				$this->getLevel()->setBlock($this, $this);
			}
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$down = $this->getSide(Vector3::SIDE_DOWN);
		if($down->getId() === self::SAND or $down->getId() === self::CACTUS){
			$block0 = $this->getSide(Vector3::SIDE_NORTH);
			$block1 = $this->getSide(Vector3::SIDE_SOUTH);
			$block2 = $this->getSide(Vector3::SIDE_WEST);
			$block3 = $this->getSide(Vector3::SIDE_EAST);
			if(!$block0->isSolid() and !$block1->isSolid() and !$block2->isSolid() and !$block3->isSolid()){
				$this->getLevel()->setBlock($this, $this, true);

				return true;
			}
		}

		return false;
	}

	public function getVariantBitmask() : int{
		return 0;
	}
}
