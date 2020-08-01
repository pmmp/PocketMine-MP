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

use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class RecordSound implements Sound{

	public const SOUND_MAP = [
		ItemIds::RECORD_13 => LevelSoundEventPacket::SOUND_RECORD_13,
		ItemIds::RECORD_CAT => LevelSoundEventPacket::SOUND_RECORD_CAT,
		ItemIds::RECORD_BLOCKS => LevelSoundEventPacket::SOUND_RECORD_BLOCKS,
		ItemIds::RECORD_CHIRP => LevelSoundEventPacket::SOUND_RECORD_CHIRP,
		ItemIds::RECORD_FAR => LevelSoundEventPacket::SOUND_RECORD_FAR,
		ItemIds::RECORD_MALL => LevelSoundEventPacket::SOUND_RECORD_MALL,
		ItemIds::RECORD_MELLOHI => LevelSoundEventPacket::SOUND_RECORD_MELLOHI,
		ItemIds::RECORD_STAL => LevelSoundEventPacket::SOUND_RECORD_STAL,
		ItemIds::RECORD_STRAD => LevelSoundEventPacket::SOUND_RECORD_STRAD,
		ItemIds::RECORD_WARD => LevelSoundEventPacket::SOUND_RECORD_WARD,
		ItemIds::RECORD_11 => LevelSoundEventPacket::SOUND_RECORD_11,
		ItemIds::RECORD_WAIT => LevelSoundEventPacket::SOUND_RECORD_WAIT,
		// PIGSTEP
	];

	/** @var int */
	private $sound;

	public function __construct(int $record){
		$this->sound = self::SOUND_MAP[$record];
	}

	public function encode(?Vector3 $pos){
		return LevelSoundEventPacket::create($this->sound, $pos);
	}
}
