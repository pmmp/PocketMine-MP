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

use pocketmine\item\Item;
use pocketmine\network\mcpe\compression\CompressBatchPromise;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipe as ProtocolFurnaceRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeIngredient;
use pocketmine\network\mcpe\protocol\types\recipe\ShapedRecipe as ProtocolShapedRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\ShapelessRecipe as ProtocolShapelessRecipe;
use pocketmine\timings\Timings;
use pocketmine\utils\Binary;
use pocketmine\uuid\UUID;
use function array_map;
use function json_encode;
use function spl_object_id;
use function str_repeat;
use function usort;

class CraftingManager{
	/** @var ShapedRecipe[][] */
	protected $shapedRecipes = [];
	/** @var ShapelessRecipe[][] */
	protected $shapelessRecipes = [];

	/** @var FurnaceRecipeManager */
	protected $furnaceRecipeManager;

	/** @var CompressBatchPromise[] */
	private $craftingDataCaches = [];

	public function __construct(){
		$this->furnaceRecipeManager = new FurnaceRecipeManager();
		$this->furnaceRecipeManager->getRecipeRegisteredCallbacks()->add(function(FurnaceRecipe $recipe) : void{
			$this->craftingDataCaches = [];
		});
	}

	/**
	 * Rebuilds the cached CraftingDataPacket.
	 */
	private function buildCraftingDataCache(Compressor $compressor) : CompressBatchPromise{
		Timings::$craftingDataCacheRebuildTimer->startTiming();
		$pk = new CraftingDataPacket();
		$pk->cleanRecipes = true;

		$counter = 0;
		$nullUUID = UUID::fromData(str_repeat("\x00", 16));
		$converter = TypeConverter::getInstance();
		foreach($this->shapelessRecipes as $list){
			foreach($list as $recipe){
				$pk->entries[] = new ProtocolShapelessRecipe(
					CraftingDataPacket::ENTRY_SHAPELESS,
					Binary::writeInt($counter++),
					array_map(function(Item $item) use ($converter) : RecipeIngredient{
						return $converter->coreItemStackToRecipeIngredient($item);
					}, $recipe->getIngredientList()),
					array_map(function(Item $item) use ($converter) : ItemStack{
						return $converter->coreItemStackToNet($item);
					}, $recipe->getResults()),
					$nullUUID,
					"crafting_table",
					50,
					$counter
				);
			}
		}
		foreach($this->shapedRecipes as $list){
			foreach($list as $recipe){
				$inputs = [];

				for($row = 0, $height = $recipe->getHeight(); $row < $height; ++$row){
					for($column = 0, $width = $recipe->getWidth(); $column < $width; ++$column){
						$inputs[$row][$column] = $converter->coreItemStackToRecipeIngredient($recipe->getIngredient($column, $row));
					}
				}
				$pk->entries[] = $r = new ProtocolShapedRecipe(
					CraftingDataPacket::ENTRY_SHAPED,
					Binary::writeInt($counter++),
					$inputs,
					array_map(function(Item $item) use ($converter) : ItemStack{
						return $converter->coreItemStackToNet($item);
					}, $recipe->getResults()),
					$nullUUID,
					"crafting_table",
					50,
					$counter
				);
			}
		}

		foreach($this->furnaceRecipeManager->getAll() as $recipe){
			$input = $converter->coreItemStackToNet($recipe->getInput());
			$pk->entries[] = new ProtocolFurnaceRecipe(
				CraftingDataPacket::ENTRY_FURNACE_DATA,
				$input->getId(),
				$input->getMeta(),
				$converter->coreItemStackToNet($recipe->getResult()),
				"furnace"
			);
		}

		$promise = new CompressBatchPromise();
		$promise->resolve($compressor->compress(PacketBatch::fromPackets($pk)->getBuffer()));

		Timings::$craftingDataCacheRebuildTimer->stopTiming();
		return $promise;
	}

