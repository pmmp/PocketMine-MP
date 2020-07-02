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

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\tile\Note as TileNote;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\player\Player;
use pocketmine\world\sound\NoteInstrument;
use pocketmine\world\sound\NoteSound;
use function assert;

class Note extends Opaque{
	public const MIN_PITCH = 0;
	public const MAX_PITCH = 24;

	private int $pitch = self::MIN_PITCH;

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if($tile instanceof TileNote){
			$this->pitch = $tile->getPitch();
		}else{
			$this->pitch = self::MIN_PITCH;
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileNote);
		$tile->setPitch($this->pitch);
	}

	public function getFuelTime() : int{
		return 300;
	}

	public function getPitch() : int{
		return $this->pitch;
	}

	/** @return $this */
	public function setPitch(int $pitch) : self{
		if($pitch < self::MIN_PITCH or $pitch > self::MAX_PITCH){
			throw new \InvalidArgumentException("Pitch must be in range " . self::MIN_PITCH . " - " . self::MAX_PITCH);
		}
		$this->pitch = $pitch;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->getSide(Facing::UP)->getId() !== BlockLegacyIds::AIR){
			return false;
		}
		$this->pitch = ++$this->pitch % (self::MAX_PITCH + 1);
		$this->pos->getWorld()->setBlock($this->pos, $this);
		$this->triggerNote();
		return true;
	}

	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		if($this->getSide(Facing::UP)->getId() !== BlockLegacyIds::AIR){
			return false;
		}
		$this->triggerNote();
		return true;
	}

	public function getInstrument() : NoteInstrument{
		$blockUnder = $this->getSide(Facing::DOWN);
		switch($blockUnder->getId()){
			case BlockLegacyIds::STONE:
			case BlockLegacyIds::SANDSTONE:
			case BlockLegacyIds::RED_SANDSTONE:
			case BlockLegacyIds::COBBLESTONE:
			case BlockLegacyIds::MOSS_STONE:
			case BlockLegacyIds::BRICK_BLOCK:
			case BlockLegacyIds::STONE_BRICKS:
			case BlockLegacyIds::NETHER_BRICK_BLOCK:
			case BlockLegacyIds::RED_NETHER_BRICK:
			case BlockLegacyIds::QUARTZ_BLOCK:
			case BlockLegacyIds::DOUBLE_STONE_SLAB:
			case BlockLegacyIds::DOUBLE_STONE_SLAB2:
			case BlockLegacyIds::DOUBLE_STONE_SLAB3:
			case BlockLegacyIds::DOUBLE_STONE_SLAB4:
			case BlockLegacyIds::STONE_SLAB:
			case BlockLegacyIds::STONE_SLAB2:
			case BlockLegacyIds::STONE_SLAB3:
			case BlockLegacyIds::STONE_SLAB4:
			case BlockLegacyIds::COBBLESTONE_STAIRS:
			case BlockLegacyIds::BRICK_STAIRS:
			case BlockLegacyIds::STONE_BRICK_STAIRS:
			case BlockLegacyIds::NETHER_BRICK_STAIRS:
			case BlockLegacyIds::SANDSTONE_STAIRS:
			case BlockLegacyIds::QUARTZ_STAIRS:
			case BlockLegacyIds::RED_SANDSTONE_STAIRS:
			case BlockLegacyIds::PURPUR_STAIRS:
			case BlockLegacyIds::COBBLESTONE_WALL:
			case BlockLegacyIds::NETHER_BRICK_FENCE:
			case BlockLegacyIds::BEDROCK:
			case BlockLegacyIds::GOLD_ORE:
			case BlockLegacyIds::IRON_ORE:
			case BlockLegacyIds::COAL_ORE:
			case BlockLegacyIds::LAPIS_ORE:
			case BlockLegacyIds::DIAMOND_ORE:
			case BlockLegacyIds::REDSTONE_ORE:
			case BlockLegacyIds::GLOWING_REDSTONE_ORE:
			case BlockLegacyIds::EMERALD_ORE:
			case BlockLegacyIds::DROPPER:
			case BlockLegacyIds::DISPENSER:
			case BlockLegacyIds::FURNACE:
			case BlockLegacyIds::BURNING_FURNACE:
			case BlockLegacyIds::OBSIDIAN:
			case BlockLegacyIds::GLOWING_OBSIDIAN:
			case BlockLegacyIds::MONSTER_SPAWNER:
			case BlockLegacyIds::STONE_PRESSURE_PLATE:
			case BlockLegacyIds::NETHERRACK:
			case BlockLegacyIds::QUARTZ_ORE:
			case BlockLegacyIds::ENCHANTING_TABLE:
			case BlockLegacyIds::END_PORTAL_FRAME:
			case BlockLegacyIds::END_STONE:
			case BlockLegacyIds::END_BRICKS:
			case BlockLegacyIds::ENDER_CHEST:
			case BlockLegacyIds::STAINED_CLAY:
			case BlockLegacyIds::TERRACOTTA:
			case BlockLegacyIds::PRISMARINE:
			case BlockLegacyIds::COAL_BLOCK:
			case BlockLegacyIds::PURPUR_BLOCK:
			case BlockLegacyIds::MAGMA:
			case BlockLegacyIds::CONCRETE:
			case BlockLegacyIds::STONECUTTER:
			case BlockLegacyIds::OBSERVER:
				return NoteInstrument::BASS_DRUM();
			case BlockLegacyIds::SAND:
			case BlockLegacyIds::GRAVEL:
			case BlockLegacyIds::CONCRETE_POWDER:
				return NoteInstrument::SNARE();
			case BlockLegacyIds::GLASS:
			case BlockLegacyIds::GLASS_PANE:
			case BlockLegacyIds::STAINED_GLASS_PANE:
			case BlockLegacyIds::STAINED_GLASS:
			case BlockLegacyIds::BEACON:
			case BlockLegacyIds::SEA_LANTERN:
				return NoteInstrument::CLICKS_AND_STICKS();
			case BlockLegacyIds::LOG:
			case BlockLegacyIds::LOG2:
			case BlockLegacyIds::PLANKS:
			case BlockLegacyIds::DOUBLE_WOODEN_SLAB:
			case BlockLegacyIds::WOODEN_SLAB:
			case BlockLegacyIds::WOODEN_STAIRS:
			case BlockLegacyIds::SPRUCE_STAIRS:
			case BlockLegacyIds::BIRCH_STAIRS:
			case BlockLegacyIds::JUNGLE_STAIRS:
			case BlockLegacyIds::ACACIA_STAIRS:
			case BlockLegacyIds::DARK_OAK_STAIRS:
			case BlockLegacyIds::FENCE:
			case BlockLegacyIds::FENCE_GATE:
			case BlockLegacyIds::SPRUCE_FENCE_GATE:
			case BlockLegacyIds::BIRCH_FENCE_GATE:
			case BlockLegacyIds::JUNGLE_FENCE_GATE:
			case BlockLegacyIds::DARK_OAK_FENCE_GATE:
			case BlockLegacyIds::ACACIA_FENCE_GATE:
			case BlockLegacyIds::OAK_DOOR_BLOCK:
			case BlockLegacyIds::SPRUCE_DOOR_BLOCK:
			case BlockLegacyIds::BIRCH_DOOR_BLOCK:
			case BlockLegacyIds::JUNGLE_DOOR_BLOCK:
			case BlockLegacyIds::ACACIA_DOOR_BLOCK:
			case BlockLegacyIds::DARK_OAK_DOOR_BLOCK:
			case BlockLegacyIds::WOODEN_PRESSURE_PLATE:
			case BlockLegacyIds::TRAPDOOR:
			case BlockLegacyIds::SIGN_POST:
			case BlockLegacyIds::WALL_SIGN:
			case BlockLegacyIds::NOTEBLOCK:
			case BlockLegacyIds::BOOKSHELF:
			case BlockLegacyIds::CHEST:
			case BlockLegacyIds::TRAPPED_CHEST:
			case BlockLegacyIds::CRAFTING_TABLE:
			case BlockLegacyIds::JUKEBOX:
			case BlockLegacyIds::BROWN_MUSHROOM_BLOCK:
			case BlockLegacyIds::RED_MUSHROOM_BLOCK:
			case BlockLegacyIds::DAYLIGHT_DETECTOR:
			case BlockLegacyIds::DAYLIGHT_DETECTOR_INVERTED:
			case BlockLegacyIds::STANDING_BANNER:
			case BlockLegacyIds::WALL_BANNER:
				return NoteInstrument::DOUBLE_BASS();
			case BlockLegacyIds::GOLD_BLOCK:
				return NoteInstrument::BELL();
			case BlockLegacyIds::CLAY_BLOCK:
				return NoteInstrument::FLUTE();
			case BlockLegacyIds::PACKED_ICE:
				return NoteInstrument::CHIMES();
			case BlockLegacyIds::WOOD:
				return NoteInstrument::GUITAR();
			case BlockLegacyIds::BONE_BLOCK:
				return NoteInstrument::XYLOPHONE();
			case BlockLegacyIds::IRON_BLOCK:
				return NoteInstrument::IRON_XYLOPHONE();
			case BlockLegacyIds::SOUL_SAND:
				return NoteInstrument::COW_BELL();
			case BlockLegacyIds::PUMPKIN:
				return NoteInstrument::DIDGERIDOO();
			case BlockLegacyIds::EMERALD_BLOCK:
				return NoteInstrument::BIT();
			case BlockLegacyIds::HAY_BALE:
				return NoteInstrument::BANJO();
			case BlockLegacyIds::GLOWSTONE:
				return NoteInstrument::PLING();
			default:
				return NoteInstrument::PIANO();
		}
	}

	public function triggerNote() : void{
		$instrument = $this->getInstrument();
		$this->pos->getWorld()->addSound($this->pos, new NoteSound($instrument, $this->pitch));
		$this->pos->getWorld()->broadcastPacketToViewers($this->pos, BlockEventPacket::create($instrument->getMagicNumber(), $this->pitch, $this->pos));
	}
}
