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
use pocketmine\item\ItemFactory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\tile\ShulkerBox as TileShulkerBox;
use pocketmine\tile\Tile;

class ShulkerBox extends Transparent{

	protected $facing = Facing::UP;

	public function readStateFromMeta(int $meta) : void{
		$this->facing = $meta;
	}

	public function writeStateToMeta() : int{
		return $this->facing;
	}

	public function getStateBitmask() : int{
		return 15;
	}

	public function getHardness() : float{
		return 6;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		$this->facing = $face;

		Tile::createTile(Tile::SHULKER_BOX, $this->getLevel(), TileShulkerBox::createNBT($this, $face, $item, $player));
		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$t = $this->getLevel()->getTile($this);
			$sb = null;
			if($t instanceof TileShulkerBox){
				$sb = $t;
			}else{
				$sb = Tile::createTile(Tile::SHULKER_BOX, $this->getLevel(), TileShulkerBox::createNBT($this));
			}

			if(!($this->getSide(Facing::UP)->isTransparent()) or !$sb->canOpenWith($item->getCustomName())){
				return true;
			}

			$player->addWindow($sb->getInventory());
		}

		return true;
	}

	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$t = $this->getLevel()->getTile($this);
		if($t instanceof TileShulkerBox){
			$item = ItemFactory::get(Item::SHULKER_BOX, $this->getVariant(), 1);
			$itemNBT = new CompoundTag();
			$t->writeSaveData($itemNBT);
			$item->setCustomBlockData($itemNBT);

			return [$item];
		}

		return [];
	}

	public function onBreak(Item $item, Player $player = null) : bool{
		$tile = $this->level->getTile($this);
		if($tile instanceof TileShulkerBox){
			$tile->getInventory()->clearAll(false);
		}
		return parent::onBreak($item, $player);
	}
}