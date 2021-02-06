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
 * This must be regenerated whenever enum members are added, removed or changed.
 * @see EnumTrait::_generateMethodAnnotations()
 *
 * @method static self PIANO()
 * @method static self BASS_DRUM()
 * @method static self SNARE()
 * @method static self CLICKS_AND_STICKS()
 * @method static self DOUBLE_BASS()
 * @method static self BELL()
 * @method static self FLUTE()
 * @method static self CHIMES()
 * @method static self GUITAR()
 * @method static self XYLOPHONE()
 * @method static self IRON_XYLOPHONE()
 * @method static self COW_BELL()
 * @method static self DIDGERIDOO()
 * @method static self BIT()
 * @method static self BANJO()
 * @method static self PLING()
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
