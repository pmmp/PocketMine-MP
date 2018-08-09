<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\inventory\transaction\action;

use pocketmine\inventory\TradeInventory;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class TradeAction extends InventoryAction{

	public const OUTPUT_SLOT = 2;

	/** @var TradeInventory */
	protected $inventory;
	/** @var bool */
	protected $output;

	public function __construct(Item $sourceItem, Item $targetItem, ?TradeInventory $inventory, bool $output = false){
		parent::__construct($sourceItem, $targetItem);
		$this->inventory = $inventory;
		$this->output = $output;
	}

	public function getInventory() : TradeInventory{
		return $this->inventory;
	}

	public function isValid(Player $source) : bool{
		return true;
	}

	public function execute(Player $source) : bool{
		return true;
	}

	public function onExecuteSuccess(Player $source) : void{
		if($this->output){
			$holder = $this->inventory->getHolder();
			$recipes = $holder->getOffers()->getListTag("Recipes");
			/** @var CompoundTag $tag */
			foreach($recipes->getAllValues() as $index => $tag){
				/** @var CompoundTag $sell */
				$sell = $tag->getTag("sell");
				$sellId = $sell->getShort("id");
				$sellCount = $sell->getByte("Count");
				if($sellId == $this->sourceItem->getId() and $sellCount == $this->sourceItem->getCount()){
					$tag->setInt("uses", $tag->getInt("uses") + 1);
					$recipes->set($index, $tag);
					break;
				}
			}

			$this->inventory->getHolder()->setWilling(mt_rand(1, 3) <= 2);
			$this->inventory->setTraded(true);
		}
	}

	public function onExecuteFail(Player $source) : void{

	}
}