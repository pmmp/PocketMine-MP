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
use pocketmine\entity\Location;
use pocketmine\entity\object\Painting;
use pocketmine\entity\object\PaintingMotive;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\PaintingPlaceSound;
use function array_rand;
use function count;

class PaintingItem extends Item{

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		if(Facing::axis($face) === Axis::Y){
			return ItemUseResult::NONE();
		}

		$motives = [];

		$totalDimension = 0;
		foreach(PaintingMotive::getAll() as $motive){
			$currentTotalDimension = $motive->getHeight() + $motive->getWidth();
			if($currentTotalDimension < $totalDimension){
				continue;
			}

			if(Painting::canFit($player->getWorld(), $blockReplace->getPosition(), $face, true, $motive)){
				if($currentTotalDimension > $totalDimension){
					$totalDimension = $currentTotalDimension;
					/*
					 * This drops all motive possibilities smaller than this
					 * We use the total of height + width to allow equal chance of horizontal/vertical paintings
					 * when there is an L-shape of space available.
					 */
					$motives = [];
				}

				$motives[] = $motive;
			}
		}

		if(count($motives) === 0){ //No space available
			return ItemUseResult::NONE();
		}

		/** @var PaintingMotive $motive */
		$motive = $motives[array_rand($motives)];

		$replacePos = $blockReplace->getPosition();
		$clickedPos = $blockClicked->getPosition();

		$entity = new Painting(Location::fromObject($replacePos, $replacePos->getWorld()), $clickedPos, $face, $motive);
		$this->pop();
		$entity->spawnToAll();

		$player->getWorld()->addSound($replacePos->add(0.5, 0.5, 0.5), new PaintingPlaceSound());
		return ItemUseResult::SUCCESS();
	}
}
