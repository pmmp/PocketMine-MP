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

use pocketmine\item\Item;
use pocketmine\Player;

class TradeAction extends InventoryAction{

	public function __construct(Item $sourceItem, Item $targetItem){
		parent::__construct($sourceItem, $targetItem);
	}

	public function isValid(Player $source) : bool{
		return true;
	}

	public function execute(Player $source) : bool{
		return $source->getInventory()->contains($this->sourceItem);
	}

	public function onExecuteSuccess(Player $source) : void{
	}

	public function onExecuteFail(Player $source) : void{
	}
}
