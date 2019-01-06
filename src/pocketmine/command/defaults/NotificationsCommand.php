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

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use function strtolower;

class NotificationsCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.notif.description",
			"%pocketmine.command.notif.usage",
			["notification", "notifications", "notifs"]);
	}

	public function testPermissionSilent(CommandSender $target) : bool{
		return $target instanceof ConsoleCommandSender;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!($sender instanceof ConsoleCommandSender)){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
			return;
		}
		switch(strtolower($args[0] ?? "list")){
			case "list":
				Server::getInstance()->getNotificationManager()->print(Server::getInstance()->getLogger());
				return;
			case "read":
				$manager = Server::getInstance()->getNotificationManager();
				$cnt = $manager->markRead();
				$sender->sendMessage(new TranslationContainer("%pocketmine.notification.markedRead", [$cnt]));
				$manager->save();
				return;
			default:
				throw new InvalidCommandSyntaxException();
		}
	}
}
