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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\level;

use pocketmine\level\Level;
use pocketmine\level\Position;

/**
 * An event that is called when a level spawn changes.
 * The previous spawn is included
 */
class SpawnChangeEvent extends LevelEvent{
	public static $handlerList = null;

	/** @var Position */
	private $previousSpawn;

	/**
	 * @param Level    $level
	 * @param Position $previousSpawn
	 */
	public function __construct(Level $level, Position $previousSpawn){
		parent::__construct($level);
		$this->previousSpawn = $previousSpawn;
	}

	/**
	 * @return Position
	 */
	public function getPreviousSpawn(){
		return $this->previousSpawn;
	}
}