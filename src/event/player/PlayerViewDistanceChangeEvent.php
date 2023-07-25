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

use pocketmine\player\Player;

/**
 * Called when a player requests a different viewing distance than the current one.
 */
class PlayerViewDistanceChangeEvent extends PlayerEvent{
	public function __construct(
		Player $player,
		protected int $oldDistance,
		protected int $newDistance
	){
		$this->player = $player;
	}

	/**
	 * Returns the new view radius, measured in chunks.
	 */
	public function getNewDistance() : int{
		return $this->newDistance;
	}

	/**
	 * Returns the old view radius, measured in chunks.
	 * A value of -1 means that the player has just connected and did not have a view distance before this event.
	 */
	public function getOldDistance() : int{
		return $this->oldDistance;
	}
}
