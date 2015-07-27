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

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;

class Ladder extends Transparent{

	protected $id = self::LADDER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Ladder";
	}

	public function hasEntityCollision(){
		return true;
	}

	public function isSolid(){
		return false;
	}

	public function getHardness(){
		return 0.4;
	}

	public function onEntityCollide(Entity $entity){
		$entity->resetFallDistance();
		$entity->onGround = true;
	}

	protected function recalculateBoundingBox(){

		$f = 0.125;

		if($this->meta === 2){
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z + 1 - $f,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		}elseif($this->meta === 3){
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + $f
			);
		}elseif($this->meta === 4){
			return new AxisAlignedBB(
				$this->x + 1 - $f,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		}elseif($this->meta === 5){
			return new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + $f,
				$this->y + 1,
				$this->z + 1
			);
		}

		return null;
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($target->isTransparent() === false){
			$faces = [
				2 => 2,
				3 => 3,
				4 => 4,
				5 => 5,
			];
			if(isset($faces[$face])){
				$this->meta = $faces[$face];
				$this->getLevel()->setBlock($block, $this, true, true);

				return true;
			}
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			/*if($this->getSide(0)->getId() === self::AIR){ //Replace with common break method
				Server::getInstance()->api->entity->drop($this, Item::get(LADDER, 0, 1));
				$this->getLevel()->setBlock($this, new Air(), true, true, true);
				return Level::BLOCK_UPDATE_NORMAL;
			}*/
		}

		return false;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getDrops(Item $item){
		return [
			[$this->id, 0, 1],
		];
	}
}