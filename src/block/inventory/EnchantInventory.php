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

use pocketmine\event\inventory\PlayerEnchantmentOptionsRequestEvent;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\TemporaryInventory;
use pocketmine\item\enchantment\EnchantmentHelper as Helper;
use pocketmine\item\enchantment\EnchantmentOption;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\world\Position;
use function array_keys;
use function array_search;
use function count;

class EnchantInventory extends SimpleInventory implements BlockInventory, TemporaryInventory{
	use BlockInventoryTrait;

	public const SLOT_INPUT = 0;
	public const SLOT_LAPIS = 1;

	/** @var EnchantmentOption[] $options */
	private array $options = [];

	public function __construct(Position $holder){
		$this->holder = $holder;
		parent::__construct(2);
	}

	protected function onSlotChange(int $index, Item $before) : void{
		if($index === self::SLOT_INPUT){
			foreach($this->viewers as $viewer){
				$this->options = [];
				$item = $this->getInput();
				$options = Helper::getEnchantOptions($this->holder, $item, $viewer->getXpSeed());

				$event = new PlayerEnchantmentOptionsRequestEvent($viewer, $this, $options);
				$event->call();
				if(!$event->isCancelled() && count($event->getOptions()) > 0){
					foreach($event->getOptions() as $option){
						$this->options[$option->getNetworkId()] = $option;
					}
					$viewer->getNetworkSession()->sendEnchantOptions($this->options);
				}
			}
		}

		parent::onSlotChange($index, $before);
	}

	public function getInput() : Item{
		return $this->getItem(self::SLOT_INPUT);
	}

	public function getOutput(int $optionId) : ?Item{
		$option = $this->options[$optionId] ?? null;
		if($option === null){
			// Failed to find an enchantment option with the passed network id
			return null;
		}

		$outputItem = $this->getInput();
		if($outputItem->getTypeId() === ItemTypeIds::BOOK){
			$outputItem = VanillaItems::ENCHANTED_BOOK();
		}

		foreach($option->getEnchantments() as $enchantment){
			$outputItem->addEnchantment($enchantment);
		}

		return $outputItem;
	}

	public function getOptionEnchantmentLevel(int $optionId) : ?int{
		$level = array_search($optionId, array_keys($this->options), true);
		return $level === false ? null : $level + 1;
	}
}
