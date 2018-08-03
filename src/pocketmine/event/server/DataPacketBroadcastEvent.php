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
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;

/**
 * Called when a list of packets is broadcasted to 1 or more players.
 */
class DataPacketBroadcastEvent extends ServerEvent implements Cancellable{
	/** @var Player[] */
	private $players;
	/** @var DataPacket[] */
	private $packets;

	/**
	 * @param Player[]     $players
	 * @param DataPacket[] $packets
	 */
	public function __construct(array $players, array $packets){
		$this->players = $players;
		$this->packets = $packets;
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers() : array{
		return $this->players;
	}

	/**
	 * @param Player[] $players
	 */
	public function setPlayers(array $players) : void{
		$this->players = $players;
	}

	/**
	 * @return DataPacket[]
	 */
	public function getPackets() : array{
		return $this->packets;
	}

	/**
	 * @param DataPacket[] $packets
	 */
	public function setPackets(array $packets) : void{
		$this->packets = $packets;
	}
}
