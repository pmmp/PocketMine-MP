<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
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
use pocketmine\Server;

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
		static $shrinkSize = 0.0625;
		return new AxisAlignedBB($shrinkSize, $shrinkSize, $shrinkSize, 1 - $shrinkSize, 1 - $shrinkSize, 1 - $shrinkSize);
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
				if(!$b->canBeFlowedInto()){
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
						Server::getInstance()->getPluginManager()->callEvent($ev = new BlockGrowEvent($b, BlockFactory::get(Block::CACTUS)));
						if(!$ev->isCancelled()){
							$this->getLevel()->setBlock($b, $ev->getNewState(), true);
						}
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
			if($block0->isTransparent() and $block1->isTransparent() and $block2->isTransparent() and $block3->isTransparent()){
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