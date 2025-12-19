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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function array_map;
use function count;
use function implode;
use function is_array;
use function ksort;

final class CommandAliasCommand{
	private const SELF_PERM = DefaultPermissionNames::COMMAND_CMDALIAS_EDIT_SELF;
	private const GLOBAL_PERM = DefaultPermissionNames::COMMAND_CMDALIAS_EDIT_GLOBAL;
	private const LIST_PERM = DefaultPermissionNames::COMMAND_CMDALIAS_LIST;

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		$builder = OverloadBuilder::make();
		foreach([false, true] as $global){
			$prefix = $global ? ["global"] : [];
			$editPerm = $global ? self::GLOBAL_PERM : self::SELF_PERM;
			$builder->branch($prefix, fn(OverloadBuilder $childBuilder) => $childBuilder
				->executor(
					[
						"create",
						new StringParameter("alias", "alias"),
						new StringParameter("target", "target")
					],
					$editPerm,
					fn(CommandSender $sender, string $alias, string $target) => self::createAlias($sender, $alias, $target, $global)
				)
				->executor(
					[
						"delete",
						new StringParameter("alias", "alias")
					],
					$editPerm,
					fn(CommandSender $sender, string $alias) => self::deleteAlias($sender, $alias, $global)
				)
				->executor(
					["list"],
					self::LIST_PERM,
					fn(CommandSender $sender) => self::listAliases($sender, $global)
				)
			);
		}
		return new Command(
			$namespace,
			$name,
			$builder->build(),
			KnownTranslationFactory::pocketmine_command_cmdalias_description(),
		);
	}

	private static function createAlias(CommandSender $sender, string $alias, string $target, bool $global) : void{
		$commandMap = $sender->getServer()->getCommandMap();
		if($global){
			$aliasMap = $commandMap->getAliasMap();
			$messageScope = fn(Translatable $t) => KnownTranslationFactory::pocketmine_command_cmdalias_template($t, KnownTranslationFactory::pocketmine_command_cmdalias_scope_global());
			$auditLog = true;
		}else{
			$aliasMap = $sender->getCommandAliasMap();
			$messageScope = fn(Translatable $t) => KnownTranslationFactory::pocketmine_command_cmdalias_template($t, KnownTranslationFactory::pocketmine_command_cmdalias_scope_userSpecific());
			$auditLog = false;
		}

		$command = $commandMap->getCommand($target, $sender->getCommandAliasMap());
		if($command === null){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_notFound(
				"/$target",
				"/" . $sender->getCommandAliasMap()->getPreferredAlias("pocketmine:help", $sender->getServer()->getCommandMap()->getAliasMap())
			)->prefix(TextFormat::RED));
			return;
		}
		if(is_array($command)){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_aliasConflict("/$target", implode(", ", array_map(fn(Command $c) => "/" . $c->getId(), $command)))->prefix(TextFormat::RED));
			return;
		}
		$aliasMap->bindAlias($command->getId(), $alias, override: true);
		$message = $messageScope(KnownTranslationFactory::pocketmine_command_cmdalias_create_success("/$alias", "/" . $command->getId()));
		if($auditLog){
			Command::broadcastCommandMessage($sender, $message);
		}else{
			$sender->sendMessage($message);
		}
	}

	private static function deleteAlias(CommandSender $sender, string $alias, bool $global) : void{
		$commandMap = $sender->getServer()->getCommandMap();
		if($global){
			$aliasMap = $commandMap->getAliasMap();
			$messageScope = fn(Translatable $t) => KnownTranslationFactory::pocketmine_command_cmdalias_template($t, KnownTranslationFactory::pocketmine_command_cmdalias_scope_global());
			$auditLog = true;
		}else{
			$aliasMap = $sender->getCommandAliasMap();
			$messageScope = fn(Translatable $t) => KnownTranslationFactory::pocketmine_command_cmdalias_template($t, KnownTranslationFactory::pocketmine_command_cmdalias_scope_userSpecific());
			$auditLog = false;
		}
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
	}

	private static function listAliases(CommandSender $sender, bool $global) : void{
		if($global){
			$aliasMap = $sender->getServer()->getCommandMap()->getAliasMap();
			$messageScope = fn(Translatable $t) => KnownTranslationFactory::pocketmine_command_cmdalias_template($t, KnownTranslationFactory::pocketmine_command_cmdalias_scope_global());
		}else{
			$aliasMap = $sender->getCommandAliasMap();
			$messageScope = fn(Translatable $t) => KnownTranslationFactory::pocketmine_command_cmdalias_template($t, KnownTranslationFactory::pocketmine_command_cmdalias_scope_userSpecific());
		}
		$allAliases = $aliasMap->getAllAliases();
		if(count($allAliases) === 0){
			$sender->sendMessage($messageScope(KnownTranslationFactory::pocketmine_command_cmdalias_list_noneSet())->prefix(TextFormat::RED));
			return;
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
	}
}
