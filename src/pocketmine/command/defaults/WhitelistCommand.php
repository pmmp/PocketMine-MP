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
use pocketmine\utils\TextFormat;

class WhitelistCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Manages the list of players allowed to use this server",
			"/whitelist (add|remove) <player>\n/whitelist (on|off|list|reload)"
		);
		$this->setPermission("pocketmine.command.whitelist.reload;pocketmine.command.whitelist.enable;pocketmine.command.whitelist.disable;pocketmine.command.whitelist.list;pocketmine.command.whitelist.add;pocketmine.command.whitelist.remove");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 1){
			if($this->badPerm($sender, strtolower($args[0]))){
				return true;
			}
			switch(strtolower($args[0])){
				case "reload":
					$sender->getServer()->reloadWhitelist();
					Command::broadcastCommandMessage($sender, "Reloaded white-list from file");

					return true;
				case "on":
					$sender->getServer()->setConfigBool("white-list", true);
					Command::broadcastCommandMessage($sender, "Turned on white-listing");

					return true;
				case "off":
					$sender->getServer()->setConfigBool("white-list", false);
					Command::broadcastCommandMessage($sender, "Turned off white-listing");

					return true;
				case "list":
					$result = "";
					foreach($sender->getServer()->getWhitelisted()->getAll(true) as $player){
						$result .= $player . ", ";
					}
					$sender->sendMessage("White-listed players: " . substr($result, 0, -2));

					return true;
			}
		}elseif(count($args) === 2){
			if($this->badPerm($sender, strtolower($args[0]))){
				return true;
			}
			switch(strtolower($args[0])){
				case "add":
					$sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(true);
					Command::broadcastCommandMessage($sender, "Added " . $args[1] . " to white-list");

					return true;
				case "remove":
					$sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(false);
					Command::broadcastCommandMessage($sender, "Removed " . $args[1] . " from white-list");

					return true;
			}
		}

		$sender->sendMessage(TextFormat::RED . "Usage:\n" . $this->usageMessage);

		return true;
	}

	private function badPerm(CommandSender $sender, $perm){
		if(!$sender->hasPermission("pocketmine.command.whitelist.$perm")){
			$sender->sendMessage(TextFormat::RED . "You do not have permission to perform this action.");

			return true;
		}

		return false;
	}
}