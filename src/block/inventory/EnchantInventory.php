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

namespace pocketmine\block\inventory;

use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\enchantment\EnchantmentHelper as Helper;
use pocketmine\item\enchantment\EnchantmentOption;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\world\Position;
use function count;

class EnchantInventory extends SimpleInventory implements BlockInventory, TemporaryInventory{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;
	public const SLOT_LAPIS = 1;

	/** @var EnchantmentOption[] $options */
	private array $options = [];

	/** @var Item[] $outputs */
	private array $outputs = [];

	public function __construct(Position $holder){
		$this->holder = $holder;
		parent::__construct(2);
	}

	protected function onSlotChange(int $index, Item $before) : void{
		if($index == self::SLOT_INPUT){
			foreach($this->viewers as $viewer){
				$this->options = Helper::getEnchantOptions($this->holder, $this->getItem(self::SLOT_INPUT), $viewer->getXpSeed());
				$this->outputs = [];
				if (count($this->options) !== 0) {
					$viewer->getNetworkSession()->sendEnchantOptions($this->options);
				}
			}
		}

		parent::onSlotChange($index, $before);
	}

	public function getInput() : Item{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getLapis() : Item{
		return $this->getItem(self::SLOT_LAPIS);
	}

	public function getOutput(int $optionId) : Item{
		$this->prepareOutput($optionId);
		return $this->outputs[$optionId];
	}

	private function prepareOutput(int $optionId) : void{
		if(isset($this->outputs[$optionId])){
			return;
		}

		$option = $this->options[$optionId] ?? null;
		if($option === null){
			throw new \RuntimeException("Failed to find enchantment option with network id $optionId");
		}

		$outputItem = $this->getItem(self::SLOT_INPUT);
		if($outputItem->getTypeId() === ItemTypeIds::BOOK){
			$outputItem = VanillaItems::ENCHANTED_BOOK();
		}

		foreach($option->getEnchantments() as $enchantment){
			$outputItem->addEnchantment($enchantment);
		}

		$this->outputs[$optionId] = $outputItem;
	}
}
