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

use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;

/**
 * This action is used by creative players to balance transactions involving the creative inventory menu.
 * The source item is the item being created ("taken" from the creative menu).
 */
class CreateItemAction extends InventoryAction{

	public function __construct(Item $sourceItem){
		parent::__construct($sourceItem, ItemFactory::air());
	}

	public function validate(Player $source) : void{
		if($source->hasFiniteResources()){
			throw new TransactionValidationException("Player has finite resources, cannot create items");
		}
		if(!CreativeInventory::getInstance()->contains($this->sourceItem)){
			throw new TransactionValidationException("Creative inventory does not contain requested item");
		}
	}

	public function execute(Player $source) : void{
		//NOOP
	}
}
