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

class PlayerToggleGlideEvent extends PlayerEvent implements Cancellable{
	/** @var bool */
	protected $isGlide;

	/**
	 * @param Player $player
	 * @param bool   $isGlide
	 */
	public function __construct(Player $player, bool $isGlide){
		$this->player = $player;
		$this->isGlide = $isGlide;
	}

	/**
	 * @return bool
	 */
	public function isGlide() : bool{
		return $this->isGlide;
	}

}