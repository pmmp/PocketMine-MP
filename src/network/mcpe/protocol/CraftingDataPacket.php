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

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\MultiRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\PotionContainerChangeRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\PotionTypeRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\RecipeWithTypeId;
use pocketmine\network\mcpe\protocol\types\recipe\ShapedRecipe;
use pocketmine\network\mcpe\protocol\types\recipe\ShapelessRecipe;
use function count;

class CraftingDataPacket extends DataPacket implements ClientboundPacket{
	public const NETWORK_ID = ProtocolInfo::CRAFTING_DATA_PACKET;

	public const ENTRY_SHAPELESS = 0;
	public const ENTRY_SHAPED = 1;
	public const ENTRY_FURNACE = 2;
	public const ENTRY_FURNACE_DATA = 3;
	public const ENTRY_MULTI = 4;
	public const ENTRY_SHULKER_BOX = 5;
	public const ENTRY_SHAPELESS_CHEMISTRY = 6;
	public const ENTRY_SHAPED_CHEMISTRY = 7;

	/** @var RecipeWithTypeId[] */
	public $entries = [];
	/** @var PotionTypeRecipe[] */
	public $potionTypeRecipes = [];
	/** @var PotionContainerChangeRecipe[] */
	public $potionContainerRecipes = [];
	/** @var bool */
	public $cleanRecipes = false;

	protected function decodePayload(PacketSerializer $in) : void{
		$recipeCount = $in->getUnsignedVarInt();
		for($i = 0; $i < $recipeCount; ++$i){
			$recipeType = $in->getVarInt();

			switch($recipeType){
				case self::ENTRY_SHAPELESS:
				case self::ENTRY_SHULKER_BOX:
				case self::ENTRY_SHAPELESS_CHEMISTRY:
					$this->entries[] = ShapelessRecipe::decode($recipeType, $in);
					break;
				case self::ENTRY_SHAPED:
				case self::ENTRY_SHAPED_CHEMISTRY:
					$this->entries[] = ShapedRecipe::decode($recipeType, $in);
					break;
				case self::ENTRY_FURNACE:
				case self::ENTRY_FURNACE_DATA:
					$this->entries[] = FurnaceRecipe::decode($recipeType, $in);
					break;
				case self::ENTRY_MULTI:
					$this->entries[] = MultiRecipe::decode($recipeType, $in);
					break;
				default:
					throw new PacketDecodeException("Unhandled recipe type $recipeType!"); //do not continue attempting to decode
			}
		}
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$inputId = $in->getVarInt();
			$inputMeta = $in->getVarInt();
			$ingredientId = $in->getVarInt();
			$ingredientMeta = $in->getVarInt();
			$outputId = $in->getVarInt();
			$outputMeta = $in->getVarInt();
			$this->potionTypeRecipes[] = new PotionTypeRecipe($inputId, $inputMeta, $ingredientId, $ingredientMeta, $outputId, $outputMeta);
		}
		for($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i){
			$input = $in->getVarInt();
			$ingredient = $in->getVarInt();
			$output = $in->getVarInt();
			$this->potionContainerRecipes[] = new PotionContainerChangeRecipe($input, $ingredient, $output);
		}
		$this->cleanRecipes = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void{
		$out->putUnsignedVarInt(count($this->entries));
		foreach($this->entries as $d){
			$out->putVarInt($d->getTypeId());
			$d->encode($out);
		}
		$out->putUnsignedVarInt(count($this->potionTypeRecipes));
		foreach($this->potionTypeRecipes as $recipe){
			$out->putVarInt($recipe->getInputItemId());
			$out->putVarInt($recipe->getInputItemMeta());
			$out->putVarInt($recipe->getIngredientItemId());
			$out->putVarInt($recipe->getIngredientItemMeta());
			$out->putVarInt($recipe->getOutputItemId());
			$out->putVarInt($recipe->getOutputItemMeta());
		}
		$out->putUnsignedVarInt(count($this->potionContainerRecipes));
		foreach($this->potionContainerRecipes as $recipe){
			$out->putVarInt($recipe->getInputItemId());
			$out->putVarInt($recipe->getIngredientItemId());
			$out->putVarInt($recipe->getOutputItemId());
		}

		$out->putBool($this->cleanRecipes);
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleCraftingData($this);
	}
}
