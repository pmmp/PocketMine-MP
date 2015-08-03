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
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\utils\Binary;

class CraftingDataPacket extends DataPacket{
	const NETWORK_ID = Info::CRAFTING_DATA_PACKET;

	const ENTRY_SHAPELESS = 0;
	const ENTRY_SHAPED = 1;
	const ENTRY_FURNACE = 2;
	const ENTRY_FURNACE_DATA = 3;
	const ENTRY_ENCHANT = 4;

	/** @var object[] */
	public $entries = [];
	public $cleanRecipes = false;

	public function writeEntry($entry){
		if($entry instanceof ShapelessRecipe){
			$this->writeShapelessRecipe($entry);
		}elseif($entry instanceof ShapedRecipe){
			$this->writeShapedRecipe($entry);
		}elseif($entry instanceof FurnaceRecipe){
			$this->writeFurnaceRecipe($entry);
		}
	}

	private function writeShapelessRecipe(ShapelessRecipe $recipe){
		$this->putInt(CraftingDataPacket::ENTRY_SHAPELESS);

		$this->putInt($recipe->getIngredientCount());
		foreach($recipe->getIngredientList() as $item){
			$this->putSlot($item);
		}

		$this->putInt(1);
		$this->putSlot($recipe->getResult());
	}

	private function writeShapedRecipe(ShapedRecipe $recipe){
		$this->putInt(CraftingDataPacket::ENTRY_SHAPED);
	}

	private function writeFurnaceRecipe(FurnaceRecipe $recipe){
		if($recipe->getInput()->getDamage() !== 0){ //Data recipe
			$this->putInt(CraftingDataPacket::ENTRY_FURNACE_DATA);
			$this->putInt(($recipe->getInput()->getId() << 16) | ($recipe->getInput()->getDamage()));
			$this->putSlot($recipe->getResult());
		}else{
			$this->putInt(CraftingDataPacket::ENTRY_FURNACE);
			$this->putInt($recipe->getInput()->getId());
			$this->putSlot($recipe->getResult());
		}
	}

	private function writeEnchant(){
		$entry = Binary::writeInt(CraftingDataPacket::ENTRY_ENCHANT);
		//TODO
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

	public function addEnchant(){
		//TODO
	}

	public function clean(){
		$this->entries = [];
		return parent::clean();
	}

	public function decode(){

	}

	public function encode(){
		$this->reset();
		$this->putInt(count($this->entries));
		foreach($this->entries as $d){
			$this->writeEntry($d);
		}

		$this->putByte($this->cleanRecipes ? 1 : 0);
	}

}
