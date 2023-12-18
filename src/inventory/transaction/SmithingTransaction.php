<?php

declare(strict_types=1);

namespace pocketmine\inventory\transaction;

use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\TieredTool;
use pocketmine\Server;
use function count;

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

		if(($inputCount = count($inputs)) !== 3){
			throw new TransactionValidationException("Expected 3 input items, got $inputCount");
		}
		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}

		$input = $addition = $template = null;
		foreach($inputs as $item){
			switch(true){
				case $item instanceof Armor || $item instanceof TieredTool:
					$input = $item;
					break;
				case $item->getTypeId() >= ItemTypeIds::NETHERITE_UPGRADE_SMITHING_TEMPLATE && $item->getTypeId() <= ItemTypeIds::SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE:
					$template = $item;
					break;
				default:
					$addition = $item;
					break;
			}
		}

		if($input === null || $addition === null || $template === null){
			throw new TransactionValidationException("The given inputs are no valid smithing ingredients");
		}

		$craftingManager = Server::getInstance()->getCraftingManager();
		$recipe = $craftingManager->matchSmithingRecipe($input, $addition, $template);

		if(($output = $recipe?->constructOutput($input, $addition, $template)) === null){
			throw new TransactionValidationException("Could find a matching output item for the given inputs");
		}

		if(!$output->equalsExact($outputs[0])){
			throw new TransactionValidationException("Invalid output item");
		}
	}
}
