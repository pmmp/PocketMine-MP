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

namespace pocketmine\level\format\generic;

use pocketmine\level\format\ChunkSnapshot;

abstract class BaseChunkSnapshot implements ChunkSnapshot{

	protected $blockId;
	protected $blockData;
	protected $skyLight;
	protected $light;

	protected $x;
	protected $z;
	protected $levelName;
	protected $levelTime;

	public function __construct($x, $z, $levelName, $levelTime, $blockId, $blockData, $skyLight, $light, $heightMap, $biome, $biomeTemp, $biomeRain){
		$this->x = $x;
		$this->z = $z;
		$this->levelName = $levelName;
		$this->levelTime = $levelTime;
		$this->blockId = $blockId;
		$this->blockData = $blockData;
		$this->skyLight = $skyLight;
		$this->light = $light;
	}

	public function getX(){
		return $this->x;
	}

	public function getZ(){
		return $this->z;
	}

	public function getLevelName(){
		return $this->levelName;
	}

	public function getLevelTime(){
		return $this->levelTime;
	}
}