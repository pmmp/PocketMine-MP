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

namespace pocketmine\inventory\transaction\action;

use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\Player;

/**
 * Represents an action involving dropping an item into the world.
 */
class DropItemAction extends InventoryAction{

	/**
	 * Verifies that the source item of a drop-item action must be air. This is not strictly necessary, just a sanity
	 * check.
	 *
	 * @param Player $source
	 * @return bool
	 */
	public function isValid(Player $source) : bool{
		return $this->sourceItem->isNull();
	}

	public function onPreExecute(Player $source) : bool{
		$source->getServer()->getPluginManager()->callEvent($ev = new PlayerDropItemEvent($source, $this->targetItem));
		if($ev->isCancelled()){
			return false;
		}

		return true;
	}

	/**
	 * Drops the target item in front of the player.
	 *
	 * @param Player $source
	 * @return bool
	 */
	public function execute(Player $source) : bool{
		return $source->dropItem($this->targetItem);
	}

	public function onExecuteSuccess(Player $source) : void{

	}

	public function onExecuteFail(Player $source) : void{

	}
}
