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
use pocketmine\data\bedrock\DyeColorIdMap;

trait ColorInMetadataTrait{
	use ColoredTrait;

	/**
	 * @see Block::readStateFromData()
	 */
	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->color = DyeColorIdMap::getInstance()->fromId($stateMeta);
	}

	/**
	 * @see Block::writeStateToMeta()
	 */
	protected function writeStateToMeta() : int{
		return DyeColorIdMap::getInstance()->toId($this->color);
	}

	/**
	 * @see Block::getStateBitmask()
	 */
	public function getStateBitmask() : int{
		return 0b1111;
	}

	/**
	 * @see Block::getNonPersistentStateBitmask()
	 */
	public function getNonPersistentStateBitmask() : int{
		return 0;
	}
}
