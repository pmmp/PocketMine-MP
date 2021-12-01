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
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function mt_rand;

class SweetBerryBush extends Flowable{
	public const STAGE_SAPLING = 0;
	public const STAGE_BUSH_NO_BERRIES = 1;
	public const STAGE_BUSH_SOME_BERRIES = 2;
	public const STAGE_MATURE = 3;

	protected int $age = self::STAGE_SAPLING;

	protected function writeStateToMeta() : int{
		return $this->age;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->age = BlockDataSerializer::readBoundedInt("stage", $stateMeta, self::STAGE_SAPLING, self::STAGE_MATURE);
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getAge() : int{ return $this->age; }

	/** @return $this */
	public function setAge(int $age) : self{
		if($age < self::STAGE_SAPLING || $age > self::STAGE_MATURE){
			throw new \InvalidArgumentException("Age must be in range 0-3");
		}
		$this->age = $age;
		return $this;
	}

	public function getBerryDropAmount() : int{
		if($this->age === self::STAGE_MATURE){
			return mt_rand(2, 3);
		}elseif($this->age >= self::STAGE_BUSH_SOME_BERRIES){
			return mt_rand(1, 2);
		}
		return 0;
	}

	protected function canBeSupportedBy(Block $block) : bool{
		$id = $block->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT || $id === BlockLegacyIds::PODZOL;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedBy($blockReplace->getSide(Facing::DOWN))){
			return false;
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->age < self::STAGE_MATURE && $item instanceof Fertilizer){
			$block = clone $this;
			$block->age++;

			$ev = new BlockGrowEvent($this, $block);
			$ev->call();

			if(!$ev->isCancelled()){
				$this->position->getWorld()->setBlock($this->position, $ev->getNewState());
				$item->pop();
			}

		}elseif(($dropAmount = $this->getBerryDropAmount()) > 0){
			$this->position->getWorld()->setBlock($this->position, $this->setAge(self::STAGE_BUSH_NO_BERRIES));
			$this->position->getWorld()->dropItem($this->position, $this->asItem()->setCount($dropAmount));
		}

		return true;
	}

	public function asItem() : Item{
		return VanillaItems::SWEET_BERRIES();
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		if(($dropAmount = $this->getBerryDropAmount()) > 0){
			return [
				$this->asItem()->setCount($dropAmount)
			];
		}

		return [];
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
		if($this->age < self::STAGE_MATURE and mt_rand(0, 2) === 1){
			$block = clone $this;
			++$block->age;
			$ev = new BlockGrowEvent($this, $block);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->position->getWorld()->setBlock($this->position, $ev->getNewState());
			}
		}
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool{
		//TODO: in MCPE, this only triggers if moving while inside the bush block - we don't have the system to deal
		//with that reliably right now
		if($this->age >= self::STAGE_BUSH_NO_BERRIES && $entity instanceof Living){
			$entity->attack(new EntityDamageByBlockEvent($this, $entity, EntityDamageByBlockEvent::CAUSE_CONTACT, 1));
		}
		return true;
	}
}
