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
use pocketmine\permission\DefaultPermissionNames;

class SetMaxPlayersCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"setmaxplayers",
			KnownTranslationFactory::pocketmine_command_setmaxplayers_description(),
			KnownTranslationFactory::commands_setmaxplayers_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_SETMAXPLAYERS);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}

		$maxPlayers = $this->getInteger($sender, $args[0], count($sender->getServer()->getOnlinePlayers()), PHP_INT_MAX);
		$sender->getServer()->getConfigGroup()->setConfigInt("max-players", $maxPlayers);
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_setmaxplayers_success((string) $maxPlayers));
		if(((int) $args[0]) < $maxPlayers){
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_setmaxplayers_success_lowerbound());
		}
		return true;
	}
}