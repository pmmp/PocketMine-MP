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
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileFlowerPot){
			$this->setPlant($tile->getPlant());
		}else{
			$this->setPlant(null);
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();

		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileFlowerPot);
		$tile->setPlant($this->plant);
	}

	public function getPlant() : ?Block{
		return $this->plant;
	}

	/** @return $this */
	public function setPlant(?Block $plant) : self{
		if($plant === null || $plant instanceof Air){
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

		return $this->isValidPlant($block);
	}

	private function isValidPlant(Block $block) : bool{
		return
			$block instanceof Cactus ||
			$block instanceof DeadBush ||
			$block instanceof Flower ||
			$block instanceof RedMushroom ||
			$block instanceof Sapling ||
			($block instanceof TallGrass && $block->getIdInfo()->getVariant() === BlockLegacyMetadata::TALLGRASS_FERN); //TODO: clean up
		//TODO: bamboo
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->contract(3 / 16, 0, 3 / 16)->trim(Facing::UP, 5 / 8)];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			return false;
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::DOWN))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	private function canBeSupportedBy(Block $block) : bool{
		return $block->getSupportType(Facing::UP)->hasCenterSupport();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$world = $this->position->getWorld();
		$plant = $item->getBlock();
		if($this->plant !== null){
			if($this->isValidPlant($plant)){
				//for some reason, vanilla doesn't remove the contents of the pot if the held item is plantable
				//and will also cause a new plant to be placed if clicking on the side
				return false;
			}

			$removedItems = [$this->plant->asItem()];
			if($player !== null){
				//this one just has to be a weirdo :(
				//this is the only block that directly adds items to the player inventory instead of just dropping items
				$removedItems = $player->getInventory()->addItem(...$removedItems);
			}
			foreach($removedItems as $drops){
				$world->dropItem($this->position->add(0.5, 0.5, 0.5), $drops);
			}

			$this->setPlant(null);
			$world->setBlock($this->position, $this);
			return true;
		}elseif($this->isValidPlant($plant)){
			$this->setPlant($plant);
			$item->pop();
			$world->setBlock($this->position, $this);

			return true;
		}

		return false;
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
