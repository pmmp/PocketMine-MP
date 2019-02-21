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
use pocketmine\event\CancellableTrait;
use pocketmine\Player;

class PlayerToggleSwimEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	/** @var bool */
	protected $isSwimming;

	/**
	 * @param Player $player
	 * @param bool   $isSwimming
	 */
	public function __construct(Player $player, bool $isSwimming){
		$this->player = $player;
		$this->isSwimming = $isSwimming;
	}

	/**
	 * @return bool
	 */
	public function isSwimming() : bool{
		return $this->isSwimming;
	}

}