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

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;

class TallGrass extends Flowable{

	protected $id = self::TALL_GRASS;

	public function __construct($meta = 1){
		$this->meta = $meta;
	}

	public function canBeReplaced(){
		return true;
	}

	public function getName(){
		static $names = [
			0 => "Dead Shrub",
			1 => "Tall Grass",
			2 => "Fern",
			3 => ""
		];
		return $names[$this->meta & 0x03];
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$down = $this->getSide(0);
		if($down->getId() === self::GRASS){
			$this->getLevel()->setBlock($block, $this, true);

			return true;
		}

		return false;
	}


	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(0)->isTransparent() === true){ //Replace with common break method
				$this->getLevel()->setBlock($this, new Air(), true, true);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function getDrops(Item $item){
		if(mt_rand(0, 15) === 0){
			return [
				[Item::WHEAT_SEEDS, 0, 1]
			];
		}

		return [];
	}

}
