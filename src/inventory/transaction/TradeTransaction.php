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

use pocketmine\entity\trade\TradeRecipe;
use pocketmine\event\player\PlayerItemTradeEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use function count;

final class TradeTransaction extends InventoryTransaction{

	private ?Item $buyA = null;
	private ?Item $buyB = null;

	private ?Item $outputItem = null;

	public function __construct(Player $source, private readonly TradeRecipe $recipe){
		parent::__construct($source);
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

		$buyA = $this->recipe->getBuyA();
		$buyB = $this->recipe->getBuyB();

		foreach($inputs as $input){
			if($input->getTypeId() === $buyA->getTypeId()){
				$this->buyA = $input;
			}elseif($buyB !== null && $input->getTypeId() === $buyB->getTypeId()){
				$this->buyB = $input;
			}
			if($this->buyA !== null && ($buyB !== null && $this->buyB !== null)){
				throw new TransactionValidationException("Transaction has too many input items");
			}
		}
		if($this->buyA === null || ($buyB !== null && $this->buyB === null)){
			throw new TransactionValidationException("No items received");
		}
		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}
		$this->outputItem = $outputs[0];

		$this->validateInputItems();
		$this->validateOutputItems();
	}

	private function validateInputItems() : void{
		if($this->buyA === null || $this->outputItem === null){
			throw new AssumptionFailedError("Expected that buyA and outputItem are not null before validating output");
		}
		$expectedBuyA = $this->recipe->getBuyA();
		$expectedBuyB = $this->recipe->getBuyB();
		if(!$expectedBuyA->equalsExact($this->buyA)){
			throw new TransactionValidationException("Invalid buyA item");
		}
		if($expectedBuyB !== null){
			if($this->buyB === null){
				throw new TransactionValidationException("Expected buyB item, but received nothing");
			}
			if(!$expectedBuyB->equalsExact($this->buyB)){
				throw new TransactionValidationException("Invalid buyB item");
			}
		}
	}

	private function validateOutputItems() : void{
		if($this->outputItem === null){
			throw new AssumptionFailedError("Expected that outputItem is not null before validating output");
		}
		$expectedOutput = $this->recipe->getSell();
		if(!$expectedOutput->equalsExact($this->outputItem)){
			throw new TransactionValidationException("Invalid output item");
		}
	}

	public function execute() : void{
		parent::execute();

		$this->recipe->setTier($this->recipe->getUses() + 1);
	}

	protected function callExecuteEvent() : bool{
		$ev = new PlayerItemTradeEvent($this->source, $this->buyA, $this->outputItem, $this->buyB);
		$ev->call();
		return !$ev->isCancelled();
	}
}
