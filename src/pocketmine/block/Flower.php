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
use pocketmine\Player;

class Flower extends Flowable{
	const POPPY = 0;
	const BLUE_ORCHID = 1;
	const ALLIUM = 2;
	const AZURE_BLUET = 3;
	const RED_TULIP = 4;
	const ORANGE_TULIP = 5;
	const WHITE_TULIP = 6;
	const PINK_TULIP = 7;
	const OXEYE_DAISY = 8;

	protected $id = self::RED_FLOWER;

	public function __construct($meta = 0){
		$this->meta = $meta;
		
	}

	public function getName(){
		static $names = [
			self::POPPY => "Poppy",
			self::BLUE_ORCHID => "Blue Orchid",
			self::ALLIUM => "Allium",
			self::AZURE_BLUET => "Azure Bluet",
			self::RED_TULIP => "Red Tulip",
			self::ORANGE_TULIP => "Orange Tulip",
			self::WHITE_TULIP => "White Tulip",
			self::PINK_TULIP => "Pink Tulip",
			self::OXEYE_DAISY => "Oxeye Daisy",
			9 => "Unknown Flower",
		];
		return $names[$this->meta & 0x09];
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($down->getId() === 2 or $down->getId() === 3 or $down->getId() === 60){
			$this->getLevel()->setBlock($block, $this, true, true);

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent() === true){
				$this->getLevel()->useBreakOn($this);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}
}