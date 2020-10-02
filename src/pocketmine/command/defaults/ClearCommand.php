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
use pocketmine\utils\TextFormat;
use function count;

class ClearCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.clear.description",
			"%pocketmine.command.clear.usage",
			[]
		);
		$this->setPermission("pocketmine.command.clear.self;pocketmine.command.clear.other");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) >= 2){
			throw new InvalidCommandSyntaxException();
		}

		if(count($args) === 1){
			if(!$sender->hasPermission("pocketmine.command.clear.other")){
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			$player = $sender->getServer()->getPlayer($args[0]);

			if($player instanceof Player){

				$cleared = 0;

				foreach ($player->getInventory()->getContents() as $content) {
					$cleared += $content->getCount();
				}

				$player->getInventory()->clearAll();
				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.clear.successful", [$player->getName(), $cleared]));
			}else{
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			}

			return true;
		}

		if($sender instanceof Player){
			if(!$sender->hasPermission("pocketmine.command.clear.self")){
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			$cleared = 0;

			foreach ($sender->getInventory()->getContents() as $content) {
				$cleared += $content->getCount();
			}

			$sender->getInventory()->clearAll();
			$sender->sendMessage(new TranslationContainer("commands.clear.successful", [$sender->getName(), $cleared]));
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
