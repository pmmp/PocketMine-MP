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
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
 *
 * @method static RecordType DISK_11()
 * @method static RecordType DISK_13()
 * @method static RecordType DISK_BLOCKS()
 * @method static RecordType DISK_CAT()
 * @method static RecordType DISK_CHIRP()
 * @method static RecordType DISK_FAR()
 * @method static RecordType DISK_MALL()
 * @method static RecordType DISK_MELLOHI()
 * @method static RecordType DISK_STAL()
 * @method static RecordType DISK_STRAD()
 * @method static RecordType DISK_WAIT()
 * @method static RecordType DISK_WARD()
 */
final class RecordType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new RecordType("disk_13", "C418 - 13", LevelSoundEventPacket::SOUND_RECORD_13, "item.record_13.desc"),
			new RecordType("disk_cat", "C418 - cat", LevelSoundEventPacket::SOUND_RECORD_CAT, "item.record_cat.desc"),
			new RecordType("disk_blocks", "C418 - blocks", LevelSoundEventPacket::SOUND_RECORD_BLOCKS, "item.record_blocks.desc"),
			new RecordType("disk_chirp", "C418 - chirp", LevelSoundEventPacket::SOUND_RECORD_CHIRP, "item.record_chirp.desc"),
			new RecordType("disk_far", "C418 - far", LevelSoundEventPacket::SOUND_RECORD_FAR, "item.record_far.desc"),
			new RecordType("disk_mall", "C418 - mall", LevelSoundEventPacket::SOUND_RECORD_MALL, "item.record_mall.desc"),
			new RecordType("disk_mellohi", "C418 - mellohi", LevelSoundEventPacket::SOUND_RECORD_MELLOHI, "item.record_mellohi.desc"),
			new RecordType("disk_stal", "C418 - stal", LevelSoundEventPacket::SOUND_RECORD_STAL, "item.record_stal.desc"),
			new RecordType("disk_strad", "C418 - strad", LevelSoundEventPacket::SOUND_RECORD_STRAD, "item.record_strad.desc"),
			new RecordType("disk_ward", "C418 - ward", LevelSoundEventPacket::SOUND_RECORD_WARD, "item.record_ward.desc"),
			new RecordType("disk_11", "C418 - 11", LevelSoundEventPacket::SOUND_RECORD_11, "item.record_11.desc"),
			new RecordType("disk_wait", "C418 - wait", LevelSoundEventPacket::SOUND_RECORD_WAIT, "item.record_wait.desc")
			//TODO: Lena Raine - Pigstep
		);
	}

	/** @var string */
	private $soundName;
	/** @var int */
	private $soundId;
	/** @var string */
	private $translationKey;

	private function __construct(string $enumName, string $soundName, int $soundId, string $translationKey){
		$this->Enum___construct($enumName);
		$this->soundName = $soundName;
		$this->soundId = $soundId;
		$this->translationKey = $translationKey;
	}

	public function getSoundName() : string{
		return $this->soundName;
	}

	public function getSoundId() : int{
		return $this->soundId;
	}

	public function getTranslationKey() : string{
		return $this->translationKey;
	}
}
