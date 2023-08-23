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
use pocketmine\utils\WaitGroup;

/**
 * This event is triggered when a player's login sequence
 * is completed and their connection is confirmed. It serves as a hook for plugins to
 * specify tasks that need to be executed before the subsequent steps take place.
 * Plugins can utilize a WaitGroup to manage synchronization between tasks.
 *
 * Usage:
 * This event is crucial when plugins require actions to be performed immediately
 * after a player logs in. By leveraging the WaitGroup mechanism, plugins can ensure
 * that each task is completed before allowing the continuation of subsequent parts
 * of the player's session.
 *
 * The typical use cases for this event include:
 * - Carrying out preparatory tasks such as configuring default preferences, setting
 * player-specific settings, or initializing in-game assets.
 * - Loading initial player data from external sources or databases before the main
 * gameplay loop begins.
 * - Providing a tailored welcome experience, which might involve sending messages,
 * granting starter items, or awarding initial rewards.
 *
 * Note:
 * After each task is completed, it's essential to call the `done` method of the WaitGroup
 * to signal its completion and allow the WaitGroup to determine when to proceed.
 */
  class PlayerCreatedEvent extends PlayerEvent{
	 private WaitGroup $waitGroup;

	public function __construct(
		 Player $player
	){
		$this->player = $player;
		$this->waitGroup = new WaitGroup();
	}

	public function getWaitGroup() : WaitGroup{
		return $this->waitGroup;
	}
 }
