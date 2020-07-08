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

namespace pocketmine\entity;

use pocketmine\utils\SingletonTrait;

final class AttributeFactory{
	use SingletonTrait;

	/** @var Attribute[] */
	private $attributes = [];

	public function __construct(){
		$this->register(Attribute::ABSORPTION, 0.00, 340282346638528859811704183484516925440.00, 0.00);
		$this->register(Attribute::SATURATION, 0.00, 20.00, 20.00);
		$this->register(Attribute::EXHAUSTION, 0.00, 5.00, 0.0, false);
		$this->register(Attribute::KNOCKBACK_RESISTANCE, 0.00, 1.00, 0.00);
		$this->register(Attribute::HEALTH, 0.00, 20.00, 20.00);
		$this->register(Attribute::MOVEMENT_SPEED, 0.00, 340282346638528859811704183484516925440.00, 0.10);
		$this->register(Attribute::FOLLOW_RANGE, 0.00, 2048.00, 16.00, false);
		$this->register(Attribute::HUNGER, 0.00, 20.00, 20.00);
		$this->register(Attribute::ATTACK_DAMAGE, 0.00, 340282346638528859811704183484516925440.00, 1.00, false);
		$this->register(Attribute::EXPERIENCE_LEVEL, 0.00, 24791.00, 0.00);
		$this->register(Attribute::EXPERIENCE, 0.00, 1.00, 0.00);
		$this->register(Attribute::UNDERWATER_MOVEMENT, 0.0, 340282346638528859811704183484516925440.0, 0.02);
		$this->register(Attribute::LUCK, -1024.0, 1024.0, 0.0);
		$this->register(Attribute::FALL_DAMAGE, 0.0, 340282346638528859811704183484516925440.0, 1.0);
		$this->register(Attribute::HORSE_JUMP_STRENGTH, 0.0, 2.0, 0.7);
		$this->register(Attribute::ZOMBIE_SPAWN_REINFORCEMENTS, 0.0, 1.0, 0.0);
		$this->register(Attribute::LAVA_MOVEMENT, 0.0, 340282346638528859811704183484516925440.0, 0.02);
	}

	public function get(string $id) : ?Attribute{
		return isset($this->attributes[$id]) ? clone $this->attributes[$id] : null;
	}

	public function mustGet(string $id) : Attribute{
		$result = $this->get($id);
		if($result === null){
			throw new \InvalidArgumentException("Attribute $id is not registered");
		}
		return $result;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function register(string $id, float $minValue, float $maxValue, float $defaultValue, bool $shouldSend = true) : Attribute{
		return $this->attributes[$id] = new Attribute($id, $minValue, $maxValue, $defaultValue, $shouldSend);
	}
}
