<?php

/**
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

class BedBlock extends TransparentBlock{
	public function __construct($type = 0){
		parent::__construct(BED_BLOCK, $type, "Bed Block");
		$this->isActivable = true;
		$this->isFullBlock = false;
		$this->hardness = 1;
	}
	
	public function onActivate(Item $item, Player $player){
		if(ServerAPI::request()->api->time->getPhase($player->level) !== "night"){
			$player->dataPacket(MC_CLIENT_MESSAGE, array(
				"message" => "You can only sleep at night"
			));
			return true;
		}
		
			$blockNorth = $this->getSide(2); //Gets the blocks around them
			$blockSouth = $this->getSide(3);
			$blockEast = $this->getSide(5);
			$blockWest = $this->getSide(4);
			if(($this->meta & 0x08) === 0x08){ //This is the Top part of bed	
				$b = $this;
			}else{ //Bottom Part of Bed
				if($blockNorth->getID() === $this->id and ($blockNorth->meta & 0x08) === 0x08){
					$b = $blockNorth;
				}elseif($blockSouth->getID() === $this->id and ($blockSouth->meta & 0x08) === 0x08){
					$b = $blockSouth;
				}elseif($blockEast->getID() === $this->id and ($blockEast->meta & 0x08) === 0x08){
					$b = $blockEast;
				}elseif($blockWest->getID() === $this->id and ($blockWest->meta & 0x08) === 0x08){
					$b = $blockWest;
				}else{
					$player->dataPacket(MC_CLIENT_MESSAGE, array(
						"message" => "The bed is incomplete"
					));
					return true;
				}
			}

		if($player->sleepOn($b) === false){
			$player->dataPacket(MC_CLIENT_MESSAGE, array(
				"message" => "This bed is occupied"
			));
		}
		return true;
	}
	
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
			$down = $this->getSide(0);
			if($down->isTransparent === false){
				$faces = array(
					0 => 3,
					1 => 4,
					2 => 2,
					3 => 5,
				);
				$d = $player->entity->getDirection();
				$next = $this->getSide($faces[(($d + 3) % 4)]);
				$downNext = $this->getSide(0);
				if($next->isReplaceable === true and $downNext->isTransparent === false){
					$meta = (($d + 3) % 4) & 0x03;
					$this->level->setBlock($block, BlockAPI::get($this->id, $meta), true, false, true);
					$this->level->setBlock($next, BlockAPI::get($this->id, $meta | 0x08), true, false, true);
					return true;
				}
			}
		return false;
	}	
	
	public function onBreak(Item $item, Player $player){
			$blockNorth = $this->getSide(2); //Gets the blocks around them
			$blockSouth = $this->getSide(3);
			$blockEast = $this->getSide(5);
			$blockWest = $this->getSide(4);
			
			if(($this->meta & 0x08) === 0x08){ //This is the Top part of bed			
				if($blockNorth->getID() === $this->id and $blockNorth->meta !== 0x08){ //Checks if the block ID and meta are right
					$this->level->setBlock($blockNorth, new AirBlock(), true, false, true);
				}elseif($blockSouth->getID() === $this->id and $blockSouth->meta !== 0x08){
					$this->level->setBlock($blockSouth, new AirBlock(), true, false, true);
				}elseif($blockEast->getID() === $this->id and $blockEast->meta !== 0x08){
					$this->level->setBlock($blockEast, new AirBlock(), true, false, true);
				}elseif($blockWest->getID() === $this->id and $blockWest->meta !== 0x08){
					$this->level->setBlock($blockWest, new AirBlock(), true, false, true);
				}
			}else{ //Bottom Part of Bed
				if($blockNorth->getID() === $this->id and ($blockNorth->meta & 0x08) === 0x08){
					$this->level->setBlock($blockNorth, new AirBlock(), true, false, true);
				}elseif($blockSouth->getID() === $this->id and ($blockSouth->meta & 0x08) === 0x08){
					$this->level->setBlock($blockSouth, new AirBlock(), true, false, true);
				}elseif($blockEast->getID() === $this->id and ($blockEast->meta & 0x08) === 0x08){
					$this->level->setBlock($blockEast, new AirBlock(), true, false, true);
				}elseif($blockWest->getID() === $this->id and ($blockWest->meta & 0x08) === 0x08){
					$this->level->setBlock($blockWest, new AirBlock(), true, false, true);
				}				
			}
			$this->level->setBlock($this, new AirBlock(), true, false, true);
			return true;
	}
	
	public function getDrops(Item $item, Player $player){
		return array(
			array(BED, 0, 1),
		);
	}
	
}