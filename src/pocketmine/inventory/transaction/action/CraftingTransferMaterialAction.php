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

use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\Player;

/**
 * Action used to take ingredients out of the crafting grid, or put secondary results into the crafting grid, when
 * crafting.
 */
class CraftingTransferMaterialAction extends InventoryAction{
	/** @var int */
	private $slot;

	public function __construct(Item $sourceItem, Item $targetItem, int $slot){
		parent::__construct($sourceItem, $targetItem);
		$this->slot = $slot;
	}

	public function onAddToTransaction(InventoryTransaction $transaction) : void{
		if($transaction instanceof CraftingTransaction){
			if($this->sourceItem->isNull()){
				$transaction->setInput($this->slot, $this->targetItem);
			}elseif($this->targetItem->isNull()){
				$transaction->setExtraOutput($this->slot, $this->sourceItem);
			}else{
				throw new \InvalidStateException("Invalid " . get_class($this) . ", either source or target item must be air, got source: " . $this->sourceItem . ", target: " . $this->targetItem);
			}
		}else{
			throw new \InvalidStateException(get_class($this) . " can only be added to CraftingTransactions");
		}
	}

	public function isValid(Player $source) : bool{
		return true;
	}

	public function execute(Player $source) : bool{
		return true;
	}

	public function onExecuteSuccess(Player $source) : void{

	}

	public function onExecuteFail(Player $source) : void{

	}
}
