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

use pocketmine\item\Armor;
use pocketmine\item\ArmorTrim;
use pocketmine\item\ArmorTrimMaterial;
use pocketmine\item\ArmorTrimPattern;
use pocketmine\item\Item;

class SmithingTrimRecipe extends SmithingRecipe{

	public function __construct(
		private RecipeIngredient $input,
		private RecipeIngredient $addition,
		private RecipeIngredient $template){
	}

	public function getInput() : RecipeIngredient{
		return $this->input;
	}

	public function getAddition() : RecipeIngredient{
		return $this->addition;
	}

	public function getTemplate() : RecipeIngredient{
		return $this->template;
	}

	public function constructOutput(Item $input, Item $addition, Item $template) : ?Item{
		if(!$input instanceof Armor){
			return null;
		}
		if(($material = ArmorTrimMaterial::fromItem($addition)) === null || ($pattern = ArmorTrimPattern::fromItem($template)) === null){
			return null;
		}
		return $input->setTrim(new ArmorTrim($material, $pattern));
	}
}
