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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use function count;

class TransferServerCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.transferserver.description",
			"%pocketmine.command.transferserver.usage"
		);
		$this->setPermission("pocketmine.command.transferserver;pocketmine.command.transferserver.other");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}elseif(count($args) === 3){
			if (!$sender->hasPermission("pocketmine.command.transferserver.other")){
				$message = $sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission");
				$sender->sendMessage($message);

				return true;
			}
			$target = $sender->getServer()->getPlayer($args[2]);
			if (!$target instanceof Player || !$target->isOnline()){
				$message = $sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.player.notFound");
				$sender->sendMessage($message);

				return true;
			}
		}elseif (!$sender instanceof Player){
			$sender->sendMessage("This command must be executed as a player");

			return true;
		}else{
			$target = $sender;
		}
		
		$target->transfer($args[0], (int) ($args[1] ?? 19132));

		return true;
	}
}
