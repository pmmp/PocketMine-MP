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

namespace pocketmine\network\mcpe\protocol\types;

class PotionTypeRecipe{
	/** @var int */
	private $inputPotionType;
	/** @var int */
	private $ingredientItemId;
	/** @var int */
	private $outputPotionType;

	public function __construct(int $inputPotionType, int $ingredientItemId, int $outputPotionType){
		$this->inputPotionType = $inputPotionType;
		$this->ingredientItemId = $ingredientItemId;
		$this->outputPotionType = $outputPotionType;
	}

	public function getInputPotionType() : int{
		return $this->inputPotionType;
	}

	public function getIngredientItemId() : int{
		return $this->ingredientItemId;
	}

	public function getOutputPotionType() : int{
		return $this->outputPotionType;
	}
}
