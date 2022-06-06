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
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;

/**
 * Called when a player runs a command or chats, before it is processed.
 *
 * If the message is prefixed with a / (forward slash), it will be interpreted as a command.
 * Otherwise, it will be broadcasted as a chat message.
 *
 * @deprecated
 * @see PlayerChatEvent to handle chat messages
 * @see CommandEvent to intercept commands
 */
class PlayerCommandPreprocessEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	/** @var string */
	protected $message;

	public function __construct(Player $player, string $message){
		$this->player = $player;
		$this->message = $message;
	}

	public function getMessage() : string{
		return $this->message;
	}

	public function setMessage(string $message) : void{
		$this->message = $message;
	}

	public function setPlayer(Player $player) : void{
		$this->player = $player;
	}
}
