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
