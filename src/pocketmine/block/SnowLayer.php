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
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class SnowLayer extends Flowable{

	protected $id = self::SNOW_LAYER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Snow Layer";
	}

	public function canBeReplaced(){
		return true;
	}

	public function getHardness(){
		return 0.1;
	}

	public function getToolType(){
		return Tool::TYPE_SHOVEL;
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($block->getSide(Vector3::SIDE_DOWN)->isSolid()){
			//TODO: fix placement
			$this->getLevel()->setBlock($block, $this, true);

			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if(!$this->getSide(Vector3::SIDE_DOWN)->isSolid()){
				$this->getLevel()->setBlock($this, Block::get(Block::AIR), false, false);

				return Level::BLOCK_UPDATE_NORMAL;
			}
		}elseif($type === Level::BLOCK_UPDATE_RANDOM){
			if($this->level->getBlockLightAt($this->x, $this->y, $this->z) >= 12){
				$this->getLevel()->setBlock($this, Block::get(Block::AIR), false, false);

				return Level::BLOCK_UPDATE_RANDOM;
			}
		}

		return false;
	}

	public function getDrops(Item $item){
		if($item->isShovel() !== false){
			return [
				[Item::SNOWBALL, 0, 1],
			];
		}

		return [];
	}
}