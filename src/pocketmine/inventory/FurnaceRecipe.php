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

namespace pocketmine\inventory;

use pocketmine\item\Item;

class FurnaceRecipe implements Recipe{

	/** @var Item */
	private $output;

	/** @var Item */
	private $ingredient;

	public function __construct(Item $result, Item $ingredient){
		$this->output = clone $result;
		$this->ingredient = clone $ingredient;
	}

	/**
	 * @return void
	 */
	public function setInput(Item $item){
		$this->ingredient = clone $item;
	}

	public function getInput() : Item{
		return clone $this->ingredient;
	}

	public function getResult() : Item{
		return clone $this->output;
	}

	/**
	 * @deprecated
	 */
	public function registerToCraftingManager(CraftingManager $manager) : void{
		$manager->registerFurnaceRecipe($this);
	}
}
