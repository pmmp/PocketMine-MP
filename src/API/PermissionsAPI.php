<?php

/**
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

/**
 * Class PermissionAPI
 *
 * An API that provides an interface for plugins to extend, or statically call.
 *
 * <p>The whole point of this permissions API is to provide developers with an expendable API
 * that allows fine tuning of each and every specific part of PocketMine.
 *
 * Thus, it should be as abstract as possible.
 *
 * Roles and levels - Administrator / Moderator etc will be handled by plugins.
 * Permissions loading and file formats etc will be handled by separate plugins.
 *
 * Permission checking on events will be handled by this API
 * Add enable-op argument to allow disabling OP systems. OP system should override this permission system, and this API
 * Will determine whether that option is set or not.</p>
 */
class PermissionsAPI
{
	function __construct()
	{

	}

	function __destruct()
	{

	}

	public function init()
	{

	}

	/**
	 * @param string $username Username of the user to check permissions
	 * @param integer $permission Permission to check the user for.
	 *
	 * @return boolean Whether the person has permissions or not.
	 */
	public static function isPermissionGranted($username, $permission)
	{

	}

	/**
	 * @param Player $player Username of the user to check permissions
	 * @param integer $permission Permission to check the user for.
	 *
	 * @return boolean True ons set. False on failure.
	 */
	public static function setPermission($player, $permission)
	{
		if(!isset($player->permissions) or !($player->permissions instanceof stdClass))
		{
			$player->permission = new stdClass();
		}//Check if the permission variable is set, and initialise it if not.


	}
}

/**
 * Class Permission
 *
 * Each Permission object will be given a level in integer, and it will be this permission object that will be assigned to players.
 * Not just an integer variable.
 *
 * Plugins can extend this to have their own Permissions assigned to players.
 */
abstract class Permission
{
	private $permissionLevel;

	public abstract function getPermissionLevel();
}