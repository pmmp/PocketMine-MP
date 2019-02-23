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
use pocketmine\level\Level;
use pocketmine\level\LevelManager;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\Server;

class WorldCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct($name, "%pocketmine.command.world.description", "%commands.world.usage", [], [
			[
				new CommandParameter("world", AvailableCommandsPacket::ARG_TYPE_RAWTEXT, false),
				new CommandParameter("player", AvailableCommandsPacket::ARG_TYPE_TARGET, true)
			]
		]);
		$this->setPermission("pocketmine.command.world");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$countArgs = count($args);
		if($countArgs <= 0 or $countArgs > 2){
			throw new InvalidCommandSyntaxException();
		}

		$level = $this->getLevelByName($sender->getServer()->getLevelManager(), $args[0]);
		if($countArgs === 1){
			if($sender instanceof Player){
				if($level !== null){
					$sender->teleport($level->getSpawnLocation());
					$sender->sendMessage(new TranslationContainer("commands.world.teleport.self", [$level->getFolderName()]));
				}else{
					$sender->sendMessage(new TranslationContainer("commands.world.level.notFound"));
				}
			}
		}elseif(count($args) === 2){
			if(($target = $sender->getServer()->getPlayer($args[1])) instanceof Player){
				if($level !== null){
					$target->teleport($level->getSpawnLocation());
					$target->sendMessage(new TranslationContainer("commands.world.teleport.self", [$level->getFolderName()]));
					$sender->sendMessage(new TranslationContainer("commands.word.teleport.other", [$level->getFolderName()]));
				}else{
					$sender->sendMessage(new TranslationContainer("commands.world.level.notFound"));
				}
			}else{
				$sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
			}
		}

		return true;
	}

	private function getLevelByName(LevelManager $manager, string $name) : ?Level{
		if($manager->isLevelGenerated($name)){
			if(($level = $manager->getLevelByName($name)) !== null){
				return $level;
			}else{
				$manager->loadLevel($name);
				return $manager->getLevelByName($name);
			}
		}

		return null;
	}
}