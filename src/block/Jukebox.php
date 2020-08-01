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

use pocketmine\block\tile\Jukebox as JukeboxTile;
use pocketmine\item\Item;
use pocketmine\item\Record;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Jukebox extends Opaque{

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(2.0, BlockToolType::AXE, 0, 15.0));
	}

	public function getFuelTime() : int{
		return 300;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player instanceof Player){
			$jukebox = $this->pos->getWorld()->getTile($this->pos);
			if($jukebox instanceof JukeboxTile){
				if(!$jukebox->ejectRecord() and $item instanceof Record and $jukebox->insertRecord($item)){
					$player->getInventory()->remove($item);
				}
			}
		}
		return true;
	}

	//Note Jukebox has redstone effects, they are not implemented.
}
