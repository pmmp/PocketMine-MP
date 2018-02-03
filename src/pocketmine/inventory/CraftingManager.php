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

use pocketmine\event\Timings;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\MainLogger;
use pocketmine\utils\UUID;

class CraftingManager{

	/** @var CraftingRecipe[] */
	protected $recipes = [];

	/** @var ShapedRecipe[][] */
	protected $shapedRecipes = [];
	/** @var ShapelessRecipe[][] */
	protected $shapelessRecipes = [];
	/** @var FurnaceRecipe[] */
	protected $furnaceRecipes = [];

	private static $RECIPE_COUNT = 0;

	/** @var BatchPacket */
	private $craftingDataCache;

	public function __construct(){
		$recipes = new Config(\pocketmine\RESOURCE_PATH . "recipes.json", Config::JSON, []);

		MainLogger::getLogger()->info("Loading recipes...");
		foreach($recipes->getAll() as $recipe){
			switch($recipe["type"]){
				case 0:
					// TODO: handle multiple result items
					$first = $recipe["output"][0];
					$result = new ShapelessRecipe(Item::jsonDeserialize($first));

					foreach($recipe["input"] as $ingredient){
						$result->addIngredient(Item::jsonDeserialize($ingredient));
					}
					$this->registerRecipe($result);
					break;
				case 1:
					$first = array_shift($recipe["output"]);

					$this->registerRecipe(new ShapedRecipe(
						Item::jsonDeserialize($first),
						$recipe["shape"],
						array_map(function(array $data) : Item{ return Item::jsonDeserialize($data); }, $recipe["input"]),
						array_map(function(array $data) : Item{ return Item::jsonDeserialize($data); }, $recipe["output"])
					));
					break;
				case 2:
				case 3:
					$result = $recipe["output"];
					$resultItem = Item::jsonDeserialize($result);
					$this->registerRecipe(new FurnaceRecipe($resultItem, ItemFactory::get($recipe["inputId"], $recipe["inputDamage"] ?? -1, 1)));
					break;
				default:
					break;
			}
		}

		$this->buildCraftingDataCache();
	}

	/**
	 * Rebuilds the cached CraftingDataPacket.
	 */
	public function buildCraftingDataCache() : void{
		Timings::$craftingDataCacheRebuildTimer->startTiming();
		$pk = new CraftingDataPacket();
		$pk->cleanRecipes = true;

		foreach($this->recipes as $recipe){
			if($recipe instanceof ShapedRecipe){
				$pk->addShapedRecipe($recipe);
			}elseif($recipe instanceof ShapelessRecipe){
				$pk->addShapelessRecipe($recipe);
			}
		}

		foreach($this->furnaceRecipes as $recipe){
			$pk->addFurnaceRecipe($recipe);
		}

		$pk->encode();

		$batch = new BatchPacket();
		$batch->addPacket($pk);
		$batch->setCompressionLevel(Server::getInstance()->networkCompressionLevel);
		$batch->encode();

		$this->craftingDataCache = $batch;
		Timings::$craftingDataCacheRebuildTimer->stopTiming();
	}

	/**
	 * Returns a pre-compressed CraftingDataPacket for sending to players. Rebuilds the cache if it is not found.
	 *
	 * @return BatchPacket
	 */
	public function getCraftingDataPacket() : BatchPacket{
		if($this->craftingDataCache === null){
			$this->buildCraftingDataCache();
		}

		return $this->craftingDataCache;
	}

	/**
	 * Function used to arrange Shapeless Recipe ingredient lists into a consistent order.
	 *
	 * @param Item $i1
	 * @param Item $i2
	 *
	 * @return int
	 */
	public function sort(Item $i1, Item $i2){
		if($i1->getId() > $i2->getId()){
			return 1;
		}elseif($i1->getId() < $i2->getId()){
			return -1;
		}elseif($i1->getDamage() > $i2->getDamage()){
			return 1;
		}elseif($i1->getDamage() < $i2->getDamage()){
			return -1;
		}elseif($i1->getCount() > $i2->getCount()){
			return 1;
		}elseif($i1->getCount() < $i2->getCount()){
			return -1;
		}else{
			return 0;
		}
	}

	/**
	 * @param UUID $id
	 * @return CraftingRecipe|null
	 */
	public function getRecipe(UUID $id) : ?CraftingRecipe{
		$index = $id->toBinary();
		return $this->recipes[$index] ?? null;
	}

	/**
	 * @return Recipe[]
	 */
	public function getRecipes() : array{
		return $this->recipes;
	}

