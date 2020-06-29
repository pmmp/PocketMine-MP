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

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Fire;
use pocketmine\block\Liquid;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;

/**
 * This trait handles falling behaviour for blocks that need them.
 * TODO: convert this into a dynamic component
 * @see Fallable
 */
trait FallableTrait{

	abstract protected function getPos() : Position;

	abstract protected function getId() : int;

	abstract protected function getMeta() : int;

	public function onNearbyBlockChange() : void{
		$pos = $this->getPos();
		$down = $pos->getWorld()->getBlock($pos->getSide(Facing::DOWN));
		if($down->getId() === BlockLegacyIds::AIR or $down instanceof Liquid or $down instanceof Fire){
			$pos->getWorld()->setBlock($pos, VanillaBlocks::AIR());

			$block = $this;
			if(!($block instanceof Block)) throw new AssumptionFailedError(__TRAIT__ . " should only be used by Blocks");

			$fall = new FallingBlock(Location::fromObject($pos->add(0.5, 0, 0.5), $pos->getWorld()), $block);
			$fall->spawnToAll();
		}
	}
}
