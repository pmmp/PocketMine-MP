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

use pocketmine\world\World;
use pocketmine\item\Record;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\sound\RecordSound;
use pocketmine\world\sound\RecordStopSound;

class Jukebox extends Spawnable{
	public const TAG_RECORD = "record";

	/** @var Record|null */
	private $record = null;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
	}

	public function getRecord() : ?Record{
		return $this->record;
	}

	public function ejectRecord() : bool{
		if($this->record !== null){
			$this->getPos()->getWorld()->dropItem($this->getPos()->add(0.5,1,0.5), $this->record);
			$this->record = null;
			$this->stopSound();
			return true;
		}
		return false;
	}

	public function insertRecord(Record $record) : bool{
		if($this->record === null){
			$this->record = $record;
			$this->startSound();
			return true;
		}
		return false;
	}

	public function startSound() : void{
		if($this->record !== null){
			$this->getPos()->getWorld()->addSound($this->getPos(), new RecordSound($this->record->getId()));
		}
	}

	public function stopSound() : void{
		$this->getPos()->getWorld()->addSound($this->getPos(), new RecordStopSound());
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if(($tag = $nbt->getCompoundTag(self::TAG_RECORD)) !== null){
			$record = Record::nbtDeserialize($tag);
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

	protected function addAdditionalSpawnData(CompoundTag $nbt): void{}
}