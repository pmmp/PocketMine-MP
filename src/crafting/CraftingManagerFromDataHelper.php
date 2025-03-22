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

use pocketmine\crafting\json\FurnaceRecipeData;
use pocketmine\crafting\json\ItemStackData;
use pocketmine\crafting\json\PotionContainerChangeRecipeData;
use pocketmine\crafting\json\PotionTypeRecipeData;
use pocketmine\crafting\json\RecipeIngredientData;
use pocketmine\crafting\json\ShapedRecipeData;
use pocketmine\crafting\json\ShapelessRecipeData;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\errorhandler\ErrorToExceptionHandler;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use Symfony\Component\Filesystem\Path;
use function base64_decode;
use function get_debug_type;
use function is_array;
use function is_object;
use function json_decode;

final class CraftingManagerFromDataHelper{

	private static function deserializeIngredient(RecipeIngredientData $data) : ?RecipeIngredient{
		if(isset($data->count) && $data->count !== 1){
			//every case we've seen so far where this isn't the case, it's been a bug and the count was ignored anyway
			//e.g. gold blocks crafted from 9 ingots, but each input item individually had a count of 9
			throw new SavedDataLoadingException("Recipe inputs should have a count of exactly 1");
		}
		if(isset($data->tag)){
			return new TagWildcardRecipeIngredient($data->tag);
		}

		$meta = $data->meta ?? null;
		if($meta === RecipeIngredientData::WILDCARD_META_VALUE){
			//this could be an unimplemented item, but it doesn't really matter, since the item shouldn't be able to
			//be obtained anyway - filtering unknown items is only really important for outputs, to prevent players
			//obtaining them
			return new MetaWildcardRecipeIngredient($data->name);
		}

		$itemStack = self::deserializeItemStackFromFields(
			$data->name,
			$meta,
			$data->count ?? null,
			$data->block_states ?? null,
			null,
			[],
			[]
		);
		if($itemStack === null){
			//probably unknown item
			return null;
		}
		return new ExactRecipeIngredient($itemStack);
	}

	public static function deserializeItemStack(ItemStackData $data) : ?Item{
		//count, name, block_name, block_states, meta, nbt, can_place_on, can_destroy
		return self::deserializeItemStackFromFields(
			$data->name,
			$data->meta ?? null,
			$data->count ?? null,
			$data->block_states ?? null,
			$data->nbt ?? null,
			$data->can_place_on ?? [],
			$data->can_destroy ?? []
		);
	}

	/**
	 * @param string[] $canPlaceOn
	 * @param string[] $canDestroy
	 */
	private static function deserializeItemStackFromFields(string $name, ?int $meta, ?int $count, ?string $blockStatesRaw, ?string $nbtRaw, array $canPlaceOn, array $canDestroy) : ?Item{
		$meta ??= 0;
		$count ??= 1;

		$blockName = BlockItemIdMap::getInstance()->lookupBlockId($name);
		if($blockName !== null){
			if($meta !== 0){
				throw new SavedDataLoadingException("Meta should not be specified for blockitems");
			}
			$blockStatesTag = $blockStatesRaw === null ?
				[] :
				(new LittleEndianNbtSerializer())
					->read(ErrorToExceptionHandler::trapAndRemoveFalse(fn() => base64_decode($blockStatesRaw, true)))
					->mustGetCompoundTag()
					->getValue();
			$blockStateData = BlockStateData::current($blockName, $blockStatesTag);
		}else{
			$blockStateData = null;
		}

		$nbt = $nbtRaw === null ? null : (new LittleEndianNbtSerializer())
			->read(ErrorToExceptionHandler::trapAndRemoveFalse(fn() => base64_decode($nbtRaw, true)))
			->mustGetCompoundTag();

		$itemStackData = new SavedItemStackData(
			new SavedItemData(
				$name,
				$meta,
				$blockStateData,
				$nbt
			),
			$count,
			null,
			null,
			$canPlaceOn,
			$canDestroy,
		);

		try{
			return GlobalItemDataHandlers::getDeserializer()->deserializeStack($itemStackData);
		}catch(ItemTypeDeserializeException){
			//probably unknown item
			return null;
		}
	}

	/**
	 * @return mixed[]
	 *
	 * @phpstan-template TData of object
	 * @phpstan-param class-string<TData> $modelCLass
	 * @phpstan-return list<TData>
	 */
	public static function loadJsonArrayOfObjectsFile(string $filePath, string $modelCLass) : array{
		$recipes = json_decode(Filesystem::fileGetContents($filePath));
		if(!is_array($recipes)){
			throw new SavedDataLoadingException("$filePath root should be an array, got " . get_debug_type($recipes));
		}

		$mapper = new \JsonMapper();
		$mapper->bStrictObjectTypeChecking = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bExceptionOnMissingData = true;

		return self::loadJsonObjectListIntoModel($mapper, $modelCLass, $recipes);
	}