	/**
	 * @return ShapelessRecipe[][]
	 */
	public function getShapelessRecipes() : array{
		return $this->shapelessRecipes;
	}

	/**
	 * @return ShapedRecipe[][]
	 */
	public function getShapedRecipes() : array{
		return $this->shapedRecipes;
	}

	/**
	 * @return FurnaceRecipe[]
	 */
	public function getFurnaceRecipes() : array{
		return $this->furnaceRecipes;
	}

	/**
	 * @param ShapedRecipe $recipe
	 */
	public function registerShapedRecipe(ShapedRecipe $recipe) : void{
		$this->shapedRecipes[json_encode($recipe->getResult())][json_encode($recipe->getIngredientMap())] = $recipe;
		$this->craftingDataCache = null;
	}

	/**
	 * @param ShapelessRecipe $recipe
	 */
	public function registerShapelessRecipe(ShapelessRecipe $recipe) : void{
		$ingredients = $recipe->getIngredientList();
		usort($ingredients, [$this, "sort"]);
		$this->shapelessRecipes[json_encode($recipe->getResult())][json_encode($ingredients)] = $recipe;
		$this->craftingDataCache = null;
	}

	/**
	 * @param FurnaceRecipe $recipe
	 */
	public function registerFurnaceRecipe(FurnaceRecipe $recipe) : void{
		$input = $recipe->getInput();
		$this->furnaceRecipes[$input->getId() . ":" . ($input->hasAnyDamageValue() ? "?" : $input->getDamage())] = $recipe;
		$this->craftingDataCache = null;
	}

	/**
	 * Clones a map of Item objects to avoid accidental modification.
	 *
	 * @param Item[][] $map
	 * @return Item[][]
	 */
	private function cloneItemMap(array $map) : array{
		/** @var Item[] $row */
		foreach($map as $y => $row){
			foreach($row as $x => $item){
				$map[$y][$x] = clone $item;
			}
		}

		return $map;
	}

	/**
	 * @param Item[][] $inputMap
	 * @param Item     $primaryOutput
	 * @param Item[][] $extraOutputMap
	 *
	 * @return CraftingRecipe|null
	 */
	public function matchRecipe(array $inputMap, Item $primaryOutput, array $extraOutputMap) : ?CraftingRecipe{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = json_encode($primaryOutput);
		if(isset($this->shapedRecipes[$outputHash])){
			$inputHash = json_encode($inputMap);
			$recipe = $this->shapedRecipes[$outputHash][$inputHash] ?? null;

			if($recipe !== null and $recipe->matchItems($this->cloneItemMap($inputMap), $this->cloneItemMap($extraOutputMap))){ //matched a recipe by hash
				return $recipe;
			}

			foreach($this->shapedRecipes[$outputHash] as $recipe){
				if($recipe->matchItems($this->cloneItemMap($inputMap), $this->cloneItemMap($extraOutputMap))){
					return $recipe;
				}
			}
		}

		if(isset($this->shapelessRecipes[$outputHash])){
			$list = array_merge(...$inputMap);
			usort($list, [$this, "sort"]);

			$inputHash = json_encode($list);
			$recipe = $this->shapelessRecipes[$outputHash][$inputHash] ?? null;

			if($recipe !== null and $recipe->matchItems($this->cloneItemMap($inputMap), $this->cloneItemMap($extraOutputMap))){
				return $recipe;
			}

			foreach($this->shapelessRecipes[$outputHash] as $recipe){
				if($recipe->matchItems($this->cloneItemMap($inputMap), $this->cloneItemMap($extraOutputMap))){
					return $recipe;
				}
			}
		}

		return null;
	}

	/**
	 * @param Item $input
	 *
	 * @return FurnaceRecipe|null
	 */
	public function matchFurnaceRecipe(Item $input) : ?FurnaceRecipe{
		return $this->furnaceRecipes[$input->getId() . ":" . $input->getDamage()] ?? $this->furnaceRecipes[$input->getId() . ":?"] ?? null;
	}

	/**
	 * @param Recipe $recipe
	 */
	public function registerRecipe(Recipe $recipe) : void{
		if($recipe instanceof CraftingRecipe){
			$result = $recipe->getResult();
			$recipe->setId($uuid = UUID::fromData((string) ++self::$RECIPE_COUNT, (string) $result->getId(), (string) $result->getDamage(), (string) $result->getCount(), $result->getCompoundTag()));
			$this->recipes[$uuid->toBinary()] = $recipe;
		}

		$recipe->registerToCraftingManager($this);
	}

}
