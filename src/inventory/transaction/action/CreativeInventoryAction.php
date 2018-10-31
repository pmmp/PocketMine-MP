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

class CreativeInventoryAction extends InventoryAction{

	/**
	 * Player put an item into the creative window to destroy it.
	 */
	public const TYPE_DELETE_ITEM = 0;
	/**
	 * Player took an item from the creative window.
	 */
	public const TYPE_CREATE_ITEM = 1;

	protected $actionType;

	public function __construct(Item $sourceItem, Item $targetItem, int $actionType){
		parent::__construct($sourceItem, $targetItem);
		$this->actionType = $actionType;
	}

	/**
	 * Checks that the player is in creative, and (if creating an item) that the item exists in the creative inventory.
	 *
	 * @param Player $source
	 *
	 * @return bool
	 */
	public function isValid(Player $source) : bool{
		return $source->isCreative(true) and
			($this->actionType === self::TYPE_DELETE_ITEM or Item::getCreativeItemIndex($this->sourceItem) !== -1);
	}

	/**
	 * Returns the type of the action.
	 */
	public function getActionType() : int{
		return $this->actionType;
	}

	/**
	 * No need to do anything extra here: this type just provides a place for items to disappear or appear from.
	 *
	 * @param Player $source
	 *
	 * @return bool
	 */
	public function execute(Player $source) : bool{
		return true;
	}

	public function onExecuteSuccess(Player $source) : void{

	}

	public function onExecuteFail(Player $source) : void{

	}
}
