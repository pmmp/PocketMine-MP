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

use pocketmine\event\player\PlayerDuplicateLoginEvent;
use pocketmine\network\mcpe\NetworkSession;
use function count;
use function spl_object_id;

class NetworkSessionManager{

	/** @var NetworkSession[] */
	private $sessions = [];
	/** @var NetworkSession[] */
	private $updateSessions = [];

	/**
	 * Adds a network session to the manager. This should only be called on session creation.
	 */
	public function add(NetworkSession $session) : void{
		$idx = spl_object_id($session);
		$this->sessions[$idx] = $this->updateSessions[$idx] = $session;
	}

	/**
	 * Removes the given network session, due to disconnect. This should only be called by a network session on
	 * disconnection.
	 */
	public function remove(NetworkSession $session) : void{
		$idx = spl_object_id($session);
		unset($this->sessions[$idx], $this->updateSessions[$idx]);
	}

	/**
	 * Requests an update to be scheduled on the given network session at the next tick.
	 */
	public function scheduleUpdate(NetworkSession $session) : void{
		$this->updateSessions[spl_object_id($session)] = $session;
	}

	/**
	 * Checks whether this network session is a duplicate of an already-connected session (same player connecting from
	 * 2 locations).
	 *
	 * @return bool if the network session is still connected.
	 */
	public function kickDuplicates(NetworkSession $connectingSession) : bool{
		foreach($this->sessions as $existingSession){
			if($existingSession === $connectingSession){
				continue;
			}
			$info = $existingSession->getPlayerInfo();
			if($info !== null and ($info->getUsername() === $connectingSession->getPlayerInfo()->getUsername() or $info->getUuid()->equals($connectingSession->getPlayerInfo()->getUuid()))){
				$ev = new PlayerDuplicateLoginEvent($connectingSession, $existingSession);
				$ev->call();
				if($ev->isCancelled()){
					$connectingSession->disconnect($ev->getDisconnectMessage());
					return false;
				}

				$existingSession->disconnect($ev->getDisconnectMessage());
			}
		}

		return true;
	}

	/**
	 * Returns the number of known connected sessions.
	 */
	public function getSessionCount() : int{
		return count($this->sessions);
	}

	/**
	 * Updates all sessions which need it.
	 */
	public function tick() : void{
		foreach($this->updateSessions as $k => $session){
			if(!$session->tick()){
				unset($this->updateSessions[$k]);
			}
		}
	}

	/**
	 * Terminates all connected sessions with the given reason.
	 */
	public function close(string $reason = "") : void{
		foreach($this->sessions as $session){
			$session->disconnect($reason);
		}
		$this->sessions = [];
		$this->updateSessions = [];
	}
}
