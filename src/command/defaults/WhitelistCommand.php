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
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;
use function implode;
use function strtolower;

class WhitelistCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.whitelist.description",
			"%commands.whitelist.usage"
		);
		$this->setPermission("pocketmine.command.whitelist.reload;pocketmine.command.whitelist.enable;pocketmine.command.whitelist.disable;pocketmine.command.whitelist.list;pocketmine.command.whitelist.add;pocketmine.command.whitelist.remove");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0 or count($args) > 2){
			throw new InvalidCommandSyntaxException();
		}

		if(count($args) === 1){
			if($this->badPerm($sender, strtolower($args[0]))){
				return false;
			}
			switch(strtolower($args[0])){
				case "reload":
					$sender->getServer()->getWhitelisted()->reload();
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.reloaded"));

					return true;
				case "on":
					$sender->getServer()->setConfigBool("white-list", true);
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.enabled"));

					return true;
				case "off":
					$sender->getServer()->setConfigBool("white-list", false);
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.disabled"));

					return true;
				case "list":
					$entries = $sender->getServer()->getWhitelisted()->getAll(true);
					$result = implode($entries, ", ");
					$count = count($entries);

					$sender->sendMessage(new TranslationContainer("commands.whitelist.list", [$count, $count]));
					$sender->sendMessage($result);

					return true;

				case "add":
					$sender->sendMessage(new TranslationContainer("commands.generic.usage", ["%commands.whitelist.add.usage"]));
					return true;

				case "remove":
					$sender->sendMessage(new TranslationContainer("commands.generic.usage", ["%commands.whitelist.remove.usage"]));
					return true;
			}
		}elseif(count($args) === 2){
			if($this->badPerm($sender, strtolower($args[0]))){
				return false;
			}
			if(!Player::isValidUserName($args[1])){
				throw new InvalidCommandSyntaxException();
			}
			switch(strtolower($args[0])){
				case "add":
					$sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(true);
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.add.success", [$args[1]]));

					return true;
				case "remove":
					$sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(false);
					Command::broadcastCommandMessage($sender, new TranslationContainer("commands.whitelist.remove.success", [$args[1]]));

					return true;
			}
		}

		return true;
	}

	private function badPerm(CommandSender $sender, string $subcommand) : bool{
		static $map = [
			"on" => "enable",
			"off" => "disable"
		];
		if(!$sender->hasPermission("pocketmine.command.whitelist." . ($map[$subcommand] ?? $subcommand))){
			$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

			return true;
		}

		return false;
	}
}
