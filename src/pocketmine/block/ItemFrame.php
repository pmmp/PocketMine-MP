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
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\ItemFrame as TileItemFrame;
use pocketmine\tile\Tile;

class ItemFrame extends Flowable{
	protected $id = Block::ITEM_FRAME_BLOCK;

	protected $itemId = Item::ITEM_FRAME;

	/** @var int */
	protected $facing = Facing::NORTH;

	public function __construct(){

	}

	protected function writeStateToMeta() : int{
		return 5 - $this->facing;
	}

	public function readStateFromMeta(int $meta) : void{
		$this->facing = 5 - $meta;
	}

	public function getStateBitmask() : int{
		return 0b11;
	}

	public function getName() : string{
		return "Item Frame";
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$tile = $this->level->getTile($this);
		if($tile instanceof TileItemFrame){
			if($tile->hasItem()){
				$tile->setItemRotation(($tile->getItemRotation() + 1) % 8);
			}elseif(!$item->isNull()){
				$tile->setItem($item->pop());
			}
		}

		return true;
	}

	public function onNearbyBlockChange() : void{
		if(!$this->getSide(Facing::opposite($this->facing))->isSolid()){
			$this->level->useBreakOn($this);
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($face === Facing::DOWN or $face === Facing::UP or !$blockClicked->isSolid()){
			return false;
		}

		$this->facing = $face;

		if(parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			Tile::createTile(Tile::ITEM_FRAME, $this->getLevel(), TileItemFrame::createNBT($this, $item));
			return true;
		}

		return false;

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
