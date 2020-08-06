<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class NoteBlockSound extends Sound{

	public const INSTRUMENT_PIANO = 0;
	public const INSTRUMENT_BASS_DRUM = 1;
	public const INSTRUMENT_CLICK = 2;
	public const INSTRUMENT_TABOUR = 3;
	public const INSTRUMENT_BASS = 4;
	public const INSTRUMENT_GLOCKENSPIEL = 5;
	public const INSTRUMENT_FLUTE = 6;
	public const INSTRUMENT_CHIME = 7;
	public const INSTRUMENT_GUITAR = 8;
	public const INSTRUMENT_XYLOPHONE = 9;
	public const INSTRUMENT_VIBRAPHONE = 10;
	public const INSTRUMENT_COW_BELL = 11;
	public const INSTRUMENT_DIDGERIDOO = 12;
	public const INSTRUMENT_SQUARE_WAVE = 13;
	public const INSTRUMENT_BANJO = 14;
	public const INSTRUMENT_ELECTRIC_PIANO = 15;

	protected $instrument = self::INSTRUMENT_PIANO;
	protected $note = 0;

	/**
	 * NoteBlockSound constructor.
	 *
	 * @param Vector3 $pos
	 * @param int $instrument
	 * @param int $note
	 */
	public function __construct(Vector3 $pos, int $instrument = self::INSTRUMENT_PIANO, int $note = 0){
		parent::__construct($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ());

		$this->instrument = $instrument;
		$this->note = $note;
	}

	public function encode(){
		$pk = new BlockEventPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->eventType = $this->instrument;
		$pk->eventData = $this->note;

		$pk2 = new LevelSoundEventPacket();
		$pk2->sound = LevelSoundEventPacket::SOUND_NOTE;
		$pk2->position = $this;
		$pk2->extraData = $this->instrument << 8 | $this->note;

		return [$pk, $pk2];
	}
}
