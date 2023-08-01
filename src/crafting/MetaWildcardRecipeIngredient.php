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

namespace pocketmine\crafting;

use pocketmine\item\Item;
use pocketmine\world\format\io\GlobalItemDataHandlers;

/**
 * Recipe ingredient that matches items by their Minecraft ID only. This is used for things like the crafting table
 * recipe from planks (multiple types of planks are accepted).
 *
 * WARNING: Plugins shouldn't usually use this. This is a hack that relies on internal Minecraft behaviour, which might
 * change or break at any time.
 *
 * @internal
 */
final class MetaWildcardRecipeIngredient implements RecipeIngredient{

	public function __construct(
		private string $itemId,
	){}

	public function getItemId() : string{ return $this->itemId; }

	public function accepts(Item $item) : bool{
		if($item->getCount() < 1){
			return false;
		}

		return GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName() === $this->itemId;
	}

	public function __toString() : string{
		return "MetaWildcardRecipeIngredient($this->itemId)";
	}
}
