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

namespace pocketmine\network\mcpe\protocol\types\recipe;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use Ramsey\Uuid\UuidInterface;
use function count;

final class ShapelessRecipe extends RecipeWithTypeId{

	/** @var string */
	private $recipeId;
	/** @var RecipeIngredient[] */
	private $inputs;
	/** @var ItemStack[] */
	private $outputs;
	/** @var UuidInterface */
	private $uuid;
	/** @var string */
	private $blockName;
	/** @var int */
	private $priority;
	/** @var int */
	private $recipeNetId;

	/**
	 * @param RecipeIngredient[] $inputs
	 * @param ItemStack[]        $outputs
	 */
	public function __construct(int $typeId, string $recipeId, array $inputs, array $outputs, UuidInterface $uuid, string $blockName, int $priority, int $recipeNetId){
		parent::__construct($typeId);
		$this->recipeId = $recipeId;
		$this->inputs = $inputs;
		$this->outputs = $outputs;
		$this->uuid = $uuid;
		$this->blockName = $blockName;
		$this->priority = $priority;
		$this->recipeNetId = $recipeNetId;
	}

	public function getRecipeId() : string{
		return $this->recipeId;
	}

	/**
	 * @return RecipeIngredient[]
	 */
	public function getInputs() : array{
		return $this->inputs;
	}

	/**
	 * @return ItemStack[]
	 */
	public function getOutputs() : array{
		return $this->outputs;
	}

	public function getUuid() : UuidInterface{
		return $this->uuid;
	}

	public function getBlockName() : string{
		return $this->blockName;
	}

	public function getPriority() : int{
		return $this->priority;
	}

	public function getRecipeNetId() : int{
		return $this->recipeNetId;
	}

	public static function decode(int $recipeType, PacketSerializer $in) : self{
		$recipeId = $in->getString();
		$input = [];
		for($j = 0, $ingredientCount = $in->getUnsignedVarInt(); $j < $ingredientCount; ++$j){
			$input[] = $in->getRecipeIngredient();
		}
		$output = [];
		for($k = 0, $resultCount = $in->getUnsignedVarInt(); $k < $resultCount; ++$k){
			$output[] = $in->getItemStackWithoutStackId();
		}
		$uuid = $in->getUUID();
		$block = $in->getString();
		$priority = $in->getVarInt();
		$recipeNetId = $in->readGenericTypeNetworkId();

		return new self($recipeType, $recipeId, $input, $output, $uuid, $block, $priority, $recipeNetId);
	}

	public function encode(PacketSerializer $out) : void{
		$out->putString($this->recipeId);
		$out->putUnsignedVarInt(count($this->inputs));
		foreach($this->inputs as $item){
			$out->putRecipeIngredient($item);
		}

		$out->putUnsignedVarInt(count($this->outputs));
		foreach($this->outputs as $item){
			$out->putItemStackWithoutStackId($item);
		}

		$out->putUUID($this->uuid);
		$out->putString($this->blockName);
		$out->putVarInt($this->priority);
		$out->writeGenericTypeNetworkId($this->recipeNetId);
	}
}
