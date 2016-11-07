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

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>


use pocketmine\inventory\FurnaceRecipe;
use pocketmine\inventory\MultiRecipe;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\item\enchantment\EnchantmentList;
use pocketmine\item\Item;
use pocketmine\utils\BinaryStream;

class CraftingDataPacket extends DataPacket{
	const NETWORK_ID = Info::CRAFTING_DATA_PACKET;

	const ENTRY_SHAPELESS = 0;
	const ENTRY_SHAPED = 1;
	const ENTRY_FURNACE = 2;
	const ENTRY_FURNACE_DATA = 3;
	const ENTRY_ENCHANT_LIST = -1; //Deprecated
	const ENTRY_MULTI = 4;

	/** @var object[] */
	public $entries = [];
	public $cleanRecipes = false;

	public function clean(){
		$this->entries = [];
		return parent::clean();
	}

	public function decode(){
		$count = $this->getUnsignedVarInt();
		for($i = 0; $i < $count; ++$i){
			$recipeType = $this->getUnsignedVarInt();
			switch($recipeType){
				case self::ENTRY_SHAPELESS:
					$ingredientCount = $this->getUnsignedVarInt();
					/** @var Item */
					$ingredients = [];
					for($j = 0; $j < $ingredientCount; ++$j){
						$ingredients[] = $this->getSlot();
					}
					$resultCount = $this->getUnsignedVarInt();
					$results = [];
					for($k = 0; $k < $resultCount; ++$k){
						$results[] = $this->getSlot();
					}
					$uuid = $this->getUUID();
					$recipe = new ShapelessRecipe($results[0]); //ouch...
					$recipe->setId($uuid);
					foreach($ingredients as $ingr){
						$recipe->addIngredient($ingr);
					}
					break;
				case self::ENTRY_SHAPED:
					$width = $this->getVarInt();
					$height = $this->getVarInt();
					$count = $width * $height;
					$ingredients = [];
					for($j = 0; $j < $count; ++$j){
						$ingredients[] = $this->getSlot();
					}
					$resultCount = $this->getUnsignedVarInt();
					$results = [];
					for($k = 0; $k < $resultCount; ++$k){
						$results[] = $this->getSlot();
					}
					$uuid = $this->getUUID();
					$recipe = new ShapedRecipe($results[0], $height, $width); //yes, blatant copy-paste...
					$recipe->setId($uuid);
					foreach($ingredients as $ingr){
						$recipe->addIngredient($ingr);
					}
					break;
				case self::ENTRY_FURNACE:
				case self::ENTRY_FURNACE_DATA:
					$inputId = $this->getVarInt();
					if($recipeType === self::ENTRY_FURNACE_DATA){
						$inputData = $this->getVarInt();
					}
					$result = $this->getSlot();
					$recipe = new FurnaceRecipe(Item::get($inputId, $inputData ?? null), $result);
					break;
				case self::ENTRY_MULTI:
					$uuid = $this->getUUID();
					$recipe = new MultiRecipe($uuid);
					break;
				default:
					throw new \UnexpectedValueException("Unhandled recipe type $recipeType!"); //do not continue attempting to decode
			}
			$this->entries[] = $recipe;
		}
		//TODO: serialize to json
	}

	private static function writeEntry($entry, BinaryStream $stream){
		if($entry instanceof ShapelessRecipe){
			return self::writeShapelessRecipe($entry, $stream);
		}elseif($entry instanceof ShapedRecipe){
			return self::writeShapedRecipe($entry, $stream);
		}elseif($entry instanceof FurnaceRecipe){
			return self::writeFurnaceRecipe($entry, $stream);
		}elseif($entry instanceof EnchantmentList){
			return self::writeEnchantList($entry, $stream);
		}

		return -1;
	}

	private static function writeShapelessRecipe(ShapelessRecipe $recipe, BinaryStream $stream){
		$stream->putUnsignedVarInt($recipe->getIngredientCount());
		foreach($recipe->getIngredientList() as $item){
			$stream->putSlot($item);
		}

		$stream->putUnsignedVarInt(1);
		$stream->putSlot($recipe->getResult());

		$stream->putUUID($recipe->getId());

		return CraftingDataPacket::ENTRY_SHAPELESS;
	}

	private static function writeShapedRecipe(ShapedRecipe $recipe, BinaryStream $stream){
		$stream->putVarInt($recipe->getWidth());
		$stream->putVarInt($recipe->getHeight());

		for($z = 0; $z < $recipe->getHeight(); ++$z){
			for($x = 0; $x < $recipe->getWidth(); ++$x){
				$stream->putSlot($recipe->getIngredient($x, $z));
			}
		}

		$stream->putUnsignedVarInt(1);
		$stream->putSlot($recipe->getResult());

		$stream->putUUID($recipe->getId());

		return CraftingDataPacket::ENTRY_SHAPED;
	}

	private static function writeFurnaceRecipe(FurnaceRecipe $recipe, BinaryStream $stream){
		if($recipe->getInput()->getDamage() !== null){ //Data recipe
			$stream->putVarInt($recipe->getInput()->getId());
			$stream->putVarInt($recipe->getInput()->getDamage());
			$stream->putSlot($recipe->getResult());

			return CraftingDataPacket::ENTRY_FURNACE_DATA;
		}else{
			$stream->putVarInt($recipe->getInput()->getId());
			$stream->putSlot($recipe->getResult());

			return CraftingDataPacket::ENTRY_FURNACE;
		}
	}

	private static function writeEnchantList(EnchantmentList $list, BinaryStream $stream){
		//TODO: new recipe type 4 (not enchanting recipe anymore)
		return -1;
		/*
		$stream->putByte($list->getSize());
		for($i = 0; $i < $list->getSize(); ++$i){
			$entry = $list->getSlot($i);
			$stream->putUnsignedVarInt($entry->getCost());
			$stream->putUnsignedVarInt(count($entry->getEnchantments()));
			foreach($entry->getEnchantments() as $enchantment){
				$stream->putUnsignedVarInt($enchantment->getId());
				$stream->putUnsignedVarInt($enchantment->getLevel());
			}
			$stream->putString($entry->getRandomName());
		}

		return CraftingDataPacket::ENTRY_ENCHANT_LIST;
		*/
	}

	public function addShapelessRecipe(ShapelessRecipe $recipe){
		$this->entries[] = $recipe;
	}

	public function addShapedRecipe(ShapedRecipe $recipe){
		$this->entries[] = $recipe;
	}

	public function addFurnaceRecipe(FurnaceRecipe $recipe){
		$this->entries[] = $recipe;
	}

	public function addEnchantList(EnchantmentList $list){
		$this->entries[] = $list;
	}

	public function encode(){
		$this->reset();
		$this->putUnsignedVarInt(count($this->entries));

		$writer = new BinaryStream();
		foreach($this->entries as $d){
			$entryType = self::writeEntry($d, $writer);
			if($entryType >= 0){
				$this->putVarInt($entryType);
				$this->put($writer->getBuffer());
			}else{
				$this->putVarInt(-1);
			}

			$writer->reset();
		}

		$this->putBool($this->cleanRecipes);
	}

}
