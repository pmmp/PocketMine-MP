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
use pocketmine\utils\TextFormat;

class KickCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Removes the specified player from the server",
			"/kick <player> [reason...]"
		);
		$this->setPermission("pocketmine.command.kick");
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
		$reason = trim(implode(" ", $args));

		if(($player = $sender->getServer()->getPlayer($name)) instanceof Player){
			$player->kick($reason);
			if(strlen($reason) >= 1){
				Command::broadcastCommandMessage($sender, "Kicked " . $player->getName() . " from the game: '{$reason}'");
			}else{
				Command::broadcastCommandMessage($sender, "Kicked " . $player->getName() . " from the game.");
			}
		}else{
			$sender->sendMessage($name . " not found.");
		}


		return true;
	}
}
