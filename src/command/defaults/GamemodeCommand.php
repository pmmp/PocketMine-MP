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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;

class GamemodeCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationKeys::POCKETMINE_COMMAND_GAMEMODE_DESCRIPTION,
			KnownTranslationKeys::COMMANDS_GAMEMODE_USAGE
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_GAMEMODE);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$gameMode = GameMode::fromString($args[0]);
		if($gameMode === null){
			$sender->sendMessage("Unknown game mode");
			return true;
		}

		if(isset($args[1])){
			$target = $sender->getServer()->getPlayerByPrefix($args[1]);
			if($target === null){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));

				return true;
			}
		}elseif($sender instanceof Player){
			$target = $sender;
		}else{
			throw new InvalidCommandSyntaxException();
		}

		$target->setGamemode($gameMode);
		if(!$gameMode->equals($target->getGamemode())){
			$sender->sendMessage("Game mode change for " . $target->getName() . " failed!");
		}else{
			if($target === $sender){
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_gamemode_success_self($gameMode->getTranslatableName()));
			}else{
				$target->sendMessage(KnownTranslationFactory::gameMode_changed($gameMode->getTranslatableName()));
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_gamemode_success_other($gameMode->getTranslatableName(), $target->getName()));
			}
		}

		return true;
	}
}
