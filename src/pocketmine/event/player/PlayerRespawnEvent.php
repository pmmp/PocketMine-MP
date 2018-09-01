<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\event\player;

use pocketmine\level\Position;
use pocketmine\Player;

/**
 * Called when a player is respawned (or first time spawned)
 */
class PlayerRespawnEvent extends PlayerEvent{
	/** @var Position */
	protected $position;

	/**
	 * @param Player   $player
	 * @param Position $position
	 */
	public function __construct(Player $player, Position $position){
		$this->player = $player;
		$this->position = $position;
	}

	/**
	 * @return Position
	 */
	public function getRespawnPosition() : Position{
		return $this->position;
	}

	/**
	 * @param Position $position
	 */
	public function setRespawnPosition(Position $position) : void{
		$this->position = $position;
	}
}
