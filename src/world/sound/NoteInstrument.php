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
 * @method static NoteInstrument BASS_DRUM()
 * @method static NoteInstrument CLICKS_AND_STICKS()
 * @method static NoteInstrument DOUBLE_BASS()
 * @method static NoteInstrument PIANO()
 * @method static NoteInstrument SNARE()
 * @method static NoteInstrument GLOCKENSPIEL()
 * @method static NoteInstrument FLUTE()
 * @method static NoteInstrument CHIME()
 * @method static NoteInstrument GUITAR()
 * @method static NoteInstrument XYLOPHONE()
 * @method static NoteInstrument VIBRAPHONE()
 * @method static NoteInstrument COW_BELL()
 * @method static NoteInstrument DIGGERIDOO()
 * @method static NoteInstrument SQUARE_WAVE()
 * @method static NoteInstrument BANJO()
 * @method static NoteInstrument ELECTRIC_PIANO()
 */
final class NoteInstrument{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("piano", 0),
			new self("bass_drum", 1),
			new self("snare", 2),
			new self("clicks_and_sticks", 3),
			new self("double_bass", 4),
			new self("glockenspiel", 5),
			new self("flute", 6),
			new self("chime", 7),
			new self("guitar", 8),
			new self("xylophone", 9),
			new self("vibraphone", 10),
			new self("cow_bell", 11),
			new self("diggeridoo", 12),
			new self("square_wave", 13),
			new self("banjo", 14),
			new self("electric_piano", 15)
		);
	}

	/** @var int */
	private $magicNumber;

	private function __construct(string $name, int $magicNumber){
		$this->Enum___construct($name);
		$this->magicNumber = $magicNumber;
	}

	public function getMagicNumber() : int{
		return $this->magicNumber;
	}
}
