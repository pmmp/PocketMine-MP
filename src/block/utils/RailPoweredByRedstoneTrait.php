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

use pocketmine\block\BlockLegacyMetadata;

trait RailPoweredByRedstoneTrait{
	use PoweredByRedstoneTrait;

	public function readStateFromData(int $id, int $stateMeta) : void{
		parent::readStateFromData($id, $stateMeta & ~BlockLegacyMetadata::REDSTONE_RAIL_FLAG_POWERED);
		$this->powered = ($stateMeta & BlockLegacyMetadata::REDSTONE_RAIL_FLAG_POWERED) !== 0;
	}

	protected function writeStateToMeta() : int{
		//TODO: railShape won't be plain metadata in the future
		return parent::writeStateToMeta() | ($this->powered ? BlockLegacyMetadata::REDSTONE_RAIL_FLAG_POWERED : 0);
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}
}
