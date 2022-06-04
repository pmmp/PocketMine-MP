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
use pocketmine\lang\Translatable;
use pocketmine\player\Player;

/**
 * Called when a player is kicked (forcibly disconnected) from the server, e.g. if an operator used /kick.
 */
class PlayerKickEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	/** @var Translatable|string */
	protected $quitMessage;

	/** @var string */
	protected $reason;

	public function __construct(Player $player, string $reason, Translatable|string $quitMessage){
		$this->player = $player;
		$this->quitMessage = $quitMessage;
		$this->reason = $reason;
	}

	/**
	 * Sets the message shown on the kicked player's disconnection screen.
	 * This message is also displayed in the console and server log.
	 */
	public function setReason(string $reason) : void{
		$this->reason = $reason;
	}

	/**
	 * Returns the message shown on the kicked player's disconnection screen.
	 * This message is also displayed in the console and server log.
	 * When kicked by the /kick command, the default is something like "Kicked by admin.".
	 */
	public function getReason() : string{
		return $this->reason;
	}

	/**
	 * Sets the quit message broadcasted to other players.
	 */
	public function setQuitMessage(Translatable|string $quitMessage) : void{
		$this->quitMessage = $quitMessage;
	}

	/**
	 * Returns the quit message broadcasted to other players, e.g. "Steve left the game".
	 */
	public function getQuitMessage() : Translatable|string{
		return $this->quitMessage;
	}
}
