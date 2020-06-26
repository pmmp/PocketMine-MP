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

namespace pocketmine\network\mcpe\protocol\types\recipe;

class PotionTypeRecipe{
	/** @var int */
	private $inputItemId;
	/** @var int */
	private $inputItemMeta;
	/** @var int */
	private $ingredientItemId;
	/** @var int */
	private $ingredientItemMeta;
	/** @var int */
	private $outputItemId;
	/** @var int */
	private $outputItemMeta;

	public function __construct(int $inputItemId, int $inputItemMeta, int $ingredientItemId, int $ingredientItemMeta, int $outputItemId, int $outputItemMeta){
		$this->inputItemId = $inputItemId;
		$this->inputItemMeta = $inputItemMeta;
		$this->ingredientItemId = $ingredientItemId;
		$this->ingredientItemMeta = $ingredientItemMeta;
		$this->outputItemId = $outputItemId;
		$this->outputItemMeta = $outputItemMeta;
	}

	public function getInputItemId() : int{
		return $this->inputItemId;
	}

	public function getInputItemMeta() : int{
		return $this->inputItemMeta;
	}

	public function getIngredientItemId() : int{
		return $this->ingredientItemId;
	}

	public function getIngredientItemMeta() : int{
		return $this->ingredientItemMeta;
	}

	public function getOutputItemId() : int{
		return $this->outputItemId;
	}

	public function getOutputItemMeta() : int{
		return $this->outputItemMeta;
	}
}
