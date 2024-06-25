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

use pocketmine\command\CommandSender;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\player\Player;
use pocketmine\utils\Utils;

/**
 * Called when a player chats something
 */
class PlayerChatEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	/**
	 * @param CommandSender[] $recipients
	 */
	public function __construct(
		Player $player,
		protected string $message,
		protected array $recipients,
		protected ChatFormatter $formatter
	){
		$this->player = $player;
	}

	public function getMessage() : string{
		return $this->message;
	}

	public function setMessage(string $message) : void{
		$this->message = $message;
	}

	/**
	 * Changes the player that is sending the message
	 */
	public function setPlayer(Player $player) : void{
		$this->player = $player;
	}

	public function getFormatter() : ChatFormatter{
		return $this->formatter;
	}

	public function setFormatter(ChatFormatter $formatter) : void{
		$this->formatter = $formatter;
	}

	/**
	 * @return CommandSender[]
	 */
	public function getRecipients() : array{
		return $this->recipients;
	}

	/**
	 * @param CommandSender[] $recipients
	 */
	public function setRecipients(array $recipients) : void{
		Utils::validateArrayValueType($recipients, function(CommandSender $_) : void{});
		$this->recipients = $recipients;
	}
}
