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

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerToggleFlightEvent extends PlayerEvent implements Cancellable{
	/** @var bool */
	protected $isFlying;

	/**
	 * @param Player $player
	 * @param bool   $isFlying
	 */
	public function __construct(Player $player, bool $isFlying){
		$this->player = $player;
		$this->isFlying = $isFlying;
	}

	/**
	 * @return bool
	 */
	public function isFlying() : bool{
		return $this->isFlying;
	}

}