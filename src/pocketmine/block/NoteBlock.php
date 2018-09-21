<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\sound\NoteblockSound;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\NoteBlock as TileNoteBlock;
use pocketmine\tile\Tile;

class Noteblock extends Solid{

	protected $id = self::NOTE_BLOCK;

	public function __construct(){

	}

	public function getHardness() : float{
		return 0.8;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	/**
	 * @return int
	 */
	public function calculateNote() : int{
		$tile = $this->level->getTile($this);
		
		if(!($tile instanceof TileNoteBlock)){
			$tile = Tile::createTile(Tile::NOTEBLOCK, $this->level, TileNoteBlock::createNBT($this));
		}
		
		$note = $tile->getNote();
		$nextNote = $note + 1;
		if($nextNote > 24) $nextNote = 0;
		
		$tile->setNote($nextNote);
		
		return $note;
	}

	/**
	 * @return int
	 */
	public function getInstrument(){
		$below = $this->getSide(Facing::DOWN);
		switch($below->getId()){
			case Block::WOOD:
			case Block::WOOD2:
			case Block::PLANKS:
			case Block::WOODEN_SLAB:
			case Block::DOUBLE_WOODEN_SLAB:
			case Block::OAK_STAIRS:
			case Block::SPRUCE_STAIRS:
			case Block::BIRCH_STAIRS:
			case Block::JUNGLE_STAIRS:
			case Block::ACACIA_STAIRS:
			case Block::DARK_OAK_STAIRS:
			case Block::FENCE:
			case Block::FENCE_GATE:
			case Block::SPRUCE_FENCE_GATE:
			case Block::BIRCH_FENCE_GATE:
			case Block::JUNGLE_FENCE_GATE:
			case Block::DARK_OAK_FENCE_GATE:
			case Block::ACACIA_FENCE_GATE:
			case Block::BOOKSHELF:
			case Block::CHEST:
			case Block::CRAFTING_TABLE:
			case Block::SIGN_POST:
			case Block::WALL_SIGN:
			case Block::OAK_DOOR_BLOCK:
			case Block::SPRUCE_DOOR_BLOCK:
			case Block::BIRCH_DOOR_BLOCK:
			case Block::JUNGLE_DOOR_BLOCK:
			case Block::ACACIA_DOOR_BLOCK:
			case Block::DARK_OAK_DOOR_BLOCK:
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
			case Block::BRICK_BLOCK:
			case Block::STONE_BRICK:
			case Block::NETHER_BRICK_BLOCK:
			case Block::QUARTZ_BLOCK:
			case Block::STONE_SLAB:
			case Block::COBBLESTONE_STAIRS:
			case Block::BRICK_STAIRS:
			case Block::STONE_BRICK_STAIRS:
			case Block::NETHER_BRICK_STAIRS:
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
			case Block::TERRACOTTA:
			case Block::COAL_BLOCK:
				return NoteblockSound::INSTRUMENT_BASS_DRUM;
		}

		return NoteblockSound::INSTRUMENT_PIANO;
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$up = $this->getSide(Facing::UP);
		if($up->getId() == Block::AIR){
			$this->getLevel()->addSound(new NoteblockSound($this, $this->getInstrument(), $this->calculateNote()));
			return true;
		}else{
			return false;
		}
	}

	public function getName() : string{
		return "Noteblock";
	}

	public function getFuelTime() : int{
		return 300;
	}

	// TODO: Support with Redstone
}