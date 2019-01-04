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
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\Player;

class DataPacketReceiveEvent extends ServerEvent implements Cancellable{
	use CancellableTrait;

	/** @var ServerboundPacket */
	private $packet;
	/** @var Player */
	private $player;

	/**
	 * @param Player            $player
	 * @param ServerboundPacket $packet
	 */
	public function __construct(Player $player, ServerboundPacket $packet){
		$this->packet = $packet;
		$this->player = $player;
	}

	/**
	 * @return ServerboundPacket
	 */
	public function getPacket() : ServerboundPacket{
		return $this->packet;
	}

	/**
	 * @return Player
	 */
	public function getPlayer() : Player{
		return $this->player;
	}
}
