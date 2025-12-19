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
use pocketmine\command\overload\StringParameter;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\ServerProperties;
use function count;
use function implode;
use function sort;
use const SORT_STRING;

final class WhitelistCommand{
	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make()
				->executor(["reload"], DefaultPermissionNames::COMMAND_WHITELIST_RELOAD, self::reloadWhitelist(...))
				->executor(["enable"], DefaultPermissionNames::COMMAND_WHITELIST_ENABLE, self::enableWhitelist(...))
				->executor(["disable"], DefaultPermissionNames::COMMAND_WHITELIST_DISABLE, self::disableWhitelist(...))
				->executor(["list"], DefaultPermissionNames::COMMAND_WHITELIST_LIST, self::listWhitelist(...))
				->executor([
					"add",
					new StringParameter("playerName", "player name")
				], DefaultPermissionNames::COMMAND_WHITELIST_ADD, self::addWhitelistedPlayer(...))
				->executor([
					"remove",
					new StringParameter("playerName", "player name"),
				], DefaultPermissionNames::COMMAND_WHITELIST_REMOVE, self::removeWhitelistedPlayer(...))
				->build(),
			KnownTranslationFactory::pocketmine_command_whitelist_description()
		);
	}

	private static function reloadWhitelist(CommandSender $sender) : void{
		$server = $sender->getServer();
		$server->getWhitelisted()->reload();
		if($server->hasWhitelist()){
			self::kickNonWhitelistedPlayers($server);
		}
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_reloaded());
	}

	private static function enableWhitelist(CommandSender $sender) : void{
		$server = $sender->getServer();
		$server->getConfigGroup()->setConfigBool(ServerProperties::WHITELIST, true);
		self::kickNonWhitelistedPlayers($server);
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_enabled());
	}

	private static function disableWhitelist(CommandSender $sender) : void{
		$sender->getServer()->getConfigGroup()->setConfigBool(ServerProperties::WHITELIST, false);
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_disabled());
	}

	private static function listWhitelist(CommandSender $sender) : void{
		$entries = $sender->getServer()->getWhitelisted()->getAll(true);
		sort($entries, SORT_STRING);
		$result = implode(", ", $entries);
		$count = (string) count($entries);

		$sender->sendMessage(KnownTranslationFactory::commands_whitelist_list($count, $count));
		$sender->sendMessage($result);
	}

	private static function addWhitelistedPlayer(CommandSender $sender, string $playerName) : void{
		if(!Player::isValidUserName($playerName)){
			throw new InvalidCommandSyntaxException();
		}
		$sender->getServer()->addWhitelist($playerName);
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_add_success($playerName));
	}

	private static function removeWhitelistedPlayer(CommandSender $sender, string $playerName) : void{
		if(!Player::isValidUserName($playerName)){
			throw new InvalidCommandSyntaxException();
		}
		$server = $sender->getServer();
		$server->removeWhitelist($playerName);
		if(!$server->isWhitelisted($playerName)){
			$server->getPlayerExact($playerName)?->kick(KnownTranslationFactory::pocketmine_disconnect_kick(KnownTranslationFactory::pocketmine_disconnect_whitelisted()));
		}
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_whitelist_remove_success($playerName));
	}

	private static function kickNonWhitelistedPlayers(Server $server) : void{
		$message = KnownTranslationFactory::pocketmine_disconnect_kick(KnownTranslationFactory::pocketmine_disconnect_whitelisted());
		foreach($server->getOnlinePlayers() as $player){
			if(!$server->isWhitelisted($player->getName())){
				$player->kick($message);
			}
		}
	}
}
