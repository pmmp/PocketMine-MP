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

use pocketmine\crafting\SmithingRecipe;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function count;

class SmithingTransaction extends InventoryTransaction{

	public function __construct(
		Player $source,
		private readonly SmithingRecipe $recipe,
		array $actions = []
	){
		parent::__construct($source, $actions);
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

		if(($inputCount = count($inputs)) !== 3){
			throw new TransactionValidationException("Expected 3 input items, got $inputCount");
		}
		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}
		if(($output = $this->recipe->getResultFor($inputs)) === null){
			throw new TransactionValidationException("Could find a matching output item for the given inputs");
		}
		if(!$output->equalsExact($outputs[0])){
			throw new TransactionValidationException("Invalid output item");
		}
	}
}
