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

use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\FlowerPot as TileFlowerPot;
use function assert;

class FlowerPot extends Flowable{

	/**
	 * TODO: get rid of this hack (it's currently needed to deal with blockfactory state handling)
	 * @var bool
	 */
	protected $occupied = false;

	/** @var Block|null */
	protected $plant = null;

	protected function writeStateToMeta() : int{
		return $this->occupied ? 1 : 0;
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->occupied = $stateMeta !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111; //vanilla uses various values, we only care about 1 and 0 for PE
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->level->getTile($this);
		if($tile instanceof TileFlowerPot){
			$this->setPlant($tile->getPlant());
		}else{
			$this->occupied = false;
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();

		$tile = $this->level->getTile($this);
		assert($tile instanceof TileFlowerPot);
		$tile->setPlant($this->plant);
	}

	/**
	 * @return Block|null
	 */
	public function getPlant() : ?Block{
		return $this->plant;
	}

	/**
	 * @param Block|null $plant
	 */
	public function setPlant(?Block $plant) : void{
		if($plant === null or $plant instanceof Air){
			$this->plant = null;
		}else{
			$this->plant = clone $plant;
		}
		$this->occupied = $this->plant !== null;
	}

	public function canAddPlant(Block $block) : bool{
		if($this->plant !== null){
			return false;
		}

		return
			$block instanceof Cactus or
			$block instanceof Dandelion or
			$block instanceof DeadBush or
			$block instanceof Flower or
			$block instanceof RedMushroom or
			$block instanceof Sapling or
			($block instanceof TallGrass and $block->getIdInfo()->getVariant() === 2); //fern - TODO: clean up
		//TODO: bamboo
	}

	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return AxisAlignedBB::one()->contract(3 / 16, 0, 3 / 16)->trim(Facing::UP, 5 / 8);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			return false;
		}

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			$this->getLevel()->useBreakOn($this);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$plant = $item->getBlock();
		if(!$this->canAddPlant($plant)){
			return false;
		}

		$this->setPlant($plant);
		$item->pop();
		$this->level->setBlock($this, $this);

		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$items = parent::getDropsForCompatibleTool($item);
		if($this->plant !== null){
			$items[] = $this->plant->asItem();
		}

		return $items;
	}

	public function getPickedItem() : Item{
		return $this->plant !== null ? $this->plant->asItem() : parent::getPickedItem();
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
