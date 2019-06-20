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
use pocketmine\inventory\CraftingRecipe;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\item\Item;
use pocketmine\player\Player;

class CraftItemEvent extends Event implements Cancellable{
	use CancellableTrait;

	/** @var CraftingTransaction */
	private $transaction;
	/** @var CraftingRecipe */
	private $recipe;
	/** @var int */
	private $repetitions;
	/** @var Item[] */
	private $inputs;
	/** @var Item[] */
	private $outputs;

	/**
	 * @param CraftingTransaction $transaction
	 * @param CraftingRecipe      $recipe
	 * @param int                 $repetitions
	 * @param Item[]              $inputs
	 * @param Item[]              $outputs
	 */
	public function __construct(CraftingTransaction $transaction, CraftingRecipe $recipe, int $repetitions, array $inputs, array $outputs){
		$this->transaction = $transaction;
		$this->recipe = $recipe;
		$this->repetitions = $repetitions;
		$this->inputs = $inputs;
		$this->outputs = $outputs;
	}

	/**
	 * Returns the inventory transaction involved in this crafting event.
	 *
	 * @return CraftingTransaction
	 */
	public function getTransaction() : CraftingTransaction{
		return $this->transaction;
	}

	/**
	 * Returns the recipe crafted.
	 *
	 * @return CraftingRecipe
	 */
	public function getRecipe() : CraftingRecipe{
		return $this->recipe;
	}

	/**
	 * Returns the number of times the recipe was crafted. This is usually 1, but might be more in the case of recipe
	 * book shift-clicks (which craft lots of items in a batch).
	 *
	 * @return int
	 */
	public function getRepetitions() : int{
		return $this->repetitions;
	}

	/**
	 * Returns a list of items destroyed as ingredients of the recipe.
	 *
	 * @return Item[]
	 */
	public function getInputs() : array{
		return $this->inputs;
	}

	/**
	 * Returns a list of items created by crafting the recipe.
	 *
	 * @return Item[]
	 */
	public function getOutputs() : array{
		return $this->outputs;
	}

	/**
	 * @return Player
	 */
	public function getPlayer() : Player{
		return $this->transaction->getSource();
	}
}
