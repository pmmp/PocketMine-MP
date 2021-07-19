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

use pocketmine\block\tile\FlowerPot as TileFlowerPot;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function assert;

class FlowerPot extends Flowable{

	protected ?Block $plant = null;

	protected function writeStateToMeta() : int{
		//TODO: HACK! this is just to make the client actually render the plant - we purposely don't read the flag back
		return $this->plant !== null ? BlockLegacyMetadata::FLOWER_POT_FLAG_OCCUPIED : 0;
	}

	public function getStateBitmask() : int{
		return 0b1;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileFlowerPot){
			$this->setPlant($tile->getPlant());
		}else{
			$this->setPlant(null);
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();

		$tile = $this->pos->getWorld()->getTile($this->pos);
		assert($tile instanceof TileFlowerPot);
		$tile->setPlant($this->plant);
	}

	public function getPlant() : ?Block{
		return $this->plant;
	}

	/** @return $this */
	public function setPlant(?Block $plant) : self{
		if($plant === null or $plant instanceof Air){
			$this->plant = null;
		}else{
			$this->plant = clone $plant;
		}
		return $this;
	}

	public function canAddPlant(Block $block) : bool{
		if($this->plant !== null){
			return false;
		}

		return
			$block instanceof Cactus or
			$block instanceof DeadBush or
			$block instanceof Flower or
			$block instanceof RedMushroom or
			$block instanceof Sapling or
			($block instanceof TallGrass and $block->getIdInfo()->getVariant() === BlockLegacyMetadata::TALLGRASS_FERN); //TODO: clean up
		//TODO: bamboo
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->contract(3 / 16, 0, 3 / 16)->trim(Facing::UP, 5 / 8)];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			return false;
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$plant = $item->getBlock();
		if(!$this->canAddPlant($plant)){
			return false;
		}

		$this->setPlant($plant);
		$item->pop();
		$this->pos->getWorld()->setBlock($this->pos, $this);

		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$items = parent::getDropsForCompatibleTool($item);
		if($this->plant !== null){
			$items[] = $this->plant->asItem();
		}

		return $items;
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return $this->plant !== null ? $this->plant->asItem() : parent::getPickedItem($addUserData);
	}
}
