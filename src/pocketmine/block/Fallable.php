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

use pocketmine\entity\Entity;
use pocketmine\math\Facing;

abstract class Fallable extends Solid{

	public function onNearbyBlockChange() : void{
		$down = $this->getSide(Facing::DOWN);
		if($down->getId() === self::AIR or $down instanceof Liquid or $down instanceof Fire){
			$this->level->setBlock($this, BlockFactory::get(Block::AIR));

			$nbt = Entity::createBaseNBT($this->add(0.5, 0, 0.5));
			$nbt->setInt("TileID", $this->getId());
			$nbt->setByte("Data", $this->getDamage());

			$fall = Entity::createEntity("FallingSand", $this->getLevel(), $nbt);

			if($fall !== null){
				$fall->spawnToAll();
			}
		}
	}

	/**
	 * @return null|Block
	 */
	public function tickFalling() : ?Block{
		return null;
	}
}
