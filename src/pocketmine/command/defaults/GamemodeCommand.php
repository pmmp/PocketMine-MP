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

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class GamemodeCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Changes the player to a specific game mode",
			"/gamemode <mode> [player]"
		);
		$this->setPermission("pocketmine.command.gamemode");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);

			return false;
		}

		$gameMode = Server::getGamemodeFromString($args[0]);

		if($gameMode === -1){
			$sender->sendMessage("Unknown game mode");

			return true;
		}

		$target = $sender;
		if(isset($args[1])){
			$target = $sender->getServer()->getPlayer($args[1]);
			if($target === null){
				$sender->sendMessage("Can't find player " . $args[1]);

				return true;
			}
		}elseif(!($sender instanceof Player)){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);

			return true;
		}

		if($gameMode !== $target->getGamemode()){
			$target->setGamemode($gameMode);
			if($gameMode !== $target->getGamemode()){
				$sender->sendMessage("Game mode change for " . $target->getName() . " failed!");
			}else{
				if($target === $sender){
					Command::broadcastCommandMessage($sender, "Set own gamemode to " . strtolower(Server::getGamemodeString($gameMode)) . " mode");
				}else{
					Command::broadcastCommandMessage($sender, "Set " . $target->getName() . "'s gamemode to " . strtolower(Server::getGamemodeString($gameMode)) . " mode");
				}
			}
		}else{
			$sender->sendMessage($target->getName() . " already has game mode " . strtolower(Server::getGamemodeString($gameMode)));

			return true;
		}

		return true;
	}
}