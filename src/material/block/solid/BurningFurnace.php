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

class BurningFurnaceBlock extends SolidBlock{
	public function __construct($meta = 0){
		parent::__construct(BURNING_FURNACE, $meta, "Burning Furnace");
		$this->isActivable = true;
		$this->hardness = 17.5;
	}

	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$faces = array(
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		);
		$this->meta = $faces[$player->entity->getDirection()];
		$this->level->setBlock($block, $this, true, false, true);
		return true;
	}
	
	public function onBreak(Item $item, Player $player){
		$this->level->setBlock($this, new AirBlock(), true, true, true);
		return true;
	}

	public function onActivate(Item $item, Player $player){

		$t = $this->level->getTile($this);
		$furnace = false;
		if($t instanceof FurnaceTile){
			$furnace = $t;
		}else{
			$furnace = new FurnaceTile($this->level, new NBTTag_Compound(false, array(
				"Items" => new NBTTag_List("Items", array()),
				"id" => new NBTTag_String("id", Tile::FURNACE),
				"x" => new NBTTag_Int("x", $this->x),
				"y" => new NBTTag_Int("y", $this->y),
				"z" =>new NBTTag_Int("z",  $this->z)			
			)));
		}
		
		if(($player->gamemode & 0x01) === 0x01){
			return true;
		}
		
		$furnace->openInventory($player);
		return true;
	}
	
	public function getBreakTime(Item $item, Player $player){
		if(($player->gamemode & 0x01) === 0x01){
			return 0.20;
		}		
		switch($item->isPickaxe()){
			case 5:
				return 0.7;
			case 4:
				return 0.9;
			case 3:
				return 1.35;
			case 2:
				return 0.45;
			case 1:
				return 2.65;
			default:
				return 17.5;
		}
	}
	
	public function getDrops(Item $item, Player $player){
		$drops = array();
		if($item->isPickaxe() >= 1){
			$drops[] = array(FURNACE, 0, 1);
		}
		$t = $this->level->getTile($this);
		if($t instanceof FurnaceTile){
			for($s = 0; $s < FurnaceTile::SLOTS; ++$s){
				$slot = $t->getSlot($s);
				if($slot->getID() > AIR and $slot->count > 0){
					$drops[] = array($slot->getID(), $slot->getMetadata(), $slot->count);
				}
			}
		}
		return $drops;
	}
}