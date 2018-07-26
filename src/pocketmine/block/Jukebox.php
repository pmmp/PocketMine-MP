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

use pocketmine\item\Record;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Jukebox as TileJukebox;
use pocketmine\tile\Tile;

class Jukebox extends Solid{

	protected $id = self::JUKEBOX;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
	}

	public function getName() : string{
		return "Jukebox";
	}

	public function getHardness() : float{
		return 2.0;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		$place = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		if($place){
			Tile::createTile(Tile::JUKEBOX, $this->getLevel(), TileJukebox::createNBT($this, $face, $item, $player));
		}

		return $place;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		if($player instanceof Player){
			$jb = $this->getTile();

			if($jb->getRecordItem() == null){
				if($item instanceof Record){
					$jb->setRecordItem($item);
					$jb->playDisc($player);
					$player->getInventory()->removeItem($item);
				}
			}else{
				$jb->dropDisc();
			}
		}

		return true;
	}

	public function onBreak(Item $item, Player $player = null) : bool{
		$this->getTile()->dropDisc();

		return parent::onBreak($item, $player);
	}

	public function getTile() : TileJukebox{
		$t = $this->getLevel()->getTileAt($this->x, $this->y, $this->z);
		if(!($t instanceof TileJukebox)){
			$t = Tile::createTile(Tile::JUKEBOX, $this->getLevel(), TileJukebox::createNBT($this));
		}

		return $t;
	}
}