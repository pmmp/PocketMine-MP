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

class AirBlock extends TransparentBlock{
	public function __construct(){
		parent::__construct(AIR, 0, "Air");
		$this->isActivable = false;
		$this->breakable = false;
		$this->isFlowable = true;
		$this->isTransparent = true;
		$this->isReplaceable = true;
		$this->isPlaceable = false;
		$this->hasPhysics = false;
		$this->isSolid = false;
		$this->isFullBlock = true;
		$this->hardness = 0;
		
	}
	
}