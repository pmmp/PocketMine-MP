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

use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\tile\ItemFrame as TileItemFrame;

class ItemFrame extends Flowable{
	protected $id = Block::ITEM_FRAME_BLOCK;

	protected $itemId = Item::ITEM_FRAME;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Item Frame";
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$tile = $this->level->getTile($this);
		if(!($tile instanceof TileItemFrame)){
			$tile = Tile::createTile(Tile::ITEM_FRAME, $this->getLevel(), TileItemFrame::createNBT($this));
		}

		if($tile->hasItem()){
			$tile->setItemRotation(($tile->getItemRotation() + 1) % 8);
		}elseif(!$item->isNull()){
			$tile->setItem($item->pop());
		}

		return true;
	}

	public function onNearbyBlockChange() : void{
		$sides = [
			0 => Facing::WEST,
			1 => Facing::EAST,
			2 => Facing::NORTH,
			3 => Facing::SOUTH
		];
		if(!$this->getSide($sides[$this->meta])->isSolid()){
			$this->level->useBreakOn($this);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($face === Facing::DOWN or $face === Facing::UP or !$blockClicked->isSolid()){
			return false;
		}

		$faces = [
			Facing::NORTH => 3,
			Facing::SOUTH => 2,
			Facing::WEST => 1,
			Facing::EAST => 0
		];

		$this->meta = $faces[$face];
		$this->level->setBlock($blockReplace, $this, true, true);

		Tile::createTile(Tile::ITEM_FRAME, $this->getLevel(), TileItemFrame::createNBT($this, $face, $item, $player));

		return true;

	}

	public function getVariantBitmask() : int{
		return 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drops = parent::getDropsForCompatibleTool($item);

		$tile = $this->level->getTile($this);
		if($tile instanceof TileItemFrame){
			$tileItem = $tile->getItem();
			if(lcg_value() <= $tile->getItemDropChance() and !$tileItem->isNull()){
				$drops[] = $tileItem;
			}
		}

		return $drops;
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}
}
