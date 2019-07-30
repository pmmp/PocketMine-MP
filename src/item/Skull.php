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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\utils\SkullType;
use pocketmine\block\VanillaBlocks;

class Skull extends Item{

	/** @var SkullType */
	private $skullType;

	public function __construct(int $id, int $variant, string $name, SkullType $skullType){
		parent::__construct($id, $variant, $name);
		$this->skullType = $skullType;
	}

	public function getBlock() : Block{
		return VanillaBlocks::MOB_HEAD();
	}

	public function getSkullType() : SkullType{
		return $this->skullType;
	}
}
