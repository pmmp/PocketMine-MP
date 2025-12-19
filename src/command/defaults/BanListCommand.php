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
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\BanEntry;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissionNames;
use function array_map;
use function count;
use function implode;
use function sort;
use const SORT_STRING;

final class BanListCommand{

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make()
				->executor(["ips"], DefaultPermissionNames::COMMAND_BAN_LIST, self::listIPBans(...))
				->executor(["players"], DefaultPermissionNames::COMMAND_BAN_LIST, self::listNameBans(...))
				->build(),
			KnownTranslationFactory::pocketmine_command_banlist_description(),
		);
	}

	private static function printList(BanList $list) : string{
		$list = array_map(function(BanEntry $entry) : string{
			return $entry->getName();
		}, $list->getEntries());
		sort($list, SORT_STRING);
		return implode(", ", $list);
	}

	private static function listIPBans(CommandSender $sender) : void{
		$list = $sender->getServer()->getIPBans();
		$sender->sendMessage(KnownTranslationFactory::commands_banlist_ips((string) count($list->getEntries())));
		$sender->sendMessage(self::printList($list));
	}

	private static function listNameBans(CommandSender $sender) : void{
		$list = $sender->getServer()->getNameBans();
		$sender->sendMessage(KnownTranslationFactory::commands_banlist_players((string) count($list->getEntries())));
		$sender->sendMessage(self::printList($list));
	}
}
