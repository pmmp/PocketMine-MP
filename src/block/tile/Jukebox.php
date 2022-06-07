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

namespace pocketmine\block\tile;

use pocketmine\item\Item;
use pocketmine\item\Record;
use pocketmine\nbt\tag\CompoundTag;

class Jukebox extends Spawnable{
	private const TAG_RECORD = "RecordItem"; //Item CompoundTag

	private ?Record $record = null;

	public function getRecord() : ?Record{
		return $this->record;
	}

	public function setRecord(?Record $record) : void{
		$this->record = $record;
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if(($tag = $nbt->getCompoundTag(self::TAG_RECORD)) !== null){
			$record = Item::nbtDeserialize($tag);
			if($record instanceof Record){
				$this->record = $record;
			}
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		if($this->record !== null){
			$nbt->setTag(self::TAG_RECORD, $this->record->nbtSerialize());
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		//this is needed for the note particles to show on the client side
		if($this->record !== null){
			$nbt->setTag(self::TAG_RECORD, $this->record->nbtSerialize());
		}
	}
}
