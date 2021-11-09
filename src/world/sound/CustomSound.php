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

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class CustomSound implements Sound{

	private string $soundName;

	private float $volume;
	private float $pitch;

	public function __construct(string $soundName, float $volume = 1.0, float $pitch = 1.0){
		$this->soundName = $soundName;

		$this->volume = $volume;
		$this->pitch = $pitch;
	}

	public function encode(Vector3 $pos) : array{
		return [PlaySoundPacket::create($this->soundName, $pos->x, $pos->y, $pos->z, $this->volume, $this->pitch)];
	}
}