	/**
	 * @phpstan-template TRecipeData of object
	 * @phpstan-param class-string<TRecipeData> $modelClass
	 * @phpstan-return TRecipeData
	 */
	private static function loadJsonObjectIntoModel(\JsonMapper $mapper, string $modelClass, object $data) : object{
		//JsonMapper does this for subtypes, but not for the base type :(
		try{
			return $mapper->map($data, (new \ReflectionClass($modelClass))->newInstanceWithoutConstructor());
		}catch(\JsonMapper_Exception $e){
			throw new SavedDataLoadingException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * @param mixed[] $data
	 * @return object[]
	 *
	 * @phpstan-template TRecipeData of object
	 * @phpstan-param class-string<TRecipeData> $modelClass
	 * @phpstan-return list<TRecipeData>
	 */
	private static function loadJsonObjectListIntoModel(\JsonMapper $mapper, string $modelClass, array $data) : array{
		$result = [];
		foreach(Utils::promoteKeys($data) as $i => $item){
			if(!is_object($item)){
				throw new SavedDataLoadingException("Invalid entry at index $i: expected object, got " . get_debug_type($item));
			}
			try{
				$result[] = self::loadJsonObjectIntoModel($mapper, $modelClass, $item);
			}catch(SavedDataLoadingException $e){
				throw new SavedDataLoadingException("Invalid entry at index $i: " . $e->getMessage(), 0, $e);
			}
		}
		return $result;
	}

	public static function make(string $directoryPath) : CraftingManager{
		$result = new CraftingManager();

		foreach(self::loadJsonArrayOfObjectsFile(Path::join($directoryPath, 'shapeless_crafting.json'), ShapelessRecipeData::class) as $recipe){
			$recipeType = match($recipe->block){
				"crafting_table" => ShapelessRecipeType::CRAFTING,
				"stonecutter" => ShapelessRecipeType::STONECUTTER,
				"smithing_table" => ShapelessRecipeType::SMITHING,
				"cartography_table" => ShapelessRecipeType::CARTOGRAPHY,
				default => null
			};
			if($recipeType === null){
				continue;
			}
			$inputs = [];
			foreach($recipe->input as $inputData){
				$input = self::deserializeIngredient($inputData);
				if($input === null){ //unknown input item
					continue 2;
				}
				$inputs[] = $input;
			}
			$outputs = [];
			foreach($recipe->output as $outputData){
				$output = self::deserializeItemStack($outputData);
				if($output === null){ //unknown output item
					continue 2;
				}
				$outputs[] = $output;
			}
			//TODO: check unlocking requirements - our current system doesn't support this
			$result->registerShapelessRecipe(new ShapelessRecipe(
				$inputs,
				$outputs,
				$recipeType
			));
		}
		foreach(self::loadJsonArrayOfObjectsFile(Path::join($directoryPath, 'shaped_crafting.json'), ShapedRecipeData::class) as $recipe){
			if($recipe->block !== "crafting_table"){ //TODO: filter others out for now to avoid breaking economics
				continue;
			}
			$inputs = [];
			foreach(Utils::stringifyKeys($recipe->input) as $symbol => $inputData){
				$input = self::deserializeIngredient($inputData);
				if($input === null){ //unknown input item
					continue 2;
				}
				$inputs[$symbol] = $input;
			}
			$outputs = [];
			foreach($recipe->output as $outputData){
				$output = self::deserializeItemStack($outputData);
				if($output === null){ //unknown output item
					continue 2;
				}
				$outputs[] = $output;
			}
			//TODO: check unlocking requirements - our current system doesn't support this
			$result->registerShapedRecipe(new ShapedRecipe(
				$recipe->shape,
				$inputs,
				$outputs
			));
		}
		foreach(self::loadJsonArrayOfObjectsFile(Path::join($directoryPath, 'smelting.json'), FurnaceRecipeData::class) as $recipe){
			$furnaceType = match ($recipe->block){
				"furnace" => FurnaceType::FURNACE,
				"blast_furnace" => FurnaceType::BLAST_FURNACE,
				"smoker" => FurnaceType::SMOKER,
				"campfire" => FurnaceType::CAMPFIRE,
				"soul_campfire" => FurnaceType::SOUL_CAMPFIRE,
				default => null
			};
			if($furnaceType === null){
				continue;
			}
			$output = self::deserializeItemStack($recipe->output);
			if($output === null){
				continue;
			}
			$input = self::deserializeIngredient($recipe->input);
			if($input === null){
				continue;
			}
			$result->getFurnaceRecipeManager($furnaceType)->register(new FurnaceRecipe(
				$output,
				$input
			));
		}

		foreach(self::loadJsonArrayOfObjectsFile(Path::join($directoryPath, 'potion_type.json'), PotionTypeRecipeData::class) as $recipe){
			$input = self::deserializeIngredient($recipe->input);
			$ingredient = self::deserializeIngredient($recipe->ingredient);
			$output = self::deserializeItemStack($recipe->output);
			if($input === null || $ingredient === null || $output === null){
				continue;
			}
			$result->registerPotionTypeRecipe(new PotionTypeRecipe(
				$input,
				$ingredient,
				$output
			));
		}
		foreach(self::loadJsonArrayOfObjectsFile(Path::join($directoryPath, 'potion_container_change.json'), PotionContainerChangeRecipeData::class) as $recipe){
			$ingredient = self::deserializeIngredient($recipe->ingredient);
			if($ingredient === null){
				continue;
			}

			$inputId = $recipe->input_item_name;
			$outputId = $recipe->output_item_name;

			//TODO: this is a really awful way to just check if an ID is recognized ...
			if(
				self::deserializeItemStackFromFields($inputId, null, null, null, null, [], []) === null ||
				self::deserializeItemStackFromFields($outputId, null, null, null, null, [], []) === null
			){
				//unknown item
				continue;
			}
			$result->registerPotionContainerChangeRecipe(new PotionContainerChangeRecipe(
				$inputId,
				$ingredient,
				$outputId
			));
		}

		//TODO: smithing

		return $result;
	}
}
