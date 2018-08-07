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

namespace pocketmine\tile;

use pocketmine\nbt\tag\CompoundTag;

class NoteBlock extends Spawnable{
	public const TAG_NOTE = "note";
	public const TAG_POWERED = "powered";

	/** @var int */
	protected $note = 0;
	/** @var bool */
	protected $powered = false;

	protected function readSaveData(CompoundTag $nbt) : void{
		$this->note = $nbt->getInt(self::TAG_NOTE, 0, true);
		$this->powered = boolval($nbt->getByte(self::TAG_POWERED, 0));
	}

	public function setNote(int $note) : void{
		$this->note = $note;
	}

	public function getNote() : int{
		return $this->note;
	}

	public function setPowered(bool $value) : void{
		$this->powered = $value;
	}

	public function isPowered() : bool{
		return $this->powered;
	}

	public function getDefaultName() : string{
		return "NoteBlock";
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_NOTE, $this->note, true);
		$nbt->setByte(self::TAG_POWERED, intval($this->powered));
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_NOTE, $this->note, true);
		$nbt->setByte(self::TAG_POWERED, intval($this->powered));
	}
}