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

use pocketmine\event\player\PlayerItemEnchantEvent;
use pocketmine\item\enchantment\EnchantingHelper;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use function count;
use function min;

class EnchantingTransaction extends InventoryTransaction{

	private ?Item $inputItem = null;
	private ?Item $outputItem = null;

	public function __construct(
		Player $source,
		private readonly EnchantingOption $option,
		private readonly int $cost
	){
		parent::__construct($source);
	}

	private function validateOutput() : void{
		if($this->inputItem === null || $this->outputItem === null){
			throw new AssumptionFailedError("Expected that inputItem and outputItem are not null before validating output");
		}

		$enchantedInput = EnchantingHelper::enchantItem($this->inputItem, $this->option->getEnchantments());
		if(!$this->outputItem->equalsExact($enchantedInput)){
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
		//XP level cost is intentionally not checked here, as the required level may be lower than the cost, allowing
		//the option to be used with less XP than the cost - in this case, as much XP as possible will be deducted.
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
				if($this->inputItem !== null){
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
			//If the required XP level is less than the XP cost, the option can be selected with less XP than the cost.
			//In this case, as much XP as possible will be taken.
			$this->source->getXpManager()->subtractXpLevels(min($this->cost, $this->source->getXpManager()->getXpLevel()));
		}
		$this->source->regenerateEnchantmentSeed();
	}

	protected function callExecuteEvent() : bool{
		if($this->inputItem === null || $this->outputItem === null){
			throw new AssumptionFailedError("Expected that inputItem and outputItem are not null before executing the event");
		}

		$event = new PlayerItemEnchantEvent($this->source, $this, $this->option, $this->inputItem, $this->outputItem, $this->cost);
		$event->call();
		return !$event->isCancelled();
	}
}
