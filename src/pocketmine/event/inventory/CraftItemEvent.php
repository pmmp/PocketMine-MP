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
use pocketmine\event\Event;
use pocketmine\inventory\Recipe;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\item\Item;
use pocketmine\Player;

class CraftItemEvent extends Event implements Cancellable{
	public static $handlerList = null;

	/** @var CraftingTransaction */
	private $transaction;

	/**
	 * @param CraftingTransaction $transaction
	 */
	public function __construct(CraftingTransaction $transaction){
		$this->transaction = $transaction;
	}

	public function getTransaction() : CraftingTransaction{
		return $this->transaction;
	}

	/**
	 * @deprecated This returns a one-dimensional array of ingredients and does not account for the positioning of
	 * items in the crafting grid. Prefer getting the input map from the transaction instead.
	 *
	 * @return Item[]
	 */
	public function getInput() : array{
		return array_map(function(Item $item) : Item{
			return clone $item;
		}, array_merge(...$this->transaction->getInputMap()));
	}

	/**
	 * @return Recipe
	 */
	public function getRecipe() : Recipe{
		$recipe = $this->transaction->getRecipe();
		if($recipe === null){
			throw new \RuntimeException("This shouldn't be called if the transaction can't be executed");
		}

		return $recipe;
	}

	/**
	 * @return Player
	 */
	public function getPlayer() : Player{
		return $this->transaction->getSource();
	}
}
