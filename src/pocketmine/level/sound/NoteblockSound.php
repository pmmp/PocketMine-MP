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

class NoteblockSound extends GenericSound {

	protected $instrument;
	protected $pitch;

	public const INSTRUMENT_PIANO = 0;
	public const INSTRUMENT_BASS_DRUM = 1;
	public const INSTRUMENT_CLICK = 2;
	public const INSTRUMENT_TABOUR = 3;
	public const INSTRUMENT_BASS = 4;

	/**
	 * NoteblockSound constructor.
	 *
	 * @param Vector3 $pos
	 * @param int     $instrument
	 * @param float     $pitch
	 */
	public function __construct(Vector3 $pos, int $instrument = self::INSTRUMENT_PIANO, float $pitch = 0){
		parent::__construct($pos, $instrument, $pitch);
		$this->instrument = $instrument;
		$this->pitch = $pitch;
	}

	/**
	 * @return array
	 */
	public function encode(){
		$pk = new BlockEventPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->eventType = $this->instrument;
		$pk->eventData = (int) $this->pitch;

		$pk2 = new LevelSoundEventPacket();
		$pk2->sound = LevelSoundEventPacket::SOUND_NOTE;
		$pk2->position = $this;
		$pk2->extraData = $this->instrument;
		$pk2->pitch = (int) $this->pitch;

		return [$pk, $pk2];
	}
}