<?php
/**
 * Cattery Mc
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Cattery Team
 */
declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DeletePlayerCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.deleteplayer.description",
			"%commands.deleteplayer.usage"
		);
		$this->setPermission("pocketmine.command.deleteplayer");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$name = $args[0];

		if(($player = $sender->getServer()->getPlayer($name)) instanceof Player){
			$player->kick("Player data deleted");
			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.kick.success.reason", [$player->getName(), "Player data deleted"]));
		}

		if(!$sender->getServer()->hasOfflinePlayerData($name)){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			return true;
		}

		$sender->getServer()->deleteOfflinePlayerData($name);
		return true;
	}
}