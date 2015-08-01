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

class ShapedRecipe implements Recipe{
	/** @var Item */
	private $output;

	private $id = null;

	/** @var string[] */
	private $rows = [];

	/** @var Item[] */
	private $ingredients = [];

	/**
	 * @param Item     $result
	 * @param string[] $shape
	 *
	 * @throws \Exception
	 */
	public function __construct(Item $result, array $shape = []){
		if(count($shape) === 0){
			throw new \InvalidArgumentException("Must provide a shape");
		}
		if(count($shape) > 3){
			throw new \InvalidStateException("Crafting recipes should be 1, 2, 3 rows, not " . count($shape));
		}
		foreach($shape as $row){
			if(strlen($row) === 0 or strlen($row) > 3){
				throw new \InvalidStateException("Crafting rows should be 1, 2, 3 characters, not " . count($row));
			}
			$this->rows[] = $row;
			$len = strlen($row);
			for($i = 0; $i < $len; ++$i){
				$this->ingredients[$row{$i}] = null;
			}
		}

		$this->output = clone $result;
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
		if(!isset($this->ingredients[$key])){
			throw new \Exception("Symbol does not appear in the shape: " . $key);
		}

		$this->ingredients[$key] = $item;

		return $this;
	}

	/**
	 * @return Item[]
	 */
	public function getIngredientMap(){
		$ingredients = [];
		foreach($this->ingredients as $key => $ingredient){
			if($ingredient instanceof Item){
				$ingredients[$key] = clone $ingredient;
			}else{
				$ingredients[$key] = $ingredient;
			}
		}

		return $ingredients;
	}

	/**
	 * @return string[]
	 */
	public function getShape(){
		return $this->rows;
	}

	public function registerToCraftingManager(){
		Server::getInstance()->getCraftingManager()->registerShapedRecipe($this);
	}
}