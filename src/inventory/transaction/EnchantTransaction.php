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
use pocketmine\item\enchantment\EnchantmentHelper;
use pocketmine\item\enchantment\EnchantOption;
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
		private readonly EnchantOption $option,
		private readonly int $cost
	){
		parent::__construct($source);
	}

	private function validateOutput() : void{
		if($this->inputItem === null || $this->outputItem === null){
			throw new AssumptionFailedError("Expected that inputItem and outputItem are not null before validating output");
		}

		$enchantedInput = EnchantmentHelper::enchantItem($this->inputItem, $this->option->getEnchantments());
		if(!$this->outputItem->equalsExact($enchantedInput)){
			throw new TransactionValidationException("Invalid output item");
		}
	}

	/**
	 * The selected option might be available to a player who has enough XP levels to meet the option's minimum level,
	 * but not enough to pay the full cost (e.g. option costs 3 levels but requires only 1 to use). As much XP as
	 * possible is spent in these cases.
	 */
	private function getAdjustedXpCost() : int{
		return min($this->cost, $this->source->getXpManager()->getXpLevel());
	}

	private function validateFiniteResources(int $lapisSpent) : void{
		if($lapisSpent !== $this->cost){
			throw new TransactionValidationException("Expected the amount of lapis lazuli spent to be $this->cost, but received $lapisSpent");
		}

		$xpLevel = $this->source->getXpManager()->getXpLevel();
		$requiredXpLevel = $this->option->getRequiredXpLevel();
		$actualCost = $this->getAdjustedXpCost();

		if($xpLevel < $requiredXpLevel){
			throw new TransactionValidationException("Player's XP level $xpLevel is less than the required XP level $requiredXpLevel");
		}
		if($xpLevel < $actualCost){
			throw new TransactionValidationException("Player's XP level $xpLevel is less than the XP level cost $actualCost");
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
			$this->source->getXpManager()->subtractXpLevels($this->getAdjustedXpCost());
		}
		$this->source->setEnchantmentSeed($this->source->generateEnchantmentSeed());
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
