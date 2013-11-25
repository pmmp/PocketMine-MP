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
class PermissionsAPI{
	public function __construct(){

	}

	public function __destruct(){

	}

	public function init()
	{
		ServerAPI::request()->api->addHandler("player.connect", function ($player)//Use a better event than player.connect. Player.create maybe?
		{
			$newPermission = ServerAPI::request()->api->dhandle("permissions.request", array('player' => $player));
			if($newPermission instanceof Permission){
				$player->permission = $newPermission;//This is a class with functions in it. So now plugins can call $player->permissions->isGranted().
			}else{
				//TODO: Give out default permission. Fall back to OP system maybe? Checking for a permissions receiver would be nice.
				$player->permission = new DefaultPermission();
			}
		}, 1);//Experimental. Use Closure / Anonymous Functions to assign new functions from each API rather than hard-coding them to Player.php.

        ServerAPI::request()->api->addHandler("player.connect", function ($player)
            {

            }, 1);
	}
}

/**
 * Class Permission
 *
 * Each Permission object will be given a level in integer, and it will be this permission object that will be assigned to players.
 * Not just an integer variable. Plugins can extend this to have their own Permissions assigned to players.
 */
interface Permission{
	/**
	 * @return integer
	 */
	public function getPermissionLevel();

	/**
	 * @param object $permission Permission to check the user for. Must be a an object implementing Permission class.
	 *
	 * @return boolean Whether the person has permissions or not.
	 */
	public function isGranted($permission);
}

class DefaultPermission implements Permission{//TODO: Remove this in the future for a better system than a default permission.
	/**
	 * @var integer
	 */
	private $permissionLevel = 0;//Highest permission possible.

	/**
	 * @param Permission $permissionToCheckGranted
	 *
	 * @return boolean
	 */
	public function isGranted($permissionToCheckGranted){
		if($this->getPermissionLevel() >= $permissionToCheckGranted->getPermissionLevel()){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @return integer
	 */
	public function getPermissionLevel(){
		return $this->permissionLevel;
	}
}