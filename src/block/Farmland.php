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
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use function lcg_value;

class Farmland extends Transparent{
	public const MAX_WETNESS = 7;

	protected int $wetness = 0; //"moisture" blockstate property in PC

	protected function writeStateToMeta() : int{
		return $this->wetness;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->wetness = BlockDataSerializer::readBoundedInt("wetness", $stateMeta, 0, self::MAX_WETNESS);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getWetness() : int{ return $this->wetness; }

	/** @return $this */
	public function setWetness(int $wetness) : self{
		if($wetness < 0 || $wetness > self::MAX_WETNESS){
			throw new \InvalidArgumentException("Wetness must be in range 0 ... " . self::MAX_WETNESS);
		}
		$this->wetness = $wetness;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()]; //TODO: this should be trimmed at the top by 1/16, but MCPE currently treats them as a full block (https://bugs.mojang.com/browse/MCPE-12109)
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::UP)->isSolid()){
			$this->position->getWorld()->setBlock($this->position, VanillaBlocks::DIRT());
		}
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$world = $this->position->getWorld();
		if(!$this->canHydrate()){
			if($this->wetness > 0){
				$this->wetness--;
				$world->setBlock($this->position, $this, false);
			}else{
				$world->setBlock($this->position, VanillaBlocks::DIRT());
			}
		}elseif($this->wetness < self::MAX_WETNESS){
			$this->wetness = self::MAX_WETNESS;
			$world->setBlock($this->position, $this, false);
		}
	}

	public function onEntityLand(Entity $entity) : ?float{
		if($entity instanceof Living && lcg_value() < $entity->getFallDistance() - 0.5){
			$ev = new EntityTrampleFarmlandEvent($entity, $this);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->getPosition()->getWorld()->setBlock($this->getPosition(), VanillaBlocks::DIRT());
			}
		}
		return null;
	}

	protected function canHydrate() : bool{
		//TODO: check rain
		$start = $this->position->add(-4, 0, -4);
		$end = $this->position->add(4, 1, 4);
		for($y = $start->y; $y <= $end->y; ++$y){
			for($z = $start->z; $z <= $end->z; ++$z){
				for($x = $start->x; $x <= $end->x; ++$x){
					if($this->position->getWorld()->getBlockAt($x, $y, $z) instanceof Water){
						return true;
					}
				}
			}
		}

		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaBlocks::DIRT()->asItem()
		];
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return VanillaBlocks::DIRT()->asItem();
	}
}
