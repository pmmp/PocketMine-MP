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

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static NoteInstrument BANJO()
 * @method static NoteInstrument BASS_DRUM()
 * @method static NoteInstrument BELL()
 * @method static NoteInstrument BIT()
 * @method static NoteInstrument CHIME()
 * @method static NoteInstrument CLICKS_AND_STICKS()
 * @method static NoteInstrument COW_BELL()
 * @method static NoteInstrument DIDGERIDOO()
 * @method static NoteInstrument DOUBLE_BASS()
 * @method static NoteInstrument FLUTE()
 * @method static NoteInstrument GUITAR()
 * @method static NoteInstrument IRON_XYLOPHONE()
 * @method static NoteInstrument PIANO()
 * @method static NoteInstrument PLING()
 * @method static NoteInstrument SNARE()
 * @method static NoteInstrument XYLOPHONE()
 */
final class NoteInstrument{
	use EnumTrait;

	protected static function setup() : void{
		self::registerAll(
			new self("piano"),
			new self("bass_drum"),
			new self("snare"),
			new self("clicks_and_sticks"),
			new self("double_bass"),
			new self("bell"),
			new self("flute"),
			new self("chime"),
			new self("guitar"),
			new self("xylophone"),
			new self("iron_xylophone"),
			new self("cow_bell"),
			new self("didgeridoo"),
			new self("bit"),
			new self("banjo"),
			new self("pling")
		);
	}
}
