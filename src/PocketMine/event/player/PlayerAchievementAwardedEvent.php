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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

/**
 * Called when a player is awarded an achievement
 */
class PlayerAchievementAwardedEvent extends PlayerEvent implements Cancellable{
	public static $handlerList = null;

	/** @var string */
	protected $achievement;

	/**
	 * @param Player $player
	 * @param string $achievementId
	 */
	public function __construct(Player $player, $achievementId){
		$this->player = $player;
		$this->achievement = $achievementId;
	}

	public function getAchievement(){
		return $this->achievement;
	}
}