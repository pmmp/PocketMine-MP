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

use pocketmine\event\inventory\EnchantItemEvent;
use pocketmine\item\enchantment\EnchantmentHelper;
use pocketmine\item\enchantment\EnchantmentOption;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use function count;

class EnchantTransaction extends InventoryTransaction{

	private ?Item $inputItem = null;
	private ?Item $outputItem = null;

	public function __construct(
		Player $source,
		private readonly EnchantmentOption $option,
		private readonly int $cost
	){
		parent::__construct($source);
	}

	private function validateOutput() : void{
		$enchantedInput = EnchantmentHelper::enchantItem($this->inputItem, $this->option->getEnchantments());

		if($this->outputItem === null || !$this->outputItem->equalsExact($enchantedInput)){
			throw new TransactionValidationException("Invalid output item");
		}
	}

	private function validateFiniteResources(int $lapisSpent) : void{
		if($lapisSpent !== $this->cost){
			throw new TransactionValidationException("Expected the amount of lapis lazuli spent to be $this->cost, but received $lapisSpent");
		}

		$xpLevel = $this->source->getXpManager()->getXpLevel();
		$requiredXpLevel = $this->option->getRequiredXpLevel();

		if($xpLevel < $requiredXpLevel){
			throw new TransactionValidationException("Player's XP level $xpLevel is less than the required XP level $requiredXpLevel");
		}
		if($xpLevel < $this->cost){
			throw new TransactionValidationException("Player's XP level $xpLevel is less than the XP level cost $this->cost");
		}
	}

	public function validate() : void{
		if(count($this->actions) < 1){
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}

		/** @var Item[] $inputs */
		$inputs = [];
		/** @var Item[] $outputs */
		$outputs = [];
		$this->matchItems($outputs, $inputs);

		$lapisSpent = 0;
		foreach($inputs as $input){
			if($input->getTypeId() === ItemTypeIds::LAPIS_LAZULI){
				$lapisSpent = $input->getCount();
			}else{
				if(isset($this->inputItem)){
					throw new TransactionValidationException("Received more than 1 items to enchant");
				}
				$this->inputItem = $input;
			}
		}

		if($this->inputItem === null){
			throw new TransactionValidationException("No item to enchant received");
		}

		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}
		$this->outputItem = $outputs[0];

		$this->validateOutput();

		if($this->source->hasFiniteResources()){
			$this->validateFiniteResources($lapisSpent);
		}
	}

	public function execute() : void{
		parent::execute();

		if($this->source->hasFiniteResources()){
			$this->source->getXpManager()->subtractXpLevels($this->cost);
		}
		$this->source->setXpSeed($this->source->generateXpSeed());
	}

	protected function callExecuteEvent() : bool{
		if ($this->inputItem === null || $this->outputItem === null) {
			throw new AssumptionFailedError("Expected inputItem and outputItem to be set before the event executing");
		}
		
		$event = new EnchantItemEvent($this, $this->option, $this->inputItem, $this->outputItem, $this->cost);
		$event->call();
		return !$event->isCancelled();
	}
}
