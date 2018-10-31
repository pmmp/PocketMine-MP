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

/**
 * Network-related classes
 */
namespace pocketmine\network;

use pocketmine\network\mcpe\NetworkSession;

/**
 * Network interfaces are transport layers which can be used to transmit packets between the server and clients.
 */
interface NetworkInterface{

	/**
	 * Performs actions needed to start the interface after it is registered.
	 */
	public function start() : void;

	/**
	 * Sends a DataPacket to the interface, returns an unique identifier for the packet if $needACK is true
	 *
	 * @param NetworkSession $session
	 * @param string         $payload
	 * @param bool           $immediate
	 */
	public function putPacket(NetworkSession $session, string $payload, bool $immediate = true) : void;

	/**
	 * Terminates the connection
	 *
	 * @param NetworkSession $session
	 * @param string         $reason
	 */
	public function close(NetworkSession $session, string $reason = "unknown reason") : void;

	/**
	 * @param string $name
	 */
	public function setName(string $name) : void;

	/**
	 * Called every tick to process events on the interface.
	 */
	public function tick() : void;

	/**
	 * Gracefully shuts down the network interface.
	 */
	public function shutdown() : void;

	/**
	 * Shuts down the network interface in an emergency situation, such as due to a crash.
	 */
	public function emergencyShutdown() : void;

}
