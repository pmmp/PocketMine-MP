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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use function array_filter;
use function array_map;
use function count;
use function implode;
use function sort;
use const SORT_STRING;

class ListCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationFactory::pocketmine_command_list_description()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_LIST);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$playerNames = array_map(function(Player $player) : string{
			return $player->getName();
		}, array_filter($sender->getServer()->getOnlinePlayers(), function(Player $player) use ($sender) : bool{
			return !($sender instanceof Player) or $sender->canSee($player);
		}));
		sort($playerNames, SORT_STRING);

		$sender->sendMessage(KnownTranslationFactory::commands_players_list((string) count($playerNames), (string) $sender->getServer()->getMaxPlayers()));
		$sender->sendMessage(implode(", ", $playerNames));

		return true;
	}
}
