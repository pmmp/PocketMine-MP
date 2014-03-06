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

namespace PocketMine\Block;
use PocketMine;

class Chest extends Transparent{
	public function __construct($meta = 0){
		parent::__construct(CHEST, $meta, "Chest");
		$this->isActivable = true;
		$this->hardness = 15;
	}

	public function place(Item\Item $item, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		$faces = array(
			0 => 4,
			1 => 2,
			2 => 5,
			3 => 3,
		);

		$chest = false;
		$this->meta = $faces[$player->getDirection()];

		for($side = 2; $side <= 5; ++$side){
			if(($this->meta === 4 or $this->meta === 5) and ($side === 4 or $side === 5)){
				continue;
			} elseif(($this->meta === 3 or $this->meta === 2) and ($side === 2 or $side === 3)){
				continue;
			}
			$c = $this->getSide($side);
			if(($c instanceof Tile\Chest) and $c->getMetadata() === $this->meta){
				if((($tile = $this->level->getTile($c)) instanceof Tile\Chest) and !$tile->isPaired()){
					$chest = $tile;
					break;
				}
			}
		}

		$this->level->setBlock($block, $this, true, false, true);
		$nbt = new NBT\Tag\Compound(false, array(
			"Items" => new NBT\Tag\Enum("Items", array()),
			"id" => new NBT\Tag\String("id", Tile\Tile::CHEST),
			"x" => new NBT\Tag\Int("x", $this->x),
			"y" => new NBT\Tag\Int("y", $this->y),
			"z" => new NBT\Tag\Int("z", $this->z)
		));
		$nbt->Items->setTagType(NBT\Tag_Compound);
		$tile = new Tile\Chest($this->level, $nbt);

		if($chest instanceof Tile\Chest){
			$chest->pairWith($tile);
			$tile->pairWith($chest);
		}

		return true;
	}

	public function onBreak(Item\Item $item, Player $player){
		$t = $this->level->getTile($this);
		if($t instanceof Tile\Chest){
			$t->unpair();
		}
		$this->level->setBlock($this, new Air(), true, true, true);

		return true;
	}

	public function onActivate(Item\Item $item, Player $player){
		$top = $this->getSide(1);
		if($top->isTransparent !== true){
			return true;
		}

		$t = $this->level->getTile($this);
		$chest = false;
		if($t instanceof Tile\Chest){
			$chest = $t;
		} else{
			$nbt = new NBT\Tag\Compound(false, array(
				"Items" => new NBT\Tag\Enum("Items", array()),
				"id" => new NBT\Tag\String("id", Tile\Tile::CHEST),
				"x" => new NBT\Tag\Int("x", $this->x),
				"y" => new NBT\Tag\Int("y", $this->y),
				"z" => new NBT\Tag\Int("z", $this->z)
			));
			$nbt->Items->setTagType(NBT\Tag_Compound);
			$chest = new Tile\Chest($this->level, $nbt);
		}


		if(($player->gamemode & 0x01) === 0x01){
			return true;
		}

		$chest->openInventory($player);

		return true;
	}

	public function getDrops(Item\Item $item, Player $player){
		$drops = array(
			array($this->id, 0, 1),
		);
		$t = $this->level->getTile($this);
		if($t instanceof Chest){
			for($s = 0; $s < Chest::SLOTS; ++$s){
				$slot = $t->getSlot($s);
				if($slot->getID() > AIR and $slot->getCount() > 0){
					$drops[] = array($slot->getID(), $slot->getMetadata(), $slot->getCount());
				}
			}
		}

		return $drops;
	}
}