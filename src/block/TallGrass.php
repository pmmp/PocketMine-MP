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

use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function mt_rand;

class TallGrass extends Flowable{

	public function canBeReplaced() : bool{
		return true;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$down = $this->getSide(Facing::DOWN)->getId();
		if($down === BlockLegacyIds::GRASS || $down === BlockLegacyIds::DIRT || $down === BlockLegacyIds::FARMLAND || $down === BlockLegacyIds::PODZOL || $down === BlockLegacyIds::MYCELIUM){
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide(Facing::DOWN)->isTransparent()){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function getDropsForIncompatibleTool(Item $item) : array{
		if(mt_rand(0, 15) === 0){
			return [
				VanillaItems::WHEAT_SEEDS()
			];
		}

		return [];
	}

	public function getFlameEncouragement() : int{
		return 60;
	}

	public function getFlammability() : int{
		return 100;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($item instanceof Fertilizer){
			$item->pop();
			$blockAbove = $this->getPosition()->getWorld()->getBlockAt($this->position->x, $this->position->y + 1, $this->position->z);

			if($blockAbove->getId() === BlockLegacyIds::AIR){
				$doubleBlock = match ($this->getMeta()) {
					BlockLegacyMetadata::TALLGRASS_NORMAL => VanillaBlocks::DOUBLE_TALLGRASS(),
					BlockLegacyMetadata::TALLGRASS_FERN => VanillaBlocks::LARGE_FERN(),
				};
				$this->position->getWorld()->setBlock($this->position, $doubleBlock, true);
				$this->position->getWorld()->setBlock($this->getSide(Facing::UP)->getPosition(), $doubleBlock->setTop(true), true);
			}
			return true;
		}

		return false;
	}
}
