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

use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Item;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class PotionContainerChangeRecipe implements BrewingRecipe{

	public function __construct(
		private string $inputItemId,
		private RecipeIngredient $ingredient,
		private string $outputItemId
	){}

	public function getInputItemId() : string{
		return $this->inputItemId;
	}

	public function getIngredient() : RecipeIngredient{
		return $this->ingredient;
	}

	public function getOutputItemId() : string{
		return $this->outputItemId;
	}

	public function getResultFor(Item $input) : ?Item{
		//TODO: this is a really awful hack, but there isn't another way for now
		//this relies on transforming the serialized item's ID, relying on the target item type's data being the same as the input.
		//This is the same assumption previously made using ItemFactory::get(), except it was less obvious how bad it was.
		//The other way is to bake the actual Potion class types into here, which isn't great for data-driving stuff.
		//We need a better solution for this.

		$data = GlobalItemDataHandlers::getSerializer()->serializeType($input);
		return $data->getName() === $this->getInputItemId() ?
			GlobalItemDataHandlers::getDeserializer()->deserializeType(new SavedItemData($this->getOutputItemId(), $data->getMeta(), $data->getBlock(), $data->getTag())) :
			null;
	}
}
