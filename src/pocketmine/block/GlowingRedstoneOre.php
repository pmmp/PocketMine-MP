<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\Player;

class GlowingRedstoneOre extends RedstoneOre{

	protected $id = self::GLOWING_REDSTONE_ORE;

	protected $itemId = self::REDSTONE_ORE;

	public function getName() : string{
		return "Glowing Redstone Ore";
	}

	public function getLightLevel() : int{
		return 9;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		return false;
	}

	public function onNearbyBlockChange() : void{

	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$this->getLevel()->setBlock($this, BlockFactory::get(Block::REDSTONE_ORE, $this->meta), false, false);
	}
}
