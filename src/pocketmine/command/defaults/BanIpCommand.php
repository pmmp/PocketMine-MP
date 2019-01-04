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

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use function array_shift;
use function count;
use function implode;
use function preg_match;

class BanIpCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.ban.ip.description",
			"%commands.banip.usage"
		);
		$this->setPermission("pocketmine.command.ban.ip");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$value = array_shift($args);
		$reason = implode(" ", $args);

		if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $value)){
			$this->processIPBan($value, $sender, $reason);

			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.banip.success", [$value]));
		}else{
			if(($player = $sender->getServer()->getPlayer($value)) instanceof Player){
				$ip = $player->getAddress();
				$this->processIPBan($ip, $sender, $reason);

				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.banip.success.players", [$ip, $player->getName()]));
			}else{
				$sender->sendMessage(new TranslationContainer("commands.banip.invalid"));

				return false;
			}
		}

		return true;
	}

	private function processIPBan(string $ip, CommandSender $sender, string $reason){
		$sender->getServer()->getIPBans()->addBan($ip, $reason, null, $sender->getName());

		foreach($sender->getServer()->getOnlinePlayers() as $player){
			if($player->getAddress() === $ip){
				$player->kick($reason !== "" ? $reason : "IP banned.");
			}
		}

		$sender->getServer()->getNetwork()->blockAddress($ip, -1);
	}
}
