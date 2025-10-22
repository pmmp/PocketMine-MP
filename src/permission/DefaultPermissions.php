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

namespace pocketmine\permission;

use pocketmine\lang\KnownTranslationParameterInfo;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissionNames as Names;
use pocketmine\utils\AssumptionFailedError;
use function count;
use function preg_last_error_msg;
use function preg_replace;

abstract class DefaultPermissions{
	public const ROOT_CONSOLE = Names::GROUP_CONSOLE;
	public const ROOT_OPERATOR = Names::GROUP_OPERATOR;
	public const ROOT_USER = Names::GROUP_USER;

	/**
	 * @param Permission[] $grantedBy
	 * @param Permission[] $deniedBy
	 */
	public static function registerPermission(Permission $candidate, array $grantedBy = [], array $deniedBy = []) : Permission{
		foreach($grantedBy as $permission){
			$permission->addChild($candidate->getName(), true);
		}
		foreach($deniedBy as $permission){
			$permission->addChild($candidate->getName(), false);
		}
		PermissionManager::getInstance()->addPermission($candidate);

		return PermissionManager::getInstance()->getPermission($candidate->getName());
	}

	/**
	 * @param Permission[] $grantedBy
	 */
	private static function registerNoArgsDesc(string $permission, array $grantedBy) : Permission{
		$translationKey = preg_replace("/^pocketmine\./", "pocketmine.permission.", $permission) ?? throw new AssumptionFailedError(preg_last_error_msg());
		$parameters = KnownTranslationParameterInfo::TABLE[$translationKey] ?? null;
		if($parameters === null){
			throw new \InvalidArgumentException("Expected translation key $translationKey not defined");
		}
		if(count($parameters) !== 0){
			throw new \InvalidArgumentException("Cannot use this function to register a permission with a parameterisable description string");
		}
		$translatable = new Translatable($translationKey);
		return self::registerPermission(new Permission($permission, $translatable), $grantedBy);
	}

	public static function registerCorePermissions() : void{
		$consoleRoot = self::registerNoArgsDesc(self::ROOT_CONSOLE, []);
		$operatorRoot = self::registerNoArgsDesc(self::ROOT_OPERATOR, [$consoleRoot]);
		$everyoneRoot = self::registerNoArgsDesc(self::ROOT_USER, [$operatorRoot]);

		self::registerNoArgsDesc(Names::COMMAND_DUMPMEMORY, [$consoleRoot]);

		foreach([
			Names::BROADCAST_ADMIN,
			Names::COMMAND_BAN_IP,
			Names::COMMAND_BAN_LIST,
			Names::COMMAND_BAN_PLAYER,
			Names::COMMAND_CLEAR_OTHER,
			Names::COMMAND_DEFAULTGAMEMODE,
			Names::COMMAND_DIFFICULTY,
			Names::COMMAND_EFFECT_OTHER,
			Names::COMMAND_EFFECT_SELF,
			Names::COMMAND_ENCHANT_OTHER,
			Names::COMMAND_ENCHANT_SELF,
			Names::COMMAND_GAMEMODE_OTHER,
			Names::COMMAND_GAMEMODE_SELF,
			Names::COMMAND_GC,
			Names::COMMAND_GIVE_OTHER,
			Names::COMMAND_GIVE_SELF,
			Names::COMMAND_KICK,
			Names::COMMAND_KILL_OTHER,
			Names::COMMAND_LIST,
			Names::COMMAND_OP_GIVE,
			Names::COMMAND_OP_TAKE,
			Names::COMMAND_PARTICLE,
			Names::COMMAND_PLUGINS,
			Names::COMMAND_SAVE_DISABLE,
			Names::COMMAND_SAVE_ENABLE,
			Names::COMMAND_SAVE_PERFORM,
			Names::COMMAND_SAY,
			Names::COMMAND_SEED,
			Names::COMMAND_SETWORLDSPAWN,
			Names::COMMAND_SPAWNPOINT_OTHER,
			Names::COMMAND_SPAWNPOINT_SELF,
			Names::COMMAND_STATUS,
			Names::COMMAND_STOP,
			Names::COMMAND_TELEPORT_OTHER,
			Names::COMMAND_TELEPORT_SELF,
			Names::COMMAND_TIME_ADD,
			Names::COMMAND_TIME_QUERY,
			Names::COMMAND_TIME_SET,
			Names::COMMAND_TIME_START,
			Names::COMMAND_TIME_STOP,
			Names::COMMAND_TIMINGS,
			Names::COMMAND_TITLE_OTHER,
			Names::COMMAND_TITLE_SELF,
			Names::COMMAND_TRANSFERSERVER,
			Names::COMMAND_UNBAN_IP,
			Names::COMMAND_UNBAN_PLAYER,
			Names::COMMAND_WHITELIST_ADD,
			Names::COMMAND_WHITELIST_DISABLE,
			Names::COMMAND_WHITELIST_ENABLE,
			Names::COMMAND_WHITELIST_LIST,
			Names::COMMAND_WHITELIST_RELOAD,
			Names::COMMAND_WHITELIST_REMOVE,
			Names::COMMAND_XP_OTHER,
			Names::COMMAND_XP_SELF,
		] as $permission){
			self::registerNoArgsDesc($permission, [$operatorRoot]);
		}

		foreach([
			Names::COMMAND_KILL_SELF,
			Names::COMMAND_ME,
			Names::COMMAND_HELP,
			Names::BROADCAST_USER,
			Names::COMMAND_CLEAR_SELF,
			Names::COMMAND_TELL,
			Names::COMMAND_VERSION,
		] as $permission){
			self::registerNoArgsDesc($permission, [$everyoneRoot]);
		}
	}
}
