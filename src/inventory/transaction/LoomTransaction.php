<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)__ _   ___      |  \/  |  _ \
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

use pocketmine\item\Banner as BannerItem;
use pocketmine\item\Dye;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function array_values;
use function count;

class LoomTransaction extends InventoryTransaction{

    public function __construct(
        Player $source,
        private readonly BannerItem $inputBanner,
        private readonly Dye $inputDye,
        private readonly BannerItem $expectedResult,
        private readonly int $repetitions
    ){
        parent::__construct($source);
    }

    public function validate() : void{
        if(count($this->actions) < 1){
            throw new TransactionValidationException("Transaction must have at least one action to be executable");
        }

        $inputs = [];
        $outputs = [];
        $this->matchItems($outputs, $inputs);

        if(($outputCount = count($outputs)) !== 1){
            throw new TransactionValidationException("Expected 1 output item, but received $outputCount");
        }
        $outputItem = $outputs[0];
        if(!$outputItem->equalsExact($this->expectedResult)){
            throw new TransactionValidationException("Output item does not match expected loom result");
        }

        $this->assertExpectedConsumption($inputs, $this->inputBanner, $this->repetitions, "banner");
        $this->assertExpectedConsumption($inputs, $this->inputDye, $this->repetitions, "dye");

        if(count($inputs) > 0){
            throw new TransactionValidationException("Unexpected items consumed by loom transaction");
        }
    }

    /**
     * @param Item[] $inputs
     */
    private function assertExpectedConsumption(array &$inputs, Item $template, int $count, string $label) : void{
        $expected = clone $template;
        $expected->setCount($count);

        foreach($inputs as $index => $item){
            if($item->equalsExact($expected)){
                unset($inputs[$index]);
                $inputs = array_values($inputs);
                return;
            }
        }

        throw new TransactionValidationException("Expected loom to consume $count $label item(s)");
    }
}
