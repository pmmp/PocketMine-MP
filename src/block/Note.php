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

	/** @var int */
	private $pitch = self::MIN_PITCH;

	/** @var NoteInstrument */
	private $instrument;

	public function __construct(BlockIdentifier $idInfo, string $name, ?BlockBreakInfo $breakInfo = null, ?NoteInstrument $noteblockInstrument = null){
		parent::__construct($idInfo, $name, $breakInfo ?? new BlockBreakInfo(0.8, BlockToolType::AXE), $noteblockInstrument ?? NoteInstrument::DOUBLE_BASS());
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->pos->getWorld()->getTile($this->pos);
		if($tile instanceof TileNote){
			$this->pitch = $tile->getPitch();
		}else{
			$this->pitch = self::MIN_PITCH;
		}
		$this->instrument = $this->getSide(Facing::DOWN)->getNoteblockInstrument();
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->pos->getWorld()->getTile($this->pos);
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

	public function onNearbyBlockChange() : void{
		$this->instrument = $this->getSide(Facing::DOWN)->getNoteblockInstrument();
	}

	/**
	 * Get the instrument that the noteblock will play when it is triggered
	 *
	 * @return NoteInstrument
	 */
	public function getInstrument() : NoteInstrument{
		return $this->instrument;
	}

	public function triggerNote() : void{
		$instrument = $this->getInstrument();
		$this->pos->getWorld()->addSound($this->pos, new NoteSound($instrument, $this->pitch));
		$this->pos->getWorld()->broadcastPacketToViewers($this->pos, BlockEventPacket::create($instrument->getMagicNumber(), $this->pitch, $this->pos));
	}
}
