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
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\RawParameter;
use pocketmine\command\overload\StringParameter;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use function inet_pton;

final class BanIpCommand{
	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::single(
				[
					//TODO: maybe split this into two overloads?
					new StringParameter("target", "name or IP address"),
					new RawParameter("reason", "reason")
				],
				DefaultPermissionNames::COMMAND_BAN_IP,
				self::execute(...)
			),
			KnownTranslationFactory::pocketmine_command_ban_ip_description(),
		);
	}

	private static function execute(CommandSender $sender, string $target, string $reason) : void{
		if(inet_pton($target) !== false){
			self::processIPBan($target, $sender, $reason);

			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_banip_success($target));
		}else{
			if(($player = $sender->getServer()->getPlayerByPrefix($target)) instanceof Player){
				$ip = $player->getNetworkSession()->getIp();
				self::processIPBan($ip, $sender, $reason);

				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_banip_success_players($ip, $player->getName()));
			}else{
				$sender->sendMessage(KnownTranslationFactory::commands_banip_invalid());
			}
		}
	}

	private static function processIPBan(string $ip, CommandSender $sender, string $reason) : void{
		$sender->getServer()->getIPBans()->addBan($ip, $reason, null, $sender->getName());

		foreach($sender->getServer()->getOnlinePlayers() as $player){
			if($player->getNetworkSession()->getIp() === $ip){
				$player->kick(KnownTranslationFactory::pocketmine_disconnect_ban($reason !== "" ? $reason : KnownTranslationFactory::pocketmine_disconnect_ban_ip()));
			}
		}

		$sender->getServer()->getNetwork()->blockAddress($ip, -1);
	}
}
