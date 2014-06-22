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

class DeopCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Takes the specified player's operator status",
			"/deop <player>"
		);
		$this->setPermission("pocketmine.command.op.take");
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

		$player = $sender->getServer()->getOfflinePlayer($name);
		$player->setOp(false);
		if($player instanceof Player){
			$player->sendMessage(TextFormat::YELLOW . "You are no longer op!");
		}
		Command::broadcastCommandMessage($sender, "De-opped " . $player->getName());

		return true;
	}
}