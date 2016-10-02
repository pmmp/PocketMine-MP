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
use pocketmine\item\Tool;
use pocketmine\level\sound\DoorSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;

class Trapdoor extends Transparent{
	const MASK_UPPER = 0x04;
	const MASK_OPENED = 0x08;
	const MASK_SIDE = 0x03;
	const MASK_SIDE_SOUTH = 2;
	const MASK_SIDE_NORTH = 3;
	const MASK_SIDE_EAST = 0;
	const MASK_SIDE_WEST = 1;

	protected $id = self::TRAPDOOR;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getName(){
		return "Wooden Trapdoor";
	}

	public function getHardness(){
		return 3;
	}

	public function canBeActivated(){
		return true;
	}

	protected function recalculateBoundingBox(){

		$damage = $this->getDamage();

		$f = 0.1875;

		if(($damage & self::MASK_UPPER) > 0){
			$bb = new AxisAlignedBB(
				$this->x,
				$this->y + 1 - $f,
				$this->z,
				$this->x + 1,
				$this->y + 1,
				$this->z + 1
			);
		}else{
			$bb = new AxisAlignedBB(
				$this->x,
				$this->y,
				$this->z,
				$this->x + 1,
				$this->y + $f,
				$this->z + 1
			);
		}

		if(($damage & self::MASK_OPENED) > 0){
			if(($damage & 0x03) === self::MASK_SIDE_NORTH){
				$bb->setBounds(
					$this->x,
					$this->y,
					$this->z + 1 - $f,
					$this->x + 1,
					$this->y + 1,
					$this->z + 1
				);
			}elseif(($damage & 0x03) === self::MASK_SIDE_SOUTH){
				$bb->setBounds(
					$this->x,
					$this->y,
					$this->z,
					$this->x + 1,
					$this->y + 1,
					$this->z + $f
				);
			}
			if(($damage & 0x03) === self::MASK_SIDE_WEST){
				$bb->setBounds(
					$this->x + 1 - $f,
					$this->y,
					$this->z,
					$this->x + 1,
					$this->y + 1,
					$this->z + 1
				);
			}
			if(($damage & 0x03) === self::MASK_SIDE_EAST){
				$bb->setBounds(
					$this->x,
					$this->y,
					$this->z,
					$this->x + $f,
					$this->y + 1,
					$this->z + 1
				);
			}
		}

		return $bb;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$directions = [
			0 => 1,
			1 => 3,
			2 => 0,
			3 => 2
		];
		if($player !== null){
			$this->meta = $directions[$player->getDirection() & 0x03];
		}
		if(($fy > 0.5 and $face !== self::SIDE_UP) or $face === self::SIDE_DOWN){
			$this->meta |= self::MASK_UPPER; //top half of block
		}
		$this->getLevel()->setBlock($block, $this, true, true);
		return true;
	}

	public function getDrops(Item $item){
		return [
			[$this->id, 0, 1],
		];
	}

	public function onActivate(Item $item, Player $player = null){
		$this->meta ^= self::MASK_OPENED;
		$this->getLevel()->setBlock($this, $this, true);
		$this->level->addSound(new DoorSound($this));
		return true;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}
}
