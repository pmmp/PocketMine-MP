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

class ChestBlock extends TransparentBlock{
	public function __construct($meta = 0){
		parent::__construct(CHEST, $meta, "Chest");
		$this->isActivable = true;
		$this->hardness = 15;
	}
	public function place(Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$faces = array(
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		);

		$chest = false;
		$this->meta = $faces[$player->entity->getDirection()];
		
		for($side = 2; $side <= 5; ++$side){
			if(($this->meta === 4 or $this->meta === 5) and ($side === 4 or $side === 5)){
				continue;
			}elseif(($this->meta === 3 or $this->meta === 2) and ($side === 2 or $side === 3)){
				continue;
			}
			$c = $this->getSide($side);
			if(($c instanceof ChestBlock) and $c->getMetadata() === $this->meta){
				if((($tile = $this->level->getTile($c)) instanceof ChestTile) and !$tile->isPaired()){
					$chest = $tile;
					break;
				}
			}
		}

		$this->level->setBlock($block, $this, true, false, true);
		$tile = new ChestTile($this->level, new NBTTag_Compound(false, array(
			"Items" => new NBTTag_List("Items", array()),
			"id" => new NBTTag_String("id", Tile::CHEST),
			"x" => new NBTTag_Int("x", $this->x),
			"y" => new NBTTag_Int("y", $this->y),
			"z" =>new NBTTag_Int("z",  $this->z)			
		)));

		if($chest instanceof ChestTile){
			$chest->pairWith($tile);
			$tile->pairWith($chest);
		}
		return true;
	}
	
	public function onBreak(Item $item, Player $player){
		$t = $this->level->getTile($this);
		if($t instanceof ChestTile){
			$t->unpair();
		}
		$this->level->setBlock($this, new AirBlock(), true, true, true);
		return true;
	}
	
	public function onActivate(Item $item, Player $player){
		$top = $this->getSide(1);
		if($top->isTransparent !== true){
			return true;
		}
	
		$t = $this->level->getTile($this);
		$chest = false;
		if($t instanceof ChestTile){
			$chest = $t;
		}else{
			$chest = new ChestTile($this->level, new NBTTag_Compound(false, array(
				"Items" => new NBTTag_List("Items", array()),
				"id" => new NBTTag_String("id", Tile::CHEST),
				"x" => new NBTTag_Int("x", $this->x),
				"y" => new NBTTag_Int("y", $this->y),
				"z" =>new NBTTag_Int("z",  $this->z)			
			)));
		}
		
		
		
		if(($player->gamemode & 0x01) === 0x01){
			return true;
		}
		
		$chest->openInventory($player);		
		return true;
	}

	public function getDrops(Item $item, Player $player){
		$drops = array(
			array($this->id, 0, 1),
		);
		$t = $this->level->getTile($this);
		if($t instanceof ChestTile){
			for($s = 0; $s < ChestTile::SLOTS; ++$s){
				$slot = $t->getSlot($s);
				if($slot->getID() > AIR and $slot->count > 0){
					$drops[] = array($slot->getID(), $slot->getMetadata(), $slot->count);
				}
			}
		}
		return $drops;
	}
}