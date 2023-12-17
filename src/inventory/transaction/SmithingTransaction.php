<?php

declare(strict_types=1);

namespace pocketmine\inventory\transaction;

use pocketmine\item\Item;
use pocketmine\Server;
use function array_map;
use function count;
use function var_dump;

class SmithingTransaction extends InventoryTransaction{

	public function validate() : void{
		if(count($this->actions) < 1){
			throw new TransactionValidationException("Transaction must have at least one action to be executable");
		}

		/** @var Item[] $inputs */
		$inputs = [];
		/** @var Item[] $outputs */
		$outputs = [];
		$this->matchItems($outputs, $inputs);

		var_dump(array_map("strval", $inputs));

		if(($inputCount = count($inputs)) !== 3){
			throw new TransactionValidationException("Expected 3 input items, got $inputCount");
		}
		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}

		[$input, $template, $addition] = $inputs;

		$craftingManager = Server::getInstance()->getCraftingManager();
		$recipe = $craftingManager->matchSmithingRecipe($input, $addition, $template);
		var_dump($recipe);

		if(($output = $recipe?->constructOutput($input, $addition, $template)) === null){
			throw new TransactionValidationException("Could find a matching output item for the given inputs");
		}
		if(!$output->equalsExact($outputs[0])){
			throw new TransactionValidationException("Invalid output item");
		}
	}
}