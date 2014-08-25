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

namespace pocketmine\block;


/**
 * Air block
 */
class Air extends Transparent{
	public $isActivable = false;
	public $breakable = false;
	public $isFlowable = true;
	public $isTransparent = true;
	public $isReplaceable = true;
	public $isPlaceable = false;
	public $hasPhysics = false;
	public $isSolid = false;
	public $isFullBlock = true;
	protected $id = self::AIR;
	protected $meta = 0;
	protected $name = "Air";
	protected $hardness = 0;

	public function __construct(){

	}

	public function getBoundingBox(){
		return null;
	}

}