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

namespace PocketMine\Command\Defaults;

use PocketMine\Command\Command;
use PocketMine\Command\CommandSender;
use PocketMine\Player;
use PocketMine\Server;
use PocketMine\Utils\TextFormat;

class BanCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Prevents the specified player from using this server",
			"/ban <player> [reason...]"
		);
		$this->setPermission("pocketmine.command.ban.player");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);
			return false;
		}

		$name = array_shift($args);
		$reason = implode(" ", $args);

		Server::getInstance()->getNameBans()->addBan($name, $reason, null, $sender->getName());

		if(($player = Player::get($name, true)) instanceof Player){
			$player->kick("Banned by admin.");
		}

		Command::broadcastCommandMessage($sender, "Banned player ". $name);
		return true;
	}
}