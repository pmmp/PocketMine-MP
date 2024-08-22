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

use pocketmine\block\tile\ItemFrame as TileItemFrame;
use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\ItemFrameAddItemSound;
use pocketmine\world\sound\ItemFrameRemoveItemSound;
use pocketmine\world\sound\ItemFrameRotateItemSound;
use function is_infinite;
use function is_nan;
use function lcg_value;

class ItemFrame extends Flowable{
	use AnyFacingTrait;

	public const ROTATIONS = 8;

	protected bool $hasMap = false; //makes frame appear large if set

	protected ?Item $framedItem = null;
	protected int $itemRotation = 0;
	protected float $itemDropChance = 1.0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->facing($this->facing);
		$w->bool($this->hasMap);
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileItemFrame){
			$this->framedItem = $tile->getItem();
			if($this->framedItem->isNull()){
				$this->framedItem = null;
			}
			$this->itemRotation = $tile->getItemRotation() % self::ROTATIONS;
			$this->itemDropChance = $tile->getItemDropChance();
		}

		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileItemFrame){
			$tile->setItem($this->framedItem);
			$tile->setItemRotation($this->itemRotation);
			$tile->setItemDropChance($this->itemDropChance);
		}
	}

	public function getFramedItem() : ?Item{
		return $this->framedItem !== null ? clone $this->framedItem : null;
	}

	/** @return $this */
	public function setFramedItem(?Item $item) : self{
		if($item === null || $item->isNull()){
			$this->framedItem = null;
			$this->itemRotation = 0;
		}else{
			$this->framedItem = clone $item;
		}
		return $this;
	}

	public function getItemRotation() : int{
		return $this->itemRotation;
	}

	/** @return $this */
	public function setItemRotation(int $itemRotation) : self{
		$this->itemRotation = $itemRotation;
		return $this;
	}

	public function getItemDropChance() : float{
		return $this->itemDropChance;
	}

	/** @return $this */
	public function setItemDropChance(float $itemDropChance) : self{
		if($itemDropChance < 0.0 || $itemDropChance > 1.0 || is_nan($itemDropChance) || is_infinite($itemDropChance)){
			throw new \InvalidArgumentException("Drop chance must be in range 0-1");
		}
		$this->itemDropChance = $itemDropChance;
		return $this;
	}

	public function hasMap() : bool{ return $this->hasMap; }

	/**
	 * This can be set irrespective of whether the frame actually contains a map or not. When set, the frame stretches
	 * to the edges of the block without leaving space around the edges.
	 *
	 * @return $this
	 */
	public function setHasMap(bool $hasMap) : self{
		$this->hasMap = $hasMap;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($this->framedItem !== null){
			$this->itemRotation = ($this->itemRotation + 1) % self::ROTATIONS;

			$this->position->getWorld()->addSound($this->position, new ItemFrameRotateItemSound());
		}elseif(!$item->isNull()){
			$this->framedItem = $item->pop();

			$this->position->getWorld()->addSound($this->position, new ItemFrameAddItemSound());
		}else{
			return true;
		}

		$this->position->getWorld()->setBlock($this->position, $this);

		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		if($this->framedItem === null){
			return false;
		}
		$world = $this->position->getWorld();
		if(lcg_value() <= $this->itemDropChance){
			$world->dropItem($this->position->add(0.5, 0.5, 0.5), clone $this->framedItem);
			$world->addSound($this->position, new ItemFrameRemoveItemSound());
		}
		$this->setFramedItem(null);
		$world->setBlock($this->position, $this);
		return true;
	}

	private function canBeSupportedAt(Block $block, int $face) : bool{
		return $block->getAdjacentSupportType($face) !== SupportType::NONE;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedAt($this, Facing::opposite($this->facing))){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedAt($blockReplace, Facing::opposite($face))){
			return false;
		}

		$this->facing = $face;

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drops = parent::getDropsForCompatibleTool($item);
		if($this->framedItem !== null && lcg_value() <= $this->itemDropChance){
			$drops[] = clone $this->framedItem;
		}

		return $drops;
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return $this->framedItem !== null ? clone $this->framedItem : parent::getPickedItem($addUserData);
	}
}
