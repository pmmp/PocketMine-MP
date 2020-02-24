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

use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\protocol\types\PotionContainerChangeRecipe;
use pocketmine\network\mcpe\protocol\types\PotionTypeRecipe;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;
#ifndef COMPILE
use pocketmine\utils\Binary;
#endif
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
	/** @var PotionTypeRecipe[] */
	public $potionTypeRecipes = [];
	/** @var PotionContainerChangeRecipe[] */
	public $potionContainerRecipes = [];
	/** @var bool */
	public $cleanRecipes = false;

	/** @var mixed[][] */
	public $decodedEntries = [];

	protected function decodePayload() : void{
		$this->decodedEntries = [];
		$recipeCount = $this->buf->getUnsignedVarInt();
		for($i = 0; $i < $recipeCount; ++$i){
			$entry = [];
			$entry["type"] = $recipeType = $this->buf->getVarInt();

			switch($recipeType){
				case self::ENTRY_SHAPELESS:
				case self::ENTRY_SHULKER_BOX:
				case self::ENTRY_SHAPELESS_CHEMISTRY:
					$entry["recipe_id"] = $this->buf->getString();
					$ingredientCount = $this->buf->getUnsignedVarInt();
					/** @var Item */
					$entry["input"] = [];
					for($j = 0; $j < $ingredientCount; ++$j){
						$entry["input"][] = $in = $this->buf->getRecipeIngredient();
						$in->setCount(1); //TODO HACK: they send a useless count field which breaks the PM crafting system because it isn't always 1
					}
					$resultCount = $this->buf->getUnsignedVarInt();
					$entry["output"] = [];
					for($k = 0; $k < $resultCount; ++$k){
						$entry["output"][] = $this->buf->getSlot();
					}
					$entry["uuid"] = $this->buf->getUUID()->toString();
					$entry["block"] = $this->buf->getString();
					$entry["priority"] = $this->buf->getVarInt();

					break;
				case self::ENTRY_SHAPED:
				case self::ENTRY_SHAPED_CHEMISTRY:
					$entry["recipe_id"] = $this->buf->getString();
					$entry["width"] = $this->buf->getVarInt();
					$entry["height"] = $this->buf->getVarInt();
					$count = $entry["width"] * $entry["height"];
					$entry["input"] = [];
					for($j = 0; $j < $count; ++$j){
						$entry["input"][] = $in = $this->buf->getRecipeIngredient();
						$in->setCount(1); //TODO HACK: they send a useless count field which breaks the PM crafting system
					}
					$resultCount = $this->buf->getUnsignedVarInt();
					$entry["output"] = [];
					for($k = 0; $k < $resultCount; ++$k){
						$entry["output"][] = $this->buf->getSlot();
					}
					$entry["uuid"] = $this->buf->getUUID()->toString();
					$entry["block"] = $this->buf->getString();
					$entry["priority"] = $this->buf->getVarInt();

					break;
				case self::ENTRY_FURNACE:
				case self::ENTRY_FURNACE_DATA:
					$inputId = $this->buf->getVarInt();
					$inputData = -1;
					if($recipeType === self::ENTRY_FURNACE_DATA){
						$inputData = $this->buf->getVarInt();
						if($inputData === 0x7fff){
							$inputData = -1;
						}
					}
					try{
						$entry["input"] = ItemFactory::get($inputId, $inputData);
					}catch(\InvalidArgumentException $e){
						throw new BadPacketException($e->getMessage(), 0, $e);
					}
					$entry["output"] = $out = $this->buf->getSlot();
					if($out->getMeta() === 0x7fff){
						$entry["output"] = ItemFactory::get($out->getId(), 0); //TODO HACK: some 1.12 furnace recipe outputs have wildcard damage values
					}
					$entry["block"] = $this->buf->getString();

					break;
				case self::ENTRY_MULTI:
					$entry["uuid"] = $this->buf->getUUID()->toString();
					break;
				default:
					throw new BadPacketException("Unhandled recipe type $recipeType!"); //do not continue attempting to decode
			}
			$this->decodedEntries[] = $entry;
		}
		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$input = $this->buf->getVarInt();
			$ingredient = $this->buf->getVarInt();
			$output = $this->buf->getVarInt();
			$this->potionTypeRecipes[] = new PotionTypeRecipe($input, $ingredient, $output);
		}
		for($i = 0, $count = $this->buf->getUnsignedVarInt(); $i < $count; ++$i){
			$input = $this->buf->getVarInt();
			$ingredient = $this->buf->getVarInt();
			$output = $this->buf->getVarInt();
			$this->potionContainerRecipes[] = new PotionContainerChangeRecipe($input, $ingredient, $output);
		}
		$this->cleanRecipes = $this->buf->getBool();
	}

	/**
	 * @param object              $entry
	 */
	private static function writeEntry($entry, NetworkBinaryStream $stream, int $pos) : int{
		if($entry instanceof ShapelessRecipe){
			return self::writeShapelessRecipe($entry, $stream, $pos);
		}elseif($entry instanceof ShapedRecipe){
			return self::writeShapedRecipe($entry, $stream, $pos);
		}elseif($entry instanceof FurnaceRecipe){
			return self::writeFurnaceRecipe($entry, $stream);
		}
		//TODO: add MultiRecipe

		return -1;
	}

	private static function writeShapelessRecipe(ShapelessRecipe $recipe, NetworkBinaryStream $stream, int $pos) : int{
		$stream->putString(Binary::writeInt($pos)); //some kind of recipe ID, doesn't matter what it is as long as it's unique
		$stream->putUnsignedVarInt($recipe->getIngredientCount());
		foreach($recipe->getIngredientList() as $item){
			$stream->putRecipeIngredient($item);
		}

		$results = $recipe->getResults();
		$stream->putUnsignedVarInt(count($results));
		foreach($results as $item){
			$stream->putSlot($item);
		}

		$stream->put(str_repeat("\x00", 16)); //Null UUID
		$stream->putString("crafting_table"); //TODO: blocktype (no prefix) (this might require internal API breaks)
		$stream->putVarInt(50); //TODO: priority

		return CraftingDataPacket::ENTRY_SHAPELESS;
	}

	private static function writeShapedRecipe(ShapedRecipe $recipe, NetworkBinaryStream $stream, int $pos) : int{
		$stream->putString(Binary::writeInt($pos)); //some kind of recipe ID, doesn't matter what it is as long as it's unique
		$stream->putVarInt($recipe->getWidth());
		$stream->putVarInt($recipe->getHeight());

		for($z = 0; $z < $recipe->getHeight(); ++$z){
			for($x = 0; $x < $recipe->getWidth(); ++$x){
				$stream->putRecipeIngredient($recipe->getIngredient($x, $z));
			}
		}

		$results = $recipe->getResults();
		$stream->putUnsignedVarInt(count($results));
		foreach($results as $item){
			$stream->putSlot($item);
		}

		$stream->put(str_repeat("\x00", 16)); //Null UUID
		$stream->putString("crafting_table"); //TODO: blocktype (no prefix) (this might require internal API breaks)
		$stream->putVarInt(50); //TODO: priority

		return CraftingDataPacket::ENTRY_SHAPED;
	}

	private static function writeFurnaceRecipe(FurnaceRecipe $recipe, NetworkBinaryStream $stream) : int{
		$stream->putVarInt($recipe->getInput()->getId());
		$result = CraftingDataPacket::ENTRY_FURNACE;
		if(!$recipe->getInput()->hasAnyDamageValue()){ //Data recipe
			$stream->putVarInt($recipe->getInput()->getMeta());
			$result = CraftingDataPacket::ENTRY_FURNACE_DATA;
		}
		$stream->putSlot($recipe->getResult());
		$stream->putString("furnace"); //TODO: blocktype (no prefix) (this might require internal API breaks)
		return $result;
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
		$this->buf->putUnsignedVarInt(count($this->entries));

		$writer = new NetworkBinaryStream();
		$counter = 0;
		foreach($this->entries as $d){
			$entryType = self::writeEntry($d, $writer, $counter++);
			if($entryType >= 0){
				$this->buf->putVarInt($entryType);
				$this->buf->put($writer->getBuffer());
			}else{
				$this->buf->putVarInt(-1);
			}

			$writer->reset();
		}
		$this->buf->putUnsignedVarInt(count($this->potionTypeRecipes));
		foreach($this->potionTypeRecipes as $recipe){
			$this->buf->putVarInt($recipe->getInputPotionType());
			$this->buf->putVarInt($recipe->getIngredientItemId());
			$this->buf->putVarInt($recipe->getOutputPotionType());
		}
		$this->buf->putUnsignedVarInt(count($this->potionContainerRecipes));
		foreach($this->potionContainerRecipes as $recipe){
			$this->buf->putVarInt($recipe->getInputItemId());
			$this->buf->putVarInt($recipe->getIngredientItemId());
			$this->buf->putVarInt($recipe->getOutputItemId());
		}

		$this->buf->putBool($this->cleanRecipes);
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleCraftingData($this);
	}
}
