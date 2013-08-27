<?php

/**
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

class OreType{
	public $material, $clusterCount, $clusterSize, $maxHeight, $minHeight;
	
	public function __construct(Block $material, $clusterCount, $clusterSize, $minHeight, $maxHeight){
		$this->material = $material;
		$this->clusterCount = (int) $clusterCount;
		$this->clusterSize = (int) $clusterSize;
		$this->maxHeight = (int) $maxHeight;
		$this->minHeight = (int) $minHeight;
	}
}