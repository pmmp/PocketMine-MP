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

namespace pocketmine\block\tile;

use pocketmine\nbt\tag\CompoundTag;

/**
 * @deprecated
 * As per the wiki, this is an old hack to force daylight sensors to get updated every game tick. This is necessary to
 * ensure that the daylight sensor's power output is always up to date with the current world time.
 * It's theoretically possible to implement this without a blockentity, but this is here to ensure that vanilla can
 * understand daylight sensors in worlds created by PM.
 */
class DaylightSensor extends Tile{

	public function readSaveData(CompoundTag $nbt) : void{

	}

	protected function writeSaveData(CompoundTag $nbt) : void{

	}
}
