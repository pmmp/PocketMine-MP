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

namespace pocketmine\event\player;

use pocketmine\entity\Human;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\entity\EntityEvent;

/**
 * @phpstan-extends EntityEvent<Human>
 */
class PlayerExhaustEvent extends EntityEvent implements Cancellable{
	use CancellableTrait;

	public const CAUSE_ATTACK = 1;
	public const CAUSE_DAMAGE = 2;
	public const CAUSE_MINING = 3;
	public const CAUSE_HEALTH_REGEN = 4;
	public const CAUSE_POTION = 5;
	public const CAUSE_WALKING = 6;
	public const CAUSE_SPRINTING = 7;
	public const CAUSE_SWIMMING = 8;
	public const CAUSE_JUMPING = 9;
	public const CAUSE_SPRINT_JUMPING = 10;
	public const CAUSE_CUSTOM = 11;

	public function __construct(
		protected Human $human,
		private float $amount,
		private int $cause
	){
		$this->entity = $human;
	}

	/**
	 * @return Human
	 */
	public function getPlayer(){
		return $this->human;
	}

	public function getAmount() : float{
		return $this->amount;
	}

	public function setAmount(float $amount) : void{
		$this->amount = $amount;
	}

	/**
	 * Returns an int cause of the exhaustion - one of the constants at the top of this class.
	 */
	public function getCause() : int{
		return $this->cause;
	}
}
