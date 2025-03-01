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
use pocketmine\utils\RegistryTrait;
use function str_replace;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static Permission BROADCAST_ADMIN()
 * @method static Permission BROADCAST_USER()
 * @method static Permission COMMAND_BAN_IP()
 * @method static Permission COMMAND_BAN_LIST()
 * @method static Permission COMMAND_BAN_PLAYER()
 * @method static Permission COMMAND_CLEAR_OTHER()
 * @method static Permission COMMAND_CLEAR_SELF()
 * @method static Permission COMMAND_DEFAULTGAMEMODE()
 * @method static Permission COMMAND_DIFFICULTY()
 * @method static Permission COMMAND_DUMPMEMORY()
 * @method static Permission COMMAND_EFFECT_OTHER()
 * @method static Permission COMMAND_EFFECT_SELF()
 * @method static Permission COMMAND_ENCHANT_OTHER()
 * @method static Permission COMMAND_ENCHANT_SELF()
 * @method static Permission COMMAND_GAMEMODE_OTHER()
 * @method static Permission COMMAND_GAMEMODE_SELF()
 * @method static Permission COMMAND_GC()
 * @method static Permission COMMAND_GIVE_OTHER()
 * @method static Permission COMMAND_GIVE_SELF()
 * @method static Permission COMMAND_HELP()
 * @method static Permission COMMAND_KICK()
 * @method static Permission COMMAND_KILL_OTHER()
 * @method static Permission COMMAND_KILL_SELF()
 * @method static Permission COMMAND_LIST()
 * @method static Permission COMMAND_ME()
 * @method static Permission COMMAND_OP_GIVE()
 * @method static Permission COMMAND_OP_TAKE()
 * @method static Permission COMMAND_PARTICLE()
 * @method static Permission COMMAND_PLUGINS()
 * @method static Permission COMMAND_SAVE_DISABLE()
 * @method static Permission COMMAND_SAVE_ENABLE()
 * @method static Permission COMMAND_SAVE_PERFORM()
 * @method static Permission COMMAND_SAY()
 * @method static Permission COMMAND_SEED()
 * @method static Permission COMMAND_SETWORLDSPAWN()
 * @method static Permission COMMAND_SPAWNPOINT_OTHER()
 * @method static Permission COMMAND_SPAWNPOINT_SELF()
 * @method static Permission COMMAND_STATUS()
 * @method static Permission COMMAND_STOP()
 * @method static Permission COMMAND_TELEPORT_OTHER()
 * @method static Permission COMMAND_TELEPORT_SELF()
 * @method static Permission COMMAND_TELL()
 * @method static Permission COMMAND_TIME_ADD()
 * @method static Permission COMMAND_TIME_QUERY()
 * @method static Permission COMMAND_TIME_SET()
 * @method static Permission COMMAND_TIME_START()
 * @method static Permission COMMAND_TIME_STOP()
 * @method static Permission COMMAND_TIMINGS()
 * @method static Permission COMMAND_TITLE_OTHER()
 * @method static Permission COMMAND_TITLE_SELF()
 * @method static Permission COMMAND_TRANSFERSERVER()
 * @method static Permission COMMAND_UNBAN_IP()
 * @method static Permission COMMAND_UNBAN_PLAYER()
 * @method static Permission COMMAND_VERSION()
 * @method static Permission COMMAND_WHITELIST_ADD()
 * @method static Permission COMMAND_WHITELIST_DISABLE()
 * @method static Permission COMMAND_WHITELIST_ENABLE()
 * @method static Permission COMMAND_WHITELIST_LIST()
 * @method static Permission COMMAND_WHITELIST_RELOAD()
 * @method static Permission COMMAND_WHITELIST_REMOVE()
 * @method static Permission COMMAND_XP_OTHER()
 * @method static Permission COMMAND_XP_SELF()
 * @method static Permission GROUP_CONSOLE()
 * @method static Permission GROUP_OPERATOR()
 * @method static Permission GROUP_USER()
 */
final class DefaultPermissions{
	use RegistryTrait;

