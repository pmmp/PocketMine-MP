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

use pocketmine\event\Event;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\promise\Promise;
use pocketmine\utils\ObjectSet;

/**
 * Allows the plugins to specify promise that need to be completed before the PlayerPreLoginEvent is fired.
 */
class PlayerLoginPrepareEvent extends Event{
	public function __construct(
		private NetworkSession $session,
		private ObjectSet $promises
	){ }

	public function getNetworkSession() : NetworkSession{
		return $this->session;
	}

	public function getAddress() : string{
		return $this->session->getIp();
	}

	public function getPort() : int{
		return $this->session->getPort();
	}

	/**
	 * Adds a promise to the waiting list for the login sequence.
	 * Once all the promises that have been added have been completed,
	 * the player login sequence will continue and the PlayerPreLoginEvent will be fired.
	 *
	 * @phpstan-param Promise<null> $promise
	 */
	public function addPromise(Promise $promise) : void{
		$this->promises->add($promise);
	}
}
