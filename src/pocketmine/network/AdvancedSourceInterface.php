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
interface AdvancedSourceInterface extends SourceInterface{

	/**
	 * Prevents packets received from the IP address getting processed for the given timeout.
	 *
	 * @param int    $timeout Seconds
	 *
	 * @return void
	 */
	public function blockAddress(string $address, int $timeout = 300);

	/**
	 * Unblocks a previously-blocked address.
	 *
	 * @return void
	 */
	public function unblockAddress(string $address);

	/**
	 * @return void
	 */
	public function setNetwork(Network $network);

	/**
	 * Sends a raw payload to the network interface, bypassing any sessions.
	 *
	 * @return void
	 */
	public function sendRawPacket(string $address, int $port, string $payload);

}
