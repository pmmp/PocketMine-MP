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

namespace pocketmine\world\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\utils\Color;

class PotionSplashParticle implements Particle{

	/** @var Color */
	private $color;

	public function __construct(Color $color){
		$this->color = $color;
	}

	/**
	 * Returns the default water-bottle splash colour.
	 *
	 * TODO: replace this with a standard surrogate object constant (first we need to implement them!)
	 *
	 * @return Color
	 */
	public static function DEFAULT_COLOR() : Color{
		return new Color(0x38, 0x5d, 0xc6);
	}

	/**
	 * @return Color
	 */
	public function getColor() : Color{
		return $this->color;
	}

	public function encode(Vector3 $pos){
		return LevelEventPacket::create(LevelEventPacket::EVENT_PARTICLE_SPLASH, $this->color->toARGB(), $pos);
	}
}
