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
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Hoe extends TieredTool{

	public function getToolType() : int{
		return Tool::TYPE_HOE;
	}

	public function isHoe(){
		return $this->tier;
	}

	public function onClickBlock(Player $player, Block $block, Block $blockClicked, int $face, float $fx, float $fy, float $fz){
		if($blockClicked->canBeTilled() and $face !== Vector3::SIDE_DOWN){ //Can click on any side to till, except bottom.
			$player->getLevel()->setBlock($blockClicked, Block::get(Block::FARMLAND), true, true);
			$this->applyDamage(1);

			return true;
		}

		return false;
	}

	public function onAttackEntity(Entity $entity, Player $player = null) : bool{
		return $this->applyDamage(1);
	}
}