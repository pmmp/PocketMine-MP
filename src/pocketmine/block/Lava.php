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
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class Lava extends Liquid{
	public function __construct($meta = 0){
		parent::__construct(self::LAVA, $meta, "Lava");
		$this->hardness = 0;
	}

	public function getBoundingBox(){
		return null;
	}


	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$ret = $this->getLevel()->setBlock($this, $this, true, false, true);
		$this->getLevel()->scheduleUpdate(clone $this, 40);

		return $ret;
	}

	public function getSourceCount(){
		$count = 0;
		for($side = 2; $side <= 5; ++$side){
			if($this->getSide($side) instanceof Lava){
				$b = $this->getSide($side);
				$level = $b->meta & 0x07;
				if($level == 0x00){
					$count++;
				}
			}
		}

		return $count;
	}

	public function checkWater(){
		for($side = 1; $side <= 5; ++$side){
			$b = $this->getSide($side);
			if($b instanceof Water){
				$level = $this->meta & 0x07;
				if($level == 0x00){
					$this->getLevel()->setBlock($this, new Obsidian(), false, false, true);
				}else{
					$this->getLevel()->setBlock($this, new Cobblestone(), false, false, true);
				}
			}
		}
	}

	public function getFrom(){
		for($side = 0; $side <= 5; ++$side){
			$b = $this->getSide($side);
			if($b instanceof Lava){
				$tlevel = $b->meta & 0x07;
				$level = $this->meta & 0x07;
				if(($tlevel + 2) == $level || ($side == 0x01 && $level == 0x01) || ($tlevel == 6 && $level == 7)){
					return $b;
				}
			}
		}

		return null;
	}

	public function onUpdate($type){
		return false;
		$newId = $this->id;
		$level = $this->meta & 0x07;
		if($type !== Level::BLOCK_UPDATE_NORMAL){
			return false;
		}

		if($this->checkWater()){
			return;
		}

		$falling = $this->meta >> 3;
		$down = $this->getSide(0);

		$from = $this->getFrom();
		if($from !== null || $level == 0x00){
			if($level !== 0x07){
				if($down instanceof Air || $down instanceof Lava){
					$this->getLevel()->setBlock($down, new Lava(0x01), false, false, true);
					Server::getInstance()->api->block->scheduleBlockUpdate(new Position($down, 0, 0, $this->level), 40, Level::BLOCK_UPDATE_NORMAL);
				}else{
					for($side = 2; $side <= 5; ++$side){
						$b = $this->getSide($side);
						if($b instanceof Lava){

						}elseif($b->isFlowable === true){
							$this->getLevel()->setBlock($b, new Lava(min($level + 2, 7)), false, false, true);
							Server::getInstance()->api->block->scheduleBlockUpdate(Position::fromObject($b, $this->level), 40, Level::BLOCK_UPDATE_NORMAL);
						}
					}
				}
			}
		}else{
			//Extend Remove for Left Lavas
			for($side = 2; $side <= 5; ++$side){
				$sb = $this->getSide($side);
				if($sb instanceof Lava){
					$tlevel = $sb->meta & 0x07;
					if($tlevel != 0x00){
						for($s = 0; $s <= 5; $s++){
							$ssb = $sb->getSide($s);
							Server::getInstance()->api->block->scheduleBlockUpdate(Position::fromObject($ssb, $this->level), 40, Level::BLOCK_UPDATE_NORMAL);
						}
						$this->getLevel()->setBlock($sb, new Air(), false, false, true);
					}
				}
				$b = $this->getSide(0)->getSide($side);
				if($b instanceof Lava){
					$tlevel = $b->meta & 0x07;
					if($tlevel != 0x00){
						for($s = 0; $s <= 5; $s++){
							$ssb = $sb->getSide($s);
							Server::getInstance()->api->block->scheduleBlockUpdate(Position::fromObject($ssb, $this->level), 40, Level::BLOCK_UPDATE_NORMAL);
						}
						$this->getLevel()->setBlock($b, new Air(), false, false, true);
					}
				}
				//Server::getInstance()->api->block->scheduleBlockUpdate(Position::fromObject($b, $this->level), 10, Level::BLOCK_UPDATE_NORMAL);
			}

			$this->getLevel()->setBlock($this, new Air(), false, false, true);
		}

		return false;
	}

}
