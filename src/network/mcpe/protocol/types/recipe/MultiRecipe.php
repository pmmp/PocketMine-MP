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

use pocketmine\network\mcpe\serializer\NetworkBinaryStream;
use pocketmine\utils\UUID;

final class MultiRecipe extends RecipeWithTypeId{

	/** @var UUID */
	private $recipeId;

	public function __construct(int $typeId, UUID $recipeId){
		parent::__construct($typeId);
		$this->recipeId = $recipeId;
	}

	public function getRecipeId() : UUID{
		return $this->recipeId;
	}

	public static function decode(int $typeId, NetworkBinaryStream $in) : self{
		return new self($typeId, $in->getUUID());
	}

	public function encode(NetworkBinaryStream $out) : void{
		$out->putUUID($this->recipeId);
	}
}
