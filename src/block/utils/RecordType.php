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

use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
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
			new RecordType("disk_13", "C418 - 13", LevelSoundEvent::RECORD_13, KnownTranslationFactory::item_record_13_desc()),
			new RecordType("disk_cat", "C418 - cat", LevelSoundEvent::RECORD_CAT, KnownTranslationFactory::item_record_cat_desc()),
			new RecordType("disk_blocks", "C418 - blocks", LevelSoundEvent::RECORD_BLOCKS, KnownTranslationFactory::item_record_blocks_desc()),
			new RecordType("disk_chirp", "C418 - chirp", LevelSoundEvent::RECORD_CHIRP, KnownTranslationFactory::item_record_chirp_desc()),
			new RecordType("disk_far", "C418 - far", LevelSoundEvent::RECORD_FAR, KnownTranslationFactory::item_record_far_desc()),
			new RecordType("disk_mall", "C418 - mall", LevelSoundEvent::RECORD_MALL, KnownTranslationFactory::item_record_mall_desc()),
			new RecordType("disk_mellohi", "C418 - mellohi", LevelSoundEvent::RECORD_MELLOHI, KnownTranslationFactory::item_record_mellohi_desc()),
			new RecordType("disk_stal", "C418 - stal", LevelSoundEvent::RECORD_STAL, KnownTranslationFactory::item_record_stal_desc()),
			new RecordType("disk_strad", "C418 - strad", LevelSoundEvent::RECORD_STRAD, KnownTranslationFactory::item_record_strad_desc()),
			new RecordType("disk_ward", "C418 - ward", LevelSoundEvent::RECORD_WARD, KnownTranslationFactory::item_record_ward_desc()),
			new RecordType("disk_11", "C418 - 11", LevelSoundEvent::RECORD_11, KnownTranslationFactory::item_record_11_desc()),
			new RecordType("disk_wait", "C418 - wait", LevelSoundEvent::RECORD_WAIT, KnownTranslationFactory::item_record_wait_desc())
			//TODO: Lena Raine - Pigstep
		);
	}

	private function __construct(
		string $enumName,
		private string $soundName,
		private int $soundId,
		private Translatable $translatableName
	){
		$this->Enum___construct($enumName);
	}

	public function getSoundName() : string{
		return $this->soundName;
	}

	public function getSoundId() : int{
		return $this->soundId;
	}

	public function getTranslatableName() : Translatable{ return $this->translatableName; }
}
