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

use pocketmine\block\utils\AnvilHelper;
use pocketmine\block\utils\AnvilResult;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use function count;

class AnvilTransaction extends InventoryTransaction{
	public function __construct(
		Player $source,
		private readonly AnvilResult $expectedResult,
		private readonly ?string $customName
	) {
		parent::__construct($source);
	}

	private function validateFiniteResources(int $xpSpent) : void{
		$expectedXpCost = $this->expectedResult->getRepairCost();
		if($xpSpent !== $expectedXpCost){
			throw new TransactionValidationException("Expected the amount of xp spent to be $expectedXpCost, but received $xpSpent");
		}

		$xpLevel = $this->source->getXpManager()->getXpLevel();
		if($xpLevel < $expectedXpCost){
			throw new TransactionValidationException("Player's XP level $xpLevel is less than the required XP level $expectedXpCost");
		}
	}

	private function validateInputs(Item $base, Item $material, Item $expectedOutput) : ?AnvilResult {
		$calculAttempt = AnvilHelper::calculateResult($this->source, $base, $material, $this->customName);
		if($calculAttempt->getResult() === null || !$calculAttempt->getResult()->equalsExact($expectedOutput)){
			return null;
		}

		return $calculAttempt;
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

		if(($outputCount = count($outputs)) !== 1){
			throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
		}
		$outputItem = $outputs[0];

		if(($inputCount = count($inputs)) < 1){
			throw new TransactionValidationException("Expected at least 1 input item, but received $inputCount");
		}
		if($inputCount > 2){
			throw new TransactionValidationException("Expected at most 2 input items, but received $inputCount");
		}

		if(count($inputs) < 2){
			$attempt = $this->validateInputs($inputs[0], VanillaItems::AIR(), $outputItem) ??
				throw new TransactionValidationException("Inputs do not match expected result");
		}else{
			$attempt = $this->validateInputs($inputs[0], $inputs[1], $outputItem) ??
				$this->validateInputs($inputs[1], $inputs[0], $outputItem) ??
				throw new TransactionValidationException("Inputs do not match expected result");
		}

		if($this->source->hasFiniteResources()){
			$this->validateFiniteResources($attempt->getRepairCost());
		}
	}

	public function execute() : void{
		parent::execute();

		if($this->source->hasFiniteResources()){
			$this->source->getXpManager()->subtractXpLevels($this->expectedResult->getRepairCost());
		}
	}
}
