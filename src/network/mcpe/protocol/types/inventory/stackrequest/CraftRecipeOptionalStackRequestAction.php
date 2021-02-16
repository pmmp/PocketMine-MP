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

namespace pocketmine\network\mcpe\protocol\types\inventory\stackrequest;

use pocketmine\network\mcpe\protocol\serializer\PacketSerializer;

/**
 * Renames an item in an anvil, or map on a cartography table.
 */
final class CraftRecipeOptionalStackRequestAction extends ItemStackRequestAction{

	/** @var int */
	private $recipeId;
	/** @var int */
	private $filterStringIndex;

	public function __construct(int $type, int $filterStringIndex){
		$this->recipeId = $type;
		$this->filterStringIndex = $filterStringIndex;
	}

	public function getRecipeId() : int{ return $this->recipeId; }

	public function getFilterStringIndex() : int{ return $this->filterStringIndex; }

	public static function getTypeId() : int{ return ItemStackRequestActionType::CRAFTING_RECIPE_OPTIONAL; }

	public static function read(PacketSerializer $in) : self{
		$recipeId = $in->readGenericTypeNetworkId();
		$filterStringIndex = $in->getLInt();
		return new self($recipeId, $filterStringIndex);
	}

	public function write(PacketSerializer $out) : void{
		$out->writeGenericTypeNetworkId($this->recipeId);
		$out->putLInt($this->filterStringIndex);
	}
}
