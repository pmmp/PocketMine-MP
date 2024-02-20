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

namespace pocketmine\crafting\json;

final class SmithingTrimRecipeData{

	/** @required */
	public RecipeIngredientData $template;
	/** @required */
	public RecipeIngredientData $input;
	/** @required */
	public RecipeIngredientData $addition;
	/** @required */
	public string $block;

	public function __construct(RecipeIngredientData $template, RecipeIngredientData $input, RecipeIngredientData $addition, string $block){
		$this->template = $template;
		$this->input = $input;
		$this->addition = $addition;
		$this->block = $block;
	}
}
