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

class SignPost extends Transparent{

	protected $id = self::SIGN_POST;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 1;
	}

	public function isSolid(){
		return false;
	}

	public function getName(){
		return "Sign Post";
	}

	public function getBoundingBox(){
		return null;
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face !== 0){
			$faces = [
				2 => 2,
				3 => 3,
				4 => 4,
				5 => 5,
			];
			if(!isset($faces[$face])){
				$this->meta = floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0F;
				$this->getLevel()->setBlock($block, Block::get(Item::SIGN_POST, $this->meta), true);

				return true;
			}else{
				$this->meta = $faces[$face];
				$this->getLevel()->setBlock($block, Block::get(Item::WALL_SIGN, $this->meta), true);

				return true;
			}
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

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true, true);

		return true;
	}

	public function getDrops(Item $item){
		return [
			[Item::SIGN, 0, 1],
		];
	}
}