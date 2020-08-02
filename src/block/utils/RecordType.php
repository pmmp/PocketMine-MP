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

namespace pocketmine\block\utils;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see RegistryTrait::_generateMethodAnnotations()
 *
 * @method static self DISK_13()
 * @method static self DISK_CAT()
 * @method static self DISK_BLOCKS()
 * @method static self DISK_CHIRP()
 * @method static self DISK_FAR()
 * @method static self DISK_MALL()
 * @method static self DISK_MELLOHI()
 * @method static self DISK_STAL()
 * @method static self DISK_STRAD()
 * @method static self DISK_WARD()
 * @method static self DISK_11()
 * @method static self DISK_WAIT()
 */
final class RecordType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new RecordType("disk_13", "C418 - 13", LevelSoundEventPacket::SOUND_RECORD_13),
			new RecordType("disk_cat", "C418 - Cat", LevelSoundEventPacket::SOUND_RECORD_CAT),
			new RecordType("disk_blocks", "C418 - Blocks", LevelSoundEventPacket::SOUND_RECORD_BLOCKS),
			new RecordType("disk_chirp", "C418 - Chirp", LevelSoundEventPacket::SOUND_RECORD_CHIRP),
			new RecordType("disk_far", "C418 - Far", LevelSoundEventPacket::SOUND_RECORD_FAR),
			new RecordType("disk_mall", "C418 - Mall", LevelSoundEventPacket::SOUND_RECORD_MALL),
			new RecordType("disk_mellohi", "C418 - Mellohi", LevelSoundEventPacket::SOUND_RECORD_MELLOHI),
			new RecordType("disk_stal", "C418 - Stal", LevelSoundEventPacket::SOUND_RECORD_STAL),
			new RecordType("disk_strad", "C418 - Strad", LevelSoundEventPacket::SOUND_RECORD_STRAD),
			new RecordType("disk_ward", "C418 - Ward", LevelSoundEventPacket::SOUND_RECORD_WARD),
			new RecordType("disk_11", "C418 - 11", LevelSoundEventPacket::SOUND_RECORD_11),
			new RecordType("disk_wait", "C418 - Wait", LevelSoundEventPacket::SOUND_RECORD_WAIT)
			//TODO: Lena Raine - Pigstep
		);
	}

	/** @var string */
	private $soundName;
	/** @var int */
	private $soundId;

	private function __construct(string $enumName, string $soundName, int $soundId){
		$this->Enum___construct($enumName);
		$this->soundName = $soundName;
		$this->soundId = $soundId;
	}

	public function getSoundName() : string{
		return $this->soundName;
	}

	public function getSoundId() : int{
		return $this->soundId;
	}
}
