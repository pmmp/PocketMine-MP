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

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;

class Carpet extends Flowable{

	protected $id = self::CARPET;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 0.1;
	}

	public function isSolid(){
		return true;
	}

	public function getName(){
		static $names = [
			0 => "White Carpet",
			1 => "Orange Carpet",
			2 => "Magenta Carpet",
			3 => "Light Blue Carpet",
			4 => "Yellow Carpet",
			5 => "Lime Carpet",
			6 => "Pink Carpet",
			7 => "Gray Carpet",
			8 => "Light Gray Carpet",
			9 => "Cyan Carpet",
			10 => "Purple Carpet",
			11 => "Blue Carpet",
			12 => "Brown Carpet",
			13 => "Green Carpet",
			14 => "Red Carpet",
			15 => "Black Carpet",
		];
		return $names[$this->meta & 0x0f];
	}

	protected function recalculateBoundingBox(){

		return new AxisAlignedBB(
			$this->x,
			$this->y,
			$this->z,
			$this->x + 1,
			$this->y + 0.0625,
			$this->z + 1
		);
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($down->getId() !== self::AIR){
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->getId() === self::AIR){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

}