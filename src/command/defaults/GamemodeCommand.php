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
use pocketmine\command\CommandoCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\args\RawStringArgument;
use pocketmine\command\args\TargetArgument;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\GameMode;
use pocketmine\player\Player;

class GamemodeCommand extends CommandoCommand{

	public function __construct(){
		parent::__construct(
			"gamemode",
			"Changes the gamemode of a player",
			"/gamemode <mode> [player]"
		);
	}

	protected function prepare(): void {
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_GAMEMODE_SELF,
			DefaultPermissionNames::COMMAND_GAMEMODE_OTHER
		]);
		$this->registerArgument(0, new RawStringArgument("gamemode", false));
		$this->registerArgument(1, new TargetArgument("player", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$gameMode = GameMode::fromString($args["gamemode"]);
		if($gameMode === null){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_gamemode_unknown($args["gamemode"]));
			return;
		}

		$targetName = $args["player"] ?? null;
		
		if($targetName === null){
			if(!($sender instanceof Player)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
				return;
			}
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_GAMEMODE_SELF)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
			$target = $sender;
		}else{
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_GAMEMODE_OTHER)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
			$target = $sender->getServer()->getPlayerByPrefix($targetName);
			if($target === null){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
				return;
			}
		}

		if($target->getGamemode() === $gameMode){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_gamemode_failure($target->getName()));
			return;
		}

		$target->setGamemode($gameMode);
		if($gameMode !== $target->getGamemode()){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_gamemode_failure($target->getName()));
		}else{
			if($target === $sender){
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_gamemode_success_self($gameMode->getTranslatableName()));
			}else{
				$target->sendMessage(KnownTranslationFactory::gameMode_changed($gameMode->getTranslatableName()));
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_gamemode_success_other($gameMode->getTranslatableName(), $target->getName()));
			}
		}
	}
}
