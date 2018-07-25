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

use pocketmine\block\utils\ColorBlockMetaHelper;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\tile\ShulkerBox as TileShulkerBox;
use pocketmine\tile\Tile;

class ShulkerBox extends Transparent{

	protected $id = self::SHULKER_BOX;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getHardness() : float{
		return 6;
	}

	public function getName() : string{
		return ColorBlockMetaHelper::getColorFromMeta($this->getVariant()) . " Shulker Box";
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$this->getLevel()->setBlock($blockReplace, $this, true, true);
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

			if(
				!($this->getSide(Vector3::SIDE_UP)->isTransparent()) or
				!$sb->canOpenWith($item->getCustomName())
			){
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
			$item = Item::get(Item::SHULKER_BOX, $this->meta, 1);
			$itemNBT = new CompoundTag();
			$t->writeSaveData($itemNBT);
			$item->setCustomBlockData($itemNBT);
			$t->getInventory()->clearAll(false);
			return [$item];
		}

		return [];
	}

	public function getVariantBitmask(): int{
		return 0x0f;
	}
}