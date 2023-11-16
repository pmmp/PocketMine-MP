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

use pocketmine\block\tile\Jukebox as JukeboxTile;
use pocketmine\item\Item;
use pocketmine\item\Record;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\RecordSound;
use pocketmine\world\sound\RecordStopSound;

class Jukebox extends Opaque{

	private ?Record $record = null;

	public function getFuelTime() : int{
		return 300;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player instanceof Player){
			if($this->record !== null){
				$this->ejectRecord();
			}elseif($item instanceof Record){
				$player->sendJukeboxPopup(KnownTranslationFactory::record_nowPlaying($item->getRecordType()->getTranslatableName()));
				$this->insertRecord($item->pop());
			}
		}

		$this->position->getWorld()->setBlock($this->position, $this);

		return true;
	}

	public function getRecord() : ?Record{
		return $this->record;
	}

	public function ejectRecord() : void{
		if($this->record !== null){
			$this->position->getWorld()->dropItem($this->position->add(0.5, 1, 0.5), $this->record);
			$this->record = null;
			$this->stopSound();
		}
	}

	public function insertRecord(Record $record) : void{
		if($this->record === null){
			$this->record = $record;
			$this->startSound();
		}
	}

	public function startSound() : void{
		if($this->record !== null){
			$this->position->getWorld()->addSound($this->position, new RecordSound($this->record->getRecordType()));
		}
	}

	public function stopSound() : void{
		$this->position->getWorld()->addSound($this->position, new RecordStopSound());
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
		$this->stopSound();
		return parent::onBreak($item, $player, $returnedItems);
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		$drops = parent::getDropsForCompatibleTool($item);
		if($this->record !== null){
			$drops[] = $this->record;
		}
		return $drops;
	}

	public function readStateFromWorld() : Block{
		parent::readStateFromWorld();
		$jukebox = $this->position->getWorld()->getTile($this->position);
		if($jukebox instanceof JukeboxTile){
			$this->record = $jukebox->getRecord();
		}

		return $this;
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$jukebox = $this->position->getWorld()->getTile($this->position);
		if($jukebox instanceof JukeboxTile){
			$jukebox->setRecord($this->record);
		}
	}

	//TODO: Jukebox has redstone effects, they are not implemented.
}
