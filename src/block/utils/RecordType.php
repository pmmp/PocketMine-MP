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
use pocketmine\utils\LegacyEnumShimTrait;
use function spl_object_id;

/**
 * TODO: These tags need to be removed once we get rid of LegacyEnumShimTrait (PM6)
 *  These are retained for backwards compatibility only.
 *
 * @method static RecordType DISK_11()
 * @method static RecordType DISK_13()
 * @method static RecordType DISK_5()
 * @method static RecordType DISK_BLOCKS()
 * @method static RecordType DISK_CAT()
 * @method static RecordType DISK_CHIRP()
 * @method static RecordType DISK_FAR()
 * @method static RecordType DISK_MALL()
 * @method static RecordType DISK_MELLOHI()
 * @method static RecordType DISK_OTHERSIDE()
 * @method static RecordType DISK_PIGSTEP()
 * @method static RecordType DISK_STAL()
 * @method static RecordType DISK_STRAD()
 * @method static RecordType DISK_WAIT()
 * @method static RecordType DISK_WARD()
 *
 * @phpstan-type TMetadata array{0: string, 1: LevelSoundEvent::*, 2: Translatable}
 */
enum RecordType{
	use LegacyEnumShimTrait;

	case DISK_13;
	case DISK_5;
	case DISK_CAT;
	case DISK_BLOCKS;
	case DISK_CHIRP;
	case DISK_FAR;
	case DISK_MALL;
	case DISK_MELLOHI;
	case DISK_OTHERSIDE;
	case DISK_PIGSTEP;
	case DISK_STAL;
	case DISK_STRAD;
	case DISK_WARD;
	case DISK_11;
	case DISK_WAIT;

	/**
	 * @phpstan-return TMetadata
	 */
	private function getMetadata() : array{
		/** @phpstan-var array<int, TMetadata> $cache */
		static $cache = [];

		return $cache[spl_object_id($this)] ??= match($this){
			self::DISK_13 => ["C418 - 13", LevelSoundEvent::RECORD_13, KnownTranslationFactory::item_record_13_desc()],
			self::DISK_5 => ["Samuel Åberg - 5", LevelSoundEvent::RECORD_5, KnownTranslationFactory::item_record_5_desc()],
			self::DISK_CAT => ["C418 - cat", LevelSoundEvent::RECORD_CAT, KnownTranslationFactory::item_record_cat_desc()],
			self::DISK_BLOCKS => ["C418 - blocks", LevelSoundEvent::RECORD_BLOCKS, KnownTranslationFactory::item_record_blocks_desc()],
			self::DISK_CHIRP => ["C418 - chirp", LevelSoundEvent::RECORD_CHIRP, KnownTranslationFactory::item_record_chirp_desc()],
			self::DISK_FAR => ["C418 - far", LevelSoundEvent::RECORD_FAR, KnownTranslationFactory::item_record_far_desc()],
			self::DISK_MALL => ["C418 - mall", LevelSoundEvent::RECORD_MALL, KnownTranslationFactory::item_record_mall_desc()],
			self::DISK_MELLOHI => ["C418 - mellohi", LevelSoundEvent::RECORD_MELLOHI, KnownTranslationFactory::item_record_mellohi_desc()],
			self::DISK_OTHERSIDE => ["Lena Raine - otherside", LevelSoundEvent::RECORD_OTHERSIDE, KnownTranslationFactory::item_record_otherside_desc()],
			self::DISK_PIGSTEP => ["Lena Raine - Pigstep", LevelSoundEvent::RECORD_PIGSTEP, KnownTranslationFactory::item_record_pigstep_desc()],
			self::DISK_STAL => ["C418 - stal", LevelSoundEvent::RECORD_STAL, KnownTranslationFactory::item_record_stal_desc()],
			self::DISK_STRAD => ["C418 - strad", LevelSoundEvent::RECORD_STRAD, KnownTranslationFactory::item_record_strad_desc()],
			self::DISK_WARD => ["C418 - ward", LevelSoundEvent::RECORD_WARD, KnownTranslationFactory::item_record_ward_desc()],
			self::DISK_11 => ["C418 - 11", LevelSoundEvent::RECORD_11, KnownTranslationFactory::item_record_11_desc()],
			self::DISK_WAIT => ["C418 - wait", LevelSoundEvent::RECORD_WAIT, KnownTranslationFactory::item_record_wait_desc()]
		};
	}

	public function getSoundName() : string{
		return $this->getMetadata()[0];
	}

	public function getSoundId() : int{
		return $this->getMetadata()[1];
	}

	public function getTranslatableName() : Translatable{
		return $this->getMetadata()[2];
	}
}
