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

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;

class EntityRegainHealthEvent extends EntityEvent{
	public static $handlerList = null;

	public const CAUSE_REGEN = 0;
	public const CAUSE_EATING = 1;
	public const CAUSE_MAGIC = 2;
	public const CAUSE_CUSTOM = 3;
	public const CAUSE_SATURATION = 4;

	/** @var float */
	private $amount;
	/** @var int */
	private $reason;


	/**
	 * @param Entity $entity
	 * @param float  $amount
	 * @param int    $regainReason
	 */
	public function __construct(Entity $entity, float $amount, int $regainReason){
		$this->entity = $entity;
		$this->amount = $amount;
		$this->reason = $regainReason;
	}

	/**
	 * @return float
	 */
	public function getAmount() : float{
		return $this->amount;
	}

	/**
	 * @param float $amount
	 */
	public function setAmount(float $amount){
		$this->amount = $amount;
	}

	/**
	 * Returns one of the CAUSE_* constants to indicate why this regeneration occurred.
	 * @return int
	 */
	public function getRegainReason() : int{
		return $this->reason;
	}

	public function isCancellable() : bool{
		return true;
	}
}