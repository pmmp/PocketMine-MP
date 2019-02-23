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

use pocketmine\block\utils\BlockDataValidator;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\ItemFrame as TileItemFrame;
use function lcg_value;

class ItemFrame extends Flowable{
	public const ROTATIONS = 8;

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $hasMap = false; //makes frame appear large if set
	/** @var Item|null */
	protected $framedItem = null;
	/** @var int */
	protected $itemRotation = 0;
	/** @var float */
	protected $itemDropChance = 1.0;

	protected function writeStateToMeta() : int{
		return (5 - $this->facing) | ($this->hasMap ? 0x04 : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::readHorizontalFacing(5 - ($stateMeta & 0x03));
		$this->hasMap = ($stateMeta & 0x04) !== 0;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->level->getTile($this);
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
		$tile = $this->level->getTile($this);
		if($tile instanceof TileItemFrame){
			$tile->setItem($this->framedItem);
			$tile->setItemRotation($this->itemRotation);
			$tile->setItemDropChance($this->itemDropChance);
		}
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	/**
	 * @return int
	 */
	public function getFacing() : int{
		return $this->facing;
	}

	/**
	 * @param int $facing
	 */
	public function setFacing(int $facing) : void{
		$this->facing = $facing;
	}

	/**
	 * @return Item|null
	 */
	public function getFramedItem() : ?Item{
		return clone $this->framedItem;
	}

	/**
	 * @param Item|null $item
	 */
	public function setFramedItem(?Item $item) : void{
		if($item === null or $item->isNull()){
			$this->framedItem = null;
			$this->itemRotation = 0;
		}else{
			$this->framedItem = clone $item;
		}
	}

	/**
	 * @return int
	 */
	public function getItemRotation() : int{
		return $this->itemRotation;
	}

	/**
	 * @param int $itemRotation
	 */
	public function setItemRotation(int $itemRotation) : void{
		$this->itemRotation = $itemRotation;
	}

	/**
	 * @return float
	 */
	public function getItemDropChance() : float{
		return $this->itemDropChance;
	}

	/**
	 * @param float $itemDropChance
	 */
	public function setItemDropChance(float $itemDropChance) : void{
		$this->itemDropChance = $itemDropChance;
	}

	public function onActivate(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->framedItem !== null){
			$this->itemRotation = ($this->itemRotation + 1) % self::ROTATIONS;
		}elseif(!$item->isNull()){
			$this->framedItem = $item->pop();
		}else{
			return true;
		}

		$this->level->setBlock($this, $this);

		return true;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Facing::opposite($this->facing))->isSolid()){
			$this->level->useBreakOn($this);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($face === Facing::DOWN or $face === Facing::UP or !$blockClicked->isSolid()){
			return false;
		}

		$this->facing = $face;

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drops = parent::getDropsForCompatibleTool($item);
		if($this->framedItem !== null and lcg_value() <= $this->itemDropChance){
			$drops[] = clone $this->framedItem;
		}

		return $drops;
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function getHardness() : float{
		return 0.25;
	}
}
