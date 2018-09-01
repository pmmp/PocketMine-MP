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
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class PlaySound extends Sound{

	/** @var string */
	protected $soundName = "";
	/** @var float */
	protected $volume = 1;
	/** @var float */
	protected $pitch = 1;

	public function __construct(Vector3 $pos, string $soundName, float $volume = 100, float $pitch = 1){
		parent::__construct($pos->x, $pos->y, $pos->z);
		$this->soundName = $soundName;
		$this->volume = $volume;
		$this->pitch = $pitch;
	}

	public function encode(){
		$pk = new PlaySoundPacket();
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->soundName = $this->soundName;
		$pk->volume = $this->volume;
		$pk->pitch = $this->pitch;

		return $pk;
	}

}