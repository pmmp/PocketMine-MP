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

namespace pocketmine\inventory\transaction;

use pocketmine\block\inventory\EnchantInventory;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use function count;

class EnchantTransaction extends InventoryTransaction{

	private int $optionId;
	private ?Item $inputItem = null;
	private int $lapisCost = 0;

	public function __construct(Player $source, int $optionId){
		$this->optionId = $optionId;
		parent::__construct($source);
	}

	public function validate() : void{
		if(count($this->actions) < 1){
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}

		$enchantWindow = $this->source->getCurrentWindow();
		if (!$enchantWindow instanceof EnchantInventory) {
			throw new TransactionValidationException("Current window was expected to be of type EnchantInventory");
		}

		if($this->inputItem === null || !$this->inputItem->equalsExact($enchantWindow->getInput())){
			throw new TransactionValidationException("Incorrect input item");
		}

		if($this->source->hasFiniteResources()){
			$enchantLevel = $this->getEnchantLevel();
			if($this->lapisCost !== $enchantLevel){
				throw new TransactionValidationException("Expected the amount of lapis lazuli spent to be {$this->getEnchantLevel()}, but received $this->lapisCost");
			}

			$xpLevel = $this->source->getXpManager()->getXpLevel();
			if($xpLevel < $enchantLevel){
				throw new TransactionValidationException("Expected player to have xp level at least of $enchantLevel, but received $xpLevel");
			}
		}
	}

	public function addAction(InventoryAction $action) : void{
		parent::addAction($action);

		$sourceItem = $action->getSourceItem();
		$targetItem = $action->getTargetItem();

		if($sourceItem->getTypeId() === ItemTypeIds::LAPIS_LAZULI){
			$this->lapisCost = $sourceItem->getCount() - $targetItem->getCount();
		}else{
			$this->inputItem = $sourceItem;
		}
	}

	public function execute() : void{
		parent::execute();
		$this->source->onEnchant($this->getEnchantLevel());
	}

	public function getEnchantLevel() : int{
		return $this->optionId + 1;
	}
}
