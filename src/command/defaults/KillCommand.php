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
use pocketmine\command\args\TargetArgument;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;

class KillCommand extends CommandoCommand{

	public function __construct(){
		parent::__construct(
			"kill",
			"Commits suicide or kills another player",
			"/kill [player]",
			["suicide"]
		);
	}

	protected function prepare(): void {
		$this->setPermissions([DefaultPermissionNames::COMMAND_KILL_SELF, DefaultPermissionNames::COMMAND_KILL_OTHER]);
		$this->registerArgument(0, new TargetArgument("target", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$targetName = $args["target"] ?? null;
		
		if($targetName === null){
			if(!($sender instanceof Player)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
				return;
			}
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_KILL_SELF)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
			$player = $sender;
		}else{
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_KILL_OTHER)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
			$player = $sender->getServer()->getPlayerByPrefix($targetName);
			if($player === null){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
				return;
			}
		}

		$player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, $player->getHealth()));
		if($player === $sender){
			$sender->sendMessage(KnownTranslationFactory::commands_kill_successful($sender->getName()));
		}else{
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_kill_successful($player->getName()));
		}
	}
}
