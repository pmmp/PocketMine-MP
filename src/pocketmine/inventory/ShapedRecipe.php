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
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector2;
use pocketmine\Server;
use pocketmine\utils\UUID;

class ShapedRecipe implements Recipe{
	/** @var Item */
	private $output;

	/** @var UUID|null */
	private $id = null;

	/** @var string[] */
	private $shape = [];

	/** @var Item[][] */
	private $ingredients = [];
	/** @var Vector2[][] */
	private $shapeItems = [];

	/**
	 * @param Item $result
	 * @param int  $height
	 * @param int  $width
	 *
	 * @throws \Exception
	 */
	public function __construct(Item $result, int $height, int $width){
		for($h = 0; $h < $height; $h++){
			if($width === 0 or $width > 3){
				throw new \InvalidStateException("Crafting rows should be 1, 2, 3 wide, not $width");
			}
			$this->ingredients[] = array_fill(0, $width, null);
		}

		$this->output = clone $result;
	}

	public function getWidth() : int{
		return count($this->ingredients[0]);
	}

	public function getHeight() : int{
		return count($this->ingredients);
	}

	/**
	 * @return Item
	 */
	public function getResult() : Item{
		return $this->output;
	}

	/**
	 * @return UUID|null
	 */
	public function getId(){
		return $this->id;
	}

	public function setId(UUID $id){
		if($this->id !== null){
			throw new \InvalidStateException("Id is already set");
		}

		$this->id = $id;
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param Item $item
	 *
	 * @return $this
	 */
	public function addIngredient(int $x, int $y, Item $item){
		$this->ingredients[$y][$x] = clone $item;
		return $this;
	}

	/**
	 * @param string $key
	 * @param Item   $item
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function setIngredient(string $key, Item $item){
		if(!array_key_exists($key, $this->shape)){
			throw new \Exception("Symbol does not appear in the shape: " . $key);
		}

		$this->fixRecipe($key, $item);

		return $this;
	}

	/**
	 * @param string $key
	 * @param Item $item
	 */
	protected function fixRecipe(string $key, Item $item){
		foreach($this->shapeItems[$key] as $entry){
			$this->ingredients[$entry->y][$entry->x] = clone $item;
		}
	}

	/**
	 * @return Item[][]
	 */
	public function getIngredientMap() : array{
		$ingredients = [];
		foreach($this->ingredients as $y => $row){
			$ingredients[$y] = [];
			foreach($row as $x => $ingredient){
				if($ingredient !== null){
					$ingredients[$y][$x] = clone $ingredient;
				}else{
					$ingredients[$y][$x] = ItemFactory::get(Item::AIR);
				}
			}
		}

		return $ingredients;
	}

	/**
	 * @param int $x
	 * @param int $y
	 *
	 * @return Item
	 */
	public function getIngredient(int $x, int $y) : Item{
		return $this->ingredients[$y][$x] ?? ItemFactory::get(Item::AIR);
	}

	/**
	 * @return string[]
	 */
	public function getShape() : array{
		return $this->shape;
	}

	public function registerToCraftingManager(){
		Server::getInstance()->getCraftingManager()->registerShapedRecipe($this);
	}
}
