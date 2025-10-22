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
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function mt_rand;

class Netherrack extends Opaque{

	public function burnsForever() : bool{
		return true;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer){
			if($this->tryTransform()){
				$item->pop();
				return true;
			}
		}
		return false;
	}

	private function tryTransform() : bool{
		$world = $this->position->getWorld();
		$pos = $this->position;

		if(!$world->getBlock($pos->up())->isTransparent()){
			return false;
		}

		$hasWarpedNylium = false;
		$hasCrimsonNylium = false;

		for($x = -1; $x <= 1; $x++){
			for($y = -1; $y <= 1; $y++){
				for($z = -1; $z <= 1; $z++){
					$blockTypeId = $world->getBlock($pos->add($x, $y, $z))->getTypeId();

					if($blockTypeId === BlockTypeIds::WARPED_NYLIUM){
						$hasWarpedNylium = true;
					}elseif($blockTypeId === BlockTypeIds::CRIMSON_NYLIUM){
						$hasCrimsonNylium = true;
					}

					if($hasWarpedNylium && $hasCrimsonNylium){
						break 3;
					}
				}
			}
		}

		if(!$hasWarpedNylium && !$hasCrimsonNylium){
			return false;
		}

		$world->setBlock($pos, match(true){
			$hasWarpedNylium && $hasCrimsonNylium => (mt_rand(0, 1) === 0 ? VanillaBlocks::WARPED_NYLIUM() : VanillaBlocks::CRIMSON_NYLIUM()),
			$hasWarpedNylium => VanillaBlocks::WARPED_NYLIUM(),
			$hasCrimsonNylium => VanillaBlocks::CRIMSON_NYLIUM(),
		});
		return true;
	}
}
