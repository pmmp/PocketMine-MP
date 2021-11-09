<?php

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
