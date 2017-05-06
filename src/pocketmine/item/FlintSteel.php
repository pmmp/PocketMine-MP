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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\Solid;
use pocketmine\Player;

class FlintSteel extends Tool{

	public function getToolType() : int{
		return 0;
	}

	public function onClickBlock(Player $player, Block $block, Block $blockClicked, int $face, float $fx, float $fy, float $fz){
		if(($block->getId() === Block::AIR or $block->getId() === Block::FIRE) and ($blockClicked instanceof Solid)){
			//MCPE deals damage to flint and steel if used on an existing fire.
			$player->getLevel()->setBlock($block, Block::get(Block::FIRE), true);
			$this->applyDamage(1);

			//TODO: nether portals

			return true;
		}

		return false;
	}
}