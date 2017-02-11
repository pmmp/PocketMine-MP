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
use pocketmine\level\sound\NoteblockSound;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Noteblock extends Solid implements ElectricalAppliance{
	protected $id = self::NOTEBLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 0.8;
	}

	public function getResistance(){
		return 4;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function getStrength(){
		if($this->meta < 24) $this->meta++;
		else $this->meta = 0;
		$this->getLevel()->setBlock($this, $this);
		return $this->meta * 1;
	}

	public function getInstrument(){
		$below = $this->getSide(Vector3::SIDE_DOWN);
		switch($below->getId()){
			case Block::WOOD:
			case Block::WOOD2:
			case Block::WOODEN_PLANK:
			case Block::WOODEN_SLABS:
			case Block::DOUBLE_WOOD_SLABS:
			case Block::OAK_WOODEN_STAIRS:
			case Block::SPRUCE_WOODEN_STAIRS:
			case Block::BIRCH_WOODEN_STAIRS:
			case Block::JUNGLE_WOODEN_STAIRS:
			case Block::ACACIA_WOODEN_STAIRS:
			case Block::DARK_OAK_WOODEN_STAIRS:
			case Block::FENCE:
			case Block::FENCE_GATE:
			case Block::FENCE_GATE_SPRUCE:
			case Block::FENCE_GATE_BIRCH:
			case Block::FENCE_GATE_JUNGLE:
			case Block::FENCE_GATE_DARK_OAK:
			case Block::FENCE_GATE_ACACIA:
			case Block::SPRUCE_WOOD_STAIRS:
			case Block::BOOKSHELF:
			case Block::CHEST:
			case Block::CRAFTING_TABLE:
			case Block::SIGN_POST:
			case Block::WALL_SIGN:
			case Block::DOOR_BLOCK:
			case Block::NOTEBLOCK:
				return NoteblockSound::INSTRUMENT_BASS;
			case Block::SAND:
			case Block::SOUL_SAND:
				return NoteblockSound::INSTRUMENT_TABOUR;
			case Block::GLASS:
			case Block::GLASS_PANE:
				return NoteblockSound::INSTRUMENT_CLICK;
			case Block::STONE:
			case Block::COBBLESTONE:
			case Block::SANDSTONE:
			case Block::MOSS_STONE:
			case Block::BRICKS:
			case Block::STONE_BRICK:
			case Block::NETHER_BRICKS:
			case Block::QUARTZ_BLOCK:
			case Block::SLAB:
			case Block::COBBLESTONE_STAIRS:
			case Block::BRICK_STAIRS:
			case Block::STONE_BRICK_STAIRS:
			case Block::NETHER_BRICKS_STAIRS:
			case Block::SANDSTONE_STAIRS:
			case Block::QUARTZ_STAIRS:
			case Block::COBBLESTONE_WALL:
			case Block::NETHER_BRICK_FENCE:
			case Block::BEDROCK:
			case Block::GOLD_ORE:
			case Block::IRON_ORE:
			case Block::COAL_ORE:
			case Block::LAPIS_ORE:
			case Block::DIAMOND_ORE:
			case Block::REDSTONE_ORE:
			case Block::GLOWING_REDSTONE_ORE:
			case Block::EMERALD_ORE:
			case Block::FURNACE:
			case Block::BURNING_FURNACE:
			case Block::OBSIDIAN:
			case Block::MONSTER_SPAWNER:
			case Block::NETHERRACK:
			case Block::ENCHANTING_TABLE:
			case Block::END_STONE:
			case Block::STAINED_CLAY:
			case Block::COAL_BLOCK:
				return NoteblockSound::INSTRUMENT_BASS_DRUM;
		}
		return NoteblockSound::INSTRUMENT_PIANO;
	}

	public function onActivate(Item $item, Player $player = null){
		$up = $this->getSide(Vector3::SIDE_UP);
		if($up->getId() == 0){
			$this->getLevel()->addSound(new NoteblockSound($this, $this->getInstrument(), $this->getStrength()));
			return true;
		}else{
			return false;
		}
	}

	public function getName() : string{
		return "Noteblock";
	}
}
