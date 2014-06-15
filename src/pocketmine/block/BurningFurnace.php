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
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\Player;
use pocketmine\tile\Furnace;
use pocketmine\tile\Tile;

class BurningFurnace extends Solid{
	public function __construct($meta = 0){
		parent::__construct(self::BURNING_FURNACE, $meta, "Burning Furnace");
		$this->isActivable = true;
		$this->hardness = 17.5;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		$faces = array(
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		);
		$this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];
		$this->getLevel()->setBlock($block, $this, true, false, true);
		$nbt = new Compound(false, array(
			new Enum("Items", []),
			new String("id", Tile::FURNACE),
			new Int("x", $this->x),
			new Int("y", $this->y),
			new Int("z", $this->z)
		));
		$nbt->Items->setTagType(NBT::TAG_Compound);
		new Furnace($this->getLevel()->getChunkAt($this->x >> 4, $this->z >> 4), $nbt);

		return true;
	}

	public function onBreak(Item $item){
		$this->getLevel()->setBlock($this, new Air(), true, true, true);

		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if($player instanceof Player){
			$t = $this->getLevel()->getTile($this);
			$furnace = false;
			if($t instanceof Furnace){
				$furnace = $t;
			}else{
				$nbt = new Compound(false, array(
					new Enum("Items", []),
					new String("id", Tile::FURNACE),
					new Int("x", $this->x),
					new Int("y", $this->y),
					new Int("z", $this->z)
				));
				$nbt->Items->setTagType(NBT::TAG_Compound);
				$furnace = new Furnace($this->getLevel()->getChunkAt($this->x >> 4, $this->z >> 4), $nbt);
			}

			if(($player->getGamemode() & 0x01) === 0x01){
				return true;
			}

			$player->addWindow($furnace->getInventory());
		}

		return true;
	}

	public function getBreakTime(Item $item){
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

	public function getDrops(Item $item){
		$drops = [];
		if($item->isPickaxe() >= 1){
			$drops[] = array(Item::FURNACE, 0, 1);
		}
		$t = $this->getLevel()->getTile($this);
		if($t instanceof Furnace){
			for($s = 0; $s < $t->getInventory()->getSize(); ++$s){
				$slot = $t->getInventory()->getItem($s);
				if($slot->getID() > Item::AIR and $slot->getCount() > 0){
					$drops[] = array($slot->getID(), $slot->getDamage(), $slot->getCount());
				}
			}
		}

		return $drops;
	}
}