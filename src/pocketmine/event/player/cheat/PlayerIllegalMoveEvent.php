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

namespace pocketmine\event\player\cheat;

use pocketmine\event\Cancellable;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Called when a player attempts to perform movement cheats such as clipping through blocks.
 */
class PlayerIllegalMoveEvent extends PlayerCheatEvent implements Cancellable{

	/** @var Vector3 */
	private $attemptedPosition;
	/** @var Vector3 */
	private $originalPosition;
	/** @var Vector3 */
	private $expectedPosition;

	public function __construct(Player $player, Vector3 $attemptedPosition, Vector3 $originalPosition){
		$this->player = $player;
		$this->attemptedPosition = $attemptedPosition;
		$this->originalPosition = $originalPosition;
		$this->expectedPosition = $player->asVector3();
	}

	/**
	 * Returns the position the player attempted to move to.
	 */
	public function getAttemptedPosition() : Vector3{
		return $this->attemptedPosition;
	}

	public function getOriginalPosition() : Vector3{
		return $this->originalPosition;
	}

	public function getExpectedPosition() : Vector3{
		return $this->expectedPosition;
	}
}
