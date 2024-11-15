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

use pocketmine\lang\KnownTranslationFactory as l10n;
use pocketmine\permission\DefaultPermissionNames as Names;

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

	public static function registerCorePermissions() : void{
		$consoleRoot = self::registerPermission(new Permission(self::ROOT_CONSOLE, l10n::pocketmine_permission_group_console()));
		$operatorRoot = self::registerPermission(new Permission(self::ROOT_OPERATOR, l10n::pocketmine_permission_group_operator()), [$consoleRoot]);
		$everyoneRoot = self::registerPermission(new Permission(self::ROOT_USER, l10n::pocketmine_permission_group_user()), [$operatorRoot]);

		self::registerPermission(new Permission(Names::BROADCAST_ADMIN, l10n::pocketmine_permission_broadcast_admin()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::BROADCAST_USER, l10n::pocketmine_permission_broadcast_user()), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_BAN_IP, l10n::pocketmine_permission_command_ban_ip()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_BAN_LIST, l10n::pocketmine_permission_command_ban_list()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_BAN_PLAYER, l10n::pocketmine_permission_command_ban_player()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_CLEAR_OTHER, l10n::pocketmine_permission_command_clear_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_CLEAR_SELF, l10n::pocketmine_permission_command_clear_self()), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_DEFAULTGAMEMODE, l10n::pocketmine_permission_command_defaultgamemode()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_DIFFICULTY, l10n::pocketmine_permission_command_difficulty()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_DUMPMEMORY, l10n::pocketmine_permission_command_dumpmemory()), [$consoleRoot]);
		self::registerPermission(new Permission(Names::COMMAND_EFFECT_OTHER, l10n::pocketmine_permission_command_effect_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_EFFECT_SELF, l10n::pocketmine_permission_command_effect_self()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_ENCHANT_OTHER, l10n::pocketmine_permission_command_enchant_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_ENCHANT_SELF, l10n::pocketmine_permission_command_enchant_self()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_GAMEMODE_OTHER, l10n::pocketmine_permission_command_gamemode_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_GAMEMODE_SELF, l10n::pocketmine_permission_command_gamemode_self()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_GC, l10n::pocketmine_permission_command_gc()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_GIVE_OTHER, l10n::pocketmine_permission_command_give_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_GIVE_SELF, l10n::pocketmine_permission_command_give_self()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_HELP, l10n::pocketmine_permission_command_help()), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_KICK, l10n::pocketmine_permission_command_kick()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_KILL_OTHER, l10n::pocketmine_permission_command_kill_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_KILL_SELF, l10n::pocketmine_permission_command_kill_self()), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_LIST, l10n::pocketmine_permission_command_list()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_ME, l10n::pocketmine_permission_command_me()), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_OP_GIVE, l10n::pocketmine_permission_command_op_give()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_OP_TAKE, l10n::pocketmine_permission_command_op_take()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_PARTICLE, l10n::pocketmine_permission_command_particle()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_PLUGINS, l10n::pocketmine_permission_command_plugins()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SAVE_DISABLE, l10n::pocketmine_permission_command_save_disable()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SAVE_ENABLE, l10n::pocketmine_permission_command_save_enable()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SAVE_PERFORM, l10n::pocketmine_permission_command_save_perform()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SAY, l10n::pocketmine_permission_command_say()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SEED, l10n::pocketmine_permission_command_seed()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SETWORLDSPAWN, l10n::pocketmine_permission_command_setworldspawn()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SPAWNPOINT_OTHER, l10n::pocketmine_permission_command_spawnpoint_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SPAWNPOINT_SELF, l10n::pocketmine_permission_command_spawnpoint_self()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_STATUS, l10n::pocketmine_permission_command_status()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_STOP, l10n::pocketmine_permission_command_stop()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TELEPORT_OTHER, l10n::pocketmine_permission_command_teleport_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TELEPORT_SELF, l10n::pocketmine_permission_command_teleport_self()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TELL, l10n::pocketmine_permission_command_tell()), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_ADD, l10n::pocketmine_permission_command_time_add()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_QUERY, l10n::pocketmine_permission_command_time_query()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_SET, l10n::pocketmine_permission_command_time_set()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_START, l10n::pocketmine_permission_command_time_start()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_STOP, l10n::pocketmine_permission_command_time_stop()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIMINGS, l10n::pocketmine_permission_command_timings()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TITLE_OTHER, l10n::pocketmine_permission_command_title_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TITLE_SELF, l10n::pocketmine_permission_command_title_self()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TRANSFERSERVER, l10n::pocketmine_permission_command_transferserver()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_UNBAN_IP, l10n::pocketmine_permission_command_unban_ip()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_UNBAN_PLAYER, l10n::pocketmine_permission_command_unban_player()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_VERSION, l10n::pocketmine_permission_command_version()), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_ADD, l10n::pocketmine_permission_command_whitelist_add()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_DISABLE, l10n::pocketmine_permission_command_whitelist_disable()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_ENABLE, l10n::pocketmine_permission_command_whitelist_enable()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_LIST, l10n::pocketmine_permission_command_whitelist_list()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_RELOAD, l10n::pocketmine_permission_command_whitelist_reload()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_REMOVE, l10n::pocketmine_permission_command_whitelist_remove()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_XP_OTHER, l10n::pocketmine_permission_command_xp_other()), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_XP_SELF, l10n::pocketmine_permission_command_xp_self()), [$operatorRoot]);
	}
}
