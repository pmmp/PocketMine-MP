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

namespace pocketmine\inventory\transaction;

use pocketmine\inventory\AnvilInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\Inventory;

class AnvilTransaction extends InventoryTransaction{

	public function validate() : void{
		$this->squashSlot(0, true);
		$this->squashSlot(1, false);
		$this->squashDuplicateSlotChanges();

		// Anvil transaction may change or delete some items from inventory so we don't check items
	}

	public function squashSlot(int $targetSlot, bool $turnDown) : void{
		/** @var SlotChangeAction[][] $slotChanges */
		$slotChanges = [];
		/** @var Inventory[] $inventories */
		$inventories = [];
		/** @var int[] $slots */
		$slots = [];

		foreach($this->actions as $key => $action){
			if($action instanceof SlotChangeAction){
				$slotChanges[$h = (spl_object_hash($action->getInventory()) . "@" . $action->getSlot())][$key] = $action;
				$inventories[$h] = $action->getInventory();
				$slots[$h] = $action->getSlot();
			}
		}

		foreach($slotChanges as $h => $list){
			if(count($list) === 1){
				continue;
			}

			$inventory = $inventories[$h];

			if($inventory instanceof AnvilInventory){
				if($turnDown){
					$list = array_reverse($list);
				}

				foreach($list as $k => $action){
					if($action->getSlot() === $targetSlot){
						unset($this->actions[$k]);
						break;
					}
				}
			}
		}
	}
}