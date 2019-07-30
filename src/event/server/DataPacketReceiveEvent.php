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

namespace pocketmine\event\server;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

class DataPacketReceiveEvent extends ServerEvent implements Cancellable{
	use CancellableTrait;

	/** @var ServerboundPacket */
	private $packet;
	/** @var NetworkSession */
	private $origin;

	/**
	 * @param NetworkSession    $origin
	 * @param ServerboundPacket $packet
	 */
	public function __construct(NetworkSession $origin, ServerboundPacket $packet){
		$this->packet = $packet;
		$this->origin = $origin;
	}

	/**
	 * @return ServerboundPacket
	 */
	public function getPacket() : ServerboundPacket{
		return $this->packet;
	}

	/**
	 * @return NetworkSession
	 */
	public function getOrigin() : NetworkSession{
		return $this->origin;
	}
}
