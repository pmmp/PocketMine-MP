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

use pocketmine\command\CommandSender;
use pocketmine\event\Cancellable;

/**
 * Called when any CommandSender runs a command, early in the process
 *
 * You don't want to use this except for a few cases like logging commands,
 * blocking commands on certain places, or applying modifiers.
 *
 * The message DOES NOT contain a slash at the start
 */
class CommandEvent extends ServerEvent implements Cancellable{
	/** @var string */
	protected $command;

	/** @var CommandSender */
	protected $sender;

	/**
	 * @param CommandSender $sender
	 * @param string        $command
	 */
	public function __construct(CommandSender $sender, string $command){
		$this->sender = $sender;
		$this->command = $command;
	}

	/**
	 * @return CommandSender
	 */
	public function getSender() : CommandSender{
		return $this->sender;
	}

	/**
	 * @return string
	 */
	public function getCommand() : string{
		return $this->command;
	}

	/**
	 * @param string $command
	 */
	public function setCommand(string $command) : void{
		$this->command = $command;
	}
}
