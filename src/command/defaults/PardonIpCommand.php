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
use pocketmine\command\overload\StringParameter;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use function inet_pton;

final class PardonIpCommand{
	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::single(
				[new StringParameter("ip", "IP address")],
				DefaultPermissionNames::COMMAND_UNBAN_IP,
				self::execute(...)
			),
			KnownTranslationFactory::pocketmine_command_unban_ip_description()
		);
	}

	private static function execute(CommandSender $sender, string $ip) : void{
		if(inet_pton($ip) !== false){
			$sender->getServer()->getIPBans()->remove($ip);
			$sender->getServer()->getNetwork()->unblockAddress($ip);
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_unbanip_success($ip));
		}else{
			$sender->sendMessage(KnownTranslationFactory::commands_unbanip_invalid());
		}
	}
}
