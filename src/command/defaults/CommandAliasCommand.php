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
use pocketmine\lang\Translatable;
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
	private const SELF_PERM = DefaultPermissionNames::COMMAND_CMDALIAS_EDIT_SELF;
	private const GLOBAL_PERM = DefaultPermissionNames::COMMAND_CMDALIAS_EDIT_GLOBAL;
	private const LIST_PERM = DefaultPermissionNames::COMMAND_CMDALIAS_LIST;

	public function __construct(string $namespace, string $name){
		parent::__construct(
			$namespace,
			$name,
			KnownTranslationFactory::pocketmine_command_cmdalias_description(),
			"/cmdalias [global] create <alias> <target> OR /cmdalias [global] delete <alias>"
		);
		$this->setPermissions([self::GLOBAL_PERM, self::SELF_PERM, self::LIST_PERM]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) === 0){
			throw new InvalidCommandSyntaxException();
		}
		$parsedArgs = $args;
		$commandMap = $sender->getServer()->getCommandMap();
		if($parsedArgs[0] === "global"){
			$editPermission = self::GLOBAL_PERM;
			$permissionCtx = $commandLabel . " global";
			array_shift($parsedArgs);
			$aliasMap = $commandMap->getAliasMap();
			$messageScope = fn(Translatable $t) => KnownTranslationFactory::pocketmine_command_cmdalias_template($t, KnownTranslationFactory::pocketmine_command_cmdalias_scope_global());
			$auditLog = true;
		}else{
			$editPermission = self::SELF_PERM;
			$permissionCtx = $commandLabel;
			$aliasMap = $sender->getCommandAliasMap();
			$messageScope = fn(Translatable $t) => KnownTranslationFactory::pocketmine_command_cmdalias_template($t, KnownTranslationFactory::pocketmine_command_cmdalias_scope_userSpecific());
			$auditLog = false;
		}
		$operation = array_shift($parsedArgs);

		if($operation === "create"){
			if(count($parsedArgs) !== 2){
				throw new InvalidCommandSyntaxException();
			}
			if(!$this->testPermission($permissionCtx, $sender, $editPermission)){
				return true;
			}

			[$alias, $target] = $parsedArgs;
			$command = $commandMap->getCommand($target, $sender->getCommandAliasMap());
			if($command === null){
				$sender->sendMessage(KnownTranslationFactory::pocketmine_command_notFound(
					"/$target",
					"/" . $sender->getCommandAliasMap()->getPreferredAlias("pocketmine:help", $sender->getServer()->getCommandMap()->getAliasMap())
				)->prefix(TextFormat::RED));
				return true;
			}
			if(is_array($command)){
				$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_aliasConflict("/$target", implode(", ", array_map(fn(Command $c) => "/" . $c->getId(), $command)))->prefix(TextFormat::RED));
				return true;
			}
			$aliasMap->bindAlias($command->getId(), $alias, override: true);
			$message = $messageScope(KnownTranslationFactory::pocketmine_command_cmdalias_create_success("/$alias", "/" . $command->getId()));
			if($auditLog){
				Command::broadcastCommandMessage($sender, $message);
			}else{
				$sender->sendMessage($message);
			}
			return true;
		}
		if($operation === "delete"){
			if(count($parsedArgs) !== 1){
				throw new InvalidCommandSyntaxException();
			}
			if(!$this->testPermission($permissionCtx, $sender, $editPermission)){
				return true;
			}

			$alias = $parsedArgs[0];

			if($aliasMap->unbindAlias($alias)){
				$message = $messageScope(KnownTranslationFactory::pocketmine_command_cmdalias_delete_success("/$alias"));
				if($auditLog){
					Command::broadcastCommandMessage($sender, $message);
				}else{
					$sender->sendMessage($message);
				}
			}else{
				$sender->sendMessage($messageScope(KnownTranslationFactory::pocketmine_command_cmdalias_delete_notFound("/$alias"))->prefix(TextFormat::RED));
			}
			return true;
		}
		if($operation === "list"){
			if(count($parsedArgs) !== 0){
				throw new InvalidCommandSyntaxException();
			}
			if(!$this->testPermission($permissionCtx, $sender, self::LIST_PERM)){
				return true;
			}
			$allAliases = $aliasMap->getAllAliases();
			if(count($allAliases) === 0){
				$sender->sendMessage($messageScope(KnownTranslationFactory::pocketmine_command_cmdalias_list_noneSet())->prefix(TextFormat::RED));
				return true;
			}
			ksort($allAliases);
			foreach(Utils::promoteKeys($allAliases) as $alias => $commandIds){
				if(is_array($commandIds)){
					$sender->sendMessage(KnownTranslationFactory::pocketmine_command_cmdalias_list_conflicted(
						TextFormat::RED . "/$alias" . TextFormat::RESET,
						implode(", ", array_map(fn(string $c) => TextFormat::RED . "/$c" . TextFormat::RESET, $commandIds))
					));
				}else{
					$sender->sendMessage(KnownTranslationFactory::pocketmine_command_cmdalias_list_normal(
						TextFormat::DARK_GREEN . "/$alias" . TextFormat::RESET,
						TextFormat::DARK_GREEN . "/$commandIds" . TextFormat::RESET
					));
				}
			}
			return true;
		}

		throw new InvalidCommandSyntaxException();
	}
}
