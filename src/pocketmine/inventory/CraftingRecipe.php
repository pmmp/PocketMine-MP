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
use pocketmine\utils\UUID;

interface CraftingRecipe extends Recipe{

	/**
	 * @return UUID|null
	 */
	public function getId() : ?UUID;

	/**
	 * @param UUID $id
	 */
	public function setId(UUID $id);

	public function requiresCraftingTable() : bool;

	/**
	 * @return Item[]
	 */
	public function getExtraResults() : array;

	/**
	 * @return Item[]
	 */
	public function getAllResults() : array;

	/**
	 * Returns whether the specified list of crafting grid inputs and outputs matches this recipe. Outputs DO NOT
	 * include the primary result item.
	 *
	 * @param Item[][] $input 2D array of items taken from the crafting grid
	 * @param Item[][] $output 2D array of items put back into the crafting grid (secondary results)
	 *
	 * @return bool
	 */
	public function matchItems(array $input, array $output) : bool;
}
