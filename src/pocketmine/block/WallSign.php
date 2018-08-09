<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

class WallSign extends SignPost{

	protected $id = self::WALL_SIGN;

	public function getName() : string{
		return "Wall Sign";
	}

	public function onNearbyBlockChange() : void{
		if($this->getSide($this->meta ^ 0x01)->getId() === self::AIR){
			$this->getLevel()->useBreakOn($this);
		}
	}
}
