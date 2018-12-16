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

namespace pocketmine\level\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class GenericSound extends Sound{

	/** @var int */
	protected $id;
	/** @var float */
	protected $pitch = 0;

	public function __construct(int $id, float $pitch = 0){
		$this->id = $id;
		$this->pitch = $pitch * 1000;
	}

	public function getPitch() : float{
		return $this->pitch / 1000;
	}

	public function setPitch(float $pitch) : void{
		$this->pitch = $pitch * 1000;
	}

	public function encode(Vector3 $pos){
		$pk = new LevelEventPacket;
		$pk->evid = $this->id;
		$pk->position = $pos;
		$pk->data = (int) $this->pitch;

		return $pk;
	}
}
