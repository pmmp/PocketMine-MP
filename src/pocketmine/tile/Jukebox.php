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

use pocketmine\item\Record;
use pocketmine\item\Item;
use pocketmine\level\particle\{GenericParticle, Particle};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\{LevelSoundEventPacket, TextPacket};
use pocketmine\Player;
use pocketmine\Server;

class Jukebox extends Spawnable{

	public const TAG_RECORD_ITEM = "RecordItem";

	/** @var Record|null */
	protected $recordItem = null;

	public function setRecordItem(?Record $item) : void{
		$this->recordItem = $item;
		$this->onChanged();
	}

	public function getRecordItem() : ?Record{
		return $this->recordItem;
	}

	public function playDisc(?Player $player = null) : void{
		if($this->getRecordItem() instanceof Record){
			$this->level->broadcastLevelSoundEvent($this, $this->getRecordItem()->getSoundId());

			if($player instanceof Player){
				$pk = new TextPacket();
				$pk->type = TextPacket::TYPE_JUKEBOX_POPUP;
				$pk->needsTranslation = true;
				$pk->message = "record.nowPlaying";
				$pk->parameters = [
					ucwords(str_ireplace([
						"record", "."
					], [
						"", ""
					], $this->getRecordItem()->getSoundId()))
				];
				$player->sendDataPacket($pk);
			}

			$this->scheduleUpdate();
		}
	}

	public function stopDisc() : void{
		if($this->getRecordItem() instanceof Record){
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_STOP_RECORD);
		}
	}

	public function dropDisc() : void{
		if($this->getRecordItem() instanceof Record){
			$this->stopDisc();
			$this->level->dropItem($this->add(0.5, 1, 0.5), $this->getRecordItem());
			$this->setRecordItem(null);
		}
	}

	public function hasRecordItem() : bool{
		return $this->recordItem instanceof Record;
	}

	public function getDefaultName() : string{
		return "Jukebox";
	}

	protected function readSaveData(CompoundTag $nbt) : void{
		if($nbt->hasTag(self::TAG_RECORD_ITEM)){
			$this->recordItem = Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_RECORD_ITEM));

			$this->scheduleUpdate();
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		if($this->recordItem !== null){
			$nbt->setTag($this->recordItem->nbtSerialize(-1, self::TAG_RECORD_ITEM));
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{

	}

	public function onUpdate() : bool{
		if($this->hasRecordItem()){
			if(Server::getInstance()->getTick() % 30 === 0){
				$this->level->addParticle(new GenericParticle($this->add(0.5, 1.5, 0.5), Particle::TYPE_NOTE, mt_rand(0, 4) | mt_rand(0, 24)));
			}
			return true;
		}
		return false;
	}

	public function spawnTo(Player $player) : bool{
		if($this->hasRecordItem()){
			$pk = new LevelSoundEventPacket();
			$pk->sound = $this->getRecordItem()->getSoundId();
			$pk->position = $this;

			$player->sendDataPacket($pk);
		}
		return parent::spawnTo($player);
	}
}