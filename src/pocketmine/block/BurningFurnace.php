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
use pocketmine\item\TieredTool;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Furnace as TileFurnace;
use pocketmine\tile\Tile;

class BurningFurnace extends Solid{

	protected $id = self::BURNING_FURNACE;

	protected $itemId = self::FURNACE;

	/** @var int */
	protected $facing = Facing::NORTH;

	public function __construct(){

	}

	public function getDamage() : int{
		return $this->facing;
	}

	public function setDamage(int $meta) : void{
		$this->facing = $meta;
	}

	public function getName() : string{
		return "Burning Furnace";
	}

	public function getHardness() : float{
		return 3.5;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function getLightLevel() : int{
		return 13;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($player !== null){
			$this->facing = Bearing::toFacing(Bearing::opposite($player->getDirection()));
		}
		if(parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			Tile::createTile(Tile::FURNACE, $this->getLevel(), TileFurnace::createNBT($this, $face, $item, $player));
			return true;
		}

		return false;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$furnace = $this->getLevel()->getTile($this);
			if(!($furnace instanceof TileFurnace)){
				$furnace = Tile::createTile(Tile::FURNACE, $this->getLevel(), TileFurnace::createNBT($this));
			}

			if(!$furnace->canOpenWith($item->getCustomName())){
				return true;
			}

			$player->addWindow($furnace->getInventory());
		}

		return true;
	}
}
