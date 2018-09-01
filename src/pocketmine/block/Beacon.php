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
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Beacon as TileBeacon;
use pocketmine\tile\Tile;

class Beacon extends Transparent{

	protected $id = self::BEACON;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Beacon";
	}

	public function getLightLevel() : int{
		return 15;
	}

	public function getHardness() : float{
		return 3;
	}

	public function getBreakTime(Item $item): float{
		return 4.5;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$placed = $this->getLevel()->setBlock($this, $this, true, true);
		if($placed){
			Tile::createTile(Tile::BEACON, $this->getLevel(), TileBeacon::createNBT($this, $face, $item, $player));
		}

		return $placed;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$top = $this->getSide(Vector3::SIDE_UP);
			if($top->isTransparent() !== true){
				return true;
			}

			$player->addWindow($this->getTile()->getInventory());
		}

		return true;
	}

	public function buildPyramidLevels(int $levels, Block $block) : void{
		for($i = 1; $i < $levels + 1; $i++){
			for($x = -$i; $x < $i + 1; $x++){
				for($z = -$i; $z < $i + 1; $z++){
					$this->level->setBlock($this->add($x, -$i, $z), $block);
				}
			}
		}
	}

	public function getTile() : TileBeacon{
		$t = $this->getLevel()->getTileAt($this->x, $this->y, $this->z);
		return $t instanceof TileBeacon ? $t : new TileBeacon($this->getLevel(), TileBeacon::createNBT($this));
	}
}