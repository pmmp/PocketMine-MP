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
use pocketmine\item\ItemFactory;
use pocketmine\item\TieredTool;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\EnderChest as TileEnderChest;
use pocketmine\tile\Tile;

class EnderChest extends Chest{

	protected $id = self::ENDER_CHEST;

	public function getHardness() : float{
		return 22.5;
	}

	public function getBlastResistance() : float{
		return 3000;
	}

	public function getLightLevel() : int{
		return 7;
	}

	public function getName() : string{
		return "Ender Chest";
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($player !== null){ //same as normal chest - TODO: clean up inheritance here
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}

		if(Block::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			Tile::createTile(Tile::ENDER_CHEST, $this->getLevel(), TileEnderChest::createNBT($this, $item));
			return true;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$enderChest = $this->getLevel()->getTile($this);
			if($enderChest instanceof TileEnderChest and $this->getSide(Facing::UP)->isTransparent()){
				$player->getEnderChestInventory()->setHolderPosition($enderChest);
				$player->addWindow($player->getEnderChestInventory());
			}
		}

		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			ItemFactory::get(Item::OBSIDIAN, 0, 8)
		];
	}

	public function getFuelTime() : int{
		return 0;
	}
}
