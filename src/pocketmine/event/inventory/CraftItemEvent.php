<?php

/**
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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\inventory\CraftingTransactionGroup;
use pocketmine\inventory\Recipe;

class CraftItemEvent extends Event implements Cancellable{
	public static $handlerList = null;

	/** @var CraftingTransactionGroup */
	private $ts;
	/** @var Recipe */
	private $recipe;

	/**
	 * @param CraftingTransactionGroup $ts
	 * @param Recipe                   $recipe
	 */
	public function __construct(CraftingTransactionGroup $ts, Recipe $recipe){
		$this->ts = $ts;
		$this->recipe = $recipe;
	}

	/**
	 * @return CraftingTransactionGroup
	 */
	public function getTransaction(){
		return $this->ts;
	}

	/**
	 * @return Recipe
	 */
	public function getRecipe(){
		return $this->recipe;
	}

}