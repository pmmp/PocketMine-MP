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

class BanIpCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Prevents the specified IP address from using this server",
			"/ban <address|player> [reason...]"
		);
		$this->setPermission("pocketmine.command.ban.ip");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);

			return false;
		}

		$value = array_shift($args);
		$reason = implode(" ", $args);

		if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $value)){
			$this->processIPBan($value, $sender, $reason);
		}else{
			if(($player = Player::get($name, true)) instanceof Player){
				$this->processIPBan($player->getIP(), $sender, $reason);
			}else{
				$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);

				return false;
			}
		}

		Command::broadcastCommandMessage($sender, "Banned player " . $name);

		return true;
	}

	private function processIPBan($ip, CommandSender $sender, $reason){
		Server::getInstance()->getIPBans()->addBan($ip, $reason, null, $sender->getName());

		foreach(Player::getAll() as $player){
			if($player->getIP() === $ip){
				$player->kick("You have been IP banned.");
			}
		}

		Command::broadcastCommandMessage($sender, "Banned IP Address " . $ip);
	}
}