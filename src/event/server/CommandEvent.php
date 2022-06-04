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
use pocketmine\event\CancellableTrait;

/**
 * Called when any CommandSender runs a command, before it is parsed.
 *
 * This can be used for logging commands, or preprocessing the command string to add custom features (e.g. selectors).
 *
 * WARNING: DO NOT use this to block commands. Many commands have aliases.
 * For example, /version can also be invoked using /ver or /about.
 * To prevent command senders from using certain commands, deny them permission to use the commands you don't want them
 * to have access to.
 *
 * @see Permissible::addAttachment()
 *
 * The message DOES NOT begin with a slash.
 */
class CommandEvent extends ServerEvent implements Cancellable{
	use CancellableTrait;

	/** @var string */
	protected $command;

	/** @var CommandSender */
	protected $sender;

	public function __construct(CommandSender $sender, string $command){
		$this->sender = $sender;
		$this->command = $command;
	}

	public function getSender() : CommandSender{
		return $this->sender;
	}

	public function getCommand() : string{
		return $this->command;
	}

	public function setCommand(string $command) : void{
		$this->command = $command;
	}
}
