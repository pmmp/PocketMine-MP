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

namespace pocketmine\item;

class FireworksExplosion{
	/** @var int|null */
	private $color;
	/** @var int|null */
	private $fade;
	/** @var bool */
	private $flicker;
	/** @var bool */
	private $trail;
	/** @var int */
	private $type;

	/**
	 * FireworksExplosion constructor.
	 * @param int|null $color
	 * @param int|null $fade
	 * @param bool     $flicker
	 * @param bool     $trail
	 * @param int      $type
	 */
	public function __construct(?int $color = null, ?int $fade = null, bool $flicker = false, bool $trail = false, int $type = Fireworks::TYPE_SMALL_BALL){
		$this->color = $color;
		$this->fade = $fade;
		$this->flicker = $flicker;
		$this->trail = $trail;
		$this->type = $type;
	}

	/**
	 * @return int|null
	 */
	public function getColor() : ?int{
		return $this->color;
	}

	/**
	 * @return int|null
	 */
	public function getFade() : ?int{
		return $this->fade;
	}

	/**
	 * @return bool
	 */
	public function isFlickering() : bool{
		return $this->flicker;
	}

	/**
	 * @return bool
	 */
	public function hasTrail() : bool{
		return $this->trail;
	}

	/**
	 * @return int
	 */
	public function getType() : int{
		return $this->type;
	}
}
