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

namespace pocketmine\entity\animation;

use pocketmine\entity\projectile\Arrow;
use pocketmine\network\mcpe\protocol\ActorEventPacket;

class ArrowShakeAnimation implements Animation{

	/** @var Arrow */
	private $arrow;
	/** @var int */
	private $durationInTicks;

	public function __construct(Arrow $arrow, int $durationInTicks){
		$this->arrow = $arrow;
		$this->durationInTicks = $durationInTicks;
	}

	public function encode() : array{
		return [
			ActorEventPacket::create($this->arrow->getId(), ActorEventPacket::ARROW_SHAKE, $this->durationInTicks)
		];
	}
}
