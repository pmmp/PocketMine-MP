<?php

/*
    _____ _                 _        __  __ _____
  / ____| |               | |      |  \/  |  __ \
 | |    | | ___  _   _  __| |______| \  / | |__) |
 | |    | |/ _ \| | | |/ _` |______| |\/| |  ___/
 | |____| | (_) | |_| | (_| |      | |  | | |
  \_____|_|\___/ \__,_|\__,_|      |_|  |_|_|

     Make of Things.
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\{
	CommandSender, defaults\VanillaCommand
};
use pocketmine\Player;

class WorldCommand extends VanillaCommand {

	public function __construct($name){
		parent::__construct(
			$name,
			"Teleport to a world",
			"/world [target player] <world name>"
		);
		$this->setPermission("pocketmine.command.world");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if($sender instanceof Player){
			if(count($args) == 1){
				$sender->getServer()->loadLevel($args[0]);
				if(($level = $sender->getServer()->getLevelByName($args[0])) !== null){
					$sender->teleport($level->getSafeSpawn());
					$sender->sendMessage("Teleported to Level: " . $level->getName());

					return true;
				}else{
					$sender->sendMessage(TextFormat::RED . "World: \"" . $args[0] . "\" Does not exist");

					return false;
				}
			}elseif(count($args) > 1 && count($args) < 3){
				$sender->getServer()->loadLevel($args[1]);
				if(($level = $sender->getServer()->getLevelByName($args[1])) !== null){
					$player = $sender->getServer()->getPlayer($args[0]);
					if($player === null){
						$sender->sendMessage("Player not found.");

						return false;
					}
					$player->teleport($level->getSafeSpawn());
					$player->sendMessage("Teleported to Level: " . $level->getName());

					return true;
				}else{
					$sender->sendMessage(TextFormat::RED . "World: \"" . $args[1] . "\" Does not exist");

					return false;
				}
			}else{
				$sender->sendMessage("Usage: /world [target player] <world name>");

				return false;
			}
		}else{
			$sender->sendMessage(TextFormat::RED . "This command must be executed as a player");

			return false;
		}
	}
}
