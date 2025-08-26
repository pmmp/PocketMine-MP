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

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;

/**
 * Called when a player performs a sprinting knockback action on an entity.
 * Cancelling this event will prevent the knockback effect from being applied.
 * This event is typically used to modify the knockback force or prevent the action entirely.
 */
class PlayerSprintKnockBackEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	private Entity $target;
	private float $knockBack;
	private float $verticalKnockBackLimit;

	public function __construct(Player $attacker, Entity $target, float $knockBack, float $verticalKnockBackLimit){
		$this->player = $attacker;
		$this->target = $target;
		$this->knockBack = $knockBack;
		$this->verticalKnockBackLimit = $verticalKnockBackLimit;
	}

	public function getAttacker(): Player {
		return $this->player;
	}

	public function getTarget(): Entity {
		return $this->target;
	}

	public function getKnockBack(): float {
		return $this->knockBack;
	}

	public function setKnockBack(float $knockBack) : void{
		$this->knockBack = $knockBack;
	}

	public function getVerticalKnockBackLimit() : float{
		return $this->verticalKnockBackLimit;
	}

	public function setVerticalKnockBackLimit(float $verticalKnockBackLimit) : void{
		$this->verticalKnockBackLimit = $verticalKnockBackLimit;
	}
}
