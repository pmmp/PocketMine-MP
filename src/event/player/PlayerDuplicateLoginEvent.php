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

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\network\mcpe\NetworkSession;

/**
 * Called when a player connects with a username or UUID that is already used by another player on the server.
 * If cancelled, the newly connecting session will be disconnected; otherwise, the existing player will be disconnected.
 */
class PlayerDuplicateLoginEvent extends Event implements Cancellable{
	use CancellableTrait;

	/** @var NetworkSession */
	private $connectingSession;
	/** @var NetworkSession */
	private $existingSession;
	/** @var string */
	private $disconnectMessage = "Logged in from another location";

	public function __construct(NetworkSession $connectingSession, NetworkSession $existingSession){
		$this->connectingSession = $connectingSession;
		$this->existingSession = $existingSession;
	}

	public function getConnectingSession() : NetworkSession{
		return $this->connectingSession;
	}

	public function getExistingSession() : NetworkSession{
		return $this->existingSession;
	}

	/**
	 * Returns the message shown to the session which gets disconnected.
	 */
	public function getDisconnectMessage() : string{
		return $this->disconnectMessage;
	}

	public function setDisconnectMessage(string $message) : void{
		$this->disconnectMessage = $message;
	}
}
