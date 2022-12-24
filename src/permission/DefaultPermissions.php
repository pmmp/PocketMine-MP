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

	private static function registerDeprecatedPermission(string $name) : Permission{
		$permission = new Permission($name, "Deprecated, kept for backwards compatibility only");
		PermissionManager::getInstance()->addPermission($permission);
		return $permission;
	}

	public static function registerCorePermissions() : void{
		$consoleRoot = self::registerPermission(new Permission(self::ROOT_CONSOLE, "Grants all console permissions"));
		$operatorRoot = self::registerPermission(new Permission(self::ROOT_OPERATOR, "Grants all operator permissions"), [$consoleRoot]);
		$everyoneRoot = self::registerPermission(new Permission(self::ROOT_USER, "Grants all non-sensitive permissions that everyone gets by default"), [$operatorRoot]);

		self::registerPermission(new Permission(Names::BROADCAST_ADMIN, "Allows the user to receive administrative broadcasts"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::BROADCAST_USER, "Allows the user to receive user broadcasts"), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_BAN_IP, "Allows the user to ban IP addresses"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_BAN_LIST, "Allows the user to list banned players"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_BAN_PLAYER, "Allows the user to ban players"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_CLEAR_OTHER, "Allows the user to clear inventory of other players"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_CLEAR_SELF, "Allows the user to clear their own inventory"), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_DEFAULTGAMEMODE, "Allows the user to change the default gamemode"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_DIFFICULTY, "Allows the user to change the game difficulty"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_DUMPMEMORY, "Allows the user to dump memory contents"), [$consoleRoot]);

		$effectRoot = self::registerDeprecatedPermission(Names::COMMAND_EFFECT);
		self::registerPermission(new Permission(Names::COMMAND_EFFECT_OTHER, "Allows the user to modify effects of other players"), [$operatorRoot, $effectRoot]);
		self::registerPermission(new Permission(Names::COMMAND_EFFECT_SELF, "Allows the user to modify their own effects"), [$operatorRoot, $effectRoot]);

		$enchantRoot = self::registerDeprecatedPermission(Names::COMMAND_ENCHANT);
		self::registerPermission(new Permission(Names::COMMAND_ENCHANT_OTHER, "Allows the user to enchant the held items of other players"), [$operatorRoot, $enchantRoot]);
		self::registerPermission(new Permission(Names::COMMAND_ENCHANT_SELF, "Allows the user to enchant their own held item"), [$operatorRoot, $enchantRoot]);

		$gameModeRoot = self::registerDeprecatedPermission(Names::COMMAND_GAMEMODE);
		self::registerPermission(new Permission(Names::COMMAND_GAMEMODE_OTHER, "Allows the user to change the game mode of other players"), [$operatorRoot, $gameModeRoot]);
		self::registerPermission(new Permission(Names::COMMAND_GAMEMODE_SELF, "Allows the user to change their own game mode"), [$operatorRoot, $gameModeRoot]);

		self::registerPermission(new Permission(Names::COMMAND_GC, "Allows the user to fire garbage collection tasks"), [$operatorRoot]);

		$giveRoot = self::registerDeprecatedPermission(Names::COMMAND_GIVE);
		self::registerPermission(new Permission(Names::COMMAND_GIVE_OTHER, "Allows the user to give items to other players"), [$operatorRoot, $giveRoot]);
		self::registerPermission(new Permission(Names::COMMAND_GIVE_SELF, "Allows the user to give items to themselves"), [$operatorRoot, $giveRoot]);

		self::registerPermission(new Permission(Names::COMMAND_HELP, "Allows the user to view the help menu"), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_KICK, "Allows the user to kick players"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_KILL_OTHER, "Allows the user to kill other players"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_KILL_SELF, "Allows the user to commit suicide"), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_LIST, "Allows the user to list all online players"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_ME, "Allows the user to perform a chat action"), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_OP_GIVE, "Allows the user to give a player operator status"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_OP_TAKE, "Allows the user to take a player's operator status"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_PARTICLE, "Allows the user to create particle effects"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_PLUGINS, "Allows the user to view the list of plugins"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SAVE_DISABLE, "Allows the user to disable automatic saving"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SAVE_ENABLE, "Allows the user to enable automatic saving"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SAVE_PERFORM, "Allows the user to perform a manual save"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SAY, "Allows the user to talk as the console"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SEED, "Allows the user to view the seed of the world"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SETWORLDSPAWN, "Allows the user to change the world spawn"), [$operatorRoot]);

		$spawnpointRoot = self::registerDeprecatedPermission(Names::COMMAND_SPAWNPOINT);
		self::registerPermission(new Permission(Names::COMMAND_SPAWNPOINT_OTHER, "Allows the user to change the respawn point of other players"), [$operatorRoot, $spawnpointRoot]);
		self::registerPermission(new Permission(Names::COMMAND_SPAWNPOINT_SELF, "Allows the user to change their own respawn point"), [$operatorRoot, $spawnpointRoot]);

		self::registerPermission(new Permission(Names::COMMAND_STATUS, "Allows the user to view the server performance"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_STOP, "Allows the user to stop the server"), [$operatorRoot]);

		$teleportRoot = self::registerDeprecatedPermission(Names::COMMAND_TELEPORT);
		self::registerPermission(new Permission(Names::COMMAND_TELEPORT_OTHER, "Allows the user to teleport other players"), [$operatorRoot, $teleportRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TELEPORT_SELF, "Allows the user to teleport themselves"), [$operatorRoot, $teleportRoot]);

		self::registerPermission(new Permission(Names::COMMAND_TELL, "Allows the user to privately message another player"), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_ADD, "Allows the user to fast-forward time"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_QUERY, "Allows the user query the time"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_SET, "Allows the user to change the time"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_START, "Allows the user to restart the time"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIME_STOP, "Allows the user to stop the time"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TIMINGS, "Allows the user to record timings to analyse server performance"), [$operatorRoot]);

		$titleRoot = self::registerDeprecatedPermission(Names::COMMAND_TITLE);
		self::registerPermission(new Permission(Names::COMMAND_TITLE_OTHER, "Allows the user to send a title to the specified player"), [$operatorRoot, $titleRoot]);
		self::registerPermission(new Permission(Names::COMMAND_TITLE_SELF, "Allows the user to send a title to themselves"), [$operatorRoot, $titleRoot]);

		self::registerPermission(new Permission(Names::COMMAND_TRANSFERSERVER, "Allows the user to transfer self to another server"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_UNBAN_IP, "Allows the user to unban IP addresses"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_UNBAN_PLAYER, "Allows the user to unban players"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_VERSION, "Allows the user to view the version of the server"), [$everyoneRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_ADD, "Allows the user to add a player to the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_DISABLE, "Allows the user to disable the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_ENABLE, "Allows the user to enable the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_LIST, "Allows the user to list all players on the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_RELOAD, "Allows the user to reload the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(Names::COMMAND_WHITELIST_REMOVE, "Allows the user to remove a player from the server whitelist"), [$operatorRoot]);
	}
}
