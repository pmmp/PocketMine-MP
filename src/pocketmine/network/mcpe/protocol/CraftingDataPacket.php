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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use pocketmine\inventory\FurnaceRecipe;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\NetworkBinaryStream;
use function count;
use function str_repeat;

class CraftingDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CRAFTING_DATA_PACKET;

	public const ENTRY_SHAPELESS = 0;
	public const ENTRY_SHAPED = 1;
	public const ENTRY_FURNACE = 2;
	public const ENTRY_FURNACE_DATA = 3;
	public const ENTRY_MULTI = 4; //TODO
	public const ENTRY_SHULKER_BOX = 5; //TODO
	public const ENTRY_SHAPELESS_CHEMISTRY = 6; //TODO
	public const ENTRY_SHAPED_CHEMISTRY = 7; //TODO

	/** @var object[] */
	public $entries = [];
	/** @var bool */
	public $cleanRecipes = false;

	public $decodedEntries = [];

	protected function decodePayload() : void{
		$this->decodedEntries = [];
		$recipeCount = $this->getUnsignedVarInt();
		for($i = 0; $i < $recipeCount; ++$i){
			$entry = [];
			$entry["type"] = $recipeType = $this->getVarInt();

			switch($recipeType){
				case self::ENTRY_SHAPELESS:
				case self::ENTRY_SHULKER_BOX:
				case self::ENTRY_SHAPELESS_CHEMISTRY:
					$ingredientCount = $this->getUnsignedVarInt();
					/** @var Item */
					$entry["input"] = [];
					for($j = 0; $j < $ingredientCount; ++$j){
						$entry["input"][] = $this->getSlot();
					}
					$resultCount = $this->getUnsignedVarInt();
					$entry["output"] = [];
					for($k = 0; $k < $resultCount; ++$k){
						$entry["output"][] = $this->getSlot();
					}
					$entry["uuid"] = $this->getUUID()->toString();

					break;
				case self::ENTRY_SHAPED:
				case self::ENTRY_SHAPED_CHEMISTRY:
					$entry["width"] = $this->getVarInt();
					$entry["height"] = $this->getVarInt();
					$count = $entry["width"] * $entry["height"];
					$entry["input"] = [];
					for($j = 0; $j < $count; ++$j){
						$entry["input"][] = $this->getSlot();
					}
					$resultCount = $this->getUnsignedVarInt();
					$entry["output"] = [];
					for($k = 0; $k < $resultCount; ++$k){
						$entry["output"][] = $this->getSlot();
					}
					$entry["uuid"] = $this->getUUID()->toString();
					break;
				case self::ENTRY_FURNACE:
				case self::ENTRY_FURNACE_DATA:
					$entry["inputId"] = $this->getVarInt();
					if($recipeType === self::ENTRY_FURNACE_DATA){
						$entry["inputDamage"] = $this->getVarInt();
					}
					$entry["output"] = $this->getSlot();
					break;
				case self::ENTRY_MULTI:
					$entry["uuid"] = $this->getUUID()->toString();
					break;
				default:
					throw new BadPacketException("Unhandled recipe type $recipeType!"); //do not continue attempting to decode
			}
			$this->decodedEntries[] = $entry;
		}
		$this->getBool(); //cleanRecipes
	}

	private static function writeEntry($entry, NetworkBinaryStream $stream) : int{
		if($entry instanceof ShapelessRecipe){
			return self::writeShapelessRecipe($entry, $stream);
		}elseif($entry instanceof ShapedRecipe){
			return self::writeShapedRecipe($entry, $stream);
		}elseif($entry instanceof FurnaceRecipe){
			return self::writeFurnaceRecipe($entry, $stream);
		}
		//TODO: add MultiRecipe

		return -1;
	}

	private static function writeShapelessRecipe(ShapelessRecipe $recipe, NetworkBinaryStream $stream) : int{
		$stream->putUnsignedVarInt($recipe->getIngredientCount());
		foreach($recipe->getIngredientList() as $item){
			$stream->putSlot($item);
		}

		$results = $recipe->getResults();
		$stream->putUnsignedVarInt(count($results));
		foreach($results as $item){
			$stream->putSlot($item);
		}

		$stream->put(str_repeat("\x00", 16)); //Null UUID

		return CraftingDataPacket::ENTRY_SHAPELESS;
	}

	private static function writeShapedRecipe(ShapedRecipe $recipe, NetworkBinaryStream $stream) : int{
		$stream->putVarInt($recipe->getWidth());
		$stream->putVarInt($recipe->getHeight());

		for($z = 0; $z < $recipe->getHeight(); ++$z){
			for($x = 0; $x < $recipe->getWidth(); ++$x){
				$stream->putSlot($recipe->getIngredient($x, $z));
			}
		}

		$results = $recipe->getResults();
		$stream->putUnsignedVarInt(count($results));
		foreach($results as $item){
			$stream->putSlot($item);
		}

		$stream->put(str_repeat("\x00", 16)); //Null UUID

		return CraftingDataPacket::ENTRY_SHAPED;
	}

	private static function writeFurnaceRecipe(FurnaceRecipe $recipe, NetworkBinaryStream $stream) : int{
		if(!$recipe->getInput()->hasAnyDamageValue()){ //Data recipe
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

	public function addShapelessRecipe(ShapelessRecipe $recipe) : void{
		$this->entries[] = $recipe;
	}

	public function addShapedRecipe(ShapedRecipe $recipe) : void{
		$this->entries[] = $recipe;
	}

	public function addFurnaceRecipe(FurnaceRecipe $recipe) : void{
		$this->entries[] = $recipe;
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->entries));

		$writer = new NetworkBinaryStream();
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

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleCraftingData($this);
	}
}
