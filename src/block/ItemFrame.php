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
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function lcg_value;

class ItemFrame extends Flowable{
	use HorizontalFacingTrait;

	public const ROTATIONS = 8;

	protected bool $hasMap = false; //makes frame appear large if set

	protected ?Item $framedItem = null;
	protected int $itemRotation = 0;
	protected float $itemDropChance = 1.0;

	protected function writeStateToMeta() : int{
		return BlockDataSerializer::write5MinusHorizontalFacing($this->facing) | ($this->hasMap ? BlockLegacyMetadata::ITEM_FRAME_FLAG_HAS_MAP : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataSerializer::read5MinusHorizontalFacing($stateMeta);
		$this->hasMap = ($stateMeta & BlockLegacyMetadata::ITEM_FRAME_FLAG_HAS_MAP) !== 0;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileItemFrame){
			$this->framedItem = $tile->getItem();
			if($this->framedItem->isNull()){
				$this->framedItem = null;
			}
			$this->itemRotation = $tile->getItemRotation() % self::ROTATIONS;
			$this->itemDropChance = $tile->getItemDropChance();
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileItemFrame){
			$tile->setItem($this->framedItem);
			$tile->setItemRotation($this->itemRotation);
			$tile->setItemDropChance($this->itemDropChance);
		}
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getFramedItem() : ?Item{
		return $this->framedItem !== null ? clone $this->framedItem : null;
	}

	/** @return $this */
	public function setFramedItem(?Item $item) : self{
		if($item === null or $item->isNull()){
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

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->framedItem !== null){
			$this->itemRotation = ($this->itemRotation + 1) % self::ROTATIONS;
		}elseif(!$item->isNull()){
			$this->framedItem = $item->pop();
		}else{
			return true;
		}

		$this->pos->getWorld()->setBlock($this->pos, $this);

		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		if($this->framedItem === null){
			return false;
		}
		if(lcg_value() <= $this->itemDropChance){
			$this->pos->getWorld()->dropItem($this->pos->add(0.5, 0.5, 0.5), clone $this->framedItem);
		}
		$this->setFramedItem(null);
		$this->pos->getWorld()->setBlock($this->pos, $this);
		return true;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Facing::opposite($this->facing))->isSolid()){
			$this->pos->getWorld()->useBreakOn($this->pos);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($face === Facing::DOWN or $face === Facing::UP or !$blockClicked->isSolid()){
			return false;
		}

		$this->facing = $face;

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drops = parent::getDropsForCompatibleTool($item);
		if($this->framedItem !== null and lcg_value() <= $this->itemDropChance){
			$drops[] = clone $this->framedItem;
		}

		return $drops;
	}

	public function getPickedItem(bool $addUserData = false) : Item{
		return $this->framedItem !== null ? clone $this->framedItem : parent::getPickedItem($addUserData);
	}
}
