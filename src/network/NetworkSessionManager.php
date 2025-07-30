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

namespace pocketmine\network;

use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\NetworkSession;
use function count;
use function spl_object_id;

class NetworkSessionManager{

	/**
	 * @var NetworkSession[]
	 * @phpstan-var array<int, NetworkSession>
	 */
	private array $sessions = [];

	/**
	 * @var NetworkSession[]
	 * @phpstan-var array<int, NetworkSession>
	 */
	private array $pendingLoginSessions = [];

	/**
	 * Adds a network session to the manager. This should only be called on session creation.
	 */
	public function add(NetworkSession $session) : void{
		$idx = spl_object_id($session);
		$this->sessions[$idx] = $session;
		$this->pendingLoginSessions[$idx] = $session;
	}

	/**
	 * Marks the session as having sent a login request. After this point, they are counted towards the total player
	 * count.
	 */
	public function markLoginReceived(NetworkSession $session) : void{
		$idx = spl_object_id($session);
		unset($this->pendingLoginSessions[$idx]);
	}

	/**
	 * Removes the given network session, due to disconnect. This should only be called by a network session on
	 * disconnection.
	 */
	public function remove(NetworkSession $session) : void{
		$idx = spl_object_id($session);
		unset($this->sessions[$idx]);
		unset($this->pendingLoginSessions[$idx]);
	}

	/**
	 * Returns the number of known connected sessions, including sessions which have not yet sent a login request.
	 */
	public function getSessionCount() : int{
		return count($this->sessions);
	}

	/**
	 * Returns the number of connected sessions which have either sent a login request, or have already completed the
	 * login sequence.
	 */
	public function getValidSessionCount() : int{
		return count($this->sessions) - count($this->pendingLoginSessions);
	}

	/** @return NetworkSession[] */
	public function getSessions() : array{ return $this->sessions; }

	/**
	 * Updates all sessions which need it.
	 */
	public function tick() : void{
		foreach($this->sessions as $k => $session){
			$session->tick();
			if(!$session->isConnected()){
				unset($this->sessions[$k]);
			}
		}
	}

	/**
	 * Terminates all connected sessions with the given reason.
	 *
	 * @param Translatable|string      $reason                  Shown in the server log - this should be a short one-line message
	 * @param Translatable|string|null $disconnectScreenMessage Shown on the player's disconnection screen (null will use the reason)
	 */
	public function close(Translatable|string $reason = "", Translatable|string|null $disconnectScreenMessage = null) : void{
		foreach($this->sessions as $session){
			$session->disconnect($reason, $disconnectScreenMessage);
		}
		$this->sessions = [];
	}
}
