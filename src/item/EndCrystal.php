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

use pocketmine\block\Air;
use pocketmine\block\Bedrock;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\Location;
use pocketmine\entity\object\EnderCrystal;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function count;

class EndCrystal extends Item{

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		if($blockClicked->getId() == BlockLegacyIds::OBSIDIAN || $blockClicked instanceof Bedrock){
			$pos = $blockClicked->getPosition();
			$world = $pos->getWorld();
			$entities = $world->getNearbyEntities(new AxisAlignedBB($pos->getX(), $pos->getY(), $pos->getZ(), $pos->getX() + 1, $pos->getY() + 2, $pos->getZ() + 1));
			if(count($entities) === 0 && $world->getBlock($pos->up()) instanceof Air && $world->getBlock($pos->up(2)) instanceof Air){
				$crystal = new EnderCrystal(Location::fromObject($pos->add(0.5, 1.5, 0.5), $world));
				$crystal->spawnToAll();

				$this->pop();
				return ItemUseResult::SUCCESS();
			}
		}
		return ItemUseResult::NONE();
	}
}
