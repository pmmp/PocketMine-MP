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

abstract class DefaultPermissions{
	public const ROOT = "pocketmine";

	public const ROOT_CONSOLE = "pocketmine.group.console";
	public const ROOT_OPERATOR = "pocketmine.group.operator";
	public const ROOT_USER = "pocketmine.group.user";

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
		$consoleRoot = self::registerPermission(new Permission(self::ROOT_CONSOLE, "Grants all console permissions"));
		$operatorRoot = self::registerPermission(new Permission(self::ROOT_OPERATOR, "Grants all operator permissions"), [$consoleRoot]);
		$everyoneRoot = self::registerPermission(new Permission(self::ROOT_USER, "Grants all non-sensitive permissions that everyone gets by default"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".broadcast.admin", "Allows the user to receive administrative broadcasts"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".broadcast.user", "Allows the user to receive user broadcasts"), [$everyoneRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.add", "Allows the user to add a player to the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.remove", "Allows the user to remove a player from the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.reload", "Allows the user to reload the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.enable", "Allows the user to enable the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.disable", "Allows the user to disable the server whitelist"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.list", "Allows the user to list all players on the server whitelist"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.ban.player", "Allows the user to ban players"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.ban.ip", "Allows the user to ban IP addresses"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.ban.list", "Allows the user to list banned players"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.unban.player", "Allows the user to unban players"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.unban.ip", "Allows the user to unban IP addresses"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.op.give", "Allows the user to give a player operator status"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.op.take", "Allows the user to take a player's operator status"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.save.enable", "Allows the user to enable automatic saving"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.save.disable", "Allows the user to disable automatic saving"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.save.perform", "Allows the user to perform a manual save"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.time.add", "Allows the user to fast-forward time"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.time.set", "Allows the user to change the time"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.time.start", "Allows the user to restart the time"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.time.stop", "Allows the user to stop the time"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.time.query", "Allows the user query the time"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.kill.self", "Allows the user to commit suicide"), [$everyoneRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.kill.other", "Allows the user to kill other players"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.clear.self", "Allows the user to clear their own inventory"), [$everyoneRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.clear.other", "Allows the user to clear inventory of other players"), [$operatorRoot]);

		self::registerPermission(new Permission(self::ROOT . ".command.me", "Allows the user to perform a chat action"), [$everyoneRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.tell", "Allows the user to privately message another player"), [$everyoneRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.say", "Allows the user to talk as the console"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.give", "Allows the user to give items to players"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.effect", "Allows the user to give/take potion effects"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.enchant", "Allows the user to enchant items"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.particle", "Allows the user to create particle effects"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.teleport", "Allows the user to teleport players"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.kick", "Allows the user to kick players"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.stop", "Allows the user to stop the server"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.list", "Allows the user to list all online players"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.help", "Allows the user to view the help menu"), [$everyoneRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.plugins", "Allows the user to view the list of plugins"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.version", "Allows the user to view the version of the server"), [$everyoneRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.gamemode", "Allows the user to change the gamemode of players"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.defaultgamemode", "Allows the user to change the default gamemode"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.seed", "Allows the user to view the seed of the world"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.status", "Allows the user to view the server performance"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.gc", "Allows the user to fire garbage collection tasks"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.dumpmemory", "Allows the user to dump memory contents"), [$consoleRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.timings", "Allows the user to records timings for all plugin events"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.spawnpoint", "Allows the user to change player's spawnpoint"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.setworldspawn", "Allows the user to change the world spawn"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.transferserver", "Allows the user to transfer self to another server"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.title", "Allows the user to send a title to the specified player"), [$operatorRoot]);
		self::registerPermission(new Permission(self::ROOT . ".command.difficulty", "Allows the user to change the game difficulty"), [$operatorRoot]);
	}
}