	/**
	 * @return Permission[]
	 * @phpstan-return array<string, Permission>
	 */
	public static function getAll() : array{
		//phpstan doesn't support generic traits yet :(
		/** @var Permission[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	/**
	 * @param Permission[] $grantedBy
	 * @param Permission[] $deniedBy
	 */
	private static function registerPermission(Permission $candidate, array $grantedBy = [], array $deniedBy = []) : Permission{
		foreach($grantedBy as $permission){
			$permission->addChild($candidate, true);
		}
		foreach($deniedBy as $permission){
			$permission->addChild($candidate, false);
		}
		PermissionManager::getInstance()->addPermission($candidate);

		$name = str_replace("pocketmine.", "", $candidate->getName());
		self::_registryRegister(str_replace(".", "_", $name), $candidate);

		return PermissionManager::getInstance()->getPermission($candidate->getName());
	}

	protected static function setup() : void{
		self::registerCorePermissions();
	}

	public static function registerCorePermissions() : void{
		$consoleRoot = self::registerPermission(new Permission("pocketmine.group.console", l10n::pocketmine_permission_group_console()));
		$operatorRoot = self::registerPermission(new Permission("pocketmine.group.operator", l10n::pocketmine_permission_group_operator()), [$consoleRoot]);
		$everyoneRoot = self::registerPermission(new Permission("pocketmine.group.user", l10n::pocketmine_permission_group_user()), [$operatorRoot]);

		self::registerPermission(new Permission("pocketmine.broadcast.admin", l10n::pocketmine_permission_broadcast_admin()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.broadcast.user", l10n::pocketmine_permission_broadcast_user()), [$everyoneRoot]);
		self::registerPermission(new Permission("pocketmine.command.ban.ip", l10n::pocketmine_permission_command_ban_ip()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.ban.list", l10n::pocketmine_permission_command_ban_list()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.ban.player", l10n::pocketmine_permission_command_ban_player()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.clear.other", l10n::pocketmine_permission_command_clear_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.clear.self", l10n::pocketmine_permission_command_clear_self()), [$everyoneRoot]);
		self::registerPermission(new Permission("pocketmine.command.defaultgamemode", l10n::pocketmine_permission_command_defaultgamemode()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.difficulty", l10n::pocketmine_permission_command_difficulty()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.dumpmemory", l10n::pocketmine_permission_command_dumpmemory()), [$consoleRoot]);
		self::registerPermission(new Permission("pocketmine.command.effect.other", l10n::pocketmine_permission_command_effect_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.effect.self", l10n::pocketmine_permission_command_effect_self()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.enchant.other", l10n::pocketmine_permission_command_enchant_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.enchant.self", l10n::pocketmine_permission_command_enchant_self()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.gamemode.other", l10n::pocketmine_permission_command_gamemode_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.gamemode.self", l10n::pocketmine_permission_command_gamemode_self()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.gc", l10n::pocketmine_permission_command_gc()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.give.other", l10n::pocketmine_permission_command_give_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.give.self", l10n::pocketmine_permission_command_give_self()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.help", l10n::pocketmine_permission_command_help()), [$everyoneRoot]);
		self::registerPermission(new Permission("pocketmine.command.kick", l10n::pocketmine_permission_command_kick()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.kill.other", l10n::pocketmine_permission_command_kill_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.kill.self", l10n::pocketmine_permission_command_kill_self()), [$everyoneRoot]);
		self::registerPermission(new Permission("pocketmine.command.list", l10n::pocketmine_permission_command_list()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.me", l10n::pocketmine_permission_command_me()), [$everyoneRoot]);
		self::registerPermission(new Permission("pocketmine.command.op.give", l10n::pocketmine_permission_command_op_give()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.op.take", l10n::pocketmine_permission_command_op_take()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.particle", l10n::pocketmine_permission_command_particle()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.plugins", l10n::pocketmine_permission_command_plugins()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.save.disable", l10n::pocketmine_permission_command_save_disable()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.save.enable", l10n::pocketmine_permission_command_save_enable()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.save.perform", l10n::pocketmine_permission_command_save_perform()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.say", l10n::pocketmine_permission_command_say()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.seed", l10n::pocketmine_permission_command_seed()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.setworldspawn", l10n::pocketmine_permission_command_setworldspawn()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.spawnpoint.other", l10n::pocketmine_permission_command_spawnpoint_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.spawnpoint.self", l10n::pocketmine_permission_command_spawnpoint_self()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.status", l10n::pocketmine_permission_command_status()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.stop", l10n::pocketmine_permission_command_stop()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.teleport.other", l10n::pocketmine_permission_command_teleport_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.teleport.self", l10n::pocketmine_permission_command_teleport_self()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.tell", l10n::pocketmine_permission_command_tell()), [$everyoneRoot]);
		self::registerPermission(new Permission("pocketmine.command.time.add", l10n::pocketmine_permission_command_time_add()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.time.query", l10n::pocketmine_permission_command_time_query()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.time.set", l10n::pocketmine_permission_command_time_set()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.time.start", l10n::pocketmine_permission_command_time_start()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.time.stop", l10n::pocketmine_permission_command_time_stop()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.timings", l10n::pocketmine_permission_command_timings()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.title.other", l10n::pocketmine_permission_command_title_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.title.self", l10n::pocketmine_permission_command_title_self()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.transferserver", l10n::pocketmine_permission_command_transferserver()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.unban.ip", l10n::pocketmine_permission_command_unban_ip()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.unban.player", l10n::pocketmine_permission_command_unban_player()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.version", l10n::pocketmine_permission_command_version()), [$everyoneRoot]);
		self::registerPermission(new Permission("pocketmine.command.whitelist.add", l10n::pocketmine_permission_command_whitelist_add()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.whitelist.disable", l10n::pocketmine_permission_command_whitelist_disable()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.whitelist.enable", l10n::pocketmine_permission_command_whitelist_enable()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.whitelist.list", l10n::pocketmine_permission_command_whitelist_list()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.whitelist.reload", l10n::pocketmine_permission_command_whitelist_reload()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.whitelist.remove", l10n::pocketmine_permission_command_whitelist_remove()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.xp.other", l10n::pocketmine_permission_command_xp_other()), [$operatorRoot]);
		self::registerPermission(new Permission("pocketmine.command.xp.self", l10n::pocketmine_permission_command_xp_self()), [$operatorRoot]);
	}
}
