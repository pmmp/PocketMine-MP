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
 * @method static NoteInstrument CHIMES()
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
			new self("bell", 5),
			new self("flute", 6),
			new self("chimes", 7),
			new self("guitar", 8),
			new self("xylophone", 9),
			new self("iron_xylophone", 10),
			new self("cow_bell", 11),
			new self("didgeridoo", 12),
			new self("bit", 13),
			new self("banjo", 14),
			new self("pling", 15)
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
