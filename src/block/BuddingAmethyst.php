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

use pocketmine\block\utils\AmethystTrait;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use function array_rand;
use function mt_rand;

final class BuddingAmethyst extends Opaque{
	use AmethystTrait;

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if(mt_rand(1, 5) === 1){
			$face = Facing::ALL[array_rand(Facing::ALL)];

			$adjacent = $this->getSide($face);
			//TODO: amethyst buds can spawn in water - we need waterlogging support for this

			$newStage = null;

			if($adjacent->getTypeId() === BlockTypeIds::AIR){
				$newStage = AmethystCluster::STAGE_SMALL_BUD;
			}elseif(
				$adjacent->getTypeId() === BlockTypeIds::AMETHYST_CLUSTER &&
				$adjacent instanceof AmethystCluster &&
				$adjacent->getStage() < AmethystCluster::STAGE_CLUSTER &&
				$adjacent->getFacing() === $face
			){
				$newStage = $adjacent->getStage() + 1;
			}
			if($newStage !== null){
				BlockEventHelper::grow($adjacent, VanillaBlocks::AMETHYST_CLUSTER()->setStage($newStage)->setFacing($face), null);
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [];
	}
}
