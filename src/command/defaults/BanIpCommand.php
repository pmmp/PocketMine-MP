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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use function array_shift;
use function count;
use function implode;
use function inet_pton;

class BanIpCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"ban-ip",
			KnownTranslationFactory::pocketmine_command_ban_ip_description(),
			KnownTranslationFactory::commands_banip_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_BAN_IP);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$value = array_shift($args);
		$reason = implode(" ", $args);

		if(inet_pton($value) !== false){
			$this->processIPBan($value, $sender, $reason);

			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_banip_success($value));
		}else{
			if(($player = $sender->getServer()->getPlayerByPrefix($value)) instanceof Player){
				$ip = $player->getNetworkSession()->getIp();
				$this->processIPBan($ip, $sender, $reason);

				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_banip_success_players($ip, $player->getName()));
			}else{
				$sender->sendMessage(KnownTranslationFactory::commands_banip_invalid());

				return false;
			}
		}

		return true;
	}

	private function processIPBan(string $ip, CommandSender $sender, string $reason) : void{
		$sender->getServer()->getIPBans()->addBan($ip, $reason, null, $sender->getName());

		foreach($sender->getServer()->getOnlinePlayers() as $player){
			if($player->getNetworkSession()->getIp() === $ip){
				$player->kick(KnownTranslationFactory::pocketmine_disconnect_ban($reason !== "" ? $reason : KnownTranslationFactory::pocketmine_disconnect_ban_ip()));
			}
		}

		$sender->getServer()->getNetwork()->blockAddress($ip, -1);
	}
}
