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

namespace pocketmine\network\mcpe\protocol;

use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\PacketHandler;

interface Packet{

	public function setOffset(int $offset) : void;

	public function setBuffer(string $buffer = "", int $offset = 0);

	public function getOffset() : int;

	public function getBuffer() : string;

	/**
	 * Returns whether the offset has reached the end of the buffer.
	 * @return bool
	 */
	public function feof() : bool;

	public function pid() : int;

	public function getName() : string;

	public function canBeSentBeforeLogin() : bool;

	/**
	 * @throws BadPacketException
	 */
	public function decode() : void;

	public function encode() : void;

	/**
	 * Performs handling for this packet. Usually you'll want an appropriately named method in the session handler for
	 * this.
	 *
	 * This method returns a bool to indicate whether the packet was handled or not. If the packet was unhandled, a
	 * debug message will be logged with a hexdump of the packet.
	 *
	 * Typically this method returns the return value of the handler in the supplied PacketHandler. See other packets
	 * for examples how to implement this.
	 *
	 * @param PacketHandler $handler
	 *
	 * @return bool true if the packet was handled successfully, false if not.
	 * @throws BadPacketException if broken data was found in the packet
	 */
	public function handle(PacketHandler $handler) : bool;
}
