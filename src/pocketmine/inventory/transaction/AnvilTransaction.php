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
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\item\Item;
use pocketmine\Player;

class AnvilTransaction extends InventoryTransaction{

	/** @var Item */
	protected $material; // useless
	/** @var Item */
	protected $input; // useless

	public function __construct(Player $source, $actions = []){
		$this->source = $source;
		$this->fixWrongActions($actions);
	}

	/**
	 * Because of very shitty anvil transaction we need to fix these
	 *
	 * @param InventoryAction[] $actions
	 */
	public function fixWrongActions(array $actions) : void{
		foreach($actions as $action){
			if($action instanceof SlotChangeAction){
				if($action->getInventory() instanceof AnvilInventory){ // anvil action
					switch($action->getSlot()){
						default:
							$this->addAction($action);
							break;
						case 1:
							if($this->material === null){
								$this->material = $action->getTargetItem();

								$originItem = $action->getInventory()->getItem(1);
								$o2 = clone $originItem;
								$o2->pop($this->material->getCount());
								$this->addAction(new SlotChangeAction($action->getInventory(), 1, $originItem, $o2));
							}
							break;
						case 0:
							if($this->input === null){
								$this->input = $action->getSourceItem();

								$this->addAction($action);
							}
							break;
					}
				}else{
					$this->addAction($action);
				}
			}else{
				$this->addAction($action);
			}
		}
	}

	public function validate() : void{
		$this->squashDuplicateSlotChanges();

		// Anvil transaction may change or delete some items from inventory so we don't check items
	}
}