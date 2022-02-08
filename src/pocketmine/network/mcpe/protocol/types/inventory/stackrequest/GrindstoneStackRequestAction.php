<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\NetworkBinaryStream;

/**
 * Repair and/or remove enchantments from an item in a grindstone.
 */
final class GrindstoneStackRequestAction extends ItemStackRequestAction{
	public static function getTypeId() : int{
		return ItemStackRequestActionType::CRAFTING_GRINDSTONE;
	}

	public function __construct(
		private int $recipeId,
		private int $repairCost
	){}

	public function getRecipeId() : int{ return $this->recipeId; }

	/** WARNING: This may be negative */
	public function getRepairCost() : int{ return $this->repairCost; }

	public static function read(NetworkBinaryStream $in) : self{
		$recipeId = $in->readGenericTypeNetworkId();
		$repairCost = $in->getVarInt(); //WHY!!!!

		return new self($recipeId, $repairCost);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->writeGenericTypeNetworkId($this->recipeId);
		$out->putVarInt($this->repairCost);
	}
}
