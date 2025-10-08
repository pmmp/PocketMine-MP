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
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function array_map;
use function array_shift;
use function count;
use function implode;
use function is_array;
use function ksort;

final class CommandAliasCommand extends Command{
	private const SELF_PERM = DefaultPermissionNames::COMMAND_CMDALIAS_SELF;
	private const GLOBAL_PERM = DefaultPermissionNames::COMMAND_CMDALIAS_GLOBAL;

	public function __construct(string $namespace, string $name){
		parent::__construct(
			$namespace,
			$name,
			"View or modify user-specific or global command aliases",
			"/cmdalias [global] create <target> <alias> OR /cmdalias [global] delete <alias>"
		);
		$this->setPermissions([self::GLOBAL_PERM, self::SELF_PERM]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}
		$parsedArgs = $args;
		$commandMap = $sender->getServer()->getCommandMap();
		if($parsedArgs[0] === "global"){
			if(!$this->testPermission($commandLabel . " global", $sender, self::GLOBAL_PERM)){
				return true;
			}
			array_shift($parsedArgs);
			$aliasMap = $commandMap->getAliasMap();
		}else{
			if(!$this->testPermission($commandLabel, $sender, self::SELF_PERM)){
				return true;
			}
			$aliasMap = $sender->getCommandAliasMap();
		}
		$operation = array_shift($parsedArgs);

		if($operation === "create"){
			if(count($parsedArgs) !== 2){
				throw new InvalidCommandSyntaxException();
			}

			[$target, $alias] = $parsedArgs;
			$command = $commandMap->getCommand($target, $sender->getCommandAliasMap());
			if($command === null){
				$sender->sendMessage(TextFormat::RED . "Failed to bind /$alias to /$target: No such command /$target");
				return true;
			}
			if(is_array($command)){
				$sender->sendMessage(
					TextFormat::RED .
					"Failed to bind /$alias to /$target: /$target could refer to multiple commands: " .
					implode(", ", array_map(fn(Command $c) => $c->getId(), $command))
				);
				return true;
			}
			$aliasMap->bindAlias($command->getId(), $alias, override: true);
			$sender->sendMessage("Successfully bound /$alias to /" . $command->getId());
			return true;
		}
		if($operation === "delete"){
			if(count($parsedArgs) !== 1){
				throw new InvalidCommandSyntaxException();
			}
			$alias = $parsedArgs[0];

			if($aliasMap->unbindAlias($alias)){
				$sender->sendMessage("Successfully unbound /" . $alias);
			}else{
				$sender->sendMessage(TextFormat::RED . "No such alias /$alias");
			}
			return true;
		}
		if($operation === "list"){
			if(count($parsedArgs) !== 0){
				throw new InvalidCommandSyntaxException();
			}
			$allAliases = $aliasMap->getAllAliases();
			if(count($allAliases) === 0){
				$sender->sendMessage(TextFormat::RED . "No command aliases set");
				return true;
			}
			ksort($allAliases);
			foreach(Utils::promoteKeys($allAliases) as $alias => $commandIds){
				if(is_array($commandIds)){
					$sender->sendMessage(TextFormat::RED . "/$alias" . TextFormat::RESET . " is conflicted: " .
						implode(", ", array_map(fn(string $c) => TextFormat::RED . "/$c" . TextFormat::RESET, $commandIds))
					);
				}else{
					$sender->sendMessage(TextFormat::DARK_GREEN . "/$alias" . TextFormat::RESET . " -> " . TextFormat::DARK_GREEN . "/$commandIds");
				}
			}
			return true;
		}

		throw new InvalidCommandSyntaxException();
	}
}
