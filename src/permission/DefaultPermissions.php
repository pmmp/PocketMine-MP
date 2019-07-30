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

	/**
	 * @param Permission $perm
	 * @param Permission $parent
	 *
	 * @return Permission
	 */
	public static function registerPermission(Permission $perm, ?Permission $parent = null) : Permission{
		if($parent instanceof Permission){
			$parent->getChildren()[$perm->getName()] = true;

			return self::registerPermission($perm);
		}
		PermissionManager::getInstance()->addPermission($perm);

		return PermissionManager::getInstance()->getPermission($perm->getName());
	}

	public static function registerCorePermissions(){
		$parent = self::registerPermission(new Permission(self::ROOT, "Allows using all PocketMine commands and utilities"));

		$broadcasts = self::registerPermission(new Permission(self::ROOT . ".broadcast", "Allows the user to receive all broadcast messages"), $parent);
		self::registerPermission(new Permission(self::ROOT . ".broadcast.admin", "Allows the user to receive administrative broadcasts", Permission::DEFAULT_OP), $broadcasts);
		self::registerPermission(new Permission(self::ROOT . ".broadcast.user", "Allows the user to receive user broadcasts", Permission::DEFAULT_TRUE), $broadcasts);
		$broadcasts->recalculatePermissibles();

		$commands = self::registerPermission(new Permission(self::ROOT . ".command", "Allows using all PocketMine commands"), $parent);

		$whitelist = self::registerPermission(new Permission(self::ROOT . ".command.whitelist", "Allows the user to modify the server whitelist", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.add", "Allows the user to add a player to the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.remove", "Allows the user to remove a player from the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.reload", "Allows the user to reload the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.enable", "Allows the user to enable the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.disable", "Allows the user to disable the server whitelist"), $whitelist);
		self::registerPermission(new Permission(self::ROOT . ".command.whitelist.list", "Allows the user to list all players on the server whitelist"), $whitelist);
		$whitelist->recalculatePermissibles();

		$ban = self::registerPermission(new Permission(self::ROOT . ".command.ban", "Allows the user to ban people", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.ban.player", "Allows the user to ban players"), $ban);
		self::registerPermission(new Permission(self::ROOT . ".command.ban.ip", "Allows the user to ban IP addresses"), $ban);
		self::registerPermission(new Permission(self::ROOT . ".command.ban.list", "Allows the user to list banned players"), $ban);
		$ban->recalculatePermissibles();

		$unban = self::registerPermission(new Permission(self::ROOT . ".command.unban", "Allows the user to unban people", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.unban.player", "Allows the user to unban players"), $unban);
		self::registerPermission(new Permission(self::ROOT . ".command.unban.ip", "Allows the user to unban IP addresses"), $unban);
		$unban->recalculatePermissibles();

		$op = self::registerPermission(new Permission(self::ROOT . ".command.op", "Allows the user to change operators", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.op.give", "Allows the user to give a player operator status"), $op);
		self::registerPermission(new Permission(self::ROOT . ".command.op.take", "Allows the user to take a player's operator status"), $op);
		$op->recalculatePermissibles();

		$save = self::registerPermission(new Permission(self::ROOT . ".command.save", "Allows the user to save the worlds", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.save.enable", "Allows the user to enable automatic saving"), $save);
		self::registerPermission(new Permission(self::ROOT . ".command.save.disable", "Allows the user to disable automatic saving"), $save);
		self::registerPermission(new Permission(self::ROOT . ".command.save.perform", "Allows the user to perform a manual save"), $save);
		$save->recalculatePermissibles();

		$time = self::registerPermission(new Permission(self::ROOT . ".command.time", "Allows the user to alter the time", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.time.add", "Allows the user to fast-forward time"), $time);
		self::registerPermission(new Permission(self::ROOT . ".command.time.set", "Allows the user to change the time"), $time);
		self::registerPermission(new Permission(self::ROOT . ".command.time.start", "Allows the user to restart the time"), $time);
		self::registerPermission(new Permission(self::ROOT . ".command.time.stop", "Allows the user to stop the time"), $time);
		self::registerPermission(new Permission(self::ROOT . ".command.time.query", "Allows the user query the time"), $time);
		$time->recalculatePermissibles();

		$kill = self::registerPermission(new Permission(self::ROOT . ".command.kill", "Allows the user to kill players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.kill.self", "Allows the user to commit suicide", Permission::DEFAULT_TRUE), $kill);
		self::registerPermission(new Permission(self::ROOT . ".command.kill.other", "Allows the user to kill other players"), $kill);
		$kill->recalculatePermissibles();

		self::registerPermission(new Permission(self::ROOT . ".command.me", "Allows the user to perform a chat action", Permission::DEFAULT_TRUE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.tell", "Allows the user to privately message another player", Permission::DEFAULT_TRUE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.say", "Allows the user to talk as the console", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.give", "Allows the user to give items to players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.effect", "Allows the user to give/take potion effects", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.enchant", "Allows the user to enchant items", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.particle", "Allows the user to create particle effects", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.teleport", "Allows the user to teleport players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.kick", "Allows the user to kick players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.stop", "Allows the user to stop the server", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.list", "Allows the user to list all online players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.help", "Allows the user to view the help menu", Permission::DEFAULT_TRUE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.plugins", "Allows the user to view the list of plugins", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.version", "Allows the user to view the version of the server", Permission::DEFAULT_TRUE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.gamemode", "Allows the user to change the gamemode of players", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.defaultgamemode", "Allows the user to change the default gamemode", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.seed", "Allows the user to view the seed of the world", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.status", "Allows the user to view the server performance", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.gc", "Allows the user to fire garbage collection tasks", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.dumpmemory", "Allows the user to dump memory contents", Permission::DEFAULT_FALSE), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.timings", "Allows the user to records timings for all plugin events", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.spawnpoint", "Allows the user to change player's spawnpoint", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.setworldspawn", "Allows the user to change the world spawn", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.transferserver", "Allows the user to transfer self to another server", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.title", "Allows the user to send a title to the specified player", Permission::DEFAULT_OP), $commands);
		self::registerPermission(new Permission(self::ROOT . ".command.difficulty", "Allows the user to change the game difficulty", Permission::DEFAULT_OP), $commands);

		$commands->recalculatePermissibles();

		$parent->recalculatePermissibles();
	}
}
