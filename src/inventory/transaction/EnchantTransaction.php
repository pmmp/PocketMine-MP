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
use pocketmine\event\inventory\EnchantItemEvent;
use pocketmine\item\enchantment\EnchantmentOption;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use function count;

class EnchantTransaction extends InventoryTransaction{

	private int $optionId;
	private Item $inputItem;
	private Item $outputItem;
	private int $lapisCost = 0;
	private EnchantmentOption $option;

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

		$option = $enchantWindow->getOption($this->optionId);
		if($option === null){
			throw new TransactionValidationException("Incorrect option id $this->optionId");
		}
		$this->option = $option;

		/** @var Item[] $inputs */
		$inputs = [];
		/** @var Item[] $outputs */
		$outputs = [];

		$this->matchItems($outputs, $inputs);

		$lapisCost = 0;

		foreach($inputs as $input){
			if($input->getTypeId() === ItemTypeIds::LAPIS_LAZULI){
				$lapisCost = $input->getCount();
			}else{
				if(isset($this->inputItem)){
					throw new TransactionValidationException("Received more than 1 items to enchant");
				}
				$this->inputItem = $input;
			}
		}

		if(!isset($this->inputItem)){
			throw new TransactionValidationException("No item to enchant received");
		}

		if(!$this->inputItem->equalsExact($enchantWindow->getInput())){
			throw new TransactionValidationException("Incorrect item to enchant");
		}

		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}
		$this->outputItem = $outputs[0];

		if($this->source->hasFiniteResources()){
			$xpLevelCost = $this->getXpLevelCost();

			if($lapisCost !== $xpLevelCost){
				throw new TransactionValidationException("Expected the amount of lapis lazuli spent to be $xpLevelCost, but received $this->lapisCost");
			}

			$xpLevel = $this->source->getXpManager()->getXpLevel();
			if($xpLevel < $xpLevelCost){
				throw new TransactionValidationException("Expected player to have xp level at least of $xpLevelCost, but received $xpLevel");
			}
		}
	}

	public function execute() : void{
		parent::execute();

		if($this->source->hasFiniteResources()){
			$this->source->getXpManager()->subtractXpLevels($this->getXpLevelCost());
		}
		$this->source->setXpSeed($this->source->generateXpSeed());
	}

	protected function callExecuteEvent() : bool{
		$event = new EnchantItemEvent($this, $this->option, $this->inputItem, $this->outputItem, $this->getXpLevelCost());
		$event->call();
		return !$event->isCancelled();
	}

	private function getXpLevelCost() : int{
		return $this->optionId + 1;
	}
}
