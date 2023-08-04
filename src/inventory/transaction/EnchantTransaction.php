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
	private int $enchantmentLevel = 0;

	public function __construct(Player $source, int $optionId){
		$this->optionId = $optionId;
		parent::__construct($source);
	}

	public function validate() : void{
		if(count($this->actions) < 1){
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}

		$enchantWindow = $this->source->getCurrentWindow();
		if(!$enchantWindow instanceof EnchantInventory){
			throw new TransactionValidationException("Current window was expected to be of type EnchantInventory");
		}

		$enchantmentLevel = $enchantWindow->getOptionEnchantmentLevel($this->optionId);
		if($enchantmentLevel === null){
			throw new TransactionValidationException("Incorrect option id $this->optionId");
		}
		$this->enchantmentLevel = $enchantmentLevel;

		if($this->inputItem === null || !$this->inputItem->equalsExact($enchantWindow->getInput())){
			throw new TransactionValidationException("Incorrect input item");
		}

		if($this->source->hasFiniteResources()){
			if($this->lapisCost !== $enchantmentLevel){
				throw new TransactionValidationException("Expected the amount of lapis lazuli spent to be $enchantmentLevel, but received $this->lapisCost");
			}

			$xpLevel = $this->source->getXpManager()->getXpLevel();
			if($xpLevel < $enchantmentLevel){
				throw new TransactionValidationException("Expected player to have xp level at least of $enchantmentLevel, but received $xpLevel");
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

		if($this->source->hasFiniteResources()){
			$this->source->getXpManager()->subtractXpLevels($this->enchantmentLevel);
		}
		$this->source->setXpSeed($this->source->generateXpSeed());
	}
}