	/**
	 * Returns a pre-compressed CraftingDataPacket for sending to players. Rebuilds the cache if it is not found.
	 */
	public function getCraftingDataPacket(Compressor $compressor) : CompressBatchPromise{
		$compressorId = spl_object_id($compressor);

		if(!isset($this->craftingDataCaches[$compressorId])){
			$this->craftingDataCaches[$compressorId] = $this->buildCraftingDataCache($compressor);
		}

		return $this->craftingDataCaches[$compressorId];
	}

	/**
	 * Function used to arrange Shapeless Recipe ingredient lists into a consistent order.
	 */
	public static function sort(Item $i1, Item $i2) : int{
		//Use spaceship operator to compare each property, then try the next one if they are equivalent.
		($retval = $i1->getId() <=> $i2->getId()) === 0 && ($retval = $i1->getMeta() <=> $i2->getMeta()) === 0 && ($retval = $i1->getCount() <=> $i2->getCount()) === 0;

		return $retval;
	}

	/**
	 * @param Item[] $items
	 *
	 * @return Item[]
	 */
	private static function pack(array $items) : array{
		/** @var Item[] $result */
		$result = [];

		foreach($items as $i => $item){
			foreach($result as $otherItem){
				if($item->equals($otherItem)){
					$otherItem->setCount($otherItem->getCount() + $item->getCount());
					continue 2;
				}
			}

			//No matching item found
			$result[] = clone $item;
		}

		return $result;
	}

	/**
	 * @param Item[] $outputs
	 */
	private static function hashOutputs(array $outputs) : string{
		$outputs = self::pack($outputs);
		usort($outputs, [self::class, "sort"]);
		foreach($outputs as $o){
			//this reduces accuracy of hash, but it's necessary to deal with recipe book shift-clicking stupidity
			$o->setCount(1);
		}

		return json_encode($outputs);
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

	public function getFurnaceRecipeManager() : FurnaceRecipeManager{
		return $this->furnaceRecipeManager;
	}

	public function registerShapedRecipe(ShapedRecipe $recipe) : void{
		$this->shapedRecipes[self::hashOutputs($recipe->getResults())][] = $recipe;

		$this->craftingDataCaches = [];
	}

	public function registerShapelessRecipe(ShapelessRecipe $recipe) : void{
		$this->shapelessRecipes[self::hashOutputs($recipe->getResults())][] = $recipe;

		$this->craftingDataCaches = [];
	}

	/**
	 * @param Item[]       $outputs
	 */
	public function matchRecipe(CraftingGrid $grid, array $outputs) : ?CraftingRecipe{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = self::hashOutputs($outputs);

		if(isset($this->shapedRecipes[$outputHash])){
			foreach($this->shapedRecipes[$outputHash] as $recipe){
				if($recipe->matchesCraftingGrid($grid)){
					return $recipe;
				}
			}
		}

		if(isset($this->shapelessRecipes[$outputHash])){
			foreach($this->shapelessRecipes[$outputHash] as $recipe){
				if($recipe->matchesCraftingGrid($grid)){
					return $recipe;
				}
			}
		}

		return null;
	}

	/**
	 * @param Item[] $outputs
	 *
	 * @return CraftingRecipe[]|\Generator
	 * @phpstan-return \Generator<int, CraftingRecipe, void, void>
	 */
	public function matchRecipeByOutputs(array $outputs) : \Generator{
		//TODO: try to match special recipes before anything else (first they need to be implemented!)

		$outputHash = self::hashOutputs($outputs);

		if(isset($this->shapedRecipes[$outputHash])){
			foreach($this->shapedRecipes[$outputHash] as $recipe){
				yield $recipe;
			}
		}

		if(isset($this->shapelessRecipes[$outputHash])){
			foreach($this->shapelessRecipes[$outputHash] as $recipe){
				yield $recipe;
			}
		}
	}
}
