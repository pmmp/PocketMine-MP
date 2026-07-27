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

namespace pocketmine\world\sound;

use pocketmine\block\utils\RecordType;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class RecordSound implements Sound{
	public function __construct(private RecordType $recordType){}

	public function encode(Vector3 $pos) : array{
		return [LevelSoundEventPacket::nonActorSound(match($this->recordType){
			RecordType::DISK_13 => LevelSoundEvent::RECORD_13,
			RecordType::DISK_5 => LevelSoundEvent::RECORD_5,
			RecordType::DISK_CAT => LevelSoundEvent::RECORD_CAT,
			RecordType::DISK_BLOCKS => LevelSoundEvent::RECORD_BLOCKS,
			RecordType::DISK_CHIRP => LevelSoundEvent::RECORD_CHIRP,
			RecordType::DISK_CREATOR => LevelSoundEvent::RECORD_CREATOR,
			RecordType::DISK_CREATOR_MUSIC_BOX => LevelSoundEvent::RECORD_CREATOR_MUSIC_BOX,
			RecordType::DISK_FAR => LevelSoundEvent::RECORD_FAR,
			RecordType::DISK_LAVA_CHICKEN => LevelSoundEvent::RECORD_LAVA_CHICKEN,
			RecordType::DISK_MALL => LevelSoundEvent::RECORD_MALL,
			RecordType::DISK_MELLOHI => LevelSoundEvent::RECORD_MELLOHI,
			RecordType::DISK_OTHERSIDE => LevelSoundEvent::RECORD_OTHERSIDE,
			RecordType::DISK_PIGSTEP => LevelSoundEvent::RECORD_PIGSTEP,
			RecordType::DISK_PRECIPICE => LevelSoundEvent::RECORD_PRECIPICE,
			RecordType::DISK_RELIC => LevelSoundEvent::RECORD_RELIC,
			RecordType::DISK_STAL => LevelSoundEvent::RECORD_STAL,
			RecordType::DISK_STRAD => LevelSoundEvent::RECORD_STRAD,
			RecordType::DISK_WARD => LevelSoundEvent::RECORD_WARD,
			RecordType::DISK_11 => LevelSoundEvent::RECORD_11,
			RecordType::DISK_WAIT => LevelSoundEvent::RECORD_WAIT
		}, $pos, false)];
	}
}
