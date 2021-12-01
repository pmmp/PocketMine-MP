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

/**
 * Advanced network interfaces have some additional capabilities, such as being able to ban addresses and process raw
 * packets.
 */
interface AdvancedNetworkInterface extends NetworkInterface{

	/**
	 * Prevents packets received from the IP address getting processed for the given timeout.
	 *
	 * @param int    $timeout Seconds
	 */
	public function blockAddress(string $address, int $timeout = 300) : void;

	/**
	 * Unblocks a previously-blocked address.
	 */
	public function unblockAddress(string $address) : void;

	public function setNetwork(Network $network) : void;

	/**
	 * Sends a raw payload to the network interface, bypassing any sessions.
	 */
	public function sendRawPacket(string $address, int $port, string $payload) : void;

	/**
	 * Adds a regex filter for raw packets to this network interface. This filter should be used to check validity of
	 * raw packets before relaying them to the main thread.
	 */
	public function addRawPacketFilter(string $regex) : void;
}
