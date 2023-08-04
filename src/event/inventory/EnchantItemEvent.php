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

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\inventory\transaction\EnchantTransaction;
use pocketmine\item\enchantment\EnchantmentOption;
use pocketmine\item\Item;
use pocketmine\player\Player;

class EnchantItemEvent extends Event implements Cancellable{
	use CancellableTrait;

	public function __construct(
		private readonly EnchantTransaction $transaction,
		private readonly EnchantmentOption $option,
		private readonly Item $inputItem,
		private readonly Item $outputItem,
		private readonly int $xpLevelCost
	){
	}

	/**
	 * Returns the inventory transaction involved in this enchant event.
	 */
	public function getTransaction() : EnchantTransaction{
		return $this->transaction;
	}

	/**
	 * Returns the enchantment option used.
	 */
	public function getOption() : EnchantmentOption{
		return $this->option;
	}

	/**
	 * Returns the item to be enchanted.
	 */
	public function getInputItem() : Item{
		return $this->inputItem;
	}

	/**
	 * Returns the enchanted item.
	 */
	public function getOutputItem() : Item{
		return $this->outputItem;
	}

	/**
	 * Returns the number of XP levels that will be subtracted after enchanting (from 1 to 3)
	 * if the player is not in creative mode.
	 */
	public function getXpLevelCost() : int{
		return $this->xpLevelCost;
	}

	public function getPlayer() : Player{
		return $this->transaction->getSource();
	}
}
