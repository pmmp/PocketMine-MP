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

use pocketmine\item\TieredTool;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Hopper as TileHopper;
use pocketmine\tile\Tile;

class Hopper extends Transparent{

	protected $id = self::HOPPER_BLOCK;
	protected $itemId = Item::HOPPER;

	protected $enabled = false; // TODO: redstone
	protected $facing = Facing::DOWN;

	public function __construct(){

	}

	public function readStateFromMeta(int $meta) : void{
		$this->enabled = ($meta & 8) != 8;
		$this->facing = $meta & 7;
	}

	public function writeStateToMeta() : int{
		return $this->facing | ($this->enabled ? 0 : 8);
    }

	public function getStateBitmask() : int{
		return 14;
	}

	public function getHardness() : float{
		return 3;
	}

	public function getBlastResistance() : float{
		return 24;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getToolHarvestLevel() : int{
		return TieredTool::TIER_WOODEN;
	}

	public function getName() : string{
		return "Hopper";
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$hopper = $this->getLevel()->getTile($this);
			if($hopper instanceof TileHopper){

				if(!$hopper->canOpenWith($item->getCustomName())){
					return true;
				}

				$player->addWindow($hopper->getInventory());
			}
		}

		return true;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$faces = [
			0 => Facing::DOWN,
			1 => Facing::DOWN, // Not used
			2 => Facing::SOUTH,
			3 => Facing::NORTH,
			4 => Facing::EAST,
			5 => Facing::WEST
		];
		$this->facing = $faces[$face];

		if(parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			Tile::createTile(Tile::HOPPER, $this->getLevel(), TileHopper::createNBT($this, $face, $item, $player));

			return true;
		}
		return true;
	}

	/**
	 * @return int
	 */
	public function getFacing() : int{
		return $this->facing;
	}

	/**
	 * @return bool
	 */
	public function isEnabled() : bool{
		return $this->enabled;
	}
}