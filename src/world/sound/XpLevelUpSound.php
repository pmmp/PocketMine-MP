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
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use function intdiv;
use function min;

class XpLevelUpSound implements Sound{

	/** @var int */
	private $xpLevel;

	public function __construct(int $xpLevel){
		$this->xpLevel = $xpLevel;
	}

	public function getXpLevel() : int{
		return $this->xpLevel;
	}

	public function encode(?Vector3 $pos){
		//No idea why such odd numbers, but this works...
		//TODO: check arbitrary volume
		return LevelSoundEventPacket::create(LevelSoundEventPacket::SOUND_LEVELUP, $pos, 0x10000000 * intdiv(min(30, $this->xpLevel), 5));
	}
}
