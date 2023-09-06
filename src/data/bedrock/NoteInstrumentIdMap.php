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

namespace pocketmine\data\bedrock;

use pocketmine\utils\SingletonTrait;
use pocketmine\world\sound\NoteInstrument;

final class NoteInstrumentIdMap{
	use SingletonTrait;
	/** @phpstan-use IntSaveIdMapTrait<NoteInstrument> */
	use IntSaveIdMapTrait;

	private function __construct(){
		$this->register(0, NoteInstrument::PIANO());
		$this->register(1, NoteInstrument::BASS_DRUM());
		$this->register(2, NoteInstrument::SNARE());
		$this->register(3, NoteInstrument::CLICKS_AND_STICKS());
		$this->register(4, NoteInstrument::DOUBLE_BASS());
		$this->register(5, NoteInstrument::BELL());
		$this->register(6, NoteInstrument::FLUTE());
		$this->register(7, NoteInstrument::CHIME());
		$this->register(8, NoteInstrument::GUITAR());
		$this->register(9, NoteInstrument::XYLOPHONE());
		$this->register(10, NoteInstrument::IRON_XYLOPHONE());
		$this->register(11, NoteInstrument::COW_BELL());
		$this->register(12, NoteInstrument::DIDGERIDOO());
		$this->register(13, NoteInstrument::BIT());
		$this->register(14, NoteInstrument::BANJO());
		$this->register(15, NoteInstrument::PLING());
	}
}
