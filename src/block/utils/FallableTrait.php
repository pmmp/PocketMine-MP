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
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\math\Facing;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use pocketmine\world\sound\Sound;

/**
 * This trait handles falling behaviour for blocks that need them.
 * TODO: convert this into a dynamic component
 * @see Fallable
 */
trait FallableTrait{

	abstract protected function getPosition() : Position;

	public function onNearbyBlockChange() : void{
		$pos = $this->getPosition();
		$world = $pos->getWorld();
		$down = $world->getBlock($pos->getSide(Facing::DOWN));
		if($down->canBeReplaced()){
			$world->setBlock($pos, VanillaBlocks::AIR());

			$block = $this;
			if(!($block instanceof Block)) throw new AssumptionFailedError(__TRAIT__ . " should only be used by Blocks");

			$fall = new FallingBlock(Location::fromObject($pos->add(0.5, 0, 0.5), $world), $block);
			$fall->spawnToAll();
		}
	}

	public function tickFalling() : ?Block{
		return null;
	}

	public function onHitGround(FallingBlock $blockEntity) : bool{
		return true;
	}

	public function getFallDamagePerBlock() : float{
		return 0.0;
	}

	public function getMaxFallDamage() : float{
		return 0.0;
	}

	public function getLandSound() : ?Sound{
		return null;
	}
}
