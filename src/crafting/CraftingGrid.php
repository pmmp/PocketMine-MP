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

use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function max;
use function min;
use const PHP_INT_MAX;

class CraftingGrid extends SimpleInventory{
	public const SIZE_SMALL = 2;
	public const SIZE_BIG = 3;

	/** @var Player */
	protected $holder;
	/** @var int */
	private $gridWidth;

	/** @var int|null */
	private $startX;
	/** @var int|null */
	private $xLen;
	/** @var int|null */
	private $startY;
	/** @var int|null */
	private $yLen;

	public function __construct(Player $holder, int $gridWidth){
		$this->holder = $holder;
		$this->gridWidth = $gridWidth;
		parent::__construct($this->getGridWidth() ** 2);
	}

	public function getGridWidth() : int{
		return $this->gridWidth;
	}

	public function setItem(int $index, Item $item) : void{
		parent::setItem($index, $item);
		$this->seekRecipeBounds();
	}

	/**
	 * @return Player
	 */
	public function getHolder(){
		return $this->holder;
	}

	private function seekRecipeBounds() : void{
		$minX = PHP_INT_MAX;
		$maxX = 0;

		$minY = PHP_INT_MAX;
		$maxY = 0;

		$empty = true;

		for($y = 0; $y < $this->gridWidth; ++$y){
			for($x = 0; $x < $this->gridWidth; ++$x){
				if(!$this->isSlotEmpty($y * $this->gridWidth + $x)){
					$minX = min($minX, $x);
					$maxX = max($maxX, $x);

					$minY = min($minY, $y);
					$maxY = max($maxY, $y);

					$empty = false;
				}
			}
		}

		if(!$empty){
			$this->startX = $minX;
			$this->xLen = $maxX - $minX + 1;
			$this->startY = $minY;
			$this->yLen = $maxY - $minY + 1;
		}else{
			$this->startX = $this->xLen = $this->startY = $this->yLen = null;
		}
	}

	/**
	 * Returns the item at offset x,y, offset by where the starts of the recipe rectangle are.
	 */
	public function getIngredient(int $x, int $y) : Item{
		if($this->startX !== null and $this->startY !== null){
			return $this->getItem(($y + $this->startY) * $this->gridWidth + ($x + $this->startX));
		}

		throw new \InvalidStateException("No ingredients found in grid");
	}

	/**
	 * Returns the width of the recipe we're trying to craft, based on items currently in the grid.
	 */
	public function getRecipeWidth() : int{
		return $this->xLen ?? 0;
	}

	/**
	 * Returns the height of the recipe we're trying to craft, based on items currently in the grid.
	 */
	public function getRecipeHeight() : int{
		return $this->yLen ?? 0;
	}
}
