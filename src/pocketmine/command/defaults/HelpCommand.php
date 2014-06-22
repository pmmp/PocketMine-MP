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
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

class HelpCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Shows the help menu",
			"/help [pageNumber]\n/help <topic> [pageNumber]",
			["?"]
		);
		$this->setPermission("pocketmine.command.help");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$command = "";
			$pageNumber = 1;
		}elseif(is_numeric($args[count($args) - 1])){
			$pageNumber = (int) array_pop($args);
			if($pageNumber <= 0){
				$pageNumber = 1;
			}
			$command = implode(" ", $args);
		}else{
			$command = implode(" ", $args);
			$pageNumber = 1;
		}

		if($sender instanceof ConsoleCommandSender){
			$pageHeight = PHP_INT_MAX;
		}else{
			$pageHeight = 5;
		}

		if($command === ""){
			$commands = [];
			foreach($sender->getServer()->getCommandMap()->getCommands() as $command){
				if($command->testPermissionSilent($sender)){
					$commands[$command->getName()] = $command;
				}
			}
			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			$commands = array_chunk($commands, $pageHeight);
			$pageNumber = (int) min(count($commands), $pageNumber);
			if($pageNumber < 1){
				$pageNumber = 1;
			}
			$message = TextFormat::RED . "-" . TextFormat::RESET . " Showing help page " . $pageNumber . " of " . count($commands) . " (/help <pageNumber>) " . TextFormat::RED . "-" . TextFormat::RESET . "\n";
			if(isset($commands[$pageNumber - 1])){
				foreach($commands[$pageNumber - 1] as $command){
					$message .= TextFormat::DARK_GREEN . "/" . $command->getName() . ": " . TextFormat::WHITE . $command->getDescription() . "\n";
				}
			}
			$sender->sendMessage($message);

			return true;
		}else{
			if(($command = $sender->getServer()->getCommandMap()->getCommand(strtolower($command))) instanceof Command){
				if($command->testPermissionSilent($sender)){
					$message = TextFormat::YELLOW . "--------- " . TextFormat::WHITE . " Help: /" . $command->getName() . TextFormat::YELLOW . " ---------\n";
					$message .= TextFormat::GOLD . "Description: " . TextFormat::WHITE . $command->getDescription() . "\n";
					$message .= TextFormat::GOLD . "Usage: " . TextFormat::WHITE . implode("\n" . TextFormat::WHITE, explode("\n", $command->getUsage())) . "\n";
					$sender->sendMessage($message);

					return true;
				}
			}
			$sender->sendMessage(TextFormat::RED . "No help for " . strtolower($command));

			return true;
		}
	}

}