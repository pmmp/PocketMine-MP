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

namespace pocketmine\inventory;

use pocketmine\item\Item;
use pocketmine\Server;
use pocketmine\utils\UUID;
use pocketmine\math\Vector2;

class ShapedRecipe implements Recipe{
	/** @var Item */
	private $output;

	private $id = null;

	/** @var string[] */
	private $shape = [];

	/** @var Item[][] */
	private $ingredients = [];
	/** @var Vector2[][] */
	private $shapeItems = [];

	/**
	 * @param Item     $result
	 * @param string[] $shape
	 *
	 * @throws \Exception
	 */
	public function __construct(Item $result, ...$shape){
		if(count($shape) === 0){
			throw new \InvalidArgumentException("Must provide a shape");
		}
		if(count($shape) > 3){
			throw new \InvalidStateException("Crafting recipes should be 1, 2, 3 rows, not " . count($shape));
		}
		foreach($shape as $y => $row){
			if(strlen($row) === 0 or strlen($row) > 3){
				throw new \InvalidStateException("Crafting rows should be 1, 2, 3 characters, not " . count($row));
			}
			$this->ingredients[] = array_fill(0, strlen($row), null);
			$len = strlen($row);
			for($i = 0; $i < $len; ++$i){
				$this->shape[$row{$i}] = null;

				if(!isset($this->shapeItems[$row{$i}])){
					$this->shapeItems[$row{$i}] = [new Vector2($i, $y)];
				}else{
					$this->shapeItems[$row{$i}][] = new Vector2($i, $y);
				}
			}
		}

		$this->output = clone $result;
	}

	public function getWidth(){
		return count($this->ingredients[0]);
	}

	public function getHeight(){
		return count($this->ingredients);
	}

	public function getResult(){
		return $this->output;
	}

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
	 * @param string $key
	 * @param Item   $item
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function setIngredient($key, Item $item){
		if(!array_key_exists($key, $this->shape)){
			throw new \Exception("Symbol does not appear in the shape: " . $key);
		}

		$this->fixRecipe($key, $item);

		return $this;
	}

	protected function fixRecipe($key, $item){
		foreach($this->shapeItems[$key] as $entry){
			$this->ingredients[$entry->y][$entry->x] = clone $item;
		}
	}

	/**
	 * @return Item[][]
	 */
	public function getIngredientMap(){
		$ingredients = [];
		foreach($this->ingredients as $y => $row){
			$ingredients[$y] = [];
			foreach($row as $x => $ingredient){
				if($ingredient !== null){
					$ingredients[$y][$x] = clone $ingredient;
				}else{
					$ingredients[$y][$x] = Item::get(Item::AIR);
				}
			}
		}

		return $ingredients;
	}

	/**
	 * @param $x
	 * @param $y
	 * @return null|Item
	 */
	public function getIngredient($x, $y){
		return isset($this->ingredients[$y][$x]) ? $this->ingredients[$y][$x] : Item::get(Item::AIR);
	}

	/**
	 * @return string[]
	 */
	public function getShape(){
		return $this->shape;
	}

	public function registerToCraftingManager(){
		Server::getInstance()->getCraftingManager()->registerShapedRecipe($this);
	}
